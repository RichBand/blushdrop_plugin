<?php
/**
 * Created by PhpStorm.
 * User: ricardobandala
 * Date: 2016-08-15
 * Time: 12:47
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) )
{
    add_action( 'wp_loaded', 'add_blushdropFull_to_cart' );
    function add_blushdropFull_to_cart()
    {
        $current_user = wp_get_current_user();
        if ( isCustomer($current_user->ID) ) {
            $product_id = 51;
            $found = isInCart($product_id);
            //check if product already in cart
            if (!$found) {
                WC()->cart->add_to_cart( $product_id );
            }
        }
    }
}
else {

    // else if woocommerce is not installed
}
function isInCart($product_id){
    $found = false;
    if ( sizeof( WC()->cart->get_cart() ) > 0 ){
        foreach ( WC()->cart->get_cart() as $cart_item_key => $values ){
            $_product = $values['data'];
            if ( $_product->id == $product_id ){
                $found = true;
                break;
            }
        }
    }
    return $found;
}
