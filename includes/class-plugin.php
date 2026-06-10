<?php

namespace Fancy_Product_Page;

defined('WPINC') || die();

class Plugin extends Component
{
    private $page_product_id;

    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->page_product_id = 0;
    }

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

    public function admin_init()
    {
        $admin_hooks = $this->get_admin_hooks();

        add_action('admin_enqueue_scripts', [$admin_hooks, 'admin_enqueue_scripts'], 10, 1);

        new Product_Meta_Box();

        $this->settings->maybe_save_settings();
    }

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

    private $settings;
    public function get_settings_controller()
    {
        return $this->settings;
    }

    private $admin_hooks;
    public function get_admin_hooks()
    {
        if (is_null($this->admin_hooks)) {
            $this->admin_hooks = new Admin_Hooks($this->name, $this->version);
        }

        return $this->admin_hooks;
    }

    public function structured_data_types_for_page($types)
    {
        if ($this->page_product_id > 0) {
            $types[] = POST_TYPE_PRODUCT;
        }

        return $types;
    }

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

    public function override_product_permalink($post_link, $post, $leavename, $sample)
    {
        if (!empty(($fancy_product_page_permalink = $this->get_product_fancy_page_permalink($post->ID)))) {
            $post_link = $fancy_product_page_permalink;
        }

        return $post_link;
    }

    public function maybe_redirect_to_fancy_page()
    {
        if (!empty(($fancy_product_page_permalink = $this->get_product_fancy_page_permalink()))) {
            wp_redirect($fancy_product_page_permalink, REDIRECT_HTTP_CODE);
            die();
        }
    }

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
