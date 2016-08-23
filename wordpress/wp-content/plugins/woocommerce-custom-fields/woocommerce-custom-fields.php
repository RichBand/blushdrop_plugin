<?php

/**
 * Plugin Name: WooCommerce Custom Fields
 * Plugin URI: http://www.rightpress.net/woocommerce-custom-fields
 * Description: Create custom fields for WooCommerce product and checkout pages
 * Version: 1.2.1
 * Author: RightPress
 * Author URI: http://www.rightpress.net
 * Requires at least: 3.6
 * Tested up to: 4.5
 *
 * Text Domain: rp_wccf
 * Domain Path: /languages
 *
 * @package WooCommerce Custom Fields
 * @category Core
 * @author RightPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('RP_WCCF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RP_WCCF_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('RP_WCCF_VERSION', '1.2.1');
define('RP_WCCF_OPTIONS_VERSION', '1');
define('RP_WCCF_SUPPORT_WP', '3.6');
define('RP_WCCF_SUPPORT_WC', '2.1');

if (!class_exists('RP_WCCF')) {

/**
 * Main plugin class
 *
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
class RP_WCCF
{
    // Singleton instance
    private static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Load translation
        load_textdomain('rp_wccf', WP_LANG_DIR . '/woocommerce-custom-fields/rp_wccf-' . apply_filters('plugin_locale', get_locale(), 'rp_wccf') . '.mo');
        load_plugin_textdomain('rp_wccf', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Some code needs to be executed after all plugins are loaded
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'));
    }

    /**
     * Executed after plugins are loaded
     *
     * @access public
     * @return void
     */
    public function on_plugins_loaded()
    {
        // Check environment
        if (!RP_WCCF::check_environment()) {
            return;
        }

        // Load includes
        foreach (glob(RP_WCCF_PLUGIN_PATH . '/includes/*.inc.php') as $filename)
        {
            include $filename;
        }

        // Load classes
        foreach (glob(RP_WCCF_PLUGIN_PATH . '/includes/classes/*.class.php') as $filename)
        {
            include $filename;
        }

        // Initialize automatic updates
        require_once(plugin_dir_path(__FILE__) . '/includes/classes/libraries/rightpress-updates.class.php');
        RightPress_Updates::init(__FILE__);

        // Initialize plugin configuration
        $this->settings = rp_wccf_plugin_settings();

        // Get settings section info
        $this->section_info = $this->get_section_info();

        // Load/parse plugin settings
        $this->opt = $this->get_options();

        // Hook to WordPress 'init' action
        add_action('init', array($this, 'on_init'), 99);

        // Admin-only hooks
        if (is_admin() && !defined('DOING_AJAX')) {

            // Additional Plugins page links
            add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'plugins_page_links'));

            // Add settings page menu link
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'plugin_options_setup'));

            // Load backend assets
            add_action('init', array($this, 'enqueue_select2'), 1);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets'));

            // Enable file uploads in admin when fields of type file are used
            add_action('post_edit_form_tag', array($this, 'maybe_add_enctype_attribute'));

            // Output hidden templates in admin footer
            add_action('admin_footer', array($this, 'output_form_builder_templates'));
        }
        else {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        }
    }

    /**
     * Add settings link on plugins page
     *
     * @access public
     * @param array $links
     * @return void
     */
    public function plugins_page_links($links)
    {
        $settings_link = '<a href="http://support.rightpress.net/" target="_blank">'.__('Support', 'rp_wccf').'</a>';
        array_unshift($links, $settings_link);
        $settings_link = '<a href="admin.php?page=woocommerce_custom_fields">'.__('Settings', 'rp_wccf').'</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * WordPress 'init'
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Intercept download file call
        if (!empty($_GET['wccf_file_download'])) {
            $this->file_download();
        }

        // Intercept delete file call
        if (!empty($_GET['wccf_file_delete'])) {
            $this->file_delete();
        }
    }

    /**
     * WordPress activation hook
     *
     * @access public
     * @return void
     */
    public function activate()
    {
        // Define options
        if (!get_option('rp_wccf_options')) {
            add_option('rp_wccf_options', array(RP_WCCF_OPTIONS_VERSION => $this->options('default')));
        }
    }

    /**
     * Exctract some options from plugin settings array
     *
     * @access public
     * @param string $name
     * @param bool $split_by_page
     * @return array
     */
    public function options($name, $split_by_page = false)
    {
        $results = array();

        // Iterate over settings array and extract values
        foreach ($this->settings as $page => $page_value) {
            $page_options = array();

            foreach ($page_value['children'] as $section => $section_value) {
                foreach ($section_value['children'] as $field => $field_value) {
                    if (isset($field_value[$name])) {
                        $page_options['rp_wccf_' . $field] = $field_value[$name];
                    }
                    if (isset($field_value['child_fields'])) {
                        foreach ($field_value['child_fields'] as $child_field_key => $child_field) {
                            if (isset($child_field[$name])) {
                                $page_options['rp_wccf_' . $child_field_key] = $child_field[$name];
                            }
                        }
                    }
                }
            }

            // Add form builder keys to default options array
            if ($name == 'default' && in_array($page, array('product', 'product_admin', 'checkout', 'order'))) {
                $page_options[$page . '_fb_config'] = array();
            }

            $results[preg_replace('/_/', '-', $page)] = $page_options;
        }

        $final_results = array();

        if (!$split_by_page) {
            foreach ($results as $value) {
                $final_results = array_merge($final_results, $value);
            }
        }
        else {
            $final_results = $results;
        }

        return $final_results;
    }

    /**
     * Get options saved in database
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        return array_merge($this->options('default'), $this->load_settings('options'));
    }

    /**
     * Get availability rules saved in database
     *
     * @access public
     * @param string $object
     * @return array
     */
    public function load_settings($object)
    {
        // Get settings from database
        $saved_options = get_option('rp_wccf_' . $object, array());

        // Get current version (for major updates in the future)
        if (!empty($saved_options)) {
            if (isset($saved_options[RP_WCCF_OPTIONS_VERSION])) {
                $saved_options = $saved_options[RP_WCCF_OPTIONS_VERSION];
            }
            else {
                // Migrate options here if needed...
            }
        }

        return is_array($saved_options) ? $saved_options : array();
    }

    /**
     * Get array of section info strings
     *
     * @access public
     * @return array
     */
    public function get_section_info()
    {
        $results = array();

        // Iterate over settings array and extract values
        foreach ($this->settings as $page_value) {
            foreach ($page_value['children'] as $section => $section_value) {
                if (isset($section_value['info'])) {
                    $results[$section] = $section_value['info'];
                }
            }
        }

        return $results;
    }

    /**
     * Return option
     *
     * @access public
     * @param string $key
     * @return string|bool
     */
    public static function option($key)
    {
        $rp_wccf = RP_WCCF::get_instance();
        $no_prefix = array('product_fb_config', 'product_admin_fb_config', 'checkout_fb_config', 'order_fb_config');
        $prefix = in_array($key, $no_prefix) ? '' : 'rp_wccf_';
        return isset($rp_wccf->opt[$prefix . $key]) ? $rp_wccf->opt[$prefix . $key] : false;
    }

    /*
     * Update single option
     *
     * @access public
     * @return bool
     */
    public function update_option($key, $value)
    {
        $this->opt[$key] = $value;
        return update_option('rp_wccf_options', $this->opt);
    }

    /**
     * Add admin submenu items
     *
     * @access public
     * @return void
     */
    public function add_admin_menu()
    {
        if (self::is_admin()) {

            global $submenu;

            if (isset($submenu['woocommerce'])) {
                add_submenu_page(
                    'woocommerce',
                    __('Custom Fields', 'rp_wccf'),
                    __('Custom Fields', 'rp_wccf'),
                    'edit_posts',
                    'woocommerce_custom_fields',
                    array($this, 'set_up_admin_page')
                );
            }
        }
    }

    /**
     * Register our settings fields with WordPress
     *
     * @access public
     * @return void
     */
    public function plugin_options_setup()
    {
        // Check if current user can manage plugin options
        if (self::is_admin()) {

            // Iterate over tabs
            foreach ($this->settings as $tab_key => $tab) {

                register_setting(
                    'rp_wccf_opt_group_' . $tab_key,
                    'rp_wccf_options',
                    array($this, 'options_validate')
                );

                // Iterate over sections
                foreach ($tab['children'] as $section_key => $section) {

                    add_settings_section(
                        $section_key,
                        $section['title'],
                        array($this, 'render_section_info'),
                        'rp_wccf-admin-' . str_replace('_', '-', $tab_key)
                    );

                    // Iterate over fields
                    foreach ($section['children'] as $field_key => $field) {
                        add_settings_field(
                            'rp_wccf_' . $field_key,
                            $field['title'],
                            array($this, 'render_field_' . $field['type']),
                            'rp_wccf-admin-' . str_replace('_', '-', $tab_key),
                            $section_key,
                            array(
                                'name'          => 'rp_wccf_' . $field_key,
                                'class'         => isset($field['class']) ? $field['class'] : '',
                                'options'       => $this->opt,
                                'after'         => isset($field['after']) ? $field['after'] : '',
                                'values'        => isset($field['values']) ? $field['values'] : array(),
                                'default'       => isset($field['default']) ? $field['default'] : '',
                                'child_fields'  => isset($field['child_fields']) ? $field['child_fields'] : array(),
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Render section info
     *
     * @access public
     * @param array $section
     * @return void
     */
    public function render_section_info($section)
    {
        if (isset($this->section_info[$section['id']])) {
            $info = $this->section_info[$section['id']];
            include RP_WCCF_PLUGIN_PATH . 'includes/views/settings/info.php';
        }

        // Display file field warning
        if (in_array($section['id'], array('product_fields', 'checkout_fields'))) {

            // Get message
            if ($section['id'] === 'product_fields') {
                $warning = __('<strong>Important:</strong> Ajax will be disabled for Add To Cart action as at least one of the fields below is a file upload field.', 'rp_wccf');
            }
            else {
                $warning = __('<strong>Important:</strong> Ajax will be disabled on Checkout page as at least one of the fields below is a file upload field.', 'rp_wccf');
            }

            // Display message
            echo '<p class="rp_wccf_section_info rp_wccf_file_warning" style="display: none;">' . $warning . '</p>';
        }

        // Display form builder
        if (in_array($section['id'], array('product_fields', 'product_admin_fields', 'checkout_fields', 'order_fields'))) {
            $this->render_form_builder($section['id']);
        }
    }

    /**
     * Render form builder
     *
     * @access public
     * @param string $section_id
     * @return void
     */
    public function render_form_builder($section_id)
    {
        // Get current tab
        $current_tab = $this->get_current_settings_tab();

        // Include templates
        include RP_WCCF_PLUGIN_PATH . 'includes/views/form-builder/form-builder.php';
    }

    /**
     * Render checkbox field
     *
     * @access public
     * @return void
     */
    public static function render_field_checkbox($args = array())
    {
        printf(
            '<input type="checkbox" id="%s" name="rp_wccf_options[%s]" value="1" %s />%s',
            $args['name'],
            $args['name'],
            checked($args['options'][$args['name']], true, false),
            !empty($args['after']) ? '&nbsp;&nbsp;' . $args['after'] : ''
        );
    }

    /**
     * Render checkbox field
     *
     * @access public
     * @return void
     */
    public static function render_field_text($args = array())
    {
        // TBD: update all these functions to use our own form builder
        printf(
            '<input type="text" id="%s" name="rp_wccf_options[%s]" value="%s" class="rp_wccf_field_long %s" />%s',
            $args['name'],
            $args['name'],
            $args['options'][$args['name']],
            (isset($args['class']) ? $args['class'] : ''),
            !empty($args['after']) ? '&nbsp;&nbsp;' . $args['after'] : ''
        );

        foreach ($args['child_fields'] as $child_field_key => $child_field) {
            $render_function = 'render_field_' . $child_field['type'];
            self::$render_function(array_merge($child_field, array('name' => 'rp_wccf_' . $child_field_key, 'options' => $args['options'])));
        }
    }

    /**
     * Render dropdown (select) field
     *
     * @access public
     * @return void
     */
    public static function render_field_dropdown($args = array())
    {
        printf('<select id="%s" name="rp_wccf_options[%s]" class="rp_wccf_field_select rp_wccf_field_long %s">', $args['name'], $args['name'], (isset($args['class']) ? $args['class'] : ''));

        foreach ($args['values'] as $value_key => $value) {
            printf('<option value="%s" %s>%s</option>', $value_key, selected($args['options'][$args['name']], $value_key, false), $value);
        }

        echo '</select>';
    }

    /**
     * Render multiselect field
     *
     * @access public
     * @return void
     */
    public static function render_field_multiselect($args = array())
    {
        printf('<select multiple id="%s" name="rp_wccf_options[%s][]" class="rp_wccf_field_multiselect rp_wccf_field_long">', $args['name'], $args['name']);

        foreach ($args['values'] as $value_key => $value) {
            printf('<option value="%s" %s>%s</option>', $value_key, selected(in_array($value_key, $args['options'][$args['name']], true), true, false), $value);
        }

        echo '</select>';
    }

    /**
     * Validate saved options
     *
     * @access public
     * @param array $input
     * @return void
     */
    public function options_validate($input)
    {
        $output = $this->opt;

        if (empty($_POST['current_tab']) || !isset($this->settings[$_POST['current_tab']])) {
            return $output;
        }

        $current_tab = $_POST['current_tab'];

        $errors = array();
        $field_array = array();

        // Generate list of settings fields for a given page
        foreach ($this->settings[$current_tab]['children'] as $section_key => $section) {
            foreach ($section['children'] as $field_key => $field) {
                $field_array[$section_key] = isset($field_array[$section_key]) ? $field_array[$section_key] : $section;

                if (isset($field['child_fields'])) {
                    foreach ($field['child_fields'] as $child_key => $child) {
                        $field_array[$section_key]['children'][$child_key] = $child;
                    }
                }
            }
        }

        // Validate form builder fields
        if (!empty($input['fb']) && is_array($input['fb'])) {

            // Load some data for validation
            $field_types = RP_WCCF_FB::types();
            $pricing_methods = RP_WCCF_Product::pricing_methods();
            $checkout_positions = RP_WCCF_Checkout::positions();
            $condition_types = RP_WCCF_Conditions::conditions($current_tab);

            // Reset existing config in output array
            $output[$current_tab . '_fb_config'] = array();

            // Iterate over fields
            foreach ($input['fb'] as $field) {

                try {

                    $current_field = array();

                    // Label
                    if (!empty($field['label'])) {
                        $current_field['label'] = $field['label'];
                    }
                    else {
                        $current_field['label'] = '';
                    }

                    // Key
                    if (empty($field['key']) || !is_string($field['key'])) {
                        throw new Exception(__('Unique key must be filled in.', 'rp_wccf'));
                    }
                    else if (!preg_match('/^[A-Z0-9_]+$/i', $field['key'])) {
                        throw new Exception(__('Unique key can only contain letters, numbers and underscores.', 'rp_wccf'));
                    }
                    else {

                        // Check if unique key is unique
                        if (isset($output) && isset($output[$current_tab . '_fb_config'])) {
                            foreach ($output[$current_tab . '_fb_config'] as $other_field) {
                                if (isset($other_field['key']) && $other_field['key'] === $field['key']) {
                                    throw new Exception(__('Unique keys must be unique, duplicate found.', 'rp_wccf'));
                                }
                            }
                        }

                        // Save valid field key
                        $current_field['key'] = strtolower($field['key']);
                    }

                    // Field type
                    if (isset($field['type']) && isset($field_types[$field['type']])) {
                        $current_field['type'] = $field['type'];
                    }
                    else {
                        throw new Exception(__('Field type not set.', 'rp_wccf'));
                    }

                    // Required
                    if (isset($field['required']) && $field['required']) {
                        $current_field['required'] = 1;
                    }
                    else {
                        $current_field['required'] = 0;
                    }

                    // Pricing
                    if (in_array($current_tab, array('product', 'product_admin'))) {
                        if (isset($field['price']) && $field['price']) {
                            $current_field['price'] = 1;
                        }
                        else {
                            $current_field['price'] = 0;
                        }
                    }

                    // Public
                    if (in_array($current_tab, array('product_admin', 'order'))) {
                        if (isset($field['public']) && $field['public']) {
                            $current_field['public'] = 1;
                        }
                        else {
                            $current_field['public'] = 0;
                        }
                    }

                    // Advanced
                    if (isset($field['advanced']) && $field['advanced']) {
                        $current_field['advanced'] = 1;
                    }
                    else {
                        $current_field['advanced'] = 0;
                    }

                    // Pricing method
                    if (isset($current_field['price']) && $current_field['price'] && !RP_WCCF_FB::has_options($current_field['type'])) {
                        if (isset($field['price_method']) && isset($pricing_methods[$field['price_method']])) {
                            $current_field['price_method'] = $field['price_method'];
                        }
                        else {
                            throw new Exception(__('Invalid pricing method.', 'rp_wccf'));
                        }
                    }

                    // Pricing value
                    if (isset($current_field['price']) && $current_field['price'] && !RP_WCCF_FB::has_options($current_field['type'])) {
                        if (!empty($field['price_value']) && is_string($field['price_value'])) {
                            if (preg_match('/^[0-9\.]+$/i', $field['price_value'])) {
                                $current_field['price_value'] = $field['price_value'];
                            }
                            else {
                                throw new Exception(__('Invalid pricing value - valid decimal must be provided.', 'rp_wccf'));
                            }
                        }
                        else {
                            $current_field['price_value'] = '';
                        }
                    }

                    // Description
                    if (isset($current_field['advanced']) && $current_field['advanced']) {
                        if (!empty($field['description'])) {
                            $current_field['description'] = $field['description'];
                        }
                        else {
                            $current_field['description'] = '';
                        }
                    }

                    // CSS
                    if (isset($current_field['advanced']) && $current_field['advanced']) {
                        if (!empty($field['css'])) {
                            $current_field['css'] = $field['css'];
                        }
                        else {
                            $current_field['css'] = '';
                        }
                    }

                    // Character Limit
                    if (isset($current_field['advanced']) && $current_field['advanced']) {
                        if (!empty($field['character_limit']) || (isset($field['character_limit']) && $field['character_limit'] === '0')) {
                            $current_field['character_limit'] = preg_replace('/[^0-9]/i', '', $field['character_limit']);
                        }
                        else {
                            $current_field['character_limit'] = '';
                        }
                    }

                    // Position
                    if ($current_tab == 'checkout') {
                        if (isset($current_field['advanced']) && $current_field['advanced']) {
                            if (isset($field['position']) && isset($checkout_positions[$field['position']])) {
                                $current_field['position'] = $field['position'];
                            }
                            else {
                                throw new Exception(__('Invalid checkout page position.', 'rp_wccf'));
                            }
                        }
                        else {
                            $checkout_position_keys = array_keys($checkout_positions);
                            $current_field['position'] = array_shift($checkout_position_keys);
                        }
                    }

                    // Conditions
                    if (isset($current_field['advanced']) && $current_field['advanced']) {
                        if (!empty($field['conditions']) && is_array($field['conditions'])) {
                            foreach($field['conditions'] as $condition) {

                                $current_condition = array();

                                // Type
                                if (isset($condition['type']) && ($group_option = RP_WCCF_Conditions::extract_group_and_option($condition['type']))) {

                                    // Extract group key and option key
                                    list($group_key, $option_key) = $group_option;

                                    // Check if such keys exist
                                    if (isset($condition_types[$group_key]) && isset($condition_types[$group_key]['options'][$option_key])) {
                                        $current_condition['type'] = $condition['type'];
                                    }
                                    else {
                                        throw new Exception(__('Invalid condition type.', 'rp_wccf'));
                                    }
                                }
                                else {
                                    throw new Exception(__('Invalid condition type.', 'rp_wccf'));
                                }

                                // Method
                                $method_key = $current_condition['type'] . '_method';

                                if (isset($condition[$method_key])) {

                                    // Get all condition methods for current condition
                                    $condition_methods = RP_WCCF_Conditions::methods($group_key, $option_key);

                                    // Check if selected condition method exists
                                    if (isset($condition_methods[$condition[$method_key]])) {
                                        $current_condition[$method_key] = $condition[$method_key];
                                    }
                                    else {
                                        throw new Exception(__('Invalid condition method.', 'rp_wccf'));
                                    }
                                }
                                else {
                                    throw new Exception(__('Invalid condition method.', 'rp_wccf'));
                                }

                                // Roles
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'roles')) {
                                    if (isset($condition['roles'])) {
                                        $current_condition['roles'] = (array) $condition['roles'];
                                    }
                                    else {
                                        $current_condition['roles'] = array();
                                    }
                                }

                                // Capabilities
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'capabilities')) {
                                    if (isset($condition['capabilities'])) {
                                        $current_condition['capabilities'] = (array) $condition['capabilities'];
                                    }
                                    else {
                                        $current_condition['capabilities'] = array();
                                    }
                                }

                                // Products
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'products')) {
                                    if (isset($condition['products'])) {
                                        $current_condition['products'] = (array) $condition['products'];
                                    }
                                    else {
                                        $current_condition['products'] = array();
                                    }
                                }

                                // Product categories
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'product_categories')) {
                                    if (isset($condition['product_categories'])) {
                                        $current_condition['product_categories'] = (array) $condition['product_categories'];
                                    }
                                    else {
                                        $current_condition['product_categories'] = array();
                                    }

                                }

                                // Product types
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'product_types')) {
                                    if (isset($condition['product_types'])) {
                                        $current_condition['product_types'] = (array) $condition['product_types'];
                                    }
                                    else {
                                        $current_condition['product_types'] = array();
                                    }
                                }

                                // Payment methods
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'payment_methods')) {
                                    if (isset($condition['payment_methods'])) {
                                        $current_condition['payment_methods'] = (array) $condition['payment_methods'];
                                    }
                                    else {
                                        $current_condition['payment_methods'] = array();
                                    }
                                }

                                // Shipping methods
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'shipping_methods')) {
                                    if (isset($condition['shipping_methods'])) {
                                        $current_condition['shipping_methods'] = (array) $condition['shipping_methods'];
                                    }
                                    else {
                                        $current_condition['shipping_methods'] = array();
                                    }
                                }

                                // Other field key
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'other_field_key')) {
                                    if (isset($condition['other_field_key']) && is_string($condition['other_field_key']) && preg_match('/^[A-Z0-9_]+$/i', $condition['other_field_key'])) {
                                        $current_condition['other_field_key'] = $condition['other_field_key'];
                                    }
                                    else {
                                        throw new Exception(__('Invalid other field key value in condition settings.', 'rp_wccf'));
                                    }
                                }

                                // Text
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'text')) {
                                    if (!empty($condition['text'])) {
                                        $current_condition['text'] = $condition['text'];
                                    }
                                    else {
                                        $current_condition['text'] = '';
                                    }
                                }

                                // Number
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'number')) {
                                    if (isset($condition['number']) && is_string($condition['number']) && preg_match('/^[0-9]+$/i', $condition['number'])) {
                                        $current_condition['number'] = $condition['number'];
                                    }
                                    else {
                                        throw new Exception(__('Invalid numeric value in field conditions.', 'rp_wccf'));
                                    }
                                }

                                // Decimal
                                if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'decimal')) {
                                    if (isset($condition['decimal']) && is_string($condition['decimal']) && preg_match('/^[0-9\.]+$/i', $condition['decimal'])) {
                                        $current_condition['decimal'] = $condition['decimal'];
                                    }
                                    else {
                                        throw new Exception(__('Invalid decimal value in field conditions.', 'rp_wccf'));
                                    }
                                }

                                // Store this condition configuration
                                if (!empty($current_condition)) {
                                    $current_field['conditions'][] = $current_condition;
                                }
                            }
                        }
                    }

                    // Options
                    if (RP_WCCF_FB::has_options($current_field['type'])) {
                        if (!empty($field['options']) && is_array($field['options'])) {
                            foreach($field['options'] as $option) {

                                $current_option = array();

                                // Key
                                if (empty($option['key']) || !is_string($option['key'])) {
                                    throw new Exception(__('Field option key must be filled in.', 'rp_wccf'));
                                }
                                else if (!preg_match('/^[A-Z0-9_]+$/i', $option['key'])) {
                                    throw new Exception(__('Field option key can only contain letters, numbers and underscores.', 'rp_wccf'));
                                }
                                else {

                                    // Check if unique key is unique
                                    if (isset($current_field['options'])) {
                                        foreach ($current_field['options'] as $other_option) {
                                            if (isset($other_option['key']) && $other_option['key'] === $option['key']) {
                                                throw new Exception(__('Field option keys must be unique, duplicate found.', 'rp_wccf'));
                                            }
                                        }
                                    }

                                    // Save valid option key
                                    $current_option['key'] = strtolower($option['key']);
                                }

                                // Label
                                if (!empty($option['label'])) {
                                    $current_option['label'] = $option['label'];
                                }
                                else {
                                    $current_option['label'] = '';
                                }

                                // Pricing method
                                if (isset($current_field['price']) && $current_field['price']) {
                                    if (isset($option['price_method']) && isset($pricing_methods[$option['price_method']])) {
                                        $current_option['price_method'] = $option['price_method'];
                                    }
                                    else {
                                        throw new Exception(__('Invalid pricing method.', 'rp_wccf'));
                                    }
                                }

                                // Pricing value
                                if (isset($current_field['price']) && $current_field['price']) {
                                    if (!empty($option['price_value']) && is_string($option['price_value'])) {
                                        if (preg_match('/^[0-9\.]+$/i', $option['price_value'])) {
                                            $current_option['price_value'] = $option['price_value'];
                                        }
                                        else {
                                            throw new Exception(__('Invalid pricing value - valid decimal must be provided.', 'rp_wccf'));
                                        }
                                    }
                                    else {
                                        $current_option['price_value'] = '';
                                    }
                                }

                                // Selected
                                if (isset($option['selected']) && $option['selected']) {
                                    $current_option['selected'] = 1;
                                }
                                else {
                                    $current_option['selected'] = 0;
                                }

                                // Store this option configuration
                                if (!empty($current_option)) {
                                    $current_field['options'][] = $current_option;
                                }
                            }
                        }
                    }

                    // Store this field configuration
                    if (!empty($current_field)) {
                        $output[$current_tab . '_fb_config'][] = $current_field;
                    }

                } catch (Exception $e) {

                    // Add notice about misconfigured field
                    add_settings_error(
                        'rp_wccf',
                        'field_not_valid',
                        $e->getMessage() . ' ' . __('Misconfigured fields were discarded.', 'rp_wccf')
                    );
                }
            }
        }
        else {
            $output[$current_tab . '_fb_config'] = array();
        }

        // Iterate over fields and validate new values
        foreach ($field_array as $section_key => $section) {
            foreach ($section['children'] as $field_key => $field) {

                $current_field_key = 'rp_wccf_' . $field_key;

                switch($field['validation']['rule']) {

                    // Checkbox
                    case 'bool':
                        $input[$current_field_key] = (!isset($input[$current_field_key]) || $input[$current_field_key] == '') ? '0' : $input[$current_field_key];
                        if (in_array($input[$current_field_key], array('0', '1'), true) || ($input[$current_field_key] == '' && $field['validation']['empty'] == true)) {
                            $output[$current_field_key] = $input[$current_field_key];
                        }
                        else {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'bool', 'title' => $field['title']));
                        }
                        break;

                    // Number
                    case 'number':
                        if (is_numeric($input[$current_field_key]) || ($input[$current_field_key] == '' && $field['validation']['empty'] == true)) {
                            $output[$current_field_key] = $input[$current_field_key];
                        }
                        else {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'number', 'title' => $field['title']));
                        }
                        break;

                    // Option
                    case 'option':
                        if (isset($input[$current_field_key]) && (isset($field['values'][$input[$current_field_key]]) || ($input[$current_field_key] == '' && $field['validation']['empty'] == true))) {
                            $output[$current_field_key] = $input[$current_field_key];
                        }
                        else if (!isset($input[$current_field_key])) {
                            $output[$current_field_key] = '';
                        }
                        else {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'option', 'title' => $field['title']));
                        }
                        break;

                    // Text input
                    default:
                        if (($input[$current_field_key] == '' && !$field['validation']['empty'])) {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'string', 'title' => $field['title']));
                        }
                        else {
                            $output[$current_field_key] = esc_attr(trim($input[$current_field_key]));
                        }
                        break;
                }
            }
        }

        // Display settings updated message
        add_settings_error(
            'rp_wccf',
            'rp_wccf_' . 'settings_updated',
            __('Your settings have been saved.', 'rp_wccf'),
            'updated'
        );

        // Display errors
        foreach ($errors as $error) {
            $reverted = __('Reverted to a previous value.', 'rp_wccf');

            $messages = array(
                'number' => __('must be numeric', 'rp_wccf') . '. ' . $reverted,
                'bool' => __('must be either 0 or 1', 'rp_wccf') . '. ' . $reverted,
                'option' => __('is not allowed', 'rp_wccf') . '. ' . $reverted,
                'email' => __('is not a valid email address', 'rp_wccf') . '. ' . $reverted,
                'url' => __('is not a valid URL', 'rp_wccf') . '. ' . $reverted,
                'string' => __('is not a valid text string', 'rp_wccf') . '. ' . $reverted,
                'time' => __('is not a valid time string', 'rp_wccf') . '. ' . $reverted,
            );

            add_settings_error(
                'rp_wccf',
                $error['code'],
                __('Value of', 'rp_wccf') . ' "' . $error['title'] . '" ' . $messages[$error['code']]
            );
        }

        return array(RP_WCCF_OPTIONS_VERSION => $output);
    }

    /**
     * Set up page
     *
     * @access public
     * @return void
     */
    public function set_up_admin_page()
    {
        // Get current tab
        $current_tab = $this->get_current_settings_tab();

        // Print notices
        settings_errors('rp_wccf');

        // Print header
        include RP_WCCF_PLUGIN_PATH . 'includes/views/settings/header.php';

        // Print settings page content
        include RP_WCCF_PLUGIN_PATH . 'includes/views/settings/fields.php';

        // Print footer
        include RP_WCCF_PLUGIN_PATH . 'includes/views/settings/footer.php';

        // Print form builder templates
        if (in_array($current_tab, array('product', 'product_admin', 'checkout', 'order'))) {
            include RP_WCCF_PLUGIN_PATH . 'includes/views/form-builder/form-builder-templates.php';
        }
    }

    /**
     * Maybe output form builder templates in admin footer on product pages
     *
     * @access public
     * @return void
     */
    public function output_form_builder_templates()
    {
        // TBD: implement product level fields
/*
        global $typenow;
        global $post;

        // Check if we are in a product edit page
        if ($typenow && $typenow === 'product' && $post && gettype($post) === 'object' && !empty($post->ID)) {
            $current_tab = 'product';
            include RP_WCCF_PLUGIN_PATH . 'includes/views/form-builder/form-builder-templates.php';
        }
*/
    }

    /**
     * Get current settings tab
     *
     * @access public
     * @return string
     */
    public function get_current_settings_tab()
    {
        // Check if we know tab identifier
        if (isset($_GET['tab']) && isset($this->settings[$_GET['tab']])) {
            $tab = $_GET['tab'];
        }
        else {
            $keys = array_keys($this->settings);
            $tab = array_shift($keys);
        }

        return $tab;
    }

    /**
     * Load frontend assets
     *
     * @access public
     * @return void
     */
    public function enqueue_frontend_assets()
    {
        // Load only on specific pages
        global $post;

        if (!is_checkout() && !(isset($post) && is_object($post) && $post->post_type === 'product')) {
            return;
        }

        // Our own scripts and styles
        wp_enqueue_script('rp-wccf-general-scripts', RP_WCCF_PLUGIN_URL . '/assets/js/general.js', array('jquery'), RP_WCCF_VERSION);
        wp_enqueue_script('rp-wccf-frontend-scripts', RP_WCCF_PLUGIN_URL . '/assets/js/frontend.js', array('jquery'), RP_WCCF_VERSION);
        wp_enqueue_style('rp-wccf-frontend-styles', RP_WCCF_PLUGIN_URL . '/assets/css/frontend.css', array(), RP_WCCF_VERSION);

        // Datepicker configuration
        wp_localize_script('rp-wccf-frontend-scripts', 'rp_wccf_datepicker_config', self::get_datepicker_config('frontend'));
        wp_localize_script('rp-wccf-frontend-scripts', 'rp_wccf_general_config', array(
            'ajaxurl'              => admin_url('admin-ajax.php'),
            'display_total_price'   => RP_WCCF::option('display_total_price')
        ));

        // Enqueue jQuery UI Datepicker
        self::enqueue_datepicker();
    }

    /**
     * Enqueue Select2
     *
     * @access public
     * @return void
     */
    public function enqueue_select2()
    {
        wp_enqueue_script('rp-wccf-select2-scripts', RP_WCCF_PLUGIN_URL . '/assets/select2/js/select2.min.js', array('jquery'), '4.0.0');
        wp_enqueue_script('rp-wccf-select2-rp', RP_WCCF_PLUGIN_URL . '/assets/js/rp-select2.js', array(), RP_WCCF_VERSION);
        wp_enqueue_style('rp-wccf-select2-styles', RP_WCCF_PLUGIN_URL . '/assets/select2/css/select2.min.css', array(), '4.0.0');
    }

    /**
     * Load backend assets conditionally
     *
     * @access public
     * @return void
     */
    public function enqueue_backend_assets()
    {
        global $typenow;

        // Load assets conditionally
        if (!self::is_settings_page() && $typenow !== 'product' && $typenow !== 'shop_order') {
            return;
        }

        // jQuery UI Accordion
        wp_enqueue_script('jquery-ui-accordion');

        // jQuery UI Sortable
        wp_enqueue_script('jquery-ui-sortable');

        // Font awesome (icons)
        wp_enqueue_style('font-awesome', RP_WCCF_PLUGIN_URL . '/assets/font-awesome/css/font-awesome.min.css', array(), '4.1');

        // Our own scripts and styles
        wp_enqueue_script('rp-wccf-general-scripts', RP_WCCF_PLUGIN_URL . '/assets/js/general.js', array('jquery'), RP_WCCF_VERSION);
        wp_enqueue_script('rp-wccf-backend-scripts', RP_WCCF_PLUGIN_URL . '/assets/js/backend.js', array('jquery'), RP_WCCF_VERSION);
        wp_enqueue_style('rp-wccf-backend-styles', RP_WCCF_PLUGIN_URL . '/assets/css/backend.css', array(), RP_WCCF_VERSION);

        // jQuery Validate
        wp_enqueue_script('rp-wccf-jquery-validate', RP_WCCF_PLUGIN_URL . '/assets/jquery-validate/jquery.validate.js', array('jquery'), '1.13.1');

        // Datepicker configuration
        wp_localize_script('rp-wccf-backend-scripts', 'rp_wccf_datepicker_config', self::get_datepicker_config('backend'));

        // Enqueue jQuery UI Datepicker
        self::enqueue_datepicker();

        // Pass variables to JS
        wp_localize_script('rp-wccf-backend-scripts', 'rp_wccf', array(
            'tab'       => $this->get_current_settings_tab(),
            'ajaxurl'   => admin_url('admin-ajax.php'),
        ));

        // Pass form builder configuration values to JS
        if (in_array($this->get_current_settings_tab(), array('product', 'product_admin', 'checkout', 'order'))) {
            $form_builder_config = $this->get_form_builder_config();
            wp_localize_script('rp-wccf-backend-scripts', 'rp_wccf_fb', $form_builder_config);
            wp_localize_script('rp-wccf-backend-scripts', 'rp_wccf_fb_multiselect_options', $this->get_selected_option_labels($form_builder_config));
        }
    }

    /**
     * Enqueue jQuery UI Datepicker
     *
     * @access public
     * @return void
     */
    public static function enqueue_datepicker()
    {
        // jQuery UI Datepicker
        wp_enqueue_script('jquery-ui-datepicker');

        // jQuery UI Datepicker styles
        wp_enqueue_style('rp-wccf-jquery-ui-styles', RP_WCCF_PLUGIN_URL . '/assets/jquery-ui/jquery-ui.min.css', array(), '1.11.4');

        // jQuery UI Datepicker language file
        $locale = self::get_optimized_locale('mixed');
        if (file_exists(RP_WCCF_PLUGIN_PATH . '/assets/jquery-ui/i18n/datepicker-' . $locale . '.js')) {
            wp_enqueue_script('rp-wccf-jquery-ui-language', RP_WCCF_PLUGIN_URL . '/assets/jquery-ui/i18n/datepicker-' . $locale . '.js', array('jquery-ui-datepicker'), RP_WCCF_VERSION);
        }
    }

    /**
     * Get form builder config to pass to Javascript
     *
     * @access public
     * @return array
     */
    public function get_form_builder_config()
    {
        if (self::is_settings_page()) {
            return self::get_fields($this->get_current_settings_tab());
        }
        else {
            return array();
        }
    }

    /**
     * Get field by context and unique key
     *
     * @access public
     * @param string $context
     * @param string $key
     * @return mixed
     */
    public static function get_field($context, $key)
    {
        // Get all fields belonging to this context
        $fields = self::get_fields($context);

        // Iterate over fields and return field by key
        foreach ($fields as $field) {
            if ((string) $field['key'] === (string) $key) {
                return $field;
            }
        }
    }

    /**
     * Get fields by context
     *
     * @access public
     * @param string $context
     * @return array
     */
    public static function get_fields($context)
    {
        $wccf = self::get_instance();
        return $wccf->opt[$context . '_fb_config'];
    }

    /**
     * Get selected multiselect field option labels
     *
     * @access public
     * @param array $config
     * @return array
     */
    public function get_selected_option_labels($config)
    {
        $labels = array();

        foreach ($config as $field_key => $field) {
            if (!empty($field['conditions']) && is_array($field['conditions'])) {
                foreach ($field['conditions'] as $condition_key => $condition) {
                    foreach (array('roles', 'capabilities', 'products', 'product_categories', 'product_types', 'payment_methods', 'shipping_methods') as $key) {
                        if (!empty($condition[$key]) && is_array($condition[$key])) {
                            $labels[$field_key]['conditions'][$condition_key][$key] = RP_WCCF_Conditions::get_items_by_ids($key, $condition[$key]);
                        }
                    }
                }
            }
        }

        return $labels;
    }

    /**
     * Include template
     *
     * @access public
     * @param string $template
     * @param array $args
     * @return string
     */
    public static function include_template($template, $args = array())
    {
        if ($args && is_array($args)) {
            extract($args);
        }

        include self::get_template_path($template);
    }

    /**
     * Select correct template (allow overrides in theme folder)
     *
     * @access public
     * @param string $template
     * @return string
     */
    public static function get_template_path($template)
    {
        $template = rtrim($template, '.php') . '.php';

        // Check if this template exists in current theme
        if (!($template_path = locate_template(array('woocommerce-custom-fields/' . $template)))) {
            $template_path = RP_WCCF_PLUGIN_PATH . 'templates/' . $template;
        }

        return $template_path;
    }

    /**
     * Check WooCommerce version
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function wc_version_gte($version)
    {
        if (defined('WC_VERSION') && WC_VERSION) {
            return version_compare(WC_VERSION, $version, '>=');
        }
        else if (defined('WOOCOMMERCE_VERSION') && WOOCOMMERCE_VERSION) {
            return version_compare(WOOCOMMERCE_VERSION, $version, '>=');
        }
        else {
            return false;
        }
    }

    /**
     * Check WordPress version
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function wp_version_gte($version)
    {
        $wp_version = get_bloginfo('version');

        if ($wp_version) {
            return version_compare($wp_version, $version, '>=');
        }

        return false;
    }

    /**
     * Check if string contains phrase that starts with a given string
     *
     * @access public
     * @param string $string
     * @param string $phrase
     * @return bool
     */
    public static function string_contains_phrase($string, $phrase)
    {
        return preg_match('/.*(^|\s|#)' . preg_quote($phrase) . '.*/i', $string) === 1 ? true : false;
    }

    /**
     * Get list of roles assigned to current user
     *
     * @access public
     * @return array
     */
    public static function current_user_roles()
    {
        // User is not logged in
        if (!is_user_logged_in()) {
            return array();
        }

        // Get user roles
        $current_user = wp_get_current_user();
        return $current_user->roles;
    }

    /**
     * Get list of capabilities assigned to current user
     *
     * @access public
     * @return array
     */
    public static function current_user_capabilities()
    {
        // Groups plugin active?
        if (class_exists('Groups_User') && class_exists('Groups_Wordpress')) {
            $groups_user = new Groups_User(get_current_user_id());

            if ($groups_user) {
                return $groups_user->capabilities_deep;
            }
            else {
                return array();
            }
        }

        // Get regular WP capabilities
        else {

            $current_user = wp_get_current_user();
            $all_current_user_capabilities = $current_user->allcaps;
            $current_user_capabilities = array();

            if (is_array($all_current_user_capabilities)) {
                foreach ($all_current_user_capabilities as $capability => $status) {
                    if ($status) {
                        $current_user_capabilities[] = $capability;
                    }
                }
            }

            return $current_user_capabilities;
        }
    }

    /**
     * Download file uploaded via custom field
     *
     * @access public
     * @return void
     */
    public function file_download()
    {
        global $wpdb;

        // No data provided?
        if (empty($_GET['wccf_file_download']) || empty($_GET['post_id']) || empty($_GET['field_key'])) {
            exit;
        }

        $access_granted = false;

        // Check if current user can download uploaded files
        if (self::is_admin()) {
            $access_granted = true;
        }

        // Checkout files can also be viewed by customers to whom that order belongs
        if (is_user_logged_in() && $_GET['wccf_file_download'] === 'wccf_checkout') {
            if (self::user_owns_order(get_current_user_id(), $_GET['post_id'])) {
                $access_granted = true;
            }
        }

        // Product files can also be viewed by customers to whom that order belongs
        if (is_user_logged_in() && $_GET['wccf_file_download'] === 'wccf') {

            // post_id is order id (legacy)
            if (get_post_type($_GET['post_id']) === 'shop_order') {

                // Check if user owns order
                if (RP_WCCF::user_owns_order(get_current_user_id(), $_GET['post_id'])) {
                    $access_granted = true;
                }
            }
            // post_id is order item id
            else {

                // Get order id
                $order_id = $wpdb->get_var($wpdb->prepare("
                    SELECT order_id
                    FROM {$wpdb->prefix}woocommerce_order_items
                    WHERE order_item_id = %d
                ", absint($_GET['post_id'])));

                // Check if user owns order
                if (!empty($_GET['post_id']) && RP_WCCF::user_owns_order(get_current_user_id(), $order_id)) {
                    $access_granted = true;
                }
            }
        }

        // Also temporary grant access for all product_admin and order files - will recheck that after we load fields
        if (in_array($_GET['wccf_file_download'], array('wccf_product_admin', 'wccf_order'))) {
            $access_granted = true;
        }

        // Access not granted?
        if (!$access_granted) {
            exit;
        }

        // Get fields for this post
        if ($_GET['wccf_file_download'] === 'wccf') {

            // Get stored fields from order item meta
            $fields = wc_get_order_item_meta($_GET['post_id'], '_wccf', true);

            // Also look in post meta (versions earlier than 1.2 saved it in wrong location)
            if (empty($fields)) {
                $fields = get_post_meta($_GET['post_id'], '_wccf', true);
            }
        }
        else {
            $fields = get_post_meta($_GET['post_id'], '_' . $_GET['wccf_file_download'], true);
        }

        // No fields?
        if (empty($fields) || !is_array($fields)) {
            exit;
        }

        // Iterate over fields
        foreach ($fields as $field) {
            if ($field['key'] === $_GET['field_key'] && !empty($field['file'])) {

                // Check access for order and product admin fields
                if (in_array($_GET['wccf_file_download'], array('wccf_product_admin', 'wccf_order')) && empty($field['public']) && !RP_WCCF::is_admin()) {
                    exit;
                }

                // Push file to browser
                if ($fp = fopen($field['file']['path'], 'rb')) {
                    header('Content-Type: ' . $field['file']['type']);
                    header('Content-Length: ' . filesize($field['file']['path']));
                    header('Content-disposition: attachment; filename="' . $field['file']['name'] . '"');
                    fpassthru($fp);
                }

                exit;
            }
        }

        exit;
    }

    /**
     * Delete file uploaded via custom field
     *
     * @access public
     * @return void
     */
    public function file_delete()
    {
        $redirect_url = admin_url('post.php?post=' . $_GET['post_id'] . '&action=edit');

        // Check if current user can delete uploaded files
        if (!self::is_admin()) {
            wp_redirect($redirect_url);
            exit;
        }

        // No data provided?
        if (empty($_GET['wccf_file_delete']) || empty($_GET['post_id']) || empty($_GET['field_key'])) {
            wp_redirect($redirect_url);
            exit;
        }

        // Get fields for this post
        $fields = (array) get_post_meta($_GET['post_id'], '_' . $_GET['wccf_file_delete'], true);

        // No fields?
        if (empty($fields)) {
            wp_redirect($redirect_url);
            exit;
        }

        // Iterate over fields and find the one that needs to be deleted
        foreach ($fields as $key => $field) {
            if ($field['key'] === $_GET['field_key'] && !empty($field['file'])) {

                // Delete file from file system
                if (!empty($new_fields[$key]['file']['path']) && file_exists($new_fields[$key]['file']['path'])) {
                    unlink($new_fields[$key]['file']['path']);
                }

                // Delete file field data from database
                $new_fields = $fields;
                unset($new_fields[$key]);
                update_post_meta($_GET['post_id'], '_' . $_GET['wccf_file_delete'], $new_fields);

                // Redirect user to post edit page
                wp_redirect($redirect_url);
                exit;
            }
        }

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Get date format
     *
     * @access public
     * @param bool $is_frontend
     * @return string
     */
    public static function get_date_format($is_frontend = false)
    {
        // Define formats available in settings
        $formats = array(
            '0' => $is_frontend ? 'm/d/y' : 'n/j/y',
            '1' => $is_frontend ? 'm/d/yy' : 'n/j/Y',
            '2' => $is_frontend ? 'd/m/y' : 'j/n/y',
            '3' => $is_frontend ? 'd/m/yy' : 'j/n/Y',
            '4' => $is_frontend ? 'y-mm-dd' : 'y-m-d',
            '5' => $is_frontend ? 'yy-mm-dd' : 'Y-m-d',
            '6' => $is_frontend ? 'dd.mm.yy' : 'd.m.Y',
            '7' => $is_frontend ? 'dd-mm-yy' : 'd-m-Y',
        );

        return apply_filters('wccf_date_format', $formats[self::option('date_format')], $is_frontend);
    }

    /**
     * Check if value is date with correct format
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function is_date($value)
    {
        $is_date = false;

        // Maybe we have a newer PHP version?
        if (version_compare(phpversion(), '5.3', '>=')) {

            // Initialize DateTime object
            $datetime = DateTime::createFromFormat(self::get_date_format(), $value, self::get_time_zone());

            // Check if dates correspond
            if ($datetime && $datetime->format(self::get_date_format()) === $value) {
                $is_date = true;
            }
        }

        // Unfortunately...
        else {

            // Remember current time zone and set ours (needed for date() function)
            $previous_timezone = @date_default_timezone_get();
            date_default_timezone_set(self::get_time_zone_string());

            // Check if date is valid
            if ($timestamp = strtotime($value)) {
                if (date(self::get_date_format(), $timestamp) === $value) {
                    $is_date = true;
                }
            }

            // Revert to previous default time zone
            date_default_timezone_set($previous_timezone);
        }

        return $is_date;
    }

    /**
     * Get timezone object
     *
     * @access public
     * @return object
     */
    public static function get_time_zone()
    {
        return new DateTimeZone(self::get_time_zone_string());
    }

    /**
     * Get timezone string
     *
     * @access public
     * @return string
     */
    public static function get_time_zone_string()
    {
        if ($time_zone = get_option('timezone_string')) {
            return $time_zone;
        }

        if ($utc_offset = get_option('gmt_offset')) {

            $utc_offset = $utc_offset * 3600;
            $dst = date('I');

            // Try to get timezone name from offset
            if ($time_zone = timezone_name_from_abbr('', $utc_offset)) {
                return $time_zone;
            }

            // Try to guess timezone by looking at a list of all timezones
            foreach (timezone_abbreviations_list() as $abbreviation) {
                foreach ($abbreviation as $city) {
                    if ($city['dst'] == $dst && $city['offset'] == $utc_offset) {
                        return $city['timezone_id'];
                    }
                }
            }
        }

        return 'UTC';
    }

    /**
     * Get optimized lowercase locale with dash as a separator
     *
     * @access public
     * @param string $method
     *    - single - return first part of the locale only
     *    - double - return both parts of the locale only
     *    - mixed - return first part if both locales match and both parts if they differ
     * @return string
     */
    public static function get_optimized_locale($method = 'single')
    {
        // Split WordPress locale
        $parts = explode('_', get_locale());

        // Expected result?
        if (is_array($parts) && count($parts) == 2 && $parts[1] != 'US') {
            $first = strtolower($parts[0]);
            $second = strtolower($parts[1]);

            // Single, double or mixed?
            if ($method == 'single') {
                return $first;
            }
            else if ($method == 'double') {
                return $first . '-' . $second;
            }
            else if ($method == 'mixed') {
                return $first == $second ? $first : $first . '-' . $second;
            }
        }

        // Fallback
        return $method == 'double' ? 'en_en' : 'en';
    }

    /**
     * Get jQuery UI Datepicker config
     *
     * @access public
     * @param string $context
     * @return array
     */
    public static function get_datepicker_config($context)
    {
        return apply_filters('wccf_datepicker_config', array(
            'dateFormat' => self::get_date_format(true),
        ), $context);
    }

    /**
     * Maybe add enctype attribute to form tag
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function maybe_add_enctype_attribute($post)
    {
        // Neither order, nor product?
        if (!in_array($post->post_type, array('shop_order', 'product'))) {
            return;
        }

        // Get field context
        $context = $post->post_type === 'shop_order' ? 'order' : 'product_admin';

        // Iterate over fields of this context
        foreach (self::get_fields($context) as $field) {
            if ($field['type'] === 'file') {
                echo ' enctype="multipart/form-data" ';
            }
        }
    }

    /**
     * Add WooCommerce error
     *
     * @access public
     * @param string $message
     * @return void
     */
    public static function add_woocommerce_error($message)
    {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($message, 'error');
        }
        else {
            global $woocommerce;
            $woocommerce->add_error($message);
        }
    }

    /**
     * Check if current request is for a plugin's settings page
     *
     * @access public
     * @return bool
     */
    public static function is_settings_page()
    {
        return preg_match('/page=woocommerce_custom_fields/i', $_SERVER['QUERY_STRING']);
    }

    /**
     * Check if environment meets requirements
     *
     * @access public
     * @return bool
     */
    public static function check_environment()
    {
        $is_ok = true;

        // Check WordPress version
        if (!self::wp_version_gte(RP_WCCF_SUPPORT_WP)) {
            add_action('admin_notices', array('RP_WCCF', 'wp_version_notice'));
            $is_ok = false;
        }

        // Check if WooCommerce is enabled
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array('RP_WCCF', 'wc_disabled_notice'));
            $is_ok = false;
        }
        else if (!self::wc_version_gte(RP_WCCF_SUPPORT_WC)) {
            add_action('admin_notices', array('RP_WCCF', 'wc_version_notice'));
            $is_ok = false;
        }

        return $is_ok;
    }

    /**
     * Display WP version notice
     *
     * @access public
     * @return void
     */
    public static function wp_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires WordPress version %s or later. Please update WordPress to use this plugin.', 'rp_wccf'), RP_WCCF_SUPPORT_WP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://support.rightpress.net/hc/en-us/requests/new">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Display WC disabled notice
     *
     * @access public
     * @return void
     */
    public static function wc_disabled_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires WooCommerce to be activate. You can download WooCommerce %s.', 'rp_wccf'), '<a href="http://www.woothemes.com/woocommerce/">' . __('here', 'rp_wccf') . '</a>') . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://support.rightpress.net/hc/en-us/requests/new">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Display WC version notice
     *
     * @access public
     * @return void
     */
    public static function wc_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires WooCommerce version %s or later. Please update WooCommerce to use this plugin.', 'rp_wccf'), RP_WCCF_SUPPORT_WC) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://support.rightpress.net/hc/en-us/requests/new">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Get array of term ids - parent term id and all children ids
     *
     * @access public
     * @param int $id
     * @param string $taxonomy
     * @return array
     */
    public static function get_term_with_children($id, $taxonomy)
    {
        $term_ids = array();

        // Check if term exists
        if (!get_term_by('id', $id, $taxonomy)) {
            return $term_ids;
        }

        // Store parent
        $term_ids[] = $id;

        // Get and store children
        $children = get_term_children($id, $taxonomy);
        $term_ids = array_unique(array_merge($term_ids, $children));

        return $term_ids;
    }

    /**
     * Get admin capability
     *
     * @access public
     * @return string
     */
    public static function get_admin_capability()
    {
        return apply_filters('rp_wccf_capability', 'manage_options');
    }

    /**
     * Check if current user is admin or it's equivalent (shop manager etc)
     *
     * @access public
     * @return bool
     */
    public static function is_admin()
    {
        return current_user_can(self::get_admin_capability());
    }

    /**
     * Get file upload directory
     *
     * @access public
     * @param string $context
     * @param array $field
     * @return mixed
     */
    public static function get_upload_directory($context = 'files', $field = null)
    {
        // Allow developers to change file storage location
        $wp_upload_dir = wp_upload_dir();
        $file_path = untrailingslashit(apply_filters('wccf_file_path', $wp_upload_dir['basedir'], $context, $field));
        $file_path .= '/wccf_' . $context;

        // Set up upload directory
        if (!self::set_up_upload_directory($file_path)) {
            return false;
        }

        // Return directory path
        return $file_path;
    }

    /**
     * Set up file upload directory
     *
     * @access public
     * @return bool
     */
    public static function set_up_upload_directory($file_path)
    {
        $result = true;

        // Create directory if it does not exist yet
        if (!file_exists($file_path)) {
            $result = mkdir($file_path, 0755, true);
        }

        // Protect files from directory listing
        if (!file_exists($file_path . '/index.php')) {
            touch($file_path . '/index.php');
        }

        return $result;
    }

    /**
     * Check if user owns order
     *
     * @access public
     * @param int $user_id
     * @param int $order_id
     * @return bool
     */
    public static function user_owns_order($user_id, $order_id)
    {
        // Load order object
        $order = RP_WCCF::wc_version_gte('2.2') ? wc_get_order($order_id) : new WC_Order($order_id);

        // Check if order was loaded and contains order user id
        if ($order && !empty($order->customer_user)) {

            // Check if order belongs to user
            if ((int) $user_id === (int) $order->customer_user) {
                return true;
            }
        }

        return false;
    }

}

RP_WCCF::get_instance();

}
