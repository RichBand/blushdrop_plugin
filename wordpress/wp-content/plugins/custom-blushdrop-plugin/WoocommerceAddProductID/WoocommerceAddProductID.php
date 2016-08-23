<?php
/**
 * Plugin Name: WooCommerce - Show products IDs
 * Plugin URI: http://www.remicorson.com/easily-find-woocommerce-products-id/
 * Description: Adds a new columns to products list page to display product IDs
 * Version: 1.0
 * Author: Remi Corson
 * Author URI: http://remicorson.com
 * Requires at least: 3.5
 * Tested up to: 3.5
 *
 * Text Domain: -
 * Domain Path: -
 *
 */

 /*
|--------------------------------------------------------------------------
| WooCommerce Product Extra Columns
|--------------------------------------------------------------------------
*/

/**
 * Load Custom Product Columns
 *
 * @access      public
 * @since       1.0
 * @return
*/
function woo_product_extra_columns($columns)
{

	$newcolumns = array(
		"cb"       		=> "<input type  = \"checkbox\" />",
		"product_ID"    => esc_html__('ID', 'woocommerce'),
	);

	$columns = array_merge($newcolumns, $columns);

	return $columns;
}
add_filter("manage_edit-product_columns", "woo_product_extra_columns");


/**
 * Charge Product Columns Content
 *
 * @access      public
 * @since       1.0
 * @return
*/
function woo_product_extra_columns_content($column)
{
	global $post;

	$product_id = $post->ID;

	switch ($column)
	{
		case "product_ID":
			echo $product_id;
		break;

	}
}
add_action("manage_posts_custom_column",  "woo_product_extra_columns_content");
