<?php

namespace Fancy_Product_Page;

defined('WPINC') || die();

class Product_Meta_Box extends Meta_Box
{
    public function __construct()
    {
        parent::__construct(POST_TYPE_PRODUCT);

        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post', [$this, 'save'], 10, 2);
    }

    public function register_meta_box()
    {
        add_meta_box(
            get_class($this), // Unique ID
            __('Fancy Product Page', 'fancy-product-page'),
            [$this, 'render'],
            $this->get_post_types()
        );
    }

    public function render($post)
    {
        echo '<div class="pp-wrap">';

        $this->render_nonce_field();

        $settings = get_settings_controller();

        // $testimonial_controller = get_testimonial_controller();
        // $service_controller = get_service_controller();
        // $testimonial = $testimonial_controller->get_post_object($post->ID);

        include PP_FPP_ADMIN_TEMPLATES_DIR . 'product-meta-box.php';

        echo '</div>';
    }

    public function save($post_id, $post)
    {
        if (!$this->is_saving_meta_box($post_id, $post)) {
            // ...
            // } elseif (empty($testimonial_controller = get_testimonial_controller())) {
            // ...
            // } elseif (empty($testimonial = $testimonial_controller->get_post_object($post->ID))) {
            // ...
        } else {
            if (array_key_exists(META_FANCY_PRODUCT_PAGE_ID, $_POST) && !empty(($page_id = intval($_POST[META_FANCY_PRODUCT_PAGE_ID])))) {
                // error_log('Save meta: ' . $post_id . ' ' . $page_id);
                update_post_meta($post_id, META_FANCY_PRODUCT_PAGE_ID, $page_id);
            } else {
                // error_log('Delete meta: ' . $post_id);
                delete_post_meta($post_id, META_FANCY_PRODUCT_PAGE_ID);
            }
        }
    }
}
