<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to Custom Field Conditions
 *
 * @class RP_WCCF_Conditions
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('RP_WCCF_Conditions')) {

class RP_WCCF_Conditions
{
    private static $conditions;

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

        // Ajax handlers
        add_action('wp_ajax_rp_wccf_load_multiselect_items', array($this, 'ajax_load_multiselect_items'));
    }

    /**
     * On init action
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Define conditions
        self::$conditions = array(

            // Customer
            'customer' => array(
                'label'     => __('Customer', 'rp_wccf'),
                'children'  => array(

                    // Is logged in
                    'is_logged_in' => array(
                        'label'         => __('Is logged in', 'rp_wccf'),
                        'method'        => 'yes_no',
                        'display'       => array('product', 'checkout'),
                        'uses_fields'   => array(),
                    ),

                    // Role
                    'role' => array(
                        'label'         => __('Role', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('product', 'checkout'),
                        'uses_fields'   => array('roles'),
                    ),

                    // Capability
                    'capability' => array(
                        'label'         => __('Capability', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('product', 'checkout'),
                        'uses_fields'   => array('capabilities'),
                    ),

                    // Role
                    'order_role' => array(
                        'label'         => __('Role', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('order'),
                        'uses_fields'   => array('roles'),
                    ),

                    // Capability from order
                    'order_capability' => array(
                        'label'         => __('Capability', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('order'),
                        'uses_fields'   => array('capabilities'),
                    ),
                ),
            ),

            // Product
            'product' => array(
                'label'     => __('Product', 'rp_wccf'),
                'children'  => array(

                    // Product
                    'product' => array(
                        'label'         => __('Product', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('product', 'product_admin'),
                        'uses_fields'   => array('products'),
                    ),

                    // Product category
                    'product_category' => array(
                        'label'         => __('Product category', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('product', 'product_admin'),
                        'uses_fields'   => array('product_categories'),
                    ),

                    // Product type
                    'product_type' => array(
                        'label'         => __('Product type', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('product', 'product_admin'),
                        'uses_fields'   => array('product_types'),
                    ),
                ),
            ),

            // Cart
            'cart' => array(
                'label'     => __('Cart', 'rp_wccf'),
                'children'  => array(

                    // Subtotal
                    'subtotal' => array(
                        'label'         => __('Cart subtotal', 'rp_wccf'),
                        'method'        => 'at_least_less_than',
                        'display'       => array('checkout'),
                        'uses_fields'   => array('decimal'),
                    ),

                    // Products in cart
                    'products_in_cart' => array(
                        'label'         => __('Products in cart', 'rp_wccf'),
                        'method'        => 'at_least_one_all_none',
                        'display'       => array('checkout'),
                        'uses_fields'   => array('products'),
                    ),

                    // Product categories in cart
                    'product_categories_in_cart' => array(
                        'label'         => __('Product categories in cart', 'rp_wccf'),
                        'method'        => 'at_least_one_all_none',
                        'display'       => array('checkout'),
                        'uses_fields'   => array('product_categories'),
                    ),
                ),
            ),

            // Order
            'order' => array(
                'label'     => __('Order', 'rp_wccf'),
                'children'  => array(

                    // Total
                    'total' => array(
                        'label'         => __('Order total', 'rp_wccf'),
                        'method'        => 'at_least_less_than',
                        'display'       => array('order'),
                        'uses_fields'   => array('decimal'),
                    ),

                    // Products in order
                    'products_in_order' => array(
                        'label'         => __('Products in order', 'rp_wccf'),
                        'method'        => 'at_least_one_all_none',
                        'display'       => array('order'),
                        'uses_fields'   => array('products'),
                    ),

                    // Product categories in order
                    'product_categories_in_order' => array(
                        'label'         => __('Product categories in order', 'rp_wccf'),
                        'method'        => 'at_least_one_all_none',
                        'display'       => array('order'),
                        'uses_fields'   => array('product_categories'),
                    ),

                    // Payment method
                    'payment_method' => array(
                        'label'         => __('Payment method', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('order'),
                        'uses_fields'   => array('payment_methods'),
                    ),

                    // Shipping method
                    'shipping_method' => array(
                        'label'         => __('Shipping method', 'rp_wccf'),
                        'method'        => 'in_list_not_in_list',
                        'display'       => array('order'),
                        'uses_fields'   => array('shipping_methods'),
                    ),
                ),
            ),

            // Custom Field
            'custom_field' => array(
                'label'     => __('Custom Field', 'rp_wccf'),
                'children'  => array(

                    // Other custom field
                    'other_custom_field' => array(
                        'label'         => __('Other custom field', 'rp_wccf'),
                        'method'        => 'other_custom_field',
                        'display'       => array('product', 'product_admin', 'checkout', 'order'),
                        'uses_fields'   => array('other_field_key', 'text'),
                    ),
                ),
            ),
        );
    }

    /**
     * Get condition group and option from group_option string
     *
     * @access public
     * @param string $group_and_option
     * @return mixed
     */
    public static function extract_group_and_option($group_and_option)
    {
        $group_key = null;

        foreach (self::$conditions as $potential_group_key => $potential_group) {
            if (strpos($group_and_option, $potential_group_key) === 0) {
                $group_key = $potential_group_key;
            }
        }

        if ($group_key === null) {
            return false;
        }

        $option_key = preg_replace('/^' . $group_key . '_/i', '', $group_and_option);

        return array($group_key, $option_key);
    }

    /**
     * Return conditions for display in admin ui
     *
     * @access public
     * @param string $context
     * @return array
     */
    public static function conditions($context = 'product')
    {
        $result = array();

        // Iterate over all conditions groups
        foreach (self::$conditions as $group_key => $group) {

            // Iterate over conditions
            foreach ($group['children'] as $condition_key => $condition) {

                // Skip current condition if it's not usable in current context
                if (!in_array($context, $condition['display'])) {
                    continue;
                }

                // Add group if needed
                if (!isset($result[$group_key])) {
                    $result[$group_key] = array(
                        'label'     => $group['label'],
                        'options'  => array(),
                    );
                }

                // Push condition to group
                $result[$group_key]['options'][$condition_key] = $condition['label'];
            }
        }

        return $result;
    }

    /**
     * Return methods of particular condition for display in admin ui
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @return array
     */
    public static function methods($group, $condition)
    {
        switch (self::$conditions[$group]['children'][$condition]['method']) {

            // yes, no
            case 'yes_no':
                return array(
                    'yes'   => __('yes', 'rp_wccf'),
                    'no'    => __('no', 'rp_wccf'),
                );

            // in list, not in list
            case 'in_list_not_in_list':
                return array(
                    'in_list'       => __('in list', 'rp_wccf'),
                    'not_in_list'   => __('not in list', 'rp_wccf'),
                );

            // at least, less than
            case 'at_least_less_than':
                return array(
                    'at_least'  => __('at least', 'rp_wccf'),
                    'less_than' => __('less than', 'rp_wccf'),
                );

            // at least one, all, none
            case 'at_least_one_all_none':
                return array(
                    'at_least_one'  => __('at least one of selected', 'rp_wccf'),
                    'all'           => __('all of selected', 'rp_wccf'),
                    'none'          => __('none of selected', 'rp_wccf'),
                );

            // is empty, is not empty, contains, does not contain, equals, does not equal etc
            case 'other_custom_field':
                return array(
                    'is_empty'          => __('is empty', 'rp_wccf'),
                    'is_not_empty'      => __('is not empty', 'rp_wccf'),
                    'contains'          => __('contains', 'rp_wccf'),
                    'does_not_contain'  => __('does not contain', 'rp_wccf'),
                    'equals'            => __('equals', 'rp_wccf'),
                    'does_not_equal'    => __('does not equal', 'rp_wccf'),
                    'less_than'         => __('less than', 'rp_wccf'),
                    'less_or_equal_to'  => __('less or equal to', 'rp_wccf'),
                    'more_than'         => __('more than', 'rp_wccf'),
                    'more_or_equal'     => __('more or equal to', 'rp_wccf'),
                    'is_checked'        => __('is checked', 'rp_wccf'),
                    'is_not_checked'    => __('is not checked', 'rp_wccf'),
                );

            default:
                return array();
        }
    }

    /**
     * Get field size
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @return string
     */
    public static function field_size($group, $condition)
    {
        // Special case for custom_field_other_custom_field (width changed dynamically via JS)
        if ($group == 'custom_field' && $condition == 'other_custom_field') {
            return 'double';
        }

        // All other cases
        switch (count(self::$conditions[$group]['children'][$condition]['uses_fields'])) {
            case 2:
                return 'single';
            case 1:
                return 'double';
            default:
                return 'triple';
        }
    }

    /**
     * Check if condition uses field
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @param string $field
     * @return bool
     */
    public static function uses_field($group, $condition, $field)
    {
        return in_array($field, self::$conditions[$group]['children'][$condition]['uses_fields']);
    }

    /**
     * Load items for multiselect fields based on search criteria
     *
     * @access public
     * @return void
     */
    public function ajax_load_multiselect_items()
    {
        // Define data types that we are aware of
        $types = array(
            'roles', 'capabilities', 'products', 'product_categories',
            'product_types', 'payment_methods', 'shipping_methods'
        );

        // Make sure we know the type which is requested and query is not empty
        if (!in_array($_POST['type'], $types) || empty($_POST['query'])) {
            $results[] = array(
                'id'        => 0,
                'text'      => __('No search query sent', 'rp_wccf'),
                'disabled'  => 'disabled'
            );
        }

        // Get items
        $selected = isset($_POST['selected']) && is_array($_POST['selected']) ? $_POST['selected'] : array();
        $results = $this->get_items($_POST['type'], $_POST['query'], $selected);

        // No items?
        if (empty($results)) {
            $results[] = array(
                'id'        => 0,
                'text'      => __('Nothing found', 'rp_wccf'),
                'disabled'  => 'disabled'
            );
        }

        // Return data and exit
        echo json_encode(array('results' => $results));
        exit;
    }

    /**
     * Load items for multiselect fields based on search criteria and item type
     *
     * @access public
     * @param string $type
     * @param string $query
     * @param array $selected
     * @return array
     */
    public function get_items($type, $query, $selected)
    {
        $items = array();

        // Get items by type
        $method = 'get_' . $type;
        $all_items = $this->$method($selected);

        // Iterate over returned items
        foreach ($all_items as $item_key => $item) {

            // Filter items that match search criteria
            if (RP_WCCF::string_contains_phrase($item['text'], $query)) {

                // Filter items that are not yet selected
                if (empty($selected) || !in_array($item['id'], $selected)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Load already selected multiselect field items by their ids
     *
     * @access public
     * @param string $type
     * @param array $ids
     * @return array
     */
    public static function get_items_by_ids($type, $ids = array())
    {
        $method = 'get_' . $type;
        $rp_wccf_conditions = new self();
        return $rp_wccf_conditions->$method(array(), $ids);
    }

    /**
     * Load roles for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_roles($selected, $ids = array())
    {
        $items = array();

        // Get roles
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // Iterate over roles and format results array
        foreach ($wp_roles->get_names() as $role_key => $role) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($role_key, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $role_key,
                'text'  => $role . ' (' . $role_key . ')',
            );
        }

        return $items;
    }

    /**
     * Load capabilities for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_capabilities($selected, $ids = array())
    {
        $items = array();

        // Groups plugin active?
        if (class_exists('Groups_User') && class_exists('Groups_Wordpress') && function_exists('_groups_get_tablename')) {

            global $wpdb;
            $capability_table = _groups_get_tablename('capability');
            $all_capabilities = $wpdb->get_results('SELECT capability FROM ' . $capability_table);

            if ($all_capabilities) {
                foreach ($all_capabilities as $capability) {

                    // Skip this item if we don't need it
                    if (!empty($ids) && !in_array($capability, $ids)) {
                        continue;
                    }

                    // Add item
                    $items[] = array(
                        'id'    => $capability->capability,
                        'text'  => $capability->capability
                    );
                }
            }
        }

        // Get standard WP capabilities
        else {
            global $wp_roles;

            if (!isset($wp_roles)) {
                get_role('administrator');
            }

            $roles = $wp_roles->roles;

            $already_added = array();

            if (is_array($roles)) {
                foreach ($roles as $rolename => $atts) {
                    if (isset($atts['capabilities']) && is_array($atts['capabilities'])) {
                        foreach ($atts['capabilities'] as $capability => $value) {
                            if (!in_array($capability, $already_added)) {

                                // Skip this item if we don't need it
                                if (!empty($ids) && !in_array($capability, $ids)) {
                                    continue;
                                }

                                // Add item
                                $items[] = array(
                                    'id'    => $capability,
                                    'text'  => $capability
                                );
                                $already_added[] = $capability;
                            }
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Load products for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_products($selected, $ids = array())
    {
        $items = array();

        // Get all product ids
        // TBD: optimize this by adding a $query contraint
        $args = array(
            'posts_per_page'    => -1,
            'post_type'         => 'product',
            'post_status'       => array('publish', 'pending', 'draft', 'future', 'private', 'inherit'),
            'fields'            => 'ids',
        );

        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        $posts_raw = get_posts($args);

        // Format results array
        foreach ($posts_raw as $post_id) {
            $items[] = array(
                'id'    => $post_id,
                'text'  => '#' . $post_id . ' ' . get_the_title($post_id)
            );
        }

        return $items;
    }

    /**
     * Load product categories for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_product_categories($selected, $ids = array())
    {
        $items = array();

        $post_categories_raw = get_terms(array('product_cat'), array('hide_empty' => 0));
        $post_categories_raw_count = count($post_categories_raw);

        foreach ($post_categories_raw as $post_cat_key => $post_cat) {
            $category_name = $post_cat->name;

            if ($post_cat->parent) {
                $parent_id = $post_cat->parent;
                $has_parent = true;

                // Make sure we don't have an infinite loop here (happens with some kind of "ghost" categories)
                $found = false;
                $i = 0;

                while ($has_parent && ($i < $post_categories_raw_count || $found)) {

                    // Reset each time
                    $found = false;
                    $i = 0;

                    foreach ($post_categories_raw as $parent_post_cat_key => $parent_post_cat) {

                        $i++;

                        if ($parent_post_cat->term_id == $parent_id) {
                            $category_name = $parent_post_cat->name . ' → ' . $category_name;
                            $found = true;

                            if ($parent_post_cat->parent) {
                                $parent_id = $parent_post_cat->parent;
                            }
                            else {
                                $has_parent = false;
                            }

                            break;
                        }
                    }
                }
            }

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($post_cat->term_id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $post_cat->term_id,
                'text'  => $category_name
            );
        }

        return $items;
    }

    /**
     * Load product types for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_product_types($selected, $ids = array())
    {
        $items = array();

        // Fetch data
        foreach (RP_WCCF_Product::get_product_types() as $type_key => $type) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($type_key, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $type_key,
                'text'  => $type . ' (' . $type_key . ')',
            );
        }

        return $items;
    }

    /**
     * Load payment methods for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_payment_methods($selected, $ids = array())
    {
        $items = array();

        // Fetch data
        foreach (WC()->payment_gateways->payment_gateways() as $gateway) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($gateway->id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $gateway->id,
                'text'  => $gateway->get_title() . ' (' . $gateway->id . ')',
            );
        }

        return $items;
    }

    /**
     * Load shipping methods for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public function get_shipping_methods($selected, $ids = array())
    {
        $items = array();

        // Fetch data
        foreach (WC()->shipping->load_shipping_methods() as $method) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($method->id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $method->id,
                'text'  => $method->get_title() . ' (' . $method->id . ')',
            );
        }

        return $items;
    }

    /**
     * Filter out fields that do not match conditions
     * Also determines conditions that need to be passed to Javascript
     *
     * @access public
     * @param array $all_fields
     * @param string $context
     * @param mixed $checkout_position
     * @param int $product_id
     * @return array
     */
    public static function filter_by_conditions($all_fields, $context, $checkout_position = null, $product_id = null)
    {
        $fields = array();

        // Iterate over passed fields
        foreach ($all_fields as $field_key => $field) {

            // Filter by checkout position
            if ($checkout_position !== null) {
                if (!isset($field['position']) || $field['position'] !== $checkout_position) {
                    continue;
                }
            }

            // Track conditions that need to be sent to frontend
            $frontend_conditions = array();

            // No conditions configured for this field
            if (!isset($field['conditions']) || !is_array($field['conditions']) || empty($field['conditions'])) {
                $fields[] = $field;
                continue;
            }

            // Track if we need to add this condition
            $is_ok = true;

            // Iterate over conditions
            foreach ($field['conditions'] as $condition_key => $condition) {

                // Save and skip frontend conditions
                if ($condition['type'] === 'custom_field_other_custom_field') {
                    $frontend_conditions[] = $condition;
                    continue;
                }

                // Check if condition is matched
                if (!self::condition_is_matched($condition, $product_id)) {
                    $is_ok = false;
                    break;
                }
            }

            // Maybe add this field to a set of fields for return
            if ($is_ok) {
                $field['frontend_conditions'] = $frontend_conditions;
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Check if a single condition is matched
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_is_matched($condition, $product_id = null)
    {
        $method = 'condition_check_' . $condition['type'];
        return self::$method($condition, $product_id);
    }

    /**
     * Condition check: Customer is logged in
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_customer_is_logged_in($condition, $product_id = null)
    {
        return $condition['customer_is_logged_in_method'] === 'no' ? !is_user_logged_in() : is_user_logged_in();
    }

    /**
     * Condition check: Customer role
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_customer_role($condition, $product_id = null)
    {
        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_role_method'], RP_WCCF::current_user_roles(), $condition['roles']);
    }

    /**
     * Condition check: Customer capability
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_customer_capability($condition, $product_id = null)
    {
        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_capability_method'], RP_WCCF::current_user_capabilities(), $condition['capabilities']);
    }

    /**
     * Condition check: Product product
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_product_product($condition, $product_id = null)
    {
        // Get product id
        $product_id = RP_WCCF_Product::get_product_id($product_id);

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_method'], $product_id, $condition['products']);
    }

    /**
     * Condition check: Product product category
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_product_product_category($condition, $product_id = null)
    {
        // Get product id
        $product_id = RP_WCCF_Product::get_product_id($product_id);

        // Store categories of current product
        $categories = array();

        // Get categories
        if ($product_id !== null) {

            $product_categories = get_the_terms($product_id, 'product_cat');

            if (!empty($product_categories) && is_array($product_categories)) {
                foreach ($product_categories as $category) {
                    $categories[] = $category->term_id;
                }
            }
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_category_method'], $categories, $condition['product_categories']);
    }

    /**
     * Condition check: Product product type
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_product_product_type($condition, $product_id = null)
    {
        // Get product id
        $product_id = RP_WCCF_Product::get_product_id($product_id);

        // Get product type
        if ($product_id !== null) {
            $product = RP_WCCF::wc_version_gte('2.2') ? wc_get_product($product_id) : get_product($product_id);
            $product_type = $product->product_type;
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_type_method'], $product_type, $condition['product_types']);
    }

    /**
     * Condition check: Cart subtotal
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_cart_subtotal($condition, $product_id = null)
    {
        global $woocommerce;
        $subtotal = $woocommerce->cart->tax_display_cart === 'excl' ? $woocommerce->cart->subtotal_ex_tax : $woocommerce->cart->subtotal;

        // Check condition
        return self::compare_at_least_less_than($condition['cart_subtotal_method'], $subtotal, $condition['decimal']);
    }

    /**
     * Condition check: Cart products in cart
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_cart_products_in_cart($condition, $product_id = null)
    {
        global $woocommerce;
        $products_in_cart = array();

        foreach ($woocommerce->cart->cart_contents as $cart_item) {
            $products_in_cart[] = $cart_item['data']->id;
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_products_in_cart_method'], $products_in_cart, $condition['products']);
    }

    /**
     * Condition check: Cart product categories in cart
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_cart_product_categories_in_cart($condition, $product_id = null)
    {
        global $woocommerce;
        $product_categories_in_cart = array();
        $condition_categories_split = array();
        $condition_categories = array();

        foreach ($woocommerce->cart->cart_contents as $cart_item) {
            $item_categories = wp_get_post_terms($cart_item['data']->id, 'product_cat');

            if (!empty($item_categories) && is_array($item_categories)) {
                foreach ($item_categories as $category) {
                    if (!in_array($category->term_id, $product_categories_in_cart)) {
                        $product_categories_in_cart[] = $category->term_id;
                    }
                }
            }
        }

        // Check if condition categories are set
        if (!empty($condition['product_categories']) && is_array($condition['product_categories'])) {

            // Get condition product categories including child categories split by parent
            foreach ($condition['product_categories'] as $category_id) {
                $condition_categories_split[$category_id] = RP_WCCF::get_term_with_children($category_id, 'product_cat');
            }

            // Get condition product categories
            $condition_categories = self::merge_all_children($condition_categories_split);
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_product_categories_in_cart_method'], $product_categories_in_cart, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Customer role from order
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_customer_order_role($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $user = get_userdata($order->customer_user);
        $roles = $user ? (array) $user->roles : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_role_method'], $roles, $condition['roles']);
    }

    /**
     * Condition check: Customer capability from order
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_customer_order_capability($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $user = get_userdata($order->customer_user);
        $capabilities = array();

        if ($user) {
            foreach ($user->allcaps as $capability => $status) {
                if ($status) {
                    $capabilities[] = $capability;
                }
            }
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_capability_method'], $capabilities, $condition['capabilities']);
    }

    /**
     * Condition check: Order total
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_order_total($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $total = $order->get_total();

        // Check condition
        return self::compare_at_least_less_than($condition['order_total_method'], $total, $condition['decimal']);
    }

    /**
     * Condition check: Order products in order
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_order_products_in_order($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $products_in_order = array();

        foreach ($order->get_items() as $order_item) {
            if (!in_array($order_item['product_id'], $products_in_order)) {
                $products_in_order[] = $order_item['product_id'];
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_products_in_order_method'], $products_in_order, $condition['products']);
    }

    /**
     * Condition check: Order product categories in order
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_order_product_categories_in_order($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $product_categories_in_order = array();
        $condition_categories_split = array();
        $condition_categories = array();

        foreach ($order->get_items() as $order_item) {
            $item_categories = wp_get_post_terms($order_item['product_id'], 'product_cat');

            if (!empty($item_categories) && is_array($item_categories)) {
                foreach ($item_categories as $category) {
                    if (!in_array($category->term_id, $product_categories_in_order)) {
                        $product_categories_in_order[] = $category->term_id;
                    }
                }
            }
        }

        // Check if condition categories are set
        if (!empty($condition['product_categories']) && is_array($condition['product_categories'])) {

            // Get condition product categories including child categories split by parent
            foreach ($condition['product_categories'] as $category_id) {
                $condition_categories_split[$category_id] = RP_WCCF::get_term_with_children($category_id, 'product_cat');
            }

            // Get condition product categories
            $condition_categories = self::merge_all_children($condition_categories_split);
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_product_categories_in_order_method'], $product_categories_in_order, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Order payment method
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_order_payment_method($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $payment_method = $order->payment_method;

        // Check condition
        return self::compare_in_list_not_in_list($condition['order_payment_method_method'], $payment_method, $condition['payment_methods']);
    }

    /**
     * Condition check: Order shipping method
     *
     * @access public
     * @param array $condition
     * @param int $product_id
     * @return bool
     */
    public static function condition_check_order_shipping_method($condition, $product_id = null)
    {
        global $post;

        $order = new WC_Order($post->ID);
        $shipping_method = $order->shipping_method;

        // Check condition
        return self::compare_in_list_not_in_list($condition['order_shipping_method_method'], $shipping_method, $condition['shipping_methods']);
    }

    /**
     * Check if frontend conditions were matched
     *
     * @access public
     * @param array $fields
     * @param array $field
     * @return bool
     */
    public static function frontend_conditions_match($fields, $field)
    {
        // Check if we have any conditions and iterate over them
        if (!empty($field['conditions'])) {
            foreach ($field['conditions'] as $condition) {

                // Other custom field
                if ($condition['type'] === 'custom_field_other_custom_field') {
                    if (!self::frontend_condition_match_other_custom_field($condition, $fields)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check if frontend condition Other Custom Field is matched
     *
     * @access public
     * @param array $condition
     * @param array $fields
     * @return bool
     */
    public static function frontend_condition_match_other_custom_field($condition, $fields)
    {
        $method = $condition['custom_field_other_custom_field_method'];

        // Iterate over fields
        foreach ($fields as $field) {

            // Fix field key
            // TBD: this is a quick hack for a bigger problem - we should either have wccf_ everywhere or not at all
            if (preg_match('/^wccf_/i', $condition['other_field_key'])) {
                $field_key = preg_match('/^wccf_/i', $field['key']) ? $field['key'] : 'wccf_' . $field['key'];
            }
            else {
                $field_key = preg_match('/^wccf_/i', $field['key']) ? preg_replace('/^wccf_/i', '', $field['key']) : $field['key'];
            }

            // Not the one that we should be matching against?
            if ($condition['other_field_key'] !== $field_key) {
                continue;
            }

            // Proceed depending on condition method
            switch ($method) {

                // Is Empty
                case 'is_empty':
                    return self::is_empty($field['value']);

                // Is Not Empty
                case 'is_not_empty':
                    return !self::is_empty($field['value']);

                // Contains
                case 'contains':
                    return self::contains($field['value'], $condition['text']);

                // Does Not Contain
                case 'does_not_contain':
                    return !self::contains($field['value'], $condition['text']);

                // Equals
                case 'equals':
                    return self::equals($field['value'], $condition['text']);

                // Does Not Equal
                case 'does_not_equal':
                    return !self::equals($field['value'], $condition['text']);

                // Less Than
                case 'less_than':
                    return self::less_than($field['value'], $condition['text']);

                // Less Or Equal To
                case 'less_or_equal_to':
                    return !self::more_than($field['value'], $condition['text']);

                // More Than
                case 'more_than':
                    return self::more_than($field['value'], $condition['text']);

                // More Or Equal
                case 'more_or_equal':
                    return !self::less_than($field['value'], $condition['text']);

                // Is Checked
                case 'is_checked':
                    return self::is_checked($field['value']);

                // Is Not Checked
                case 'is_not_checked':
                    return !self::is_checked($field['value']);

                default:
                    return true;
            }
        }

        // Target field does not exist - return value depends on whether method is positive or negative
        return in_array($method, array('is_empty', 'does_not_contain', 'does_not_equal', 'more_than', 'is_not_checked')) ? true : false;
    }

    /**
     * Check if value is empty (but not zero)
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_empty($value)
    {
        return ($value === '' || $value === null || count($value) === 0);
    }

    /**
     * Check if value contains string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function contains($value, $string)
    {
        if (gettype($value) === 'array') {
            return in_array($string, $value);
        }
        else {
            return (strpos($value, $string) !== false);
        }

        return false;
    }

    /**
     * Check if value equals string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function equals($value, $string)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value === $string) {
                    return true;
                }
            }
        }
        else {
            return ($value === $string);
        }

        return false;
    }

    /**
     * Check if value is less than number
     *
     * @access public
     * @param mixed $value
     * @param string $number
     * @return bool
     */
    public static function less_than($value, $number)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value < $number) {
                    return true;
                }
            }
        }
        else {
            return ($value < $number);
        }

        return false;
    }

    /**
     * Check if value is more than number
     *
     * @access public
     * @param mixed $value
     * @param string $number
     * @return bool
     */
    public static function more_than($value, $number)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value > $number) {
                    return true;
                }
            }
        }
        else {
            return ($value > $number);
        }

        return false;
    }

    /**
     * Check if value represents field being checked
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_checked($value)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value) {
                    return true;
                }
            }
        }
        else if ($value) {
            return true;
        }

        return false;
    }

    /**
     * Compare list of items with list of elements in conditions
     *
     * @access public
     * @param string $method
     * @param array $items
     * @param array $condition_items
     * @param array $condition_items_split
     * @return bool
     */
    public static function compare_at_least_one_all_none($method, $items, $condition_items, $condition_items_split = array())
    {
        // Make sure items was passed as array
        $items = (array) $items;

        // None
        if ($method === 'none') {
            if (count(array_intersect($items, $condition_items)) == 0) {
                return true;
            }
        }

        // All - regular check
        else if ($method === 'all' && empty($condition_items_split)) {
            if (count(array_intersect($items, $condition_items)) == count($condition_items)) {
                return true;
            }
        }

        // All - special case
        // Check with respect to parent items (e.g. parent categories)
        // This is a special case - we can't simply compare against
        // $condition_items which include child items since this would
        // require for them to also be present in $items
        else if ($method === 'all') {

            // Iterate over all condition items split by parent
            foreach ($condition_items_split as $parent_with_children) {

                // At least one item must match at least one item in parent/children array
                if (count(array_intersect($items, $parent_with_children)) == 0) {
                    return false;
                }
            }

            return true;
        }

        // At least one
        else if (count(array_intersect($items, $condition_items)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if item is in list of items
     *
     * @access public
     * @param string $method
     * @param mixed $items
     * @param array $condition_items
     * @return bool
     */
    public static function compare_in_list_not_in_list($method, $items, $condition_items)
    {
        // Make sure items was passed as array
        $items = (array) $items;

        // Proceed depending on method
        if ($method === 'not_in_list') {
            if (count(array_intersect($items, $condition_items)) == 0) {
                return true;
            }
        }
        else if (count(array_intersect($items, $condition_items)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Text comparison
     *
     * @access public
     * @param string $method
     * @return bool
     */
    public static function compare_text_comparison($method, $text, $condition_text)
    {
        // Text must be set, otherwise there's nothing to compare against
        if (empty($text)) {
            return false;
        }

        // No text set in conditions
        if (empty($condition_text)) {
            return in_array($method, array('equals', 'does_not_contain')) ? false : true;
        }

        // Proceed depending on condition method
        switch ($method) {

            // Equals
            case 'equals':
                return self::equals($text, $condition_text);

            // Does Not Equal
            case 'does_not_equal':
                return !self::equals($text, $condition_text);

            // Contains
            case 'contains':
                return self::contains($text, $condition_text);

            // Does Not Contain
            case 'does_not_contain':
                return !self::contains($text, $condition_text);

            // Begins with
            case 'begins_with':
                return self::begins_with($text, $condition_text);

            // Ends with
            case 'ends_with':
                return self::ends_with($text, $condition_text);

            default:
                return true;
        }
    }

    /**
     * Compare number with another number
     *
     * @access public
     * @param string $method
     * @param int $number
     * @param int $condition_number
     * @return bool
     */
    public static function compare_at_least_less_than($method, $number, $condition_number)
    {
        if ($method === 'less_than') {
            if ($number < $condition_number) {
                return true;
            }
        }
        else if ($number >= $condition_number) {
            return true;
        }

        return false;
    }

    /**
     * Merge all child taxonomy terms from a list split by parent
     *
     * @access public
     * @param array $items_split
     * @return array
     */
    public static function merge_all_children($items_split)
    {
        $items = array();

        // Iterate over parents
        foreach ($items_split as $parent_id => $children) {

            // Add parent to children array
            $children[] = (int) $parent_id;

           // Add unique parent/children to main array
            $items = array_unique(array_merge($items, $children));
        }

        return $items;
    }

}

new RP_WCCF_Conditions();

}
