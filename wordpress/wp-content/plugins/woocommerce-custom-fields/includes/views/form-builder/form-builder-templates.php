<?php

/**
 * View for Form Builder Templates
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="rp_wccf_fb_templates" style="display: none">

    <!-- NOTHING TO DISPLAY -->
    <div id="rp_wccf_fb_template_no_fields">
        <div id="rp_wccf_fb_no_fields"><?php _e('No fields configured.', 'rp_wccf'); ?></div>
    </div>

    <!-- ADD FIELD BUTTON -->
    <div id="rp_wccf_fb_template_add_field">
        <div id="rp_wccf_fb_add_field">
            <button type="button" class="button" value="<?php _e('Add Field', 'rp_wccf'); ?>">
                <i class="fa fa-plus">&nbsp;&nbsp;<?php _e('Add Field', 'rp_wccf'); ?></i>
            </button>
        </div>
    </div>

    <!-- FIELD WRAPPER -->
    <div id="rp_wccf_fb_template_field_wrapper">
        <div id="rp_wccf_fb_field_wrapper"></div>
    </div>

    <!-- FIELD -->
    <div id="rp_wccf_fb_template_field">

        <div class="rp_wccf_fb_field">

            <div class="rp_wccf_accordion_handle">
                <div class="rp_wccf_fb_field_sort_handle"><i class="fa fa-sort"></i></div>
                <span class="rp_wccf_fb_field_title">
                    <span class="rp_wccf_field_title_label"></span>
                    <span class="rp_wccf_field_title_key"></span>
                </span>
                <div class="rp_wccf_fb_field_remove_handle"><i class="fa fa-times"></i></div>
            </div>

            <div class="rp_wccf_fb_field_content">

                <div class="rp_wccf_fb_field_first_row">
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_single">
                        <?php RP_WCCF_FB::text(array(
                            'id'        => 'rp_wccf_key_{i}',
                            'name'      => 'rp_wccf_options[fb][{i}][key]',
                            'class'     => 'rp_wccf_input_key',
                            'value'     => '{value}',
                            'label'     => __('Unique Key', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_single">
                        <?php RP_WCCF_FB::text(array(
                            'id'        => 'rp_wccf_label_{i}',
                            'name'      => 'rp_wccf_options[fb][{i}][label]',
                            'class'     => 'rp_wccf_input_label',
                            'value'     => '{value}',
                            'label'     => __('Field Label', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_single">
                        <?php RP_WCCF_FB::select(array(
                            'id'        => 'rp_wccf_type_{i}',
                            'name'      => 'rp_wccf_options[fb][{i}][type]',
                            'class'     => 'rp_wccf_input_type rp_wccf_fb_select2',
                            'options'   => RP_WCCF_FB::types(),
                            'label'     => __('Field Type', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_single">
                        <div class="rp_wccf_fb_field_setting_checkbox">
                            <?php RP_WCCF_FB::checkbox(array(
                                'id'        => 'rp_wccf_required_{i}',
                                'name'      => 'rp_wccf_options[fb][{i}][required]',
                                'class'     => 'rp_wccf_input_required',
                                'label'     => __('Required', 'rp_wccf'),
                            )); ?>
                        </div>
                        <div class="rp_wccf_fb_field_setting_checkbox">
                            <?php RP_WCCF_FB::checkbox(array(
                                'id'        => 'rp_wccf_price_{i}',
                                'name'      => 'rp_wccf_options[fb][{i}][price]',
                                'class'     => 'rp_wccf_input_price',
                                'label'     => __('Pricing', 'rp_wccf'),
                            )); ?>
                        </div>
                        <div class="rp_wccf_fb_field_setting_checkbox">
                            <?php RP_WCCF_FB::checkbox(array(
                                'id'        => 'rp_wccf_public_{i}',
                                'name'      => 'rp_wccf_options[fb][{i}][public]',
                                'class'     => 'rp_wccf_input_public',
                                'label'     => __('Public', 'rp_wccf'),
                            )); ?>
                        </div>
                        <div class="rp_wccf_fb_field_setting_checkbox">
                            <?php RP_WCCF_FB::checkbox(array(
                                'id'        => 'rp_wccf_advanced_{i}',
                                'name'      => 'rp_wccf_options[fb][{i}][advanced]',
                                'class'     => 'rp_wccf_input_advanced',
                                'label'     => __('Advanced', 'rp_wccf'),
                            )); ?>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <div class="rp_wccf_fb_field_options_row">
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_full_size">
                        <label><?php _e('Options', 'rp_wccf'); ?></label>
                        <div class="rp_wccf_fb_field_inner_wrapper">
                            <div class="rp_wccf_fb_add_option">
                                <button type="button" class="button" value="<?php _e('Add Option', 'rp_wccf'); ?>">
                                    <i class="fa fa-plus">&nbsp;&nbsp;<?php _e('Add Option', 'rp_wccf'); ?></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <div class="rp_wccf_fb_field_price_row" style="display: none;">
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_full_size">
                        <label><?php _e('Pricing', 'rp_wccf'); ?></label>
                        <div class="rp_wccf_fb_field_inner_wrapper rp_wccf_fb_field_inner_wrapper_pricing">
                            <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_double">
                                <?php RP_WCCF_FB::select(array(
                                    'id'        => 'rp_wccf_price_method_{i}',
                                    'name'      => 'rp_wccf_options[fb][{i}][price_method]',
                                    'class'     => 'rp_wccf_price_method rp_wccf_fb_select2',
                                    'options'   => RP_WCCF_Product::pricing_methods(),
                                )); ?>
                            </div>
                            <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_double">
                                <?php RP_WCCF_FB::text(array(
                                    'id'            => 'rp_wccf_price_value_{i}',
                                    'name'          => 'rp_wccf_options[fb][{i}][price_value]',
                                    'class'         => 'rp_wccf_price_value',
                                    'placeholder'   => '0.00',
                                    'value'         => '{value}',
                                )); ?>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <div class="rp_wccf_fb_field_advanced_row">
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_full_size rp_wccf_fb_field_conditions_row">
                        <label><?php _e('Conditions', 'rp_wccf'); ?></label>
                        <div class="rp_wccf_fb_field_inner_wrapper">
                            <div class="rp_wccf_fb_add_condition">
                                <button type="button" class="button" value="<?php _e('Add Condition', 'rp_wccf'); ?>">
                                    <i class="fa fa-plus">&nbsp;&nbsp;<?php _e('Add Condition', 'rp_wccf'); ?></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_double">
                        <?php RP_WCCF_FB::text(array(
                            'id'        => 'rp_wccf_description_{i}',
                            'name'      => 'rp_wccf_options[fb][{i}][description]',
                            'class'     => 'rp_wccf_input_description',
                            'value'     => '{value}',
                            'label'     => __('Field Description', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_double">
                        <?php RP_WCCF_FB::text(array(
                            'id'            => 'rp_wccf_css_{i}',
                            'name'          => 'rp_wccf_options[fb][{i}][css]',
                            'class'         => 'rp_wccf_input_css',
                            'placeholder'   => 'e.g. width: 50%;',
                            'value'         => '{value}',
                            'label'         => __('Custom CSS Rules', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_double">
                        <?php RP_WCCF_FB::number(array(
                            'id'            => 'rp_wccf_character_limit_{i}',
                            'name'          => 'rp_wccf_options[fb][{i}][character_limit]',
                            'class'         => 'rp_wccf_input_character_limit',
                            'placeholder'   => __('No limit', 'rp_wccf'),
                            'value'         => '{value}',
                            'label'         => __('Character Limit', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div class="rp_wccf_fb_field_setting rp_wccf_fb_field_setting_double">
                        <?php RP_WCCF_FB::select(array(
                            'id'        => 'rp_wccf_position_{i}',
                            'name'      => 'rp_wccf_options[fb][{i}][position]',
                            'class'     => 'rp_wccf_input_position rp_wccf_fb_select2',
                            'options'   => RP_WCCF_Checkout::positions(),
                            'label'     => __('Position', 'rp_wccf'),
                        )); ?>
                    </div>
                    <div style="clear: both;"></div>
                </div>

            </div>
        </div>
    </div>

    <!-- NO OPTIONS -->
    <div id="rp_wccf_fb_template_no_options">
        <div class="rp_wccf_fb_no_options"><?php _e('No options configured.', 'rp_wccf'); ?></div>
    </div>

    <!-- OPTION WRAPPER -->
    <div id="rp_wccf_fb_template_option_wrapper">
        <div class="rp_wccf_fb_option_header">
            <div class="rp_wccf_fb_option_sort rp_wccf_fb_option_sort_header"></div>
            <div class="rp_wccf_fb_option_content rp_wccf_fb_option_content_header">
                <div class="rp_wccf_fb_option_header_item rp_wccf_fb_option_resize"><label><?php _e('Unique Key', 'rp_wccf'); ?></label></div>
                <div class="rp_wccf_fb_option_header_item rp_wccf_fb_option_resize"><label><?php _e('Label', 'rp_wccf'); ?></label></div>
                <div class="rp_wccf_fb_option_header_item rp_wccf_fb_option_price" style="display: none;"><label><?php _e('Pricing', 'rp_wccf'); ?></label></div>
                <div class="rp_wccf_fb_option_header_item rp_wccf_fb_option_header_item_checkbox"><label><?php _e('Selected', 'rp_wccf'); ?></label></div>
            </div>
            <div class="rp_wccf_fb_option_remove rp_wccf_fb_option_remove_header"></div>
            <div style="clear: both;"></div>
        </div>
        <div class="rp_wccf_fb_option_wrapper"></div>
    </div>

    <!-- OPTION -->
    <div id="rp_wccf_fb_template_option">
        <div class="rp_wccf_fb_option">
            <div class="rp_wccf_fb_option_sort">
                <div class="rp_wccf_fb_option_sort_handle">
                    <i class="fa fa-sort"></i>
                </div>
            </div>

            <div class="rp_wccf_fb_option_content">
                <div class="rp_wccf_fb_option_setting rp_wccf_fb_option_setting_single rp_wccf_fb_option_resize">
                    <?php RP_WCCF_FB::text(array(
                        'id'        => 'rp_wccf_options_{i}_key_{j}',
                        'name'      => 'rp_wccf_options[fb][{i}][options][{j}][key]',
                        'class'     => 'rp_wccf_option_key',
                        'value'     => '{value}',
                    )); ?>
                </div>
                <div class="rp_wccf_fb_option_setting rp_wccf_fb_option_setting_single rp_wccf_fb_option_resize">
                    <?php RP_WCCF_FB::text(array(
                        'id'        => 'rp_wccf_options_{i}_label_{j}',
                        'name'      => 'rp_wccf_options[fb][{i}][options][{j}][label]',
                        'class'     => 'rp_wccf_option_label',
                        'value'     => '{value}',
                    )); ?>
                </div>
                <div class="rp_wccf_fb_option_setting rp_wccf_fb_option_setting_single rp_wccf_fb_option_price" style="display: none;">
                    <?php RP_WCCF_FB::select(array(
                        'id'        => 'rp_wccf_options_{i}_price_method_{j}',
                        'name'      => 'rp_wccf_options[fb][{i}][options][{j}][price_method]',
                        'class'     => 'rp_wccf_option_price_method rp_wccf_fb_select2',
                        'options'   => RP_WCCF_Product::pricing_methods(false),
                    )); ?>
                    <?php RP_WCCF_FB::text(array(
                        'id'            => 'rp_wccf_options_{i}_price_value_{j}',
                        'name'          => 'rp_wccf_options[fb][{i}][options][{j}][price_value]',
                        'class'         => 'rp_wccf_option_price_value',
                        'placeholder'   => '0.00',
                        'value'         => '{value}',
                    )); ?>
                </div>
                <div class="rp_wccf_fb_option_setting rp_wccf_fb_option_setting_checkbox">
                    <?php RP_WCCF_FB::checkbox(array(
                        'id'        => 'rp_wccf_options_{i}_selected_{j}',
                        'name'      => 'rp_wccf_options[fb][{i}][options][{j}][selected]',
                        'class'     => 'rp_wccf_option_selected',
                    )); ?>
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="rp_wccf_fb_option_remove">
                <div class="rp_wccf_fb_option_remove_handle">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <!-- NO CONDITIONS -->
    <div id="rp_wccf_fb_template_no_conditions">
        <div class="rp_wccf_fb_no_conditions"><?php _e('No conditions configured.', 'rp_wccf'); ?></div>
    </div>

    <!-- CONDITIONS WRAPPER -->
    <div id="rp_wccf_fb_template_condition_wrapper">
        <div class="rp_wccf_fb_condition_wrapper"></div>
    </div>

    <!-- CONDITION -->
    <div id="rp_wccf_fb_template_condition">
        <div class="rp_wccf_fb_condition">
            <div class="rp_wccf_fb_condition_sort">
                <div class="rp_wccf_fb_condition_sort_handle">
                    <i class="fa fa-sort"></i>
                </div>
            </div>

            <div class="rp_wccf_fb_condition_content">
                <div class="rp_wccf_fb_condition_setting rp_wccf_fb_condition_setting_single rp_wccf_fb_condition_setting_type">
                    <?php RP_WCCF_FB::grouped_select(array(
                        'id'        => 'rp_wccf_conditions_{i}_type_{j}',
                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][type]',
                        'class'     => 'rp_wccf_condition_type rp_wccf_fb_select2',
                        'options'   => RP_WCCF_Conditions::conditions($current_tab),
                    )); ?>
                </div>

                <?php foreach(RP_WCCF_Conditions::conditions($current_tab) as $group_key => $group): ?>
                    <?php foreach($group['options'] as $option_key => $option): ?>
                        <div class="rp_wccf_fb_condition_setting_fields rp_wccf_fb_condition_setting_fields_<?php echo $group_key . '_' . $option_key ?>" style="display: none;">

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'other_field_key')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::text(array(
                                        'id'            => 'rp_wccf_conditions_{i}_other_field_key_{j}',
                                        'name'          => 'rp_wccf_options[fb][{i}][conditions][{j}][other_field_key]',
                                        'class'         => 'rp_wccf_conditions_other_field_key',
                                        'placeholder'   => __('unique key of other field', 'rp_wccf'),
                                        'value'         => '{value}',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                <?php RP_WCCF_FB::select(array(
                                    'id'        => 'rp_wccf_conditions_{i}_' . $group_key . '_' . $option_key . '_method_{j}',
                                    'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][' . $group_key . '_' . $option_key . '_method]',
                                    'class'     => 'rp_wccf_condition_method rp_wccf_fb_select2',
                                    'options'   => RP_WCCF_Conditions::methods($group_key, $option_key),
                                )); ?>
                            </div>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'roles')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_roles_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][roles][]',
                                        'class'     => 'rp_wccf_condition_roles rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'capabilities')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_capabilities_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][capabilities][]',
                                        'class'     => 'rp_wccf_condition_capabilities rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'products')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_products_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][products][]',
                                        'class'     => 'rp_wccf_condition_products rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'product_categories')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_product_categories_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][product_categories][]',
                                        'class'     => 'rp_wccf_condition_product_categories rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'product_types')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_product_types_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][product_types][]',
                                        'class'     => 'rp_wccf_condition_product_types rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'number')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::text(array(
                                        'id'            => 'rp_wccf_conditions_{i}_number_{j}',
                                        'name'          => 'rp_wccf_options[fb][{i}][conditions][{j}][number]',
                                        'class'         => 'rp_wccf_conditions_number',
                                        'placeholder'   => '0',
                                        'value'         => '{value}',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'decimal')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::text(array(
                                        'id'            => 'rp_wccf_conditions_{i}_decimal_{j}',
                                        'name'          => 'rp_wccf_options[fb][{i}][conditions][{j}][decimal]',
                                        'class'         => 'rp_wccf_conditions_decimal',
                                        'placeholder'   => '0.00',
                                        'value'         => '{value}',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'text')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>" <?php echo (($group_key == 'custom_field' && $option_key == 'other_custom_field') ? 'style="display: none;"' : ''); ?>>
                                    <?php RP_WCCF_FB::text(array(
                                        'id'        => 'rp_wccf_conditions_{i}_text_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][text]',
                                        'class'     => 'rp_wccf_conditions_text',
                                        'value'     => '{value}',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'payment_methods')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_payment_methods_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][payment_methods][]',
                                        'class'     => 'rp_wccf_condition_payment_methods rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCCF_Conditions::uses_field($group_key, $option_key, 'shipping_methods')): ?>
                                <div class="rp_wccf_fb_condition_setting_fields_<?php echo RP_WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCCF_FB::multiselect(array(
                                        'id'        => 'rp_wccf_conditions_{i}_shipping_methods_{j}',
                                        'name'      => 'rp_wccf_options[fb][{i}][conditions][{j}][shipping_methods][]',
                                        'class'     => 'rp_wccf_condition_shipping_methods rp_wccf_fb_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <div style="clear: both;"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div style="clear: both;"></div>
            </div>

            <div class="rp_wccf_fb_condition_remove">
                <div class="rp_wccf_fb_condition_remove_handle">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

</div>