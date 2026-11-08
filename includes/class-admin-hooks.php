<?php
/**
 * Admin asset hooks.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined( 'WPINC' ) || die();

/**
 * Enqueues the plugin's admin assets on the relevant product screens.
 */
class Admin_Hooks extends Component {

	/**
	 * Enqueue the admin stylesheet on the product add/edit/list screens.
	 *
	 * Callback for `admin_enqueue_scripts`.
	 *
	 * @param string $current_page The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $current_page ) {
		$are_assets_required = false;

		$settings = get_settings_controller();

		if ( ! current_user_can( $settings->get_settings_cap() ) ) {
			// Assets are only for users who can manage the plugin.
		} elseif ( in_array( $current_page, array( 'post.php', 'edit.php' ), true ) ) {
			$are_assets_required = POST_TYPE_PRODUCT === get_post_type();
		} elseif ( 'post-new.php' === $current_page ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check; nothing is processed.
			$requested_post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
			$are_assets_required = POST_TYPE_PRODUCT === $requested_post_type;
		} else {
			// Not a product screen.
		}

		if ( $are_assets_required ) {
			wp_enqueue_style(
				$this->name . '-admin',
				PP_FPP_ASSETS_URL . 'fpp-admin.css',
				null,
				$this->version
			);
		}
	}
}
