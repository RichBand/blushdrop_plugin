<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Cart
 *
 * @class RP_WCCF_Cart
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('RP_WCCF_Cart')) {

class RP_WCCF_Cart
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Add to cart validation
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_cart_item_data'), 10, 3);

        // Add fields to cart item meta data
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);

        // Adjust product pricing
        add_filter('woocommerce_add_cart_item', array($this, 'adjust_cart_item_pricing'), 11);

        // Adjust product pricing when cart is loaded
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'cart_loaded'), 11, 3);

        // Get values for display in cart
        add_filter('woocommerce_get_item_data', array($this, 'get_data_for_display'), 11, 2);
    }

    /**
     * Validate field data on add to cart
     *
     * @access public
     * @param bool $is_valid
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function validate_cart_item_data($is_valid, $product_id, $quantity)
    {
        // Validate all fields
        if (!RP_WCCF_FB::validate_field_set_data('product')) {
            return false;
        }

        return $is_valid;
    }

    /**
     * Process field data on add to cart
     *
     * @access public
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation_id
     * @return array
     */
    public function add_cart_item_data($cart_item_data, $product_id, $variation_id)
    {
        if ($sanitized = RP_WCCF_FB::process_field_data('product')) {
            $cart_item_data['wccf'] = $sanitized;
        }

        return $cart_item_data;
    }

    /**
     * Adjust cart item pricing
     *
     * @access public
     * @param array $cart_item
     * @return
     */
    public function adjust_cart_item_pricing($cart_item)
    {
        // Proceed only if we have any custom fields set
        if (!empty($cart_item['wccf'])) {

            // Get potentially adjusted price
            $adjusted_price = RP_WCCF_Product::get_adjusted_price($cart_item['data']->price, 'product', $cart_item['product_id'], $cart_item['wccf']);

            // Check if price was actually adjusted
            if ((float) $adjusted_price !== (float) $cart_item['data']->price) {

                // Set new price
                $cart_item['data']->set_price($adjusted_price);
            }
        }

        return $cart_item;
    }

    /**
     * Adjust product pricing when cart is loaded
     *
     * @access public
     * @param array $cart_item
     * @param array $values
     * @param string $key
     * @return array
     */
    public function cart_loaded($cart_item, $values, $key)
    {
        if (!empty($values['wccf'])) {
            $cart_item['wccf'] = $values['wccf'];
            $cart_item = $this->adjust_cart_item_pricing($cart_item);
        }

        return $cart_item;
    }

    /**
     * Get custom field values to display in cart
     *
     * @access public
     * @param array $data
     * @param array $cart_item
     * @return array
     */
    public function get_data_for_display($data, $cart_item)
    {
        if (!empty($cart_item['wccf'])) {
            foreach ($cart_item['wccf'] as $field) {

                $data[] = array(
                    'name'      => (!empty($field['label']) || $field['label'] !== '') ? $field['label'] : preg_replace('/^wccf_/i', '', $field['key']),
                    'value'     => is_array($field['value']) ? implode(', ', $field['value']) : $field['value'],
                    'display'   => !empty($field['option_labels']) ? implode(', ', $field['option_labels']) : (!empty($field['value_for_display']) ? $field['value_for_display'] : ''),
                );
            }
        }

        return $data;
    }

}

new RP_WCCF_Cart();

}
