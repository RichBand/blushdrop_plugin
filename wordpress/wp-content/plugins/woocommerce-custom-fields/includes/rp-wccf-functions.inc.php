<?php

/*
 * Global functions for this plugin
 */

if (!function_exists('wccf_display_product_properties')) {

    /**
     * Display product properties in frontend
     *
     * @param int $product_id
     * @return string
     */
    function wccf_display_product_properties($product_id = null)
    {
        echo RP_WCCF_Product::render_product_properties_function($product_id);
    }
}
