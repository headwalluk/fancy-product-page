<?php
/**
 * Main Plugin Class
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

namespace Fancy_Product_Page;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Registers the hooks and implements the permalink, redirect, structured-data
 * and add-to-cart behaviour.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @since 1.5.0
	 * @var ?Plugin
	 */
	private static $instance = null;

	/**
	 * Product whose structured data was generated for the current fancy page, or 0.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private int $page_product_id = 0;

	/**
	 * Admin asset hooks, created on admin_init.
	 *
	 * @since 1.0.0
	 * @var ?Admin_Hooks
	 */
	private ?Admin_Hooks $admin_hooks = null;

	/**
	 * Get single instance of the class.
	 *
	 * @since 1.5.0
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register all WordPress and WooCommerce hooks.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_woocommerce_compatibility' ) );

		add_filter( 'post_type_link', array( $this, 'override_product_permalink' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'maybe_redirect_to_fancy_page' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp', array( $this, 'late_init' ) );

		if ( CONVERT_ADD_TO_CART_SKU_TO_ID ) {
			add_action( 'wp_loaded', array( $this, 'add_to_cart_action' ), 19 );
		}

		add_filter( 'woocommerce_structured_data_type_for_page', array( $this, 'structured_data_types_for_page' ), 10, 1 );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * Needed for the translations shipped in the plugin's own languages/ directory.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'fancy-product-page', false, dirname( FANCY_PRODUCT_PAGE_BASENAME ) . '/languages' );
	}

	/**
	 * Declare HPOS and Checkout block compatibility. The plugin reads neither orders nor the checkout.
	 *
	 * @since 1.5.0
	 */
	public function declare_woocommerce_compatibility(): void {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', FANCY_PRODUCT_PAGE_FILE, true );
			FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', FANCY_PRODUCT_PAGE_FILE, true );
		}
	}

	/**
	 * Register the admin stylesheet hook and the product meta box.
	 *
	 * @since 1.0.0
	 */
	public function admin_init(): void {
		$this->admin_hooks = new Admin_Hooks();
		add_action( 'admin_enqueue_scripts', array( $this->admin_hooks, 'admin_enqueue_scripts' ), 10, 1 );

		new Product_Meta_Box();
	}

	/**
	 * Convert a non-numeric `add-to-cart` request value (a SKU) to a product ID.
	 *
	 * Runs on wp_loaded at priority 19, before WooCommerce's own handler at 20,
	 * and rewrites $_REQUEST['add-to-cart'] in place when the SKU matches a product.
	 *
	 * @since 1.0.0
	 */
	public function add_to_cart_action(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WooCommerce's add-to-cart links carry no nonce; WooCommerce itself handles the request.
		$sku = isset( $_REQUEST['add-to-cart'] ) && is_string( $_REQUEST['add-to-cart'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['add-to-cart'] ) ) : '';

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
	 * On a fancy page, generate WooCommerce structured data for its product.
	 *
	 * Records the product for structured_data_types_for_page(). Does nothing when
	 * the product has opted out of the schema.
	 *
	 * @since 1.0.0
	 */
	public function late_init(): void {
		if ( ! is_woocommerce_available() ) {
			// WooCommerce is not active; Requires Plugins normally prevents this.
		} elseif ( ! is_singular( get_fancy_page_post_types() ) ) {
			// Not a page that can be a fancy product page.
		} else {
			$product_ids = get_posts(
				array(
					'post_type'   => POST_TYPE_PRODUCT,
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- The product is found by its fancy-page meta; one row, singular pages only.
					'meta_query'  => array(
						array(
							'key'     => META_FANCY_PRODUCT_PAGE_ID,
							'value'   => get_the_ID(),
							'compare' => '=',
						),
					),
				)
			);

			$product_id = empty( $product_ids ) ? 0 : (int) $product_ids[0];
			$product    = ( $product_id > 0 && ! is_fancy_page_product_schema_suppressed( $product_id ) ) ? wc_get_product( $product_id ) : null;

			if ( $product instanceof \WC_Product && isset( WC()->structured_data ) ) {
				WC()->structured_data->generate_product_data( $product );
				$this->page_product_id = $product_id;
			}
		}
	}

	/**
	 * Add `product` to the structured-data types when the page is a fancy product page.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $types Structured-data type slugs.
	 * @return mixed The types, with `product` added when it applies.
	 */
	public function structured_data_types_for_page( mixed $types ): mixed {
		if ( $this->page_product_id > 0 && is_array( $types ) ) {
			$types[] = POST_TYPE_PRODUCT;
		}

		return $types;
	}

	/**
	 * Get the permalink of the fancy page associated with a product.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id WooCommerce product ID. Defaults to the current product.
	 * @return ?string The fancy page permalink, or null if none.
	 */
	public function get_product_fancy_page_permalink( int $product_id = 0 ): ?string {
		$permalink = null;
		$post_id   = get_fancy_product_page_id( $product_id );

		if ( $post_id > 0 ) {
			$fancy_product_page_permalink = get_permalink( $post_id );

			if ( is_string( $fancy_product_page_permalink ) && '' !== $fancy_product_page_permalink ) {
				$permalink = $fancy_product_page_permalink;
			}
		}

		return $permalink;
	}

	/**
	 * Rewrite a product's permalink to its fancy page, if one is set.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post_link The post's permalink.
	 * @param mixed $post      The post object.
	 * @return mixed The fancy page's permalink, or the link unchanged.
	 */
	public function override_product_permalink( mixed $post_link, mixed $post ): mixed {
		$fancy_product_page_permalink = null;

		if ( $post instanceof \WP_Post && POST_TYPE_PRODUCT === $post->post_type ) {
			$fancy_product_page_permalink = $this->get_product_fancy_page_permalink( $post->ID );
		}

		if ( null !== $fancy_product_page_permalink ) {
			$post_link = $fancy_product_page_permalink;
		}

		return $post_link;
	}

	/**
	 * Redirect a request for the product's own URL to its fancy page.
	 *
	 * @since 1.0.0
	 */
	public function maybe_redirect_to_fancy_page(): void {
		$fancy_product_page_permalink = $this->get_product_fancy_page_permalink();

		if ( null !== $fancy_product_page_permalink ) {
			// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Target is this site's own permalink; wp_safe_redirect() would break domain-mapped sites.
			wp_redirect( $fancy_product_page_permalink, REDIRECT_HTTP_CODE );
			exit();
		}
	}

	/**
	 * Output WooCommerce structured data for the recorded product IDs.
	 *
	 * @deprecated 1.3.0 Never hooked, and never worked. Structured data is
	 *                   generated in late_init() instead. Removed in 2.0.0.
	 */
	public function output_structured_data(): void {
		_deprecated_function( __METHOD__, '1.3.0', __CLASS__ . '::late_init()' );
	}
}
