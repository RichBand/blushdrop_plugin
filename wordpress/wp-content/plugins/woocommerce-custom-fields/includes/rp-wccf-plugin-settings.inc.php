<?php

/*
 * Returns settings for this plugin
 *
 * @return array
 */
if (!function_exists('rp_wccf_plugin_settings')) {
function rp_wccf_plugin_settings()
{
    return array(
        'product' => array(
            'title' => __('Product Fields', 'rp_wccf'),
            'children' => array(
                'product_info' => array(
                    'title' => __('About Product Fields', 'rp_wccf'),
                    'info' => __('Product fields are used to gather additional information from customers about each product. You can also configure product-specific fields in the product edit page.', 'rp_wccf'),
                    'children' => array(),
                 ),
                'product_fields' => array(
                    'title' => __('Fields', 'rp_wccf'),
                    'children' => array(),
                 ),
            ),
        ),
        'product_admin' => array(
            'title' => __('Product Properties', 'rp_wccf'),
            'children' => array(
                'product_admin_info' => array(
                    'title' => __('About Product Properties', 'rp_wccf'),
                    'info' => __('Product properties are used to provide additional product related information to your customers. They are available in the product edit interface for shop managers to fill in.', 'rp_wccf'),
                    'children' => array(),
                 ),
                'product_admin_fields' => array(
                    'title' => __('Fields', 'rp_wccf'),
                    'children' => array(),
                 ),
            ),
        ),
        'checkout' => array(
            'title' => __('Checkout Fields', 'rp_wccf'),
            'children' => array(
                'checkout_info' => array(
                    'title' => __('About Checkout Fields', 'rp_wccf'),
                    'info' => __('Checkout fields are used to gather additional information from customers during checkout.', 'rp_wccf'),
                    'children' => array(),
                 ),
                'checkout_fields' => array(
                    'title' => __('Fields', 'rp_wccf'),
                    'children' => array(),
                 ),
            ),
        ),
        'order' => array(
            'title' => __('Order Fields', 'rp_wccf'),
            'children' => array(
                'order_info' => array(
                    'title' => __('About Order Fields', 'rp_wccf'),
                    'info' => __('Order fields are used by shop managers during order processing and fulfilment.', 'rp_wccf'),
                    'children' => array(),
                 ),
                'order_fields' => array(
                    'title' => __('Fields', 'rp_wccf'),
                    'children' => array(),
                 ),
            ),
        ),
        'settings' => array(
            'title' => __('Settings', 'rp_wccf'),
            'children' => array(
                'general_settings' => array(
                    'title' => __('General Settings', 'rp_wccf'),
                    'children' => array(
                        'date_format' => array(
                            'title' => __('Date format', 'rp_wccf'),
                            'type' => 'dropdown',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'option',
                                'empty' => false
                            ),
                            'values' => array(
                                '0' => __('mm/dd/yy', 'rp_wccf'),
                                '1' => __('mm/dd/yyyy', 'rp_wccf'),
                                '2' => __('dd/mm/yy', 'rp_wccf'),
                                '3' => __('dd/mm/yyyy', 'rp_wccf'),
                                '4' => __('yy-mm-dd', 'rp_wccf'),
                                '5' => __('yyyy-mm-dd', 'rp_wccf'),
                                '6' => __('dd.mm.yyyy', 'rp_wccf'),
                                '7' => __('dd-mm-yyyy', 'rp_wccf'),
                            ),
                        ),
                    ),
                 ),
                'file_uploads' => array(
                    'title' => __('File Uploads', 'rp_wccf'),
                    'children' => array(
                        'attach_new_order' => array(
                            'title' => __('Attach files to New Order emails', 'rp_wccf'),
                            'type' => 'checkbox',
                            'default' => 0,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                        ),
                    ),
                ),
                'addon_pricing' => array(
                    'title' => __('Add-on Pricing', 'rp_wccf'),
                    'children' => array(
                        'prices_product_page' => array(
                            'title' => __('Display add-on pricing on product pages', 'rp_wccf'),
                            'type' => 'checkbox',
                            'default' => 1,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                        ),
                        'prices_cart_order_page' => array(
                            'title' => __('Display add-on pricing on cart/order pages', 'rp_wccf'),
                            'type' => 'checkbox',
                            'default' => 1,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                        ),
                        'display_total_price' => array(
                            'title' => __('Display grand total row', 'rp_wccf'),
                            'type' => 'checkbox',
                            'default' => 1,
                            'validation' => array(
                                'rule' => 'bool',
                                'empty' => false
                            ),
                        ),
                    ),
                ),
                'aliases' => array(
                    'title' => __('Aliases', 'rp_wccf'),
                    'children' => array(
                        'alias_product' => array(
                            'title' => __('Product Fields', 'rp_wccf'),
                            'type' => 'text',
                            'default' => __('Product Fields', 'rp_wccf'),
                            'validation' => array(
                                'rule' => 'string',
                                'empty' => false
                            ),
                        ),
                        'alias_product_admin' => array(
                            'title' => __('Product Properties', 'rp_wccf'),
                            'type' => 'text',
                            'default' => __('Product Properties', 'rp_wccf'),
                            'validation' => array(
                                'rule' => 'string',
                                'empty' => false
                            ),
                        ),
                        'alias_checkout' => array(
                            'title' => __('Checkout Fields', 'rp_wccf'),
                            'type' => 'text',
                            'default' => __('Checkout Fields', 'rp_wccf'),
                            'validation' => array(
                                'rule' => 'string',
                                'empty' => false
                            ),
                        ),
                        'alias_order' => array(
                            'title' => __('Order Fields', 'rp_wccf'),
                            'type' => 'text',
                            'default' => __('Order Fields', 'rp_wccf'),
                            'validation' => array(
                                'rule' => 'string',
                                'empty' => false
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
}
}