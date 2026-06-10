<?php

/**
 * Product meta box for choosing a fancy product page.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

/**
 * "Fancy Product Page" meta box on the WooCommerce product editor.
 *
 * Lets the administrator select the Page/Post to use as a product's front-end,
 * and persists the choice to the {@see META_FANCY_PRODUCT_PAGE_ID} post meta.
 */
class Product_Meta_Box extends Meta_Box
{
    /**
     * Constructor. Registers the meta box and its save handler.
     */
    public function __construct()
    {
        parent::__construct(POST_TYPE_PRODUCT);

        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post', [$this, 'save'], 10, 2);
    }

    /**
     * Register the meta box with WordPress.
     *
     * Callback for `add_meta_boxes`.
     *
     * @return void
     */
    public function register_meta_box()
    {
        add_meta_box(
            get_class($this), // Unique ID
            __('Fancy Product Page', 'fancy-product-page'),
            [$this, 'render'],
            $this->get_post_types()
        );
    }

    /**
     * Render the meta box contents.
     *
     * @param \WP_Post $post The product post being edited.
     *
     * @return void
     */
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

    /**
     * Persist the selected fancy page ID, or delete the meta when none is set.
     *
     * Callback for `save_post`; guarded by {@see Meta_Box::is_saving_meta_box()}.
     *
     * @param int      $post_id The product ID being saved.
     * @param \WP_Post $post    The product post object.
     *
     * @return void
     */
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
