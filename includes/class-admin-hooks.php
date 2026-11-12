<?php
/**
 * Admin Hooks
 *
 * @package Fancy_Product_Page
 * @since 1.0.0
 */

namespace Fancy_Product_Page;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Enqueues the admin stylesheet on the product screens.
 *
 * @since 1.0.0
 */
class Admin_Hooks {

	/**
	 * Enqueue the admin stylesheet on the product add, edit and list screens.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $current_page The current admin page hook suffix.
	 */
	public function admin_enqueue_scripts( mixed $current_page ): void {
		$are_assets_required = false;

		if ( ! current_user_can( 'edit_products' ) ) {
			// The meta box is only shown to users who can edit products.
		} elseif ( in_array( $current_page, array( 'post.php', 'edit.php' ), true ) ) {
			$are_assets_required = POST_TYPE_PRODUCT === get_post_type();
		} elseif ( 'post-new.php' === $current_page ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check; nothing is processed.
			$requested_post_type = isset( $_GET['post_type'] ) && is_string( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
			$are_assets_required = POST_TYPE_PRODUCT === $requested_post_type;
		} else {
			// Not a product screen.
		}

		if ( $are_assets_required ) {
			wp_enqueue_style(
				STYLE_HANDLE_ADMIN,
				FANCY_PRODUCT_PAGE_URL . 'assets/fpp-admin.css',
				array(),
				FANCY_PRODUCT_PAGE_VERSION
			);
		}
	}
}
