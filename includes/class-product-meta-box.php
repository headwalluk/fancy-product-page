<?php
/**
 * Product meta box for choosing a fancy product page.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined( 'WPINC' ) || die();

/**
 * "Fancy Product Page" meta box on the WooCommerce product editor.
 *
 * Lets the administrator select the Page/Post to use as a product's front-end,
 * and persists the choice to the {@see META_FANCY_PRODUCT_PAGE_ID} post meta.
 */
class Product_Meta_Box extends Meta_Box {

	/**
	 * Constructor. Registers the meta box and its save handler.
	 */
	public function __construct() {
		parent::__construct( POST_TYPE_PRODUCT );

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register the meta box with WordPress.
	 *
	 * Callback for `add_meta_boxes`.
	 *
	 * The title is the plugin's name — a proper noun — so it is deliberately
	 * NOT wrapped in a translation function. Machine translation renders it as
	 * a description ("Page produit haut de gamme" in French) rather than
	 * leaving the brand intact.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		add_meta_box(
			get_class( $this ),
			'Fancy Product Page',
			array( $this, 'render' ),
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
	public function render( $post ) {
		echo '<div class="pp-wrap">';

		$this->render_nonce_field();

		$settings = get_settings_controller();

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
	public function save( $post_id, $post ) {
		if ( ! $this->is_saving_meta_box( $post_id, $post ) ) {
			// Not a valid save of this meta box.
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by is_saving_meta_box() above.
			$page_id = isset( $_POST[ META_FANCY_PRODUCT_PAGE_ID ] ) ? (int) $_POST[ META_FANCY_PRODUCT_PAGE_ID ] : 0;

			if ( $page_id > 0 ) {
				update_post_meta( $post_id, META_FANCY_PRODUCT_PAGE_ID, $page_id );
			} else {
				delete_post_meta( $post_id, META_FANCY_PRODUCT_PAGE_ID );
			}

			/*
			 * Reverse logic: the checkbox asks whether to WRITE the schema, but
			 * the meta records SUPPRESSION, so it is only stored when the box is
			 * unticked. An unticked checkbox posts nothing at all, hence the
			 * array_key_exists() test.
			 */
			if ( array_key_exists( FIELD_WRITE_FANCY_PAGE_PRODUCT_SCHEMA, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by is_saving_meta_box() above.
				delete_post_meta( $post_id, META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA );
			} else {
				update_post_meta( $post_id, META_SUPPRESS_FANCY_PAGE_PRODUCT_SCHEMA, 'yes' );
			}
		}
	}
}
