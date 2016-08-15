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

$blushdropPath = "/blushdrop";
require_once 'connectDropbox.php';
require_once 'getFolderMetadata.php';

//todo, check the out of the box $oob add customer
function create_page_newUser($newUser){
    $path = $GLOBALS["blushdropPath"]."/".$newUser->user_login;
    $oob = '[outofthebox 
    dir="'+$path+'" 
    mode="files" 
    viewrole="administrator|author|customer|guest" 
    downloadrole="administrator|author|subscriber|customer" 
    upload="1" 
    rename="1" 
    renamefilesrole="administrator|editor|author|contributor" 
    renamefoldersrole="administrator|editor|author|contributor" 
    move="1" 
    delete="1" 
    deletefilesrole="administrator|editor|author|contributor" 
    deletefoldersrole="administrator|editor|author|contributor" 
    addfolder="1" 
    addfolderrole="administrator|editor|author|contributor"]';
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

//todo page hook load methadata
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

function getCountTime($path){
    $countTime = 0;
    $count = 0;
    $count = [
        "totalTime" => 0,
        "seconds" => 0,
        "minutes" => 0,
        "hours" => 0,
    ];
    try {
        $folderMetadata = getFolderMetadata($path);
        $contents = $folderMetadata["contents"];
        if (is_array($contents) || is_object($contents)) {
            foreach ($contents as $metadatos) {
                $count++;
                $countTime += isset($metadatos["video_info"]["duration"]) ? $metadatos["video_info"]["duration"] : '';
            };
            $uSec = $countTime % 1000;
            $input = floor($countTime / 1000);
            $seconds = $input % 60;
            $input = floor($input / 60);
            $minutes = $input % 60;
            $input = floor($input / 60);
            $hours = $input % 60;
            $count = [
                "totalTime" => $countTime,
                "seconds" => $seconds,
                "minutes" => $minutes,
                "hours" => $hours,
            ];
        };

    } catch (Exception $e) {
        //echo 'Excepción capturada: ',  $e->getMessage(), "\n";
    }

    return $count;
}

function newUser($user_id) {
    if(isCustomer($user_id)) {
        add_user_meta( $user_id, '_new_user', '1' );
        $newUser = get_userdata($user_id);
        $path = $GLOBALS["blushdropPath"]."/".$newUser->user_login;
        $GLOBALS["dbxClient"]->createFolder($path);
        create_page_newUser($newUser);
    }
}
add_action( 'user_register', 'newUser');

function loginUser( $user_login, $user ) {
    $myID = $user->ID;
    if(isCustomer($myID)){
        $thisSite = get_site_url()."/";
        wp_redirect( $thisSite.$user->user_login, 302 ); exit;
    };
}
add_action('wp_login', 'loginUser', 10, 2);

function register_script() {
    wp_register_script( 'custom_jquery', plugins_url('/js/custom-jquery.js', __FILE__), array('jquery'), '2.5.1' );

    wp_register_style( 'new_style', plugins_url('/css/new-style.css', __FILE__), false, '1.0.0', 'all');
}
//add_action('init', 'register_script');
// use the registered jquery and style above
//add_action('wp_enqueue_scripts', 'enqueue_style');

function enqueue_style(){
    wp_enqueue_script('custom_jquery');

    wp_enqueue_style( 'new_style' );
}

function showTotalTime($wp_query){
    $current_user = wp_get_current_user();
    $page_title = $wp_query->post->post_title;
    if ( 0 == $current_user->ID ) {
    } else {
        $folderMetadata = getCountTime($GLOBALS["blushdropPath"]."/".$page_title);
        return $folderMetadata["totalTime"];
    }
}



