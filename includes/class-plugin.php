<?php
/**
 * Main plugin orchestrator.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined( 'WPINC' ) || die();

/**
 * Top-level plugin class.
 *
 * Registers every WordPress/WooCommerce hook the plugin uses (in {@see run()})
 * and implements the permalink-override, SEO structured-data, and add-to-cart
 * behaviour. Owns the lazily-constructed Settings and Admin_Hooks instances.
 */
class Plugin extends Component {

	/**
	 * Product ID resolved for the current fancy page, or 0 if none.
	 *
	 * @var int
	 */
	private $page_product_id;

	/**
	 * The settings controller.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * The admin hooks component (lazily instantiated).
	 *
	 * @var Admin_Hooks
	 */
	private $admin_hooks;

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin name/slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( $name, $version ) {
		parent::__construct( $name, $version );

		$this->page_product_id = 0;
	}

	/**
	 * Register all WordPress/WooCommerce hooks.
	 *
	 * @return void
	 */
	public function run() {
		$this->settings = new Settings( $this->name, $this->version );

		add_action( 'init', array( $this, 'load_textdomain' ) );

		add_filter( 'post_type_link', array( $this, 'override_product_permalink' ), 10, 4 );

		add_action( 'template_redirect', array( $this, 'maybe_redirect_to_fancy_page' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp', array( $this, 'late_init' ) );

		if ( CONVERT_ADD_TO_CART_SKU_TO_ID ) {
			add_action( 'wp_loaded', array( $this, 'add_to_cart_action' ), 19 );
		}

		add_filter( 'woocommerce_structured_data_type_for_page', array( $this, 'structured_data_types_for_page' ), 10, 1 );
	}

	/**
	 * Load the plugin's translations from the bundled `languages/` directory.
	 *
	 * Hooked to `init` (not earlier) per WordPress 6.7+ guidance on just-in-time
	 * text-domain loading.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'fancy-product-page', false, dirname( plugin_basename( PP_FPP_FILE ) ) . '/languages' );
	}

	/**
	 * Admin bootstrap: wire admin asset hooks, register the meta box, and
	 * handle a settings save.
	 *
	 * @return void
	 */
	public function admin_init() {
		$admin_hooks = $this->get_admin_hooks();

		add_action( 'admin_enqueue_scripts', array( $admin_hooks, 'admin_enqueue_scripts' ), 10, 1 );

		new Product_Meta_Box();

		$this->settings->maybe_save_settings();
	}

	/**
	 * Convert a non-numeric `add-to-cart` request value (a SKU) to a product ID.
	 *
	 * Runs on `wp_loaded` before WooCommerce processes the request, mutating
	 * `$_REQUEST['add-to-cart']` in place when a SKU resolves to a product.
	 *
	 * @param mixed $url Unused; passed by the `wp_loaded` action.
	 *
	 * @return void
	 */
	public function add_to_cart_action( $url ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WooCommerce's add-to-cart links carry no nonce; WooCommerce itself handles the request.
		$sku = isset( $_REQUEST['add-to-cart'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['add-to-cart'] ) ) : '';

		if ( '' === $sku || is_numeric( $sku ) ) {
			// No add-to-cart request, or already a product ID.
		} elseif ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			// WooCommerce is not active.
		} else {
			$product_id = (int) wc_get_product_id_by_sku( $sku );

			if ( $product_id > 0 ) {
				$_REQUEST['add-to-cart'] = $product_id;
			}
		}
	}

	/**
	 * On a fancy product page, generate WooCommerce structured data for the
	 * owning product and record its ID for {@see structured_data_types_for_page()}.
	 *
	 * Any post type returned by {@see get_fancy_page_post_types()} counts — a
	 * fancy page does not have to be a `page`.
	 *
	 * Does nothing when the product has opted out via
	 * {@see META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA}; `$page_product_id` then
	 * stays at 0, so `product` is never added to the page's structured-data
	 * types either.
	 *
	 * Hooked to `wp`.
	 *
	 * @return void
	 */
	public function late_init() {
		if ( ! function_exists( 'WC' ) ) {
			error_log( 'Fancy_Product_Page ' . __METHOD__ . ' [error]: WooCommerce is not active; product structured data not generated.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Records a missing hard dependency.
		} elseif ( ! is_singular( get_fancy_page_post_types() ) ) {
			// Not a page that can be a fancy product page.
		} else {
			$query = array(
				'post_type'   => POST_TYPE_PRODUCT,
				'post_status' => 'publish',
				'numberposts' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- The product is found by its fancy-page meta; one row, singular pages only.
				'meta_query'  => array(
					array(
						'key'     => META_FANCY_PRODUCT_PAGE_ID,
						'value'   => get_the_ID(),
						'compare' => '=',
					),
				),
			);

			$posts = get_posts( $query );
			if ( is_array( $posts ) && count( $posts ) > 0 ) {
				$post       = $posts[0];
				$product_id = $post->ID;

				$product = is_fancy_page_product_schema_suppressed( $product_id ) ? null : wc_get_product( $product_id );

				if ( $product instanceof \WC_Product ) {
					WC()->structured_data->generate_product_data( $product );
					$this->page_product_id = $product_id;
				}
			}
		}
	}

	/**
	 * Get the settings controller.
	 *
	 * @return Settings
	 */
	public function get_settings_controller() {
		return $this->settings;
	}

	/**
	 * Get the admin hooks component, instantiating it on first use.
	 *
	 * @return Admin_Hooks
	 */
	public function get_admin_hooks() {
		if ( is_null( $this->admin_hooks ) ) {
			$this->admin_hooks = new Admin_Hooks( $this->name, $this->version );
		}

		return $this->admin_hooks;
	}

	/**
	 * Add `product` to the structured-data types for the current page when it
	 * is a fancy product page.
	 *
	 * Filter callback for `woocommerce_structured_data_type_for_page`.
	 *
	 * @param array $types Structured-data type slugs.
	 *
	 * @return array
	 */
	public function structured_data_types_for_page( $types ) {
		if ( $this->page_product_id > 0 ) {
			$types[] = POST_TYPE_PRODUCT;
		}

		return $types;
	}

	/**
	 * Get the permalink of the fancy page associated with a product.
	 *
	 * @param int $product_id WooCommerce product ID. Defaults to the current product.
	 *
	 * @return string|null The fancy page permalink, or null if none.
	 */
	public function get_product_fancy_page_permalink( int $product_id = 0 ) {
		$permalink = null;
		$post_id   = get_fancy_product_page_id( $product_id );

		if ( $post_id > 0 ) {
			$fancy_product_page_permalink = get_the_permalink( $post_id );

			if ( is_string( $fancy_product_page_permalink ) && '' !== $fancy_product_page_permalink ) {
				$permalink = $fancy_product_page_permalink;
			}
		}

		return $permalink;
	}

	/**
	 * Rewrite a product's permalink to its fancy page, if one is set.
	 *
	 * Filter callback for `post_type_link`.
	 *
	 * @param string   $post_link The post's permalink.
	 * @param \WP_Post $post      The post object.
	 * @param bool     $leavename Whether to keep the post name.
	 * @param bool     $sample    Whether this is a sample permalink.
	 *
	 * @return string
	 */
	public function override_product_permalink( $post_link, $post, $leavename, $sample ) {
		$fancy_product_page_permalink = $post instanceof \WP_Post ? $this->get_product_fancy_page_permalink( $post->ID ) : null;

		if ( null !== $fancy_product_page_permalink ) {
			$post_link = $fancy_product_page_permalink;
		}

		return $post_link;
	}

	/**
	 * Redirect the current request to the product's fancy page.
	 *
	 * Hooked on `template_redirect`: when the canonical `/product/...` URL is
	 * requested and the product has an associated fancy page, 301 to that page
	 * so links, visitors and search engines resolve to the fancy page.
	 *
	 * @return void
	 */
	public function maybe_redirect_to_fancy_page() {
		$fancy_product_page_permalink = $this->get_product_fancy_page_permalink();

		if ( null !== $fancy_product_page_permalink ) {
			// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Target is this site's own permalink; wp_safe_redirect() would break domain-mapped sites.
			wp_redirect( $fancy_product_page_permalink, REDIRECT_HTTP_CODE );
			exit;
		}
	}

	/**
	 * Output WooCommerce structured data for the recorded product IDs.
	 *
	 * @deprecated 1.3.0 Never hooked, and never worked. Structured data is
	 *                   generated in {@see late_init()} instead. Scheduled for
	 *                   removal in a future release.
	 *
	 * @return void
	 */
	public function output_structured_data() {
		_deprecated_function( __METHOD__, '1.3.0', __CLASS__ . '::late_init()' );
	}
}
