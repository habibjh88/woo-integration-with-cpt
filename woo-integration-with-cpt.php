<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WooCommerce integration for CPT
 * Plugin URI:        https://wordpress.org/plugins/woo-integration-with-cpt/
 * Description:       FAQ section for Classified Listing.
 * Version:           1.0.0
 * Author:            DevOfWP
 * Author URI:        https://devofwp.com
 * Text Domain:       woo-integration-with-cpt
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Define Constants.
if ( ! defined( 'DOWP_WOO_VERSION' ) ) {
	define( 'DOWP_WOO_VERSION', '1.0.0' );
	define( 'DOWP_WOO_PLUGIN_FILE', __FILE__ );
	define( 'DOWP_WOO_PLUGIN_PATH', plugin_dir_path( DOWP_WOO_PLUGIN_FILE ) );
	define( 'DOWP_WOO_URL', plugins_url( '', DOWP_WOO_PLUGIN_FILE ) );
	define( 'DOWP_WOO_SLUG', basename( dirname( DOWP_WOO_PLUGIN_FILE ) ) );
	define( 'DOWP_WOO_PLUGIN_DIRNAME', dirname( plugin_basename( DOWP_WOO_PLUGIN_FILE ) ) );
	define( 'DOWP_WOO_PLUGIN_BASENAME', plugin_basename( DOWP_WOO_PLUGIN_FILE ) );
}

// Include Files.
require_once 'app/post-type-register.php';

/**
 * Price support for book
 */
add_filter( 'woocommerce_data_stores', 'my_woocommerce_data_stores' );
function my_woocommerce_data_stores( $stores ) {
	require_once DOWP_WOO_PLUGIN_PATH . 'app/class-data-store-cpt.php';
	$stores['product'] = 'MY_Product_Data_Store_CPT';

	return $stores;
}

/**
 * Checkout support
 */
add_filter( 'woocommerce_product_get_price', 'my_woocommerce_product_get_price', 10, 2 );
function my_woocommerce_product_get_price( $price, $product ) {

	if ( get_post_type( $product->get_id() ) === 'book' ) {
		$price = get_post_meta( $product->get_id(), '_book_price', true );
	}

	return $price;
}


// Register Meta Box
function dowp_register_meta_box() {
	add_meta_box(
		'dowp-meta-box-id',
		esc_html__( 'Book Info', 'text-domain' ),
		'dowp_meta_box_callback',
		'book',
		'advanced',
		'high'
	);
}

add_action( 'add_meta_boxes', 'dowp_register_meta_box' );

function dowp_meta_box_callback( $post ) {
	wp_nonce_field( 'dowp_inner_custom_box', 'dowp_inner_custom_box_nonce' );
	$value = get_post_meta( $post->ID, '_book_price', true );
	?>
    <label for="_book_price">
		<?php _e( 'Book Price', 'textdomain' ); ?>
    </label>
    <input type="text" class="form-control" name="_book_price" value="<?php echo esc_html( $value ); ?>"/>
	<?php
}

add_action( 'save_post', 'dowp_save_book_price' );
function dowp_save_book_price( $post_id ) {


	if ( ! isset( $_POST['dowp_inner_custom_box_nonce'] ) ) {
		return $post_id;
	}

	$nonce = $_POST['dowp_inner_custom_box_nonce'];


	if ( ! wp_verify_nonce( $nonce, 'dowp_inner_custom_box' ) ) {
		return $post_id;
	}


	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( 'book' !== $_POST['post_type'] ) {
		return $post_id;
	}

	$mydata = sanitize_text_field( $_POST['_book_price'] );

	// Update the meta field.
	update_post_meta( $post_id, '_book_price', $mydata );
}

function dowp_book_sigle_template( $template ) {
	if ( is_singular( 'book' ) ) {
		if ( $theme_file = locate_template( array ( 'single-book.php' ) ) ) {
			$template = $theme_file;
		} else {
			$template = DOWP_WOO_PLUGIN_PATH . 'templates/single-book.php';
		}
	}
	return $template;
}
add_filter( 'template_include', 'dowp_book_sigle_template' );
