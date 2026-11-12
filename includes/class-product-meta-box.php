<?php
/**
 * Product Meta Box
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * The "Fancy Product Page" meta box on the product editor: choose the page, and
 * whether to write product schema to it.
 *
 * @since 1.0.0
 */
class Product_Meta_Box {

	/**
	 * Register the meta box and its save handler.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes_' . POST_TYPE_PRODUCT, array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . POST_TYPE_PRODUCT, array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register the meta box with WordPress.
	 *
	 * The title is the plugin's name, so it is deliberately not translated.
	 *
	 * @since 1.0.0
	 */
	public function register_meta_box(): void {
		add_meta_box(
			META_BOX_ID,
			'Fancy Product Page',
			array( $this, 'render' ),
			POST_TYPE_PRODUCT
		);
	}

	/**
	 * Render the meta box contents.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post The product post being edited.
	 */
	public function render( mixed $post ): void {
		if ( $post instanceof \WP_Post ) {
			echo '<div class="pp-wrap">';
			wp_nonce_field( NONCE_META_BOX_ACTION, NONCE_META_BOX_FIELD );
			include FANCY_PRODUCT_PAGE_PATH . 'views/product-meta-box.php';
			echo '</div>';
		}
	}

	/**
	 * Save the chosen fancy page and the schema setting.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post_id The product ID being saved.
	 * @param mixed $post    The product post object.
	 */
	public function save( mixed $post_id, mixed $post ): void {
		if ( $this->is_saving_meta_box( $post_id, $post ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by is_saving_meta_box() above.
			$page_id = isset( $_POST[ META_FANCY_PRODUCT_PAGE_ID ] ) && is_numeric( $_POST[ META_FANCY_PRODUCT_PAGE_ID ] ) ? (int) $_POST[ META_FANCY_PRODUCT_PAGE_ID ] : 0;

			if ( $page_id > 0 ) {
				update_post_meta( (int) $post_id, META_FANCY_PRODUCT_PAGE_ID, $page_id );
			} else {
				delete_post_meta( (int) $post_id, META_FANCY_PRODUCT_PAGE_ID );
			}

			// An unticked checkbox posts nothing, so the meta records suppression only when the field is absent.
			if ( array_key_exists( FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by is_saving_meta_box() above.
				delete_post_meta( (int) $post_id, META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA );
			} else {
				update_post_meta( (int) $post_id, META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA, 'yes' );
			}
		}
	}

	/**
	 * Check that this save came from the meta box: nonce, post type, capability, not an autosave or revision.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post_id The post ID being saved.
	 * @param mixed $post    The post object.
	 * @return bool True when the meta box's fields should be saved.
	 */
	public function is_saving_meta_box( mixed $post_id, mixed $post ): bool {
		$is_saving = false;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised on the same line.
		$nonce = isset( $_POST[ NONCE_META_BOX_FIELD ] ) && is_string( $_POST[ NONCE_META_BOX_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ NONCE_META_BOX_FIELD ] ) ) : '';

		if ( '' === $nonce ) {
			// This meta box was not submitted.
		} elseif ( ! wp_verify_nonce( $nonce, NONCE_META_BOX_ACTION ) ) {
			// Invalid or expired nonce.
		} elseif ( ! is_numeric( $post_id ) || ! $post instanceof \WP_Post || POST_TYPE_PRODUCT !== $post->post_type ) {
			// Not a product save.
		} elseif ( ! current_user_can( 'edit_post', (int) $post_id ) ) {
			// User may not edit this product.
		} elseif ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( (int) $post_id ) || wp_is_post_revision( (int) $post_id ) ) {
			// Autosaves and revisions don't carry meta box fields.
		} else {
			$is_saving = true;
		}

		return $is_saving;
	}
}
