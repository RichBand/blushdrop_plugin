<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to Custom Checkout Fields
 *
 * @class RP_WCCF_Checkout
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('RP_WCCF_Checkout')) {

class RP_WCCF_Checkout
{
    private static $positions;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Make sure some actions run after all classes are initiated
        add_action('init', array($this, 'on_init'));

        // Move product custom field values to order item meta
        add_action('woocommerce_add_order_item_meta', array($this, 'add_order_item_meta'), 10, 2);

        // Validate checkout field data
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_checkout_data'));

        // Move checkout custom field values to order meta
        add_action('woocommerce_checkout_update_order_meta', array($this, 'add_order_meta'), 10, 2);

        // Disable Ajax Checkout if at least one of custom Checkout fields is of type file
        add_action('wp_enqueue_scripts', array($this, 'maybe_disable_ajax_checkout'), 99);
    }

    /**
     * On init action
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Define field positions
        self::$positions = array(
            'woocommerce_checkout_before_customer_details'  => __('Above Customer Details', 'rp_wccf'),
            'woocommerce_checkout_after_customer_details'   => __('Below Customer Details', 'rp_wccf'),
            'woocommerce_before_checkout_billing_form'      => __('Above Billing Fields', 'rp_wccf'),
            'woocommerce_after_checkout_billing_form'       => __('Below Billing Fields', 'rp_wccf'),
            'woocommerce_before_checkout_shipping_form'     => __('Above Shipping Fields', 'rp_wccf'),
            'woocommerce_after_checkout_shipping_form'      => __('Below Shipping Fields', 'rp_wccf'),
            'woocommerce_before_order_notes'                => __('Above Order Notes', 'rp_wccf'),
            'woocommerce_after_order_notes'                 => __('Below Order Notes', 'rp_wccf'),
            'woocommerce_checkout_order_review_above'       => __('Above Order Review', 'rp_wccf'), // Hack
            'woocommerce_checkout_order_review'             => __('Below Order Review', 'rp_wccf'),
        );

        // Add actions
        foreach (self::$positions as $hook => $label) {
            if ($hook === 'woocommerce_checkout_order_review_above') {
                add_action('woocommerce_checkout_order_review', array($this, 'display_frontend_fields_above'), 9);
            }
            else if ($hook === 'woocommerce_checkout_order_review') {
                add_action($hook, array($this, 'display_frontend_fields_default'), 11);
            }
            else {
                add_action($hook, array($this, 'display_frontend_fields_default'));
            }
        }
    }

    /**
     * Get list of field positions on Checkout page
     *
     * @access public
     * @return array
     */
    public static function positions()
    {
        return self::$positions;
    }

    /**
     * Above order review position hack
     *
     * @access public
     * @return void
     */
    public function display_frontend_fields_above()
    {
        $this->display_frontend_fields('woocommerce_checkout_order_review_above');
    }

    /**
     * Default frontend field display hook
     *
     * @access public
     * @return void
     */
    public function display_frontend_fields_default()
    {
        $this->display_frontend_fields(current_filter());
    }

    /**
     * Add checkout fields
     *
     * @access public
     * @param string $current_filter
     * @return void
     */
    public function display_frontend_fields($current_filter)
    {
        // Get fields to display
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('checkout'), 'checkout', $current_filter);

        // Display list of fields
        RP_WCCF_FB::display_fields($fields, 'checkout');
    }

    /**
     * Move product custom field values from cart item meta to order item meta
     *
     * @access public
     * @param int $item_id
     * @param array $values
     * @return void
     */
    public function add_order_item_meta($item_id, $values)
    {
        global $wpdb;

        if (!empty($values['wccf'])) {

            // Get order id
            $order_id = $wpdb->get_var('SELECT order_id FROM ' . $wpdb->prefix . 'woocommerce_order_items WHERE order_item_id="' . $item_id . '"');

            // Iterate over fields and add data to order item meta
            foreach ($values['wccf'] as $field) {

                // Field has options?
                if (!empty($field['option_labels'])) {
                    $value = implode(', ', $field['option_labels']);
                }
                else {
                    $value = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
                }

                // Fix empty field label
                if (empty($field['label']) && $field['label'] === '') {
                    $field['label'] = preg_replace('/^wccf_/i', '', $field['key']);
                }

                // Add order item meta
                wc_update_order_item_meta($item_id, $field['key'], $value); // TBD: maybe display pricing here in parentheses

                // Save attribute label to order meta
                update_post_meta($order_id, '_wccf_label_' . $field['key'], $field['label']);
            }

            // Also store data in original format
            wc_update_order_item_meta($item_id, '_wccf', $values['wccf']);
        }
    }

    /**
     * Validate checkout field data
     *
     * @access public
     * @param array $posted
     * @return void
     */
    public function validate_checkout_data($posted)
    {
        RP_WCCF_FB::validate_field_set_data('checkout');
    }

    /**
     * Move checkout custom field values to order meta
     *
     * @access public
     * @param int $order_id
     * @param array $posted
     * @return void
     */
    public function add_order_meta($order_id, $posted)
    {
        if ($sanitized = RP_WCCF_FB::process_field_data('checkout')) {
            update_post_meta($order_id, '_wccf_checkout', $sanitized);
        }
    }

    /**
     * Disable Ajax Checkout if at least one of Checkout fields is of type file
     *
     * @access public
     * @return void
     */
    public function maybe_disable_ajax_checkout()
    {
        // Get all checkout fields
        $fields = RP_WCCF::get_fields('checkout');

        // Iterate over checkout fields and check if at least one of them is of type file
        foreach ($fields as $field) {
            if ($field['type'] === 'file') {
                wp_dequeue_script('wc-checkout');
                break;
            }
        }
    }

}

new RP_WCCF_Checkout();

}
