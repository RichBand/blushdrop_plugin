<?php
/**
 * The MAIN FUNCTIONS FILE for OPTIMIZER
 *
 * Stores all the Function of the template.
 *
 * @package LayerFramework
 * 
 * @since  LayerFramework 1.0
 */


//**************Optimizer Global******************//
/*CHECK IF optimizer row exist in the wp_options table. Needed for Redux Conversion process*/ 
$optimizerdb = get_option( 'optimizer' );

//**************optimizer FUNCTIONS******************//
require(get_template_directory() . '/lib/functions/defaults.php');
require(get_template_directory() . '/framework/core-functions.php');		//Include LayerFramework Core Functions 
require(get_template_directory() . '/lib/includes/breadcrumb-trail.php'); 	//Include Breadcrumbs Function
require(get_template_directory() . '/lib/functions/core.php');				//Include Theme Core Functions
require(get_template_directory() . '/lib/functions/enqueue.php');			//Include Enqueue CSS/JS Scripts
require(get_template_directory() . '/lib/includes/widgets.php');			//Include Widgets (admin)
require(get_template_directory() . '/lib/includes/shortcodes.php');			//Include Shortcodes (admin)
require(get_template_directory() . '/lib/includes/category-image.php');		//Include Category header Image option (admin)
require(get_template_directory() . '/lib/includes/custom-menu-fields.php');	//Include Menu Icons (admin)
require(get_template_directory() . '/lib/includes/google_fonts.php');		//Google Fonts
require(get_template_directory() . '/lib/functions/admin.php');				//Include Admin Functions (admin)
require(get_template_directory() . '/lib/functions/woocommerce.php');		//Include Woocommerce Functions
require(get_template_directory() . '/lib/functions/yoast.php');				//Include Yoast SEO Functions
require(get_template_directory() . '/lib/functions/preset_importer.php');
require(get_template_directory() . '/lib/functions/portfolio.php');
require(get_template_directory() . '/lib/functions/widget_backup.php');

include_once(get_template_directory() . '/customizer/customizer.php');
include_once(get_template_directory() . '/lib/functions/converter.php');
include_once(get_template_directory() . '/lib/functions/inlinedit.php');
include_once(get_template_directory() . '/lib/functions/schema.php');

require(get_template_directory() . '/frontpage/widgets/init.php');
require(get_template_directory() . '/framework/core-posts.php');		
require(get_template_directory() . '/framework/core-pagination.php');

include_once(get_template_directory() . '/lib/metabox/setup.php');

//Widgets Partial Refresh 
include_once(get_template_directory() . '/plugins/customize-partial-refresh/customize-partial-refresh.php');


//Widgets Partial Refresh 
add_theme_support( 'customize-partial-refresh-widgets' );
add_filter( 'customize_widget_partial_refreshable', '__return_true' );

//**************optimizer SETUP******************//
function optimizer_setup() {
	
	//Editor Style
	add_editor_style( '/assets/css/editor-style.css' );
	
	//add_theme_support( 'custom-header' );
	add_theme_support( 'title-tag' );			//WP 4.1 Site Title
	add_theme_support( 'woocommerce' );			//Woocommerce Support
	add_theme_support('automatic-feed-links');	//RSS FEED LINK
	add_theme_support( 'post-thumbnails' );		//Post Thumbnail
	//Custom Background	
	add_theme_support( 'custom-background', array( 'default-color' => 'ffffff') );	
	//Make theme available for translation
	load_theme_textdomain('optimizer', get_template_directory() . '/languages/');  
	
	//Custom Thumbnail Size	
	if ( function_exists( 'add_image_size' ) ) { 
		add_image_size( 'optimizer_thumb', 400, 270, true ); /*(cropped)*/
	}

	//Register Menus
	register_nav_menus( array(
			'primary' => __( 'Header Navigation', 'optimizer' ),
			'topbar' => __( 'Topbar Navigation', 'optimizer' ),
			'footer' => __( 'Footer Navigation', 'optimizer' ),
	) );
	

}
add_action( 'after_setup_theme', 'optimizer_setup' );


//THEME UPDATER
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'EDD_SL_STORE_URL', 'https://optimizerwp.com' );
define( 'EDD_SL_THEME_NAME', 'Optimizer PRO' ); 
if( !class_exists( 'EDD_Theme_Updater' ) ) {
	// load our custom theme updater
	require( get_template_directory() . '/updater/theme-updater.php' );
}


// rename the coupon field on the cart page
function woocommerce_rename_coupon_field_on_cart( $translated_text, $text, $text_domain ) {
	// bail if not modifying frontend woocommerce text
	if ( is_admin() || 'woocommerce' !== $text_domain ) {
		return $translated_text;
	}
	if ( 'Apply Coupon' === $text ) {
		$translated_text = 'All 6 digits or Promo Code';
	}
	return $translated_text;
}
add_filter( 'gettext', 'woocommerce_rename_coupon_field_on_cart', 10, 3 );

// rename the "Have a Coupon?" message on the checkout page
function woocommerce_rename_coupon_message_on_checkout() {
	return 'Have a 6 digit card or Promo Code?' . ' <a href="#" class="showcoupon">' . __( 'Click here to enter all 6 digits or Promo Code', 'woocommerce' ) . '</a>';
}
add_filter( 'woocommerce_checkout_coupon_message', 'woocommerce_rename_coupon_message_on_checkout' );
// rename the coupon field on the checkout page
function woocommerce_rename_coupon_field_on_checkout( $translated_text, $text, $text_domain ) {
	// bail if not modifying frontend woocommerce text
	if ( is_admin() || 'woocommerce' !== $text_domain ) {
		return $translated_text;
	}
	if ( 'Coupon code' === $text ) {
		$translated_text = 'All 6 digits or Promo Code';
	
	} elseif ( 'Apply Coupon' === $text ) {
		$translated_text = 'Apply Promo Code';
	}
	return $translated_text;
}
add_filter( 'gettext', 'woocommerce_rename_coupon_field_on_checkout', 10, 3 );