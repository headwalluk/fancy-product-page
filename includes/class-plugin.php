<?php

/**
 * Main plugin orchestrator.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

/**
 * Top-level plugin class.
 *
 * Registers every WordPress/WooCommerce hook the plugin uses (in {@see run()})
 * and implements the permalink-override, SEO structured-data, and add-to-cart
 * behaviour. Owns the lazily-constructed Settings and Admin_Hooks instances.
 */
class Plugin extends Component
{
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
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->page_product_id = 0;
    }

    /**
     * Register all WordPress/WooCommerce hooks.
     *
     * @return void
     */
    public function run()
    {
        $this->settings = new Settings($this->name, $this->version);

        add_filter('post_type_link', [$this, 'override_product_permalink'], 10, 4);

        // add_action('template_redirect', [$this, 'maybe_redirect_to_fancy_page']);

        add_action('admin_init', [$this, 'admin_init']);
        add_action('wp', [$this, 'late_init']);

        if (CONVERT_ADD_TO_CART_SKU_TO_ID) {
            add_action('wp_loaded', [$this, 'add_to_cart_action'], 19);
        }

        add_filter('woocommerce_structured_data_type_for_page', [$this, 'structured_data_types_for_page'], 10, 1);
    }

    /**
     * Admin bootstrap: wire admin asset hooks, register the meta box, and
     * handle a settings save.
     *
     * @return void
     */
    public function admin_init()
    {
        $admin_hooks = $this->get_admin_hooks();

        add_action('admin_enqueue_scripts', [$admin_hooks, 'admin_enqueue_scripts'], 10, 1);

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
    public function add_to_cart_action($url)
    {
        $sku = null;

        if (!array_key_exists('add-to-cart', $_REQUEST)) {
            // ...
        } elseif (is_numeric($sku = wp_unslash($_REQUEST['add-to-cart'])) || empty($sku)) {
            // ...
        } elseif (!function_exists('wc_get_product_id_by_sku')) {
            // ...
        } elseif (($product_id = wc_get_product_id_by_sku($sku)) <= 0) {
            // ...
        } else {
            $_REQUEST['add-to-cart'] = $product_id;
        }
    }

    /**
     * On a fancy product page, generate WooCommerce structured data for the
     * owning product and record its ID for {@see structured_data_types_for_page()}.
     *
     * Hooked to `wp`.
     *
     * @return void
     */
    public function late_init()
    {
        if (!function_exists('WC')) {
            error_log(__FUNCTION__ . ' : Class WC_Structured_Data not found');
        } elseif (!is_page()) {
            // ...
        } else {
            $query = [
                'post_type' => POST_TYPE_PRODUCT,
                'post_status' => 'publish',
                'numberposts' => 1,
                'meta_query' => [
                    [
                        'key' => META_FANCY_PRODUCT_PAGE_ID,
                        'value' => get_the_ID(),
                        'compare' => '='
                    ]
                ]
            ];

            $posts = get_posts($query);
            if (is_array($posts) && count($posts) > 0) {
                $post = $posts[0];
                $product_id = $post->ID;
                $product = wc_get_product($product_id);

                WC()->structured_data->generate_product_data($product);
                $this->page_product_id = $product_id;
            }
        }
    }

    /**
     * Get the settings controller.
     *
     * @return Settings
     */
    public function get_settings_controller()
    {
        return $this->settings;
    }

    /**
     * Get the admin hooks component, instantiating it on first use.
     *
     * @return Admin_Hooks
     */
    public function get_admin_hooks()
    {
        if (is_null($this->admin_hooks)) {
            $this->admin_hooks = new Admin_Hooks($this->name, $this->version);
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
    public function structured_data_types_for_page($types)
    {
        if ($this->page_product_id > 0) {
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
    public function get_product_fancy_page_permalink(int $product_id = 0)
    {
        $permalink = null;

        if (empty(($post_id = get_fancy_product_page_id($product_id)))) {
            // ...
        } elseif (empty(($fancy_product_page_permalink = get_the_permalink($post_id)))) {
            // ...
        } else {
            $permalink = $fancy_product_page_permalink;
        }

        /*
        if (!function_exists('WC')) {
            // ...
        } elseif ($product_id <= 0) {
            // Invalid product id
        } elseif (get_post_type($product_id) != 'product') {
            // ...
        } elseif (($fancy_product_page_id = intval(get_post_meta($product_id, META_FANCY_PRODUCT_PAGE_ID, true))) < 0) {
            // ...
        } elseif (!in_array(get_post_type($fancy_product_page_id), ['page', 'post'])) {
            // ...
        } elseif (empty(($fancy_product_page_permalink = get_the_permalink($fancy_product_page_id)))) {
            // ...
            // } elseif (empty(($fancy_product_page_permalink = get_page_link($fancy_product_page_id)))) {
        } else {
            $permalink = $fancy_product_page_permalink;
        }
        */

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
    public function override_product_permalink($post_link, $post, $leavename, $sample)
    {
        if (!empty(($fancy_product_page_permalink = $this->get_product_fancy_page_permalink($post->ID)))) {
            $post_link = $fancy_product_page_permalink;
        }

        return $post_link;
    }

    /**
     * Redirect the current request to the product's fancy page.
     *
     * Not hooked by default; available for opt-in `template_redirect` use.
     *
     * @return void
     */
    public function maybe_redirect_to_fancy_page()
    {
        if (!empty(($fancy_product_page_permalink = $this->get_product_fancy_page_permalink()))) {
            wp_redirect($fancy_product_page_permalink, REDIRECT_HTTP_CODE);
            die();
        }
    }

    /**
     * Output WooCommerce structured data for the recorded product IDs.
     *
     * @return void
     */
    public function output_structured_data()
    {
        if (empty($this->rendered_product_ids)) {
            // ...
        } elseif (!class_exists('WC_Structured_Data')) {
            error_log(__FUNCTION__ . ' : Class WC_Structured_Data not found');
        } else {
            error_log('Output structured data: ' . json_encode($this->rendered_product_ids));

            $structured_data = new \WC_Structured_Data();
            foreach ($this->rendered_product_ids as $product_id) {
                $structured_data->generate_product_data();
            }
        }
    }
}
