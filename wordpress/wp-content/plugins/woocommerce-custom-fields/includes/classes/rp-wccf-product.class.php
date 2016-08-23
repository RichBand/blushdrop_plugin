<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to Custom Product Fields
 *
 * @class RP_WCCF_Product
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('RP_WCCF_Product')) {

class RP_WCCF_Product
{
    private static $pricing_methods;

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

        // Display frontend fields
        add_action('woocommerce_before_add_to_cart_button', array($this, 'display_frontend_fields'));

        // Display product admin fields
        add_action('add_meta_boxes', array($this, 'add_meta_box_product_admin'), 99, 2);

        // Display product level form builder
        add_action('add_meta_boxes', array($this, 'add_meta_box_product_level_fields'), 99, 2);

        // Return product attribute label for display
        add_filter('woocommerce_attribute_label', array($this, 'attribute_label'));

        // Save product admin field data
        add_action('save_post', array($this, 'process_product_admin_field_data'), 10, 2);

        // Display product properties in frontend custom tab
        add_filter('woocommerce_product_tabs', array($this, 'add_product_properties_tab'));

        // Shortcodes
        add_shortcode('wccf_display_product_properties', array($this, 'render_product_properties_shortcode'));

        // Change Add To Cart link in category pages if product contains at least one custom field
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'maybe_change_add_to_cart_link'), 10, 2);

        // Adjust product pricing based on product properties configured by admin
        add_filter('woocommerce_get_price', array($this, 'maybe_change_price'), 10, 2);

        // Ajax price update
        add_action('wp_ajax_rp_wccf_product_price_update', array($this, 'ajax_product_price_update'));
        add_action('wp_ajax_nopriv_rp_wccf_product_price_update', array($this, 'ajax_product_price_update'));
    }

    /**
     * On init action
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Define price adjustment methods
        self::$pricing_methods = array(
            'surcharge'                 => __('Surcharge', 'rp_wccf'),
            'surcharge_per_character'   => __('Surcharge per character', 'rp_wccf'),
            'discount'                  => __('Discount', 'rp_wccf'),
        );
    }

    /**
     * Get list of price adjustment methods
     *
     * @access public
     * @param bool $include_per_character
     * @return array
     */
    public static function pricing_methods($include_per_character = true)
    {
        if ($include_per_character) {
            return self::$pricing_methods;
        }
        else {
            $pricing_methods = self::$pricing_methods;
            unset($pricing_methods['surcharge_per_character']);
            return $pricing_methods;
        }
    }

    /**
     * Get WooCommerce product types
     *
     * @access public
     * @return array
     */
    public static function get_product_types()
    {
        if (RP_WCCF::wc_version_gte('2.2')) {
            return wc_get_product_types();
        }
        else {
            return (array) apply_filters('product_type_selector', array(
                'simple'   => __('Simple product', 'woocommerce'),
                'grouped'  => __('Grouped product', 'woocommerce'),
                'external' => __('External/Affiliate product', 'woocommerce'),
                'variable' => __('Variable product', 'woocommerce')
            ));
        }
    }

    /**
     * Display frontend custom product fields
     *
     * @access public
     * @return void
     */
    public function display_frontend_fields()
    {
        // Get fields to display
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('product'), 'product');

        // Display list of fields
        RP_WCCF_FB::display_fields($fields, 'product');

        // Display grand total
        self::display_grand_total();
    }

    /**
     * Display grand total
     *
     * @access public
     * @return void
     */
    public static function display_grand_total()
    {
        // Check if we need to display grand total
        if (RP_WCCF::option('display_total_price')) {

            // Get data to display
            $label = __('Total', 'rp_wccf');
            $price = self::get_product_price_html();

            // Display data
            echo '<dl class="wccf_grand_total"><dt>' . $label . '</dt><dd>' . $price . ' </dd></dl>';

            // Hide WooCommerce single variation price
            echo '<style>div.single_variation_wrap div.single_variation span.price { display: none; }</style>';
        }
    }

    /**
     * Add meta box for product admin fields (product properties)
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_product_admin($post_type, $post)
    {
        // Not product?
        if ($post_type !== 'product') {
            return;
        }

        // Get fields to display
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('product_admin'), 'product_admin');

        // Add meta box if we have any fields to display
        if (!empty($fields)) {
            add_meta_box(
                'wccf_product_admin',
                apply_filters('wccf_context_label', RP_WCCF::option('alias_product_admin'), 'product_admin', 'backend'),
                array($this, 'render_wccf_product_admin'),
                'product',
                'normal',
                'high'
            );
        }
    }

    /**
     * Add meta box for product level fields
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_product_level_fields($post_type, $post)
    {
        // TBD: implement product level fields
/*
        // Not product?
        if ($post_type !== 'product') {
            return;
        }

        // Add meta box if we have any fields to display
        add_meta_box(
            'wccf_product_level_fields',
            apply_filters('wccf_context_label', RP_WCCF::option('alias_product'), 'product', 'backend'),
            array($this, 'render_wccf_product_level_fields'),
            'product',
            'normal',
            'high'
        );
*/
    }

    /**
     * Render backend product fields (product properties)
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function render_wccf_product_admin($post)
    {
        // Get fields to display
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('product_admin'), 'product_admin');

        // Get stored values
        $stored = get_post_meta($post->ID, '_wccf_product_admin', true);
        $stored = $stored === '' ? array() : $stored;

        // Display list of fields
        RP_WCCF_FB::display_fields($fields, 'product_admin', $stored);
    }

    /**
     * Render product level form builder
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function render_wccf_product_level_fields($post)
    {
        // Include templates
        // TBD: implement product level fields
        //include RP_WCCF_PLUGIN_PATH . 'includes/views/form-builder/form-builder.php';
    }

    /**
     * Return product attribute label for display
     *
     * @access public
     * @param string $meta_key
     * @return string
     */
    public function attribute_label($meta_key)
    {
        global $post;

        $label = $meta_key;
        $order_id = null;

        // Try to get order id from post
        if (gettype($post) === 'object' && isset($post->post_type) && $post->post_type === 'shop_order') {
            $order_id = $post->ID;
        }
        else if (get_query_var('view-order')) {
            $order_id = get_query_var('view-order');
        }
        else if (get_query_var('order-received')) {
            $order_id = get_query_var('order-received');
        }

        // Get label by order id
        if ($order_id) {
            $meta = get_post_meta($order_id, '_wccf_label_' . strtolower($meta_key), true);
            $label = $meta !== '' ? $meta : $label;
        }

        // Try to figure out the label if it looks like our key
        else if (preg_match('/^wccf_/i', $meta_key)) {

            // Get key in our format
            $meta_key = strtolower(preg_replace('/^wccf_/i', '', $meta_key));

            // Get all custom product fields
            $fields = RP_WCCF::option('product_fb_config');

            // Search for this key in product fields and return label if found
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if ($field['key'] === $meta_key) {
                        return (!empty($field['label']) || $field['label'] !== '') ? $field['label'] : preg_replace('/^wccf_/i', '', $field['key']);
                    }
                }
            }
        }

        return $label;
    }

    /**
     * Process product admin field data on order save action
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function process_product_admin_field_data($post_id, $post)
    {
        // Only process posts with type product
        if ($post->post_type !== 'product') {
            return;
        }

        // Load existing fields (if any)
        $existing = get_post_meta($post_id, '_wccf_product_admin', true);
        $existing = $existing === '' ? array() : $existing;

        // Sanitize fields and save values
        if ($fields = RP_WCCF_FB::process_field_data('product_admin', $existing)) {
            update_post_meta($post_id, '_wccf_product_admin', $fields);
        }
    }

    /**
     * Maybe add product properties tab in product page
     *
     * @access public
     * @param array $tabs
     * @return array
     */
    public function add_product_properties_tab($tabs)
    {
        global $post;

        // Get product admin fields
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('product_admin'), 'product_admin', null, $post->ID);

        // Get stored field data
        $stored = get_post_meta($post->ID, '_wccf_product_admin', true);
        $stored = empty($stored) ? array() : $stored;

        // Iterate over fields and check if at least one of them is public and has a value
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!empty($field['public'])) {
                    foreach ($stored as $stored_field) {
                        if (preg_replace('/^wccf_/i', '', $stored_field['key']) === $field['key'] && isset($stored_field['value'])) {
                            if (apply_filters('wccf_display_product_properties', true, $post->ID, $stored)) {
                                return array_merge($tabs, array('wccf_product_properties' => array(
                                    'callback'  => array($this, 'render_product_properties_tab_content'),
                                    'title'     => apply_filters('wccf_context_label', RP_WCCF::option('alias_product_admin'), 'product_admin', 'frontend'),
                                    'priority'  => apply_filters('wccf_product_properties_display_position', 21)
                                )));
                            }
                        }
                    }
                }
            }
        }

        return $tabs;
    }

    /**
     * Display product properties tab content
     *
     * @access public
     * @return void
     */
    public function render_product_properties_tab_content()
    {
        self::render_product_properties_content();
    }

    /**
     * Display product properties anywhere via shortcode
     *
     * @access public
     * @param array $atts
     * @param string $content
     * @param bool $is_function
     * @return void
     */
    public function render_product_properties_shortcode($atts, $content = '', $is_function = false)
    {
        // Fix product attributes in case of shortcode
        if (!$is_function) {
            $atts = shortcode_atts(array('product_id' => ''), $atts);
        }

        // Get product id from attributes
        $product_id = !empty($atts['product_id']) ? $atts['product_id'] : null;

        // Get content and return
        return self::render_product_properties_function($product_id);
    }

    /**
     * Display product properties anywhere via PHP function
     *
     * @access public
     * @param int $product_id
     * @return void
     */
    public static function render_product_properties_function($product_id = null)
    {
        // Get content and return
        return self::render_product_properties_content($product_id, true);
    }

    /**
     * Display product properties tab content
     *
     * @access public
     * @param int $product_id
     * @param bool $return_html
     * @return void
     */
    public static function render_product_properties_content($product_id = null, $return_html = false)
    {
        // Get product id if it was not passed in
        if ($product_id === null) {
            global $post;
            $product_id = $post->ID;
        }

        $display = array();

        // Get product admin fields
        $fields = RP_WCCF::get_fields('product_admin');

        // Get stored field data
        $stored = get_post_meta($product_id, '_wccf_product_admin', true);
        $stored = empty($stored) ? array() : $stored;

        // Iterate over fields and check if at least one of them is public and has a value
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!empty($field['public'])) {
                    foreach ($stored as $stored_field) {
                        if (preg_replace('/^wccf_/i', '', $stored_field['key']) === $field['key'] && isset($stored_field['value'])) {

                            // Get stored value
                            $field['value'] = isset($stored_field['value']) ? $stored_field['value'] : '';

                            // Get option labels for select fields
                            // TBD: this may need to be updated to simply use labels from 'options' directly, current solution is just a hacky quick fix
                            if (!empty($stored_field['option_labels'])) {
                                $field['option_labels'] = $stored_field['option_labels'];
                            }

                            // Fix empty field label
                            if (empty($field['label']) && $field['label'] === '') {
                                $field['label'] = preg_replace('/^wccf_/i', '', $field['key']);
                            }

                            // Format value for display and push to array
                            $display[] = array_merge($field, array(
                                'value_to_display' => RP_WCCF_FB::format_value_for_frontend_display($field, 'wccf_product_admin', $product_id)
                            ));
                        }
                    }
                }
            }
        }

        // Allow developers to hide or modify some fields
        $display = apply_filters('wccf_frontend_product_properties_data', $display, $product_id);

        // Include template if we have at least one public field with value
        if (!empty($display)) {

            // Return instead of output?
            if ($return_html) {
                ob_start();
            }

            // Include template
            RP_WCCF::include_template('product/product-properties-data', array(
                'fields'    => $display,
            ));

            // Return instead of output?
            if ($return_html) {
                $content = ob_get_contents();
                ob_end_clean();
                return $content;
            }
        }
        else if ($return_html) {
            return '';
        }
    }

    /**
     * Change Add To Cart link in category pages if product contains at least one custom field
     *
     * @access public
     * @param string $link
     * @param object $product
     * @return string
     */
    public function maybe_change_add_to_cart_link($link, $product)
    {
        // Get fields by conditions
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields('product'), 'product', null, $product->id);

        // Disable Add To Cart from category pages and link to product page to fill in custom fields
        if (!empty($fields)) {
            return sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button %s product_type_%s">%s</a>',
                esc_url(get_permalink($product->id)),
                esc_attr($product->id),
                esc_attr($product->get_sku()),
                esc_attr(isset($quantity) ? $quantity : 1),
                '',
                esc_attr($product->product_type),
                esc_html(apply_filters('wccf_category_add_to_cart_text', __('View Product', 'rp_wccf'), $this))
            );
        }

        return $link;
    }

    /**
     * Get product price to display
     *
     * @access public
     * @param float $adjustment
     * @return string
     */
    public static function get_product_price_html($adjustment = 0)
    {
        // Get adjusted product price
        $price = self::get_product_price($adjustment);

        // Format and return price html
        return wc_price($price);
    }

    /**
     * Get product price
     *
     * @access public
     * @param float $adjustment
     * @return float
     */
    public static function get_product_price($adjustment = 0, $quantity = 1)
    {
        // Load product object
        $product_id = self::get_product_id();
        $product = RP_WCCF::wc_version_gte('2.2') ? wc_get_product($product_id) : get_product($product_id);

        // Get default product price and possibly adjust it
        $price = $product->get_price() + $adjustment;

        // Retrieve and return product price
        $tax = (get_option('woocommerce_tax_display_shop') == 'incl');
        return $tax ? $product->get_price_including_tax($quantity, $price) : $product->get_price_excluding_tax($quantity, $price);
    }

    /**
     * Attempt to get product id
     *
     * @access public
     * @param mixed $product_id
     * @return void
     */
    public static function get_product_id($product_id = null)
    {
        // Already set
        if ($product_id !== null) {
            return $product_id;
        }

        global $post;

        // Post of type product
        if ($post && isset($post->post_type) && $post->post_type === 'product') {
            return $post->ID;
        }

        // Add to cart via GET
        if (isset($_GET['add-to-cart']) && is_numeric($_GET['add-to-cart'])) {
            $product_id = $_GET['add-to-cart'];
        }

        // Add to cart via POST - version 1
        if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_add_to_cart' && isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
            return $_POST['product_id'];
        }

        // Add to cart via POST - version 2
        if (isset($_POST['add-to-cart']) && is_numeric($_POST['add-to-cart'])) {
            return $_POST['add-to-cart'];
        }

        // Failed miserably :-/
        return $product_id;
    }

    /**
     * Maybe change product price depending on product properties configured by admin
     * Currently this will simply adjust the price as WooCommerce retrieves it from product settings
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return float
     */
    public function maybe_change_price($price, $product)
    {
        // Get stored values
        $stored = get_post_meta($product->id, '_wccf_product_admin', true);
        $stored = $stored === '' ? array() : $stored;

        // Return possibly adjusted price
        return self::get_adjusted_price($price, 'product_admin', $product->id, $stored);
    }

    /**
     * Get adjusted price
     *
     * @access public
     * @param float $price
     * @param string $context
     * @param int $product_id
     * @param array $inputs
     * @param bool $is_product_page
     * @return float
     */
    public static function get_adjusted_price($price, $context, $product_id, $inputs, $is_product_page = false)
    {
        $adjusted_price = $price;

        // Get fields by conditions
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields($context), $context, null, $product_id);

        // Load product so we can access pricing functions
        $product = RP_WCCF::wc_version_gte('2.2') ? wc_get_product($product_id) : get_product($product_id);

        // Iterate over fields
        foreach ($fields as $field) {

            // Field has pricing enabled?
            if (empty($field['price'])) {
                continue;
            }

            // Iterate over stored fields and try to match with current field
            foreach ($inputs as $input) {
                if (preg_replace('/^wccf_/i', '', $input['key']) === $field['key'] && isset($input['value'])) {

                    // Field has options
                    if (!empty($field['options'])) {

                        // Iterate over options
                        foreach ($field['options'] as $option) {

                            // Pricing configured?
                            if (!empty($option['price_method']) && isset($option['price_value']) && $option['price_value'] !== '') {

                                // Adjust price if current option is selected as value
                                if ((gettype($input['value']) === 'string' && $input['value'] === $option['key'])
                                   || (gettype($input['value']) === 'array' && in_array($option['key'], $input['value']))) {
                                    $adjusted_price = self::adjust_price($adjusted_price, $price, $option['price_method'], $option['price_value'], $product, $is_product_page, $input['value']);
                                }
                            }
                        }
                    }

                    // Field does not have options
                    else {

                        // Pricing configured?
                        if (!empty($field['price_method']) && isset($field['price_value']) && $field['price_value'] !== '') {

                            // Adjust price if any non-empty (except of zero) value is present
                            if (!empty($input['value']) || (isset($input['value']) && $input['value'] === '0')) {
                                $adjusted_price = self::adjust_price($adjusted_price, $price, $field['price_method'], $field['price_value'], $product, $is_product_page, $input['value']);
                            }
                        }
                    }

                }
            }
        }

        return $adjusted_price;
    }

    /**
     * Apply pricing adjustment
     *
     * @access public
     * @param float $adjusted_price
     * @param float $original_price
     * @param string $price_method
     * @param float $price_value
     * @param object $product
     * @param bool $is_product_page
     * @param mixed $field_value
     * @return float
     */
    public static function adjust_price($adjusted_price, $original_price, $price_method, $price_value, $product, $is_product_page, $field_value)
    {
        // Maybe tax-adjust price adjustment value
        $tax_adjusted_price_value = $is_product_page ? $product->get_display_price($price_value) : $price_value;

        // Proceed depending on pricing method
        switch ($price_method) {

            case 'surcharge':
                return (float) ($adjusted_price + $tax_adjusted_price_value);

            case 'surcharge_per_character':
                return (float) ($adjusted_price + ($tax_adjusted_price_value * strlen(trim((string) $field_value))));

            case 'discount':
                return (float) ($adjusted_price - $tax_adjusted_price_value);

            default:
                return $adjusted_price;
        }
    }

    /**
     * Dynamically update product price via Ajax on add-on selection
     *
     * @access public
     * @return void
     */
    public function ajax_product_price_update()
    {
        try {

            // Check if data was posted
            if (empty($_POST['data'])) {
                throw new Exception('No data received.');
            }

            // Parse product data and configuration
            $data = urldecode($_POST['data']);
            parse_str($data, $data);

            // Check if product ID is set
            if (isset($data['variation_id']) && is_numeric($data['variation_id'])) {
                $product_id = $data['variation_id'];
            }
            else if (isset($data['add-to-cart']) && is_numeric($data['add-to-cart'])) {
                $product_id = $data['add-to-cart'];
            }
            else {
                throw new Exception('Product is not defined.');
            }

            // Get original display price
            $product = RP_WCCF::wc_version_gte('2.2') ? wc_get_product($product_id) : get_product($product_id);
            $price = $product->get_display_price('', 1);

            // Reconstruct configuration array
            $wccf = array();

            // Iterate over fields
            if (!empty($data['wccf']) && !empty($data['wccf']['product']) && is_array($data['wccf']['product'])) {
                foreach ($data['wccf']['product'] as $key => $value) {
                    $wccf[] = array(
                        'key'   => $key,
                        'value' => $value
                    );
                }
            }

            // Get adjusted price
            $adjusted_price = self::get_adjusted_price($price, 'product', $product_id, $wccf, true);

            echo json_encode(array(
                'error'         => 0,
                'price'         => $adjusted_price,
                'price_html'    => wc_price($adjusted_price)
            ));

        } catch (Exception $exc) {
            echo json_encode(array(
                'error' => 1
            ));
        }

        exit;
    }


}

new RP_WCCF_Product();

}
