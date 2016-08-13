<?php
/**
 * Plugin Name: Custom blushdrop Plugin
 * Description: handle all the custom needs of blushdrop.
 * Version: 0.0
 * Author: Ricardo Bandala
 * Author URI: http://ricardobandala.com
 * License: private
 */
//TODO experiment with App folder type at dropbox developers
require_once 'connectDropbox.php';
$blushdropPath = "/blushdrop";
function create_page_newUser($newUser, $blushdropPath){
    $path = $GLOBALS["blushdropPath"]."/".$newUser->user_login;
    $oob = "[outofthebox 
    dir='/blushdrop/clients/".$path."' 
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

    $page['post_type']    = 'page';
    $page['post_content'] = $oob;
    $page['post_parent']  = 0;
    $page['post_author']  = $newUser->ID;
    $page['post_status']  = 'publish';
    $page['post_title']   = $newUser->user_login;
    //$page = apply_filters('yourplugin_add_new_page', $page, 'teams');
    $pageid = wp_insert_post ($page);
    if ($pageid == 0) {
        //find what to do with the error, maybe a suggestion to reload?
    }
}
function isCustomer($myID) {
    $user_meta = get_userdata($myID);
    $user_roles= $user_meta->roles;
    if (in_array("Customer", $user_roles) || in_array("customer", $user_roles)){
        return true;
    }
    else{
        return false;
    }
}
function newUser($user_id) {
    if(isCustomer($user_id)) {
        add_user_meta( $user_id, '_new_user', '1' );
        $newUser = get_userdata($user_id);
        $path = $GLOBALS["blushdropPath"]."/".$newUser->user_login;
        $folderMetadata = $GLOBALS["dbxClient"]->createFolder($path);
        create_page_newUser($newUser);
    }
}
add_action( 'user_register', 'newUser');
function loginUser( $user_login, $user ) {
    $myID = $user->ID;
    if(isCustomer($myID)){
        wp_redirect( 'http://localhost:8888/blushdrop/wordpress/'.$user->user_login, 302 ); exit;
    };
}
add_action('wp_login', 'loginUser', 10, 2);


/*
function printMetadata($dbxClient){
    $path = $GLOBALS["blushdropPath"]."/".$path;
    $folderMetadata = $dbxClient->getMetadataWithChildren($path);
    print_r($folderMetadata);
}
printMetadata($dbxClient, "braden");
*/

