<?php
/**
 * Plugin Name: Custom blushdrop Plugin
 * Description: handle all the custom needs of blushdrop.
 * Version: 0.0
 * Author: Ricardo Bandala
 * Author URI: http://ricardobandala.com
 * License: private
 */



function your_function( $user_login, $user ) {
    $HOlA = $user;
}
add_action('wp_login', 'your_function', 10, 2);

