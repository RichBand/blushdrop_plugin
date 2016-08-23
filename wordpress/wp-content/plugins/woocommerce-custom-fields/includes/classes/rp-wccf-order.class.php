<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to Custom Order Fields
 *
 * @class RP_WCCF_Order
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('RP_WCCF_Order')) {

class RP_WCCF_Order
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Display checkout field values
        add_action('add_meta_boxes', array($this, 'add_meta_box_checkout'), 99, 2);

        // Display backend fields
        add_action('add_meta_boxes', array($this, 'add_meta_box_order'), 99, 2);

        // Hide item meta of type file
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_files_from_meta'));

        // Download links for file upload fields
        add_action('woocommerce_after_order_itemmeta', array($this, 'file_download_links'), 10, 3);

        // Save order field data
        add_action('save_post', array($this, 'process_order_field_data'), 10, 2);

        // Frontend order field data display
        add_action('woocommerce_order_details_after_order_table', array($this, 'frontend_order_field_data_display'));

        // Frontend checkout field data display
        $position = apply_filters('wccf_checkout_field_data_display_position', 10);
        add_action('woocommerce_order_details_after_order_table', array($this, 'frontend_checkout_field_data_display'), $position);

        // Send product and checkout field files as attachments
        add_filter('woocommerce_email_attachments', array($this, 'woocommerce_email_attachments'), 99, 3);

        // Format order item fields (WooCommerce 2.4+ only)
        add_filter('woocommerce_order_items_meta_get_formatted', array($this, 'format_order_items_meta'), 10, 2);
    }

    /**
     * Add meta box for order fields
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_order($post_type, $post)
    {
        // Not product?
        if ($post_type !== 'shop_order') {
            return;
        }

        // Get fields to display
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('order'), 'order');

        // Add meta box if we have any fields to display
        if (!empty($fields)) {
            add_meta_box(
                'wccf_order',
                apply_filters('wccf_context_label', RP_WCCF::option('alias_order'), 'order', 'backend'),
                array($this, 'render_wccf_order'),
                'shop_order',
                'normal',
                'high'
            );
        }
    }

    /**
     * Add meta box to display checkout field values
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_checkout($post_type, $post)
    {
        // Not product?
        if ($post_type !== 'shop_order') {
            return;
        }

        // Get checkout fields stored for this order
        $fields = get_post_meta($post->ID, '_wccf_checkout', true);

        // Add meta box if we have any data to display
        if (!empty($fields)) {
            add_meta_box(
                'wccf_checkout',
                apply_filters('wccf_context_label', RP_WCCF::option('alias_checkout'), 'checkout', 'backend'),
                array($this, 'render_wccf_checkout'),
                'shop_order',
                'normal',
                'high'
            );
        }
    }

    /**
     * Render backend order fields
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function render_wccf_order($post)
    {
        // Get fields to display
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('order'), 'order');

        // Get stored values
        $stored = get_post_meta($post->ID, '_wccf_order', true);
        $stored = $stored === '' ? array() : $stored;

        // Display list of fields
        RP_WCCF_FB::display_fields($fields, 'order', $stored);
    }

    /**
     * Render checkout field data
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function render_wccf_checkout($post)
    {
        // Get checkout fields stored for this order
        $fields = get_post_meta($post->ID, '_wccf_checkout', true);

        // Display data
        if (!empty($fields)) {

            // Start container
            echo '<div class="view"><table cellspacing="0" class="display_meta"><tbody>';

            // Iterate over fields and display data
            foreach ($fields as $field) {

                // File download link
                if ($field['type'] === 'file') {
                    $prepend = '<tr><th>' . $field['label'] . ':</th><td><p>';
                    $append = '</p></td></tr>';
                    echo RP_WCCF_FB::file_download_link_html($post->ID, $field, $prepend, $append, 'wccf_checkout');
                }

                // Other field types
                else {

                    // Fix empty field label
                    $field_label = (!empty($field['label']) || $field['label'] !== '') ? $field['label'] : preg_replace('/^wccf_/i', '', $field['key']);

                    // Start table row and display label
                    echo '<tr><th>' . $field_label . ':</th><td><p>';

                    // Display values
                    if (!empty($field['option_labels'])) {
                        echo implode(', ', $field['option_labels']);
                    }
                    else {
                        echo is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
                    }

                    // End table row
                    echo '</p></td></tr>';
                }
            }

            // End container
            echo '</tbody></table></div>';
        }
    }

    /**
     * Hide item meta of type file
     *
     * @access public
     * @param array $keys
     * @return array
     */
    public function hide_files_from_meta($keys)
    {
        global $post;

        if ($post && $post->post_type === 'shop_order') {

            // Load order object
            $order = new WC_Order($post->ID);

            // Iterate over order items
            foreach ($order->get_items() as $order_item_key => $order_item) {

                // Get stored fields
                $meta = wc_get_order_item_meta($order_item_key, '_wccf', true);

                // Also look in post meta (versions earlier than 1.2 saved it in wrong location)
                if (empty($meta)) {
                    $meta = get_post_meta($order_item_key, '_wccf', true);
                }

                // Iterate over stored fields
                if ($meta !== '') {
                    foreach ($meta as $field) {
                        if ($field['type'] === 'file') {
                            $keys[] = $field['key'];
                        }
                    }
                }
            }
        }

        return $keys;
    }

    /**
     * Render file download links for file fields
     *
     * @access public
     * @param int $item_id
     * @param array $item
     * @param object $product
     * @return void
     */
    public function file_download_links($item_id, $item, $product)
    {
        $links = '';

        // Get stored fields
        $post_meta = wc_get_order_item_meta($item_id, '_wccf', true);

        // Also look in post meta (versions earlier than 1.2 saved it in wrong location)
        if (empty($post_meta)) {
            $post_meta = get_post_meta($item_id, '_wccf', true);
        }

        // Iterate over fields used in this order item
        if (!empty($post_meta)) {
            foreach ($post_meta as $field) {
                if ($field['type'] === 'file' && !empty($field['file'])) {
                    $prepend = '<tr><th>' . $field['label'] . ':</th><td><p>';
                    $append = '</p></td></tr>';
                    $links .= RP_WCCF_FB::file_download_link_html($item_id, $field, $prepend, $append);
                }
            }
        }

        // Display list of downloads
        if (!empty($links)) {
            echo '<div class="view"><table cellspacing="0" class="display_meta"><tbody>' . $links . '</tbody></table></div>';
        }
    }

    /**
     * Process order field data on order save action
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function process_order_field_data($post_id, $post)
    {
        // Only process posts with type shop_order
        if ($post->post_type !== 'shop_order') {
            return;
        }

        // Load existing fields (if any)
        $existing = get_post_meta($post_id, '_wccf_order', true);
        $existing = $existing === '' ? array() : $existing;

        // Sanitize fields and save values
        if ($fields = RP_WCCF_FB::process_field_data('order', $existing)) {
            update_post_meta($post_id, '_wccf_order', $fields);
        }
    }

    /**
     * Frontend order field data display
     *
     * @access public
     * @param object $order
     * @return void
     */
    public function frontend_order_field_data_display($order)
    {
        $display = array();

        // Get order fields
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('order'), 'order', null, $order->id);

        // Get stored field data
        $stored = get_post_meta($order->id, '_wccf_order', true);
        $stored = empty($stored) ? array() : $stored;

        // Iterate over fields and check if at least one of them is public and has a value
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!empty($field['public'])) {
                    foreach ($stored as $stored_field) {
                        if (preg_replace('/^wccf_/i', '', $stored_field['key']) === $field['key'] && isset($stored_field['value'])) {

                            // Get stored value
                            $field['value'] = isset($stored_field['value']) ? $stored_field['value'] : '';

                            // Fix empty field label
                            if (empty($field['label']) && $field['label'] === '') {
                                $field['label'] = preg_replace('/^wccf_/i', '', $field['key']);
                            }

                            // Format value for display and push to array
                            $display[] = array_merge($field, array(
                                'value_to_display' => RP_WCCF_FB::format_value_for_frontend_display($field, 'wccf_order', $order->id)
                            ));
                        }
                    }
                }
            }
        }

        // Allow developers to hide or modify some fields
        $display = apply_filters('wccf_frontend_order_field_data', $display, $order->id);

        // Include template if we have at least one public field with value
        if (!empty($display)) {
            RP_WCCF::include_template('order/order-field-data', array(
                'order'     => $order,
                'fields'    => $display,
            ));
        }
    }

    /**
     * Frontend checkout field data display
     *
     * @access public
     * @param object $order
     * @return void
     */
    public function frontend_checkout_field_data_display($order)
    {
        // Get stored field data
        $stored = get_post_meta($order->id, '_wccf_checkout', true);

        // Only display if there's at least one field saved
        if ($stored !== '' && is_array($stored) && !empty($stored)) {

            $display = array();

            // Format values for display
            foreach ($stored as $stored_field) {

                // Fix empty field label
                if (empty($stored_field['label']) && $stored_field['label'] === '') {
                    $stored_field['label'] = preg_replace('/^wccf_/i', '', $stored_field['key']);
                }

                // Format value for display and push to array
                $display[] = array_merge($stored_field, array(
                    'value_to_display' => RP_WCCF_FB::format_value_for_frontend_display($stored_field, 'wccf_checkout', $order->id)
                ));
            }

            // Allow developers to hide or modify some fields
            $display = apply_filters('wccf_frontend_checkout_field_data', $display, $order->id);

            // Include template
            if (!empty($display)) {
                RP_WCCF::include_template('order/checkout-field-data', array(
                    'order'     => $order,
                    'fields'    => $display,
                ));
            }
        }
    }

    /**
     * Send product and checkout field files as attachments
     *
     * @access public
     * @param array $attachments
     * @param string $email_id
     * @param object $order
     * @return array
     */
    public function woocommerce_email_attachments($attachments, $email_id, $order)
    {
        // Send attachments only with specific emails
        if (!in_array($email_id, array('new_order'))) {
            return $attachments;
        }

        // Send attachments only if this functionality is enabled
        if (!RP_WCCF::option('attach_new_order')) {
            return $attachments;
        }

        // Make sure that $attachments is actually an array
        $attachments = (array) $attachments;

        // Get saved Checkout Fields
        $checkout_fields = get_post_meta($order->id, '_wccf_checkout', true);

        // Iterate over Checkout Fields
        if (!empty($checkout_fields) && is_array($checkout_fields)) {
            foreach ($checkout_fields as $checkout_field) {
                if (isset($checkout_field['type']) && $checkout_field['type'] === 'file' && !empty($checkout_field['file'])) {
                    if (!in_array($checkout_field['file']['path'], $attachments)) {
                        $attachments[] = $checkout_field['file']['path'];
                    }
                }
            }
        }

        // Get Order Items
        $order_items = $order->get_items();

        // Iterate over Order Items
        if (!empty($order_items) && is_array($order_items)) {
            foreach ($order_items as $order_item_key => $order_item) {

                // Get saved Product Fields
                $product_fields = wc_get_order_item_meta($order_item_key, '_wccf', true);

                // Iterate over Product Fields
                if (!empty($product_fields) && is_array($product_fields)) {
                    foreach ($product_fields as $product_field) {
                        if (isset($product_field['type']) && $product_field['type'] === 'file' && !empty($product_field['file'])) {
                            if (!in_array($product_field['file']['path'], $attachments)) {
                                $attachments[] = $product_field['file']['path'];
                            }
                        }
                    }
                }
            }
        }

        return $attachments;
    }

    /**
     * Format order items meta
     *
     * @access public
     * @param array $formatted_meta
     * @param object $item_meta
     * @return array
     */
    public function format_order_items_meta($formatted_meta, $item_meta)
    {
        global $wpdb;
        $product_fields = null;
        $order_item_id = null;

        // Iterate over meta
        if (is_array($formatted_meta)) {
            foreach ($formatted_meta as $meta_key => $meta) {

                // Check if this is custom field
                if (preg_match('/^wccf_/i', $meta['key'])) {

                    // Load product fields if not yet loaded
                    $product_fields = $product_fields !== null ? $product_fields : RP_WCCF::get_fields('product');

                    // Try to match product field
                    foreach ($product_fields as $product_field) {
                        if ($meta['key'] === 'wccf_' . $product_field['key']) {

                            // Display download link for file uploads in frontend
                            if ($product_field['type'] === 'file' && !is_admin()) {

                                // Get order item id if not set yet
                                if ($order_item_id === null) {
                                    $order_item_id = $wpdb->get_var($wpdb->prepare("
                                        SELECT order_item_id
                                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                                        WHERE meta_id = %d
                                    ", absint($meta_key)));
                                }

                                // Ger filed download link and set new meta value
                                $meta['value'] = RP_WCCF_FB::file_download_link_html($order_item_id, array('key' => $meta['key'], 'value' => $meta['value']));
                                $formatted_meta[$meta_key] = $meta;
                            }
                        }
                    }
                }
            }
        }

        return $formatted_meta;
    }

}

new RP_WCCF_Order();

}
