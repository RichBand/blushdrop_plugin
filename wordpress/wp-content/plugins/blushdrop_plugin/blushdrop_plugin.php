<?php
/**
 * Plugin Name: Blushdrop Plugin
 * Description: handle all the custom needs of blushdrop.
 * Version: 1.1
 * Author: Ricardo Bandala
 * Live to code, code to live, MVC is beautiful
 * Author URI: http://ricardobandala.com
 * License: private
 */


require_once 'blushdrop.php';
$args = [
	"path" => null,
];
$bdp = new Blushdrop($args);


function add_taxonomies_to_pages() {
	register_taxonomy_for_object_type( 'post_tag', 'page' );
	register_taxonomy_for_object_type( 'category', 'page' );
}
add_action( 'init', 'add_taxonomies_to_pages' );
if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'category_and_tag_archives' );

}
function category_and_tag_archives( $wp_query ) {
	$my_post_array = array('post','page');

	if ( $wp_query->get( 'category_name' ) || $wp_query->get( 'cat' ) )
		$wp_query->set( 'post_type', $my_post_array );

	if ( $wp_query->get( 'tag' ) )
		$wp_query->set( 'post_type', $my_post_array );
}