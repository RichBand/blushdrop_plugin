<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Form Builder Class
 *
 * @class RP_WCCF_FB
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('RP_WCCF_FB')) {

class RP_WCCF_FB
{
    private static $types;

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
    }

    /**
     * On init action
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Define field types
        self::$types = array(
            'text'          => __('Text', 'rp_wccf'),
            'textarea'      => __('Text area', 'rp_wccf'),
            'password'      => __('Password', 'rp_wccf'),
            'email'         => __('Email', 'rp_wccf'),
            'number'        => __('Number', 'rp_wccf'),
            'date'          => __('Date picker', 'rp_wccf'),
            'select'        => __('Select', 'rp_wccf'),
            'multiselect'   => __('Multiselect', 'rp_wccf'),
            'checkbox'      => __('Checkbox', 'rp_wccf'),
            'radio'         => __('Radio buttons', 'rp_wccf'),
            'file'          => __('File upload', 'rp_wccf'),
        );
    }

    /**
     * Get list of field types
     *
     * @access public
     * @return array
     */
    public static function types()
    {
        return self::$types;
    }

    /**
     * Render text field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function text($params, $context = 'admin')
    {
        self::input('text', $params, array('value', 'maxlength', 'placeholder'), $context);
    }

    /**
     * Render text area field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function textarea($params, $context = 'admin')
    {
        // Get attributes
        $attributes = self::attributes($params, array('value', 'maxlength', 'placeholder'), 'textarea', $context);

        // Get value
        $value = !empty($params['value']) ? $params['value'] : '';

        // Generate field html
        $field_html = '<textarea ' . $attributes . '>' . $value . '</textarea>';

        // Render field
        self::output($params, $field_html, $context, 'textarea');
    }

    /**
     * Render password field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function password($params, $context = 'admin')
    {
        $params['autocomplete'] = 'off';
        self::input('password', $params, array('value', 'maxlength', 'placeholder'), $context);
    }

    /**
     * Render email field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function email($params, $context = 'admin')
    {
        // Display as regular text field, will do our own validation
        self::input('text', $params, array('value', 'maxlength', 'placeholder'), $context);
    }

    /**
     * Render number field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function number($params, $context = 'admin')
    {
        // Display as regular text field, will do our own validation
        self::input('text', $params, array('value', 'maxlength', 'placeholder'), $context);
    }

    /**
     * Render date field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function date($params, $context = 'admin')
    {
        // Disable autocomplete
        $params['autocomplete'] = 'off';

        // Display as regular text field, will initialize jQuery UI Datepicker based on object's class
        self::input('text', $params, array('value'), $context, true);
    }

    /**
     * Render select field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @param bool $is_multiple
     * @param bool $is_grouped
     * @return void
     */
    public static function select($params, $context = 'admin', $is_multiple = false, $is_grouped = false)
    {
        // If no options are selected, we need to add a blank option at the very beginning of options
        if (!RP_WCCF::is_settings_page() && !$is_multiple && empty($params['value']) && empty($params['selected']) && isset($params['options'])) {

            // Also skip select fields in product level form builder
            if (empty($params['name']) || !preg_match('/^rp_wccf_/i', $params['name'])) {
                $params['options'] = array('' => '') + $params['options'];
            }
        }

        // Get attributes
        $attributes = self::attributes($params, array(), 'select', $context);

        // Get options
        $options = self::options($params, $is_grouped);

        // Check if it's multiselect
        $multiple_html = $is_multiple ? 'multiple' : '';

        // Generate field html
        $field_html = '<select ' . $multiple_html . ' ' . $attributes . '>' . $options . '</select>';

        // Render field
        $field_type = $is_multiple ? 'multiselect' : ($is_grouped ? 'grouped_select' : 'select');
        self::output($params, $field_html, $context, $field_type);
    }

    /**
     * Render grouped select field (for internal use only)
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function grouped_select($params, $context = 'admin')
    {
        self::select($params, $context, false, true);
    }

    /**
     * Render multiselect field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function multiselect($params, $context = 'admin')
    {
        self::select($params, $context, true);
    }

    /**
     * Render checkbox field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function checkbox($params, $context = 'admin')
    {
        self::checkbox_or_radio('checkbox', $params, $context);
    }

    /**
     * Render radio field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function radio($params, $context = 'admin')
    {
        self::checkbox_or_radio('radio', $params, $context);
    }

    /**
     * Render checkbox or radio field
     *
     * @access public
     * @param string $type
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function checkbox_or_radio($type, $params, $context = 'admin')
    {
        $field_html = '';

        // Single field?
        if (empty($params['options'])) {
            $attributes = self::attributes($params, array('value', 'checked'), $type, $context);
            $field_html .= '<input type="' . $type . '" ' . $attributes . '>';
        }

        // Set of fields - iterate over options and generate field for each option
        else {

            // Open list
            $field_html .= '<ul>';

            // Iterate over field options and display as individual items
            foreach ($params['options'] as $key => $label) {

                // Check if option has pricing
                if (!empty($params['option_pricing']) && !empty($params['option_pricing'][$key])) {
                    $price_html = self::get_pricing_string($params['option_pricing'][$key]['price_method'], $params['option_pricing'][$key]['price_value'], true);
                }
                else {
                    $price_html = '';
                }

                // Customize params
                $custom_params = $params;
                $custom_params['id'] = $custom_params['id'] . '_' . $key;

                // Get attributes
                $attributes = self::attributes($custom_params, array(), $type, $context);

                // Check if this item needs to be checked
                if (isset($params['value'])) {
                    $values = (array) $params['value'];
                    $checked = in_array($key, $values) ? 'checked="checked"' : '';
                }
                else {
                    $checked = (isset($params['checked']) && in_array($key, $params['checked']) ? 'checked="checked"' : '');
                }

                // Generate HTML
                $field_html .= '<li><input type="' . $type . '" value="' . $key . '" ' . $checked . ' ' . $attributes . '>' . (!empty($label) ? ' ' . $label : '') . $price_html . '</li>';
            }

            // Close list
            $field_html .= '</ul>';
        }

        // Render field
        self::output($params, $field_html, $context, $type);
    }

    /**
     * Render file field
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    public static function file($params, $context = 'admin')
    {
        self::input('file', $params, array('accept'), $context);
    }

    /**
     * Render generic input field
     *
     * @access public
     * @param string $type
     * @param array $params
     * @param array $custom_attributes
     * @param string $context
     * @param bool $is_date
     * @return void
     */
    private static function input($type, $params, $custom_attributes = array(), $context = 'admin', $is_date = false)
    {
        // Get attributes
        $attributes = self::attributes($params, $custom_attributes, $type, $context);

        // Generate field html
        $field_html = '<input type="' . $type . '" ' . $attributes . '>';

        // Render field
        self::output($params, $field_html, $context, $type, $is_date);
    }

    /**
     * Render attributes
     *
     * @access public
     * @param array $params
     * @param array $custom
     * @param string $type
     * @param string $context
     * @return void
     */
    private static function attributes($params, $custom = array(), $type = 'text', $context = 'admin')
    {
        $html = '';

        // Get full list of attributes
        $attributes = array_merge(array('type', 'name', 'id', 'class', 'autocomplete', 'style'), $custom);

        // Additional attributes for admin ui
        if (is_admin()) {
            $attributes[] = 'required';
        }

        // Allow developers to add custom attributes (e.g. placeholder)
        $attributes = apply_filters('wccf_field_attributes', $attributes, $type, $context);

        // Allow developers to add custom attribute values (e.g. placeholder string)
        $params = apply_filters('wccf_field_attribute_values', $params, $type, $context);

        // Extract attributes and append to html string
        foreach ($attributes as $attribute) {
            if (!empty($params[$attribute])) {
                $html .= $attribute . '="' . $params[$attribute] . '" ';
            }
        }

        return $html;
    }

    /**
     * Get options for select field
     *
     * @access public
     * @param array $params
     * @param bool $is_grouped
     * @return string
     */
    private static function options($params, $is_grouped = false)
    {
        $html = '';
        $selected = array();

        // Get selected option(s)
        if (isset($params['value'])) {
            $selected = (array) $params['value'];
        }
        else if (!empty($params['selected'])) {
            $selected = (array) $params['selected'];
        }

        // Extract options and append to html string
        if (!empty($params['options']) && is_array($params['options'])) {

            // Fix array depth if options are not grouped
            if (!$is_grouped) {
                $params['options'] = array(
                    'rp_wccf_not_grouped' => array(
                        'options' => $params['options'],
                    ),
                );
            }

            // Iterate over option groups
            foreach ($params['options'] as $group_key => $group) {

                // Option group start
                if ($is_grouped) {
                    $html .= '<optgroup label="' . $group['label'] . '">';
                }

                // Iterate over options
                foreach ($group['options'] as $option_key => $option) {

                    // Check if option has pricing
                    if (!empty($params['option_pricing']) && !empty($params['option_pricing'][$option_key])) {
                        $price_html = self::get_pricing_string($params['option_pricing'][$option_key]['price_method'], $params['option_pricing'][$option_key]['price_value']);
                    }
                    else {
                        $price_html = '';
                    }

                    // Get option key
                    $option_key = ($is_grouped ? $group_key . '_' . $option_key : $option_key);

                    // Check if option is selected
                    $selected_html = in_array($option_key, $selected) ? 'selected="selected"' : '';

                    // Format option html
                    $html .= '<option value="' . $option_key . '" ' . $selected_html . '>' . $option . $price_html . '</option>';
                }

                // Option group end
                if ($is_grouped) {
                    $html .= '</optgroup>';
                }
            }
        }

        return $html;
    }

    /**
     * Render field label
     *
     * @access public
     * @param array $params
     * @return string
     */
    private static function label($params)
    {
        echo self::label_html($params);
    }

    /**
     * Get field label html
     *
     * @access public
     * @param array $params
     * @return string
     */
    private static function label_html($params)
    {
        // Check if label needs to be displayed
        if (!empty($params['id']) && !empty($params['label'])) {

            // Field is required
            $required_html = !empty($params['required']) ? ' <abbr class="required" title="' . __('required', 'rp_wccf') . '">*</abbr>' : '';

            // Get pricing information
            if (isset($params['field_type']) && !self::has_options($params['field_type']) && !empty($params['price_method']) && !empty($params['price_value'])) {
                $price_html = self::get_pricing_string($params['price_method'], $params['price_value'], true);
            }
            else {
                $price_html = '';
            }

            // Return label html
            return '<label for="' . $params['id'] . '">' . $params['label'] . $required_html . $price_html . '</label>';
        }

        return '';
    }

    /**
     * Maybe display character limit information
     *
     * @access public
     * @param array $params
     * @param string $context
     * @return void
     */
    private static function character_limit($params, $context)
    {
        if (!empty($params['maxlength']) || (isset($params['maxlength']) && $params['maxlength'] === '0')) {
            if (apply_filters('wccf_display_character_limit', true, $params, $context)) {
                echo '<small class="wccf_character_limit" style="display: none;"><span class="wccf_characters_remaining">' . $params['maxlength'] . '</span> ' . __('characters remaining', 'rp_wccf') . '</small>';
            }
        }
    }

    /**
     * Render field description
     *
     * @access public
     * @param array $params
     * @param string $context
     * @param string $where
     * @return void
     */
    private static function description($params, $context, $where)
    {
        // Determine position
        if ($where === 'before' && apply_filters('wccf_description_before_field', false, $params, $context)) {
            $display = true;
        }
        else if ($where === 'after' && !apply_filters('wccf_description_before_field', false, $params, $context)) {
            $display = true;
        }
        else {
            $display = false;
        }

        // Display description
        if (!$display) {
            echo self::description_html($params);
        }
    }

    /**
     * Get field description html
     *
     * @access public
     * @param array $params
     * @return string
     */
    private static function description_html($params)
    {
        if (!empty($params['description'])) {
            return '<small>' . $params['description'] . '</small>';
        }

        return '';
    }

    /**
     * Output frontend conditions
     *
     * @access public
     * @param array $params
     * @params string $context
     * @return void
     */
    private static function frontend_conditions($params, $context)
    {
        if (!empty($params['frontend_conditions'])) {

            $id = $params['id'];

            // Checkboxes, Radio Buttons
            if (in_array($params['field_type'], array('checkbox', 'radio'))) {
                $option_keys = array_keys($params['options']);
                $id .= '_' . array_shift($option_keys);
            }

            // Pass both conditions and contect string
            $data = array(
                'context'       => $context,
                'conditions'    => $params['frontend_conditions']
            );

            // Output script element
            echo '<script type="text/javascript">var wccf_conditions_' . $id . ' = ' . json_encode($data) . ';</script>';
        }
    }

    /**
     * Output field based on context
     *
     * @access public
     * @param array $params
     * @param string $field_html
     * @param string $context
     * @param string $type
     * @param bool $is_date
     * @return void
     */
    private static function output($params, $field_html, $context, $type, $is_date = false)
    {
        // Open container
        self::output_begin($context, $type, $is_date);

        // Print frontend conditions
        self::frontend_conditions($params, $context);

        // Print label
        self::label($params);

        // Print description before field
        self::description($params, $context, 'before');

        // Print current file download link
        if ($type === 'file' && !empty($params['value'])) {
            global $post;

            if (in_array($post->post_type, array('shop_order', 'product'))) {
                $access_key = $post->post_type === 'shop_order' ? 'wccf_order' : 'wccf_product_admin';
                $delete_url = home_url('/?wccf_file_delete=' . $access_key . '&post_id=' . $post->ID . '&field_key=' . 'wccf_' . $params['key']);
                $prepend = '<div class="wccf_current_file">';
                $append = ' <a href="' . $delete_url . '" class="wccf_delete_file">[x]</a></div>';
                echo self::file_download_link_html($post->ID, array('value' => $params['value']['name'], 'key' => 'wccf_' . $params['key']), $prepend, $append, $access_key);
            }
        }

        // Print field
        echo $field_html;

        // Print character limit information
        self::character_limit($params, $context);

        // Print description after field
        self::description($params, $context, 'after');

        // Close container
        self::output_end($context, $type);
    }

    /**
     * Output container begin
     *
     * @access public
     * @param string $context
     * @param string $type
     * @param bool $is_date
     * @return void
     */
    private static function output_begin($context, $type, $is_date = false)
    {
        $date_class = $is_date ? 'wccf_date_container' : '';

        // Product Fields
        if ($context === 'product') {
            echo '<div class="wccf_field_container wccf_field_container_' . $context . ' wccf_field_container_' . $type . ' wccf_field_container_' . $context . '_' . $type . ' ' . $date_class . '">';
        }

        // Checkout Fields
        else if ($context === 'checkout') {
            echo '<div class="wccf_field_container wccf_field_container_' . $context . ' wccf_field_container_' . $type . ' wccf_field_container_' . $context . '_' . $type . ' ' . $date_class . '">';
        }

        // Product Properties, Order Fields
        else if (in_array($context, array('product_admin', 'order'))) {
            echo '<div class="wccf_meta_box_field_container wccf_' . $context . '_field_container ' . $date_class . '">';
        }
    }

    /**
     * Output container end
     *
     * @access public
     * @param string $context
     * @param string $type
     * @return void
     */
    private static function output_end($context, $type)
    {
        if (in_array($context, array('product', 'product_admin', 'order', 'checkout'))) {
            echo '</div>';
        }
    }

    /**
     * Check if field type has options
     *
     * @access public
     * @param string $type
     * @return bool
     */
    public static function has_options($type)
    {
        return in_array($type, array('select', 'multiselect', 'checkbox', 'radio'));
    }

    /**
     * Display a list of fields
     *
     * @access public
     * @param array $fields
     * @param string $context
     * @param array $stored
     * @return void
     */
    public static function display_fields($fields, $context, $stored = array())
    {
        // Iterate over fields and display them
        foreach ($fields as $field) {

            // Get method name
            $method = $field['type'];

            // Check if field needs to accept multiple values
            $multiple = ($field['type'] === 'multiselect' || ($field['type'] === 'checkbox' && isset($field['options']) && count($field['options']) > 1));

            // Fix empty field labels
            if (in_array($context, array('order', 'product_admin')) && empty($field['label']) && $field['label'] === '') {
                $field_label = preg_replace('/^wccf_/i', '', $field['key']);
            }
            else {
                $field_label = $field['label'];
            }

            // Configure field
            // TBD: maybe we should simply enrich the main $fields array instead of doing this?
            $config = array(
                'id'                    => 'wccf_' . $context . '_' . $field['key'],
                'name'                  => 'wccf[' . $context . '][' . $field['key'] . ']' . ($multiple ? '[]' : ''),
                'class'                 => 'wccf wccf_' . $context . ' wccf_' . $field['type'] . ' wccf_' . $context . '_' . $field['type'],
                'label'                 => $field_label,
                'key'                   => $field['key'],
                'required'              => $field['required'],
                'field_type'            => $field['type'],
                'maxlength'             => isset($field['character_limit']) ? $field['character_limit'] : '',
                'description'           => isset($field['description']) ? $field['description'] : '',
                'frontend_conditions'   => isset($field['frontend_conditions']) ? $field['frontend_conditions'] : null,
                'field_options'         => isset($field['options']) ? $field['options'] : array(),
            );

            // Field has pricing and we need to display pricing details on product page?
            if (RP_WCCF::option('prices_product_page') && !empty($field['price_method']) && !empty($field['price_value'])) {
                $config['price_method'] = $field['price_method'];
                $config['price_value'] = $field['price_value'];
            }

            // Field value already stored?
            if (!empty($stored)) {
                foreach ($stored as $stored_field) {
                    if (preg_replace('/^wccf_/i', '', $stored_field['key']) === $field['key'] && isset($stored_field['value'])) {
                        if ($field['type'] === 'file' && !empty($stored_field['file'])) {
                            $config['value'] = $stored_field['file'];
                        }
                        else {
                            $config['value'] = $stored_field['value'];
                        }
                    }
                }
            }

            // Have field data posted and can reuse it?
            else if (!empty($_POST['wccf'][$context])) {
                foreach ($_POST['wccf'][$context] as $posted_field_key => $posted_field_value) {
                    if (preg_replace('/^wccf_/i', '', $posted_field_key) === $field['key'] && isset($posted_field_value)) {
                        if ($field['type'] !== 'file') {
                            $config['value'] = $posted_field_value;
                        }
                    }
                }
            }

            // Any options?
            if (self::has_options($field['type']) && isset($field['options'])) {
                foreach ($field['options'] as $option_key => $option) {

                    // Add option
                    $config['options'][$option['key']] = $option['label'];

                    // Show as selected?
                    if (isset($option['selected']) && $option['selected']) {
                        $key = in_array($field['type'], array('checkbox', 'radio')) ? 'checked' : 'selected';
                        if (!isset($config[$key]) || in_array($field['type'], array('multiselect', 'checkbox'))) {
                            $config[$key][] = $option['key'];
                        }
                    }

                    // Option has pricing and we need to display pricing details on product page?
                    if (RP_WCCF::option('prices_product_page') && !empty($option['price_method']) && !empty($option['price_value'])) {
                        $config['option_pricing'][$option['key']] = array(
                            'price_method'  => $option['price_method'],
                            'price_value'   => $option['price_value']
                        );
                    }
                }
            }

            // Custom styles?
            if (!empty($field['css'])) {
                $config['style'] = $field['css'];
            }

            // Display field
            self::$method($config, $context);
        }
    }

    /**
     * Validate and sanitize field values
     *
     * @access public
     * @param string $context
     * @param array $existing
     * @param string $key
     * @param mixed $value
     * @param bool $return_field_data
     * @param bool $is_validation
     * @return mixed
     */
    public static function sanitize($context, $existing, $key, $value, $return_field_data = false, $is_validation = false)
    {
        // Get field
        $field = RP_WCCF::get_field($context, $key);

        // Check if we have such field
        if ($field === null) {
            return false;
        }

        // Trim white space from strings
        if (gettype($value) === 'string') {
            $value = trim($value);
        }

        // Ensure character limit
        if (!empty($field['character_limit']) || (isset($field['character_limit']) && $field['character_limit'] === '0')) {
            $value = substr($value, 0, (int) $field['character_limit']);
        }

        // Sanitize field of this type
        $method = 'sanitize_' . $field['type'];
        $value = apply_filters('wccf_sanitize_field_value', self::$method($field, $value, $existing, $is_validation), $field, $value, $context);

        if ($value !== false && $return_field_data) {

            // Format pricing array
            if (!empty($field['options'])) {
                foreach ($field['options'] as $option) {
                    if ((is_array($value) && in_array($option['key'], $value)) || $option['key'] == $value) {

                        // Set option labels
                        if (!empty($option['label'])) {
                            $field['option_labels'][$option['key']] = $option['label'];
                        }

                        // Set pricing values
                        if (!empty($option['price_method']) && !empty($option['price_value'])) {
                            $field['pricing'][$option['key']] = array(
                                'method'    => $option['price_method'],
                                'value'     => $option['price_value'],
                            );
                        }
                    }
                }
            }
            else if (!empty($field['price_method']) && !empty($field['price_value'])) {
                $field['pricing'] = array(
                    'method'    => $field['price_method'],
                    'value'     => $field['price_value'],
                );
            }

            // Assign value
            $field['value'] = $value;

            // Return field
            return $field;
        }
        else {
            return $value;
        }
    }

    /**
     * Sanitize text field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_text($field, $value)
    {
        return $value;
    }

    /**
     * Sanitize textarea field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_textarea($field, $value)
    {
        return $value;
    }

    /**
     * Sanitize password field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_password($field, $value)
    {
        return $value;
    }

    /**
     * Sanitize email field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_email($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Sanitize number field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_number($field, $value)
    {
        return (!empty($value) && is_numeric($value)) ? $value : false;
    }

    /**
     * Sanitize date field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_date($field, $value)
    {
        return RP_WCCF::is_date($value) ? $value : false;
    }

    /**
     * Sanitize select field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_select($field, $value)
    {
        return self::sanitize_select_and_radio($field, $value);
    }

    /**
     * Sanitize radio field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_radio($field, $value)
    {
        return self::sanitize_select_and_radio($field, $value);
    }

    /**
     * Sanitize select and radio fields
     *
     * @access public
     * @param array $field
     * @param string $value
     * @return mixed
     */
    public static function sanitize_select_and_radio($field, $value)
    {
        // No options configured?
        if (empty($field['options'])) {
            return false;
        }

        // Check if this value exists in options
        foreach ($field['options'] as $option) {
            if ($option['key'] == $value) {
                return $option['key'];
            }
        }

        return false;
    }

    /**
     * Sanitize multiselect field
     *
     * @access public
     * @param array $field
     * @param mixed $value
     * @return mixed
     */
    public static function sanitize_multiselect($field, $values)
    {
        return self::sanitize_multiselect_and_checkbox($field, $values);
    }

    /**
     * Sanitize checkbox field
     *
     * @access public
     * @param array $field
     * @param mixed $values
     * @return mixed
     */
    public static function sanitize_checkbox($field, $values)
    {
        return self::sanitize_multiselect_and_checkbox($field, $values);
    }

    /**
     * Sanitize multiselect and checkbox fields
     *
     * @access public
     * @param array $field
     * @param mixed $values
     * @return mixed
     */
    public static function sanitize_multiselect_and_checkbox($field, $values)
    {
        $values = (array) $values;
        $valid_values = array();

        // No options configured?
        if (empty($field['options'])) {
            return false;
        }

        // Check if all of values exist in options
        foreach ($values as $value) {
            foreach ($field['options'] as $option) {
                if ($option['key'] == $value) {
                    $valid_values[] = $option['key'];
                }
            }
        }

        // Some values do not exist?
        if (count($values) !== count($valid_values)) {
            return false;
        }

        return $valid_values;
    }

    /**
     * Sanitize file field
     *
     * @access public
     * @param array $field
     * @param string $value
     * @param array $existing
     * @param bool $is_validation
     * @return mixed
     */
    public static function sanitize_file($field, $value, $existing = array(), $is_validation = false)
    {
        // No file uploaded but we have one stored already? Don't override existing file then as we have a separate handler for that
        if (empty($value['name']) && !empty($existing)) {

            // Iterate over existing fields and try to find given field
            foreach ($existing as $existing_field) {
                if ($existing_field['key'] === 'wccf_' . $field['key'] && !empty($existing_field['file'])) {
                    return $existing_field['file'];
                }
            }
        }

        // Allow developers to reject some file types
        $wccf_file_mime_types = (array) apply_filters('wccf_file_mime_types', array(), $field);
        $file_type_in_list = in_array($value['type'], $wccf_file_mime_types);

        if (apply_filters('wccf_file_mime_types_is_blacklist', true, $field)) {
            if ($file_type_in_list) {
                return false;
            }
        }
        else if (!$file_type_in_list) {
            return false;
        }

        // Allow developers to reject large files
        $wccf_max_file_size = apply_filters('wccf_max_file_size', 0, $field);

        if ($wccf_max_file_size > 0 && $value['size'] > $wccf_max_file_size) {
            return false;
        }

        // Don't save actual file if it's only validation
        if ($is_validation) {
            return true;
        }

        // Generate random file key to avoid collisions
        $file_key = md5(time() . rand());

        // Get upload directory
        $upload_directory = RP_WCCF::get_upload_directory('files', $field);

        // Get directory path and file path
        $directory_path = $upload_directory . '/' . $file_key;
        $file_path = $directory_path . '/' . $value['name'];

        // Set up dedicated directory for this file
        // TBD: frontend error should be displayed if upload fails
        if ($upload_directory && RP_WCCF::set_up_upload_directory($directory_path)) {

            // Move file from temp files to dedicated directory
            if (move_uploaded_file($value['tmp_name'], $file_path)) {

                // Return file info
                return array(
                    'name' => $value['name'],
                    'type' => $value['type'],
                    'size' => $value['size'],
                    'path' => $file_path,
                );
            }
        }

        return false;
    }

    /**
     * Process field data
     *
     * @access public
     * @param string $context
     * @param array $existing
     * @return mixed
     */
    public static function process_field_data($context, $existing = array())
    {
        $results = array();

        // Check if any custom fields were passed and iterate over them
        if (isset($_POST['wccf'][$context]) && is_array($_POST['wccf'][$context])) {
            foreach ($_POST['wccf'][$context] as $key => $value) {

                // Sanitize and store it
                if (($field = self::sanitize_and_format_field_data($key, $value, $context, $existing)) !== false){
                    $results[] = $field;
                }
            }
        }

        // Handle file uploads differently
        if (isset($_FILES['wccf']['name'][$context]) && is_array($_FILES['wccf']['name'][$context])) {
            foreach ($_FILES['wccf']['name'][$context] as $key => $name) {

                // Get all file upload fields
                $value = array(
                    'name'      => $_FILES['wccf']['name'][$context][$key],
                    'type'      => $_FILES['wccf']['type'][$context][$key],
                    'tmp_name'  => $_FILES['wccf']['tmp_name'][$context][$key],
                    'error'     => $_FILES['wccf']['error'][$context][$key],
                    'size'      => $_FILES['wccf']['size'][$context][$key],
                );

                // Sanitize and store it
                if (($field = self::sanitize_and_format_field_data($key, $value, $context, $existing, true)) !== false){
                    $results[] = $field;
                }
            }
        }

        // Iterate over fields
        foreach ($results as $field_key => $field) {

            // Filter out fields that do not match frontend conditions
            if (!RP_WCCF_Conditions::frontend_conditions_match($results, $field)) {
                unset($results[$field_key]);
                continue;
            }

            // Filter out fields that are empty
            if (empty($field['value']) && $field['value'] === '') {
                unset($results[$field_key]);
                continue;
            }

            // Maybe append pricing information
            if (RP_WCCF::option('prices_cart_order_page') && !empty($field['pricing'])) {

                // Check if field has options
                if (!empty($field['option_labels'])) {

                    // Iterate over options
                    foreach ((array) $field['value'] as $value_key => $value) {

                        // Check if this option has pricing adjustment
                        if (!empty($field['pricing'][$value]) && !empty($field['option_labels'][$value])) {

                            // Append pricing adjustment string to option label
                            $pricing_string = self::get_pricing_string($field['pricing'][$value]['method'], $field['pricing'][$value]['value'], false, '(', ')');
                            $results[$field_key]['option_labels'][$value] = $field['option_labels'][$value] . $pricing_string;
                        }
                    }
                }

                // Field has no options, e.g. regular text
                else {

                    // Append pricing adjustment string to option label
                    $pricing_string = self::get_pricing_string($field['pricing']['method'], $field['pricing']['value'], false, '(', ')');
                    $results[$field_key]['value_for_display'] = $field['value'] . $pricing_string;
                }
            }
        }

        return !empty($results) ? $results : false;
    }

    /**
     * Sanitize field data and format value array
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @param string $context
     * @param array $existing
     * @param bool $is_file
     * @param bool $is_validation
     * @return mixed
     */
    public static function sanitize_and_format_field_data($key, $value, $context, $existing = array(), $is_file = false, $is_validation = false)
    {
        // Get sanitized field array, format meta data array and return it
        if (($field = self::sanitize($context, $existing, $key, $value, true, $is_validation)) !== false) {
            return array(
                'key'           => 'wccf_' . strtolower($key),
                'label'         => $field['label'],
                'value'         => $is_file ? $field['value']['name'] : $field['value'],
                'type'          => $field['type'],
                'option_labels' => !empty($field['option_labels']) ? $field['option_labels'] : array(),
                'pricing'       => !empty($field['pricing']) ? $field['pricing'] : array(),
                'file'          => $is_file ? $field['value'] : array(),
                'conditions'    => !empty($field['conditions']) ? $field['conditions'] : array(),
                'public'        => !empty($field['public']) ? $field['public'] : 0,
            );
        }

        return false;
    }

    /**
     * Render file download link for file field
     *
     * @access public
     * @param int $post_id
     * @param array $fields
     * @param string $prepend
     * @param string $append
     * @param string $storage_key
     * @return void
     */
    public static function file_download_link_html($post_id, $field, $prepend = '', $append = '', $storage_key = 'wccf')
    {
        global $wpdb;
        $access_granted = false;

        // Allow admin to download everything
        if (RP_WCCF::is_admin()) {
            $access_granted = true;
        }
        // Allow public Product Properties
        else if ($storage_key === 'wccf_product_admin' && !empty($field['public'])) {
            $access_granted = true;
        }
        // Allow access to own public Order Fields
        else if ($storage_key === 'wccf_order' && !empty($field['public']) && RP_WCCF::user_owns_order(get_current_user_id(), $post_id)) {
            $access_granted = true;
        }
        // Allow access to own Checkout Fields
        else if ($storage_key === 'wccf_checkout' && RP_WCCF::user_owns_order(get_current_user_id(), $post_id)) {
            $access_granted = true;
        }
        // Allow access to own Product Fields
        else if ($storage_key === 'wccf') {

            // $post_id is order id (legacy)
            if (get_post_type($post_id) === 'shop_order') {

                // Check if user owns order
                if (RP_WCCF::user_owns_order(get_current_user_id(), $post_id)) {
                    $access_granted = true;
                }
            }
            // $post_id is order item id
            else {

                // Get order id
                $order_id = $wpdb->get_var($wpdb->prepare("
                    SELECT order_id
                    FROM {$wpdb->prefix}woocommerce_order_items
                    WHERE order_item_id = %d
                ", absint($post_id)));

                // Check if user owns order
                if (!empty($order_id) && RP_WCCF::user_owns_order(get_current_user_id(), $order_id)) {
                    $access_granted = true;
                }
            }
        }

        // Access granted - format and return file download link
        if ($access_granted) {
            $url = home_url('/?wccf_file_download=' . $storage_key . '&post_id=' . $post_id . '&field_key=' . $field['key']);
            return $prepend . '<a href="' . $url . '">' . $field['value'] . '</a>' . $append;
        }

        return '';
    }

    /**
     * Format value for frontend display
     *
     * @access public
     * @param array $field
     * @param string $access_key
     * @param int $post_id
     * @return mixed
     */
    public static function format_value_for_frontend_display($field, $access_key, $post_id)
    {
        // Get value to display
        if ($field['type'] === 'file') {
            return self::file_download_link_html($post_id, $field, '', '', $access_key);
        }
        else if (!empty($field['option_labels'])) {
            return implode(', ', $field['option_labels']);
        }
        else {
            return is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
        }
    }

    /**
     * Get field empty error by field type
     *
     * @access public
     * @param array $field
     * @param string $context
     * @return void
     */
    public static function get_empty_error_by_field_type($field, $context)
    {
        // Proceed depending on field type
        switch ($field['type']) {

            // Selectable required fields
            case 'date':
            case 'select':
            case 'multiselect':
            case 'checkbox':
            case 'radio':
                $message = sprintf(__('<strong>%s</strong> must be selected.', 'rp_wccf'), $field['label']);
                break;

            // Required file upload
            case 'file':
                $message = sprintf(__('<strong>%s</strong> must be uploaded.', 'rp_wccf'), $field['label']);
                break;

            // All types of required text inputs
            default:
                $message = sprintf(__('<strong>%s</strong> is a required field.', 'rp_wccf'), $field['label']);
        }

        // Allow developers to override value and return it
        return apply_filters('wccf_field_empty_error', $message, $field, $context);
    }

    /**
     * Get field value error by field type
     *
     * @access public
     * @param array $field
     * @param string $context
     * @return void
     */
    public static function get_value_error_by_field_type($field, $context)
    {
        // Proceed depending on field type
        switch ($field['type']) {

            // Email address
            case 'email':
                $message = sprintf(__('<strong>%s</strong> is not a valid email.', 'rp_wccf'), $field['label']);
                break;

            // Number
            case 'number':
                $message = sprintf(__('<strong>%s</strong> is not a valid number.', 'rp_wccf'), $field['label']);
                break;

            // Date
            case 'date':
                $message = sprintf(__('<strong>%s</strong> is not a valid date.', 'rp_wccf'), $field['label']);
                break;

            // Invalid file upload
            case 'file':
                $message = sprintf(__('<strong>%s</strong> is not a valid file.', 'rp_wccf'), $field['label']);
                break;

            // All other types of invalid inputs
            default:
                $message = sprintf(__('<strong>%s</strong> value is not valid.', 'rp_wccf'), $field['label']);
        }

        // Allow developers to override value and return it
        return apply_filters('wccf_field_value_error', $message, $field, $context);
    }

    /**
     * Validate POSTed field set data and add WooCommerce notices
     *
     * @access public
     * @param string $context
     * @return bool
     */
    public static function validate_field_set_data($context)
    {
        $is_valid = true;

        // Get fields that match conditions
        $fields = RP_WCCF_Conditions::filter_by_conditions(RP_WCCF::get_fields($context), $context);

        // Iterate over fields and prepare values
        foreach ($fields as $field_key => $field) {

            // Select value by field type
            if ($field['type'] === 'file') {
                $value = empty($_FILES['wccf']['name'][$context][$field['key']]) ? null : array(
                    'name'      => $_FILES['wccf']['name'][$context][$field['key']],
                    'type'      => $_FILES['wccf']['type'][$context][$field['key']],
                    'tmp_name'  => $_FILES['wccf']['tmp_name'][$context][$field['key']],
                    'error'     => $_FILES['wccf']['error'][$context][$field['key']],
                    'size'      => $_FILES['wccf']['size'][$context][$field['key']],
                );
            }
            else {
                $value = !isset($_POST['wccf'][$context][$field['key']]) ? null : $_POST['wccf'][$context][$field['key']];
            }

            // Trim white space from text strings
            $fields[$field_key]['value'] = gettype($value) === 'string' ? trim($value) : $value;
        }

        // Iterate over fields and validate values
        foreach ($fields as $field) {

            // Skip field if it is not required based on frontend conditions
            if (!RP_WCCF_Conditions::frontend_conditions_match($fields, $field)) {
                continue;
            }

            // Check if field is of type file
            $is_file = ($field['type'] === 'file');

            // Check if value is not set or empty
            $is_empty = ($field['value'] === null || $field['value'] === '' || $field['value'] === array());

            // If value is not present but is required
            if ($is_empty && $field['required']) {
                RP_WCCF::add_woocommerce_error(self::get_empty_error_by_field_type($field, $context));
                $is_valid = false;
            }

            // If value is present but is not valid
            if (!$is_empty && ($sanitized_field = self::sanitize_and_format_field_data($field['key'], $field['value'], $context, array(), $is_file, true)) === false) {
                RP_WCCF::add_woocommerce_error(self::get_value_error_by_field_type($field, $context));
                $is_valid = false;
            }
        }

        return $is_valid;
    }

    /**
     * Format and return pricing string
     *
     * @access public
     * @param string $method
     * @param float $value
     * @param bool $is_html
     * @param string $prepend
     * @param string $append
     * @return string
     */
    public static function get_pricing_string($method, $value, $is_html = false, $prepend = '', $append = '')
    {
        // Get WC config
        $price_format = get_woocommerce_price_format();
        $currency_symbol = get_woocommerce_currency_symbol();

        // Format WC price string
        $string = sprintf($price_format, $currency_symbol, $value);

        // Append plus/minus character
        $string = ($method === 'discount' ? '-' : '+') . $string;

        // Per character
        if ($method === 'surcharge_per_character') {
            $string .= ' ' . __('per character', 'rp_wccf');
        }

        // Prepend and append strings
        $string = !empty($prepend) ? $prepend . $string : $string;
        $string = !empty($append) ? $string . $append : $string;

        // Wrap it up
        if ($is_html) {
            $string = ' <span class="wccf_price_label">' . $string . '</span>';
        }
        else {
            $string = ' ' . $string;
        }

        // Allow developers to override and return
        return apply_filters('wccf_addon_price', $string, $method, $value, $is_html);
    }

}

new RP_WCCF_FB();

}
