<?php
//TODO implement function createFolder($path)
/*
*This function check whenever a user is registered and set _new_user as true
*/
add_action( 'user_register', 'newUser');
function newUser($user_id) {
    add_user_meta( $user_id, '_new_user', '1' );
}
/*
*This function return the role of the active user
*/
function isCustomer($user_id) {
    $user_meta = get_userdata($user_id);
    $user_roles= $user_meta->roles;
    if (in_array("Customer", $user_roles) || in_array("customer", $user_roles)){
        return true;
    }
    else{
        return false;
    }
}
//TODO, continue to order this stuff from here nex time (2016 08 03 21:00)
/*
*This function check whenever a user loging, if it's the first time, in that case
*it marks it as a already logged with: update_user_meta()
*check if the user is a customer, and
*calls create_page_newUser() passing the user,
*redirects the user to the new page
*/
function loginCustomer($user_login, $user) {
    $logincontrol = get_user_meta($user->ID, '_new_user', 'TRUE');
    if ($logincontrol) {//if is a new user
        update_user_meta( $user->ID, '_new_user', '0' );
        if(isCustomer($user->ID)){
            create_page_newUser($user);
        }
        if(isCustomer($user->ID)){
            wp_redirect( 'http://localhost:8888/blushdrop/wordpress/'.$user->user_login, 302 ); exit;
        }
    }
}
add_action('wp_login', 'loginCustomer', 10, 2);

function firstLoginCustomer($user_login, $user) {
    connect_dropbox($user);
    //wp_redirect( 'http://sandbox.blushdrop.com/'.$user->user_login, 302 ); exit;
    exit();
}
require_once 'connectDropbox.php';
Function getMetada($newUser, $dbxClient){
    $path = "/blushdrop/clients/".$newUser->ID;
    $folderMetadata = $dbxClient->getMetadataAndMediainfo($path);
}














/*
*This String variable set the folder of out of the box plugin,
*not ready further    improove is needed
*check the API to see if it can create a folder automatically if doesnt exist previously
*/
$oob = "[outofthebox dir='/blushdrop/clients/".$ul."' 
mode='files' 
viewrole='editor|author|contributor|subscriber|guest' 
downloadrole='administrator|editor|author|contributor|subscriber' 
notificationdownload='1' 
upload='1' 
uploadrole='administrator|editor|author|contributor|subscriber|guest' 
overwrite='1' 
notificationupload='1' 
rename='1' renamefilesrole='administrator|editor|author|contributor' 
renamefoldersrole='administrator|editor|author|contributor' 
move='1' 
delete='1' deletefilesrole='administrator|editor|author|contributor' 
deletefoldersrole='administrator|editor|author|contributor' 
notificationdeletion='1' 
addfolder='1' 
addfolderrole='administrator|editor|author|contributor']";








/*
*Create a new page and got for title the new user_login
*/




/*****SCRAP*****
function woo_add_cart_fee($subtotal) {
global $woocommerce;
$woocommerce->cart->add_fee( __('Custom', 'woocommerce'), $subtotal );
}
add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee' );



//
add_filter("the_content","cbp_main");
function get_user_role() {
global $current_user;
$user_roles = $current_user->roles;
$user_role = array_shift($user_roles);
return $user_role;
}
require_once '/home2/blushdrop/public_html/sandbox/wp-content/plugins/BlushDrop-app/dropbox-sdk-php/lib/Dropbox/autoload.php';
use \Dropbox as dbx;

if(!function_exist("cbp_main")){
if (is_user_logged_in()){
$cu = wp_get_current_user();
function cbp_main($cbp_content){
if(get_user_role()=='custom'){
//do something
}
}






return $cbp_content.
}
}
 */
