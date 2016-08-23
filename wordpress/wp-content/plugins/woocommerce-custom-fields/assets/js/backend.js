/**
 * WooCommerce Custom Fields Plugin Backend Scripts
 */
jQuery(document).ready(function() {

    /**
     * Define some variables in the entire scope
     */
    var rp_wccf_child_elements = ['option', 'condition'];

    /**
     * FORM BUILDER
     * Set up form builder
     */
    jQuery('#rp_wccf_fb').each(function() {

        // No config?
        if (typeof rp_wccf_fb !== 'object') {
            jQuery(this).html('Error. Please reload this page.');
            return;
        }

        // No form fields?
        if (rp_wccf_fb.length === 0) {
            add_no_fields();
        }
        // At least one form field exists
        else {

            // Iterate over list of form fields and add them to form builder
            for (var key in rp_wccf_fb) {
                add_field(rp_wccf_fb[key]);
            }

            // Fix field identifiers
            fix_fields();

            // Fix field values
            fix_field_values(false);

            // Iterate over fields
            jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper .rp_wccf_fb_field').each(function() {

                // Initial condition fix
                jQuery(this).find('.rp_wccf_fb_condition').each(function() {
                    fix_condition(jQuery(this));
                });

                // Field header fix
                jQuery(this).find('.rp_wccf_field_title_label').html(jQuery(this).find('.rp_wccf_input_label').val());
                jQuery(this).find('.rp_wccf_field_title_key').html(jQuery(this).find('.rp_wccf_input_key').val());
            });

            // Fix field visibility
            fix_field_visibility();

            // Display notice in product and checkout pages if file field is used
            check_frontend_file_upload();
        }

        // Render Add Field button
        append(jQuery(this), 'add_field')

        // Bind click action
        jQuery('#rp_wccf_fb_add_field button').click(function() {
            add_field(false);
        });
    });

    /**
     * FORM BUILDER
     * Add "Nothing to display" notice
     */
    function add_no_fields()
    {
        prepend('#rp_wccf_fb', 'no_fields');
    }

    /**
     * FORM BUILDER
     * Remove "Nothing to display" notice
     */
    function remove_no_fields()
    {
        jQuery('#rp_wccf_fb #rp_wccf_fb_no_fields').remove();
    }

    /**
     * FORM BUILDER
     * Add field wrapper
     */
    function add_field_wrapper()
    {
        // Make sure we don't have one yet before proceeding
        if (jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper').length === 0) {

            // Add wrapper
            prepend('#rp_wccf_fb', 'field_wrapper', null);

            // Make it sortable accordion
            jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper').accordion({
                header: '> div > div.rp_wccf_accordion_handle',
                icons: false,
                collapsible: true,
                heightStyle: 'content'
            }).sortable({
                handle: '.rp_wccf_fb_field_sort_handle',
                axis:   'y',
                stop: function(event, ui) {

                    // Fix field identifiers
                    fix_fields();
                }
            });
        }
    }

    /**
     * FORM BUILDER
     * Remove field wrapper
     */
    function remove_field_wrapper()
    {
        jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper').remove();
    }

    /**
     * FORM BUILDER
     * Add one field
     */
    function add_field(config)
    {
        // Add field wrapper
        add_field_wrapper();

        // Make sure we don't have the "Nothing to display" notice
        remove_no_fields();

        // Add field element
        append('#rp_wccf_fb #rp_wccf_fb_field_wrapper', 'field', null);

        // Select current field
        var field = jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper').children().last();

        // Fix field identifiers, values and visibility on new fields
        if (config === false) {
            fix_fields();
            fix_field_values(true);
            fix_field_visibility();
        }

        // Set up child elements (options, conditions etc)
        jQuery.each(rp_wccf_child_elements, function(index, type) {
            set_up(type + 's', field, config);
        });

        // Refresh accordion
        jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper').accordion('refresh');
        jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper').accordion('option', 'active', -1);

        // Handle delete action
        jQuery('#rp_wccf_fb .rp_wccf_fb_field_remove_handle').last().click(function() {
            remove_field(jQuery(this).closest('.rp_wccf_fb_field'));
            check_frontend_file_upload();
        });

        // Reflect changes of label and key in header
        jQuery('#rp_wccf_fb .rp_wccf_input_label').last().on('keyup change', function() {
            jQuery(this).closest('.rp_wccf_fb_field').find('.rp_wccf_field_title_label').html(jQuery(this).val());
        });
        jQuery('#rp_wccf_fb .rp_wccf_input_key').last().on('keyup change', function() {
            jQuery(this).closest('.rp_wccf_fb_field').find('.rp_wccf_field_title_key').html(jQuery(this).val());
        });

        // Display note if file upload field is used on product or checkout pages
        jQuery('#rp_wccf_fb .rp_wccf_input_type').last().change(function() {
            check_frontend_file_upload();
        });
    }

    /**
     * FORM BUILDER
     * Remove one field
     */
    function remove_field(field)
    {
        // Last field? Remove the entire wrapper and add "Nothing to display" wrapper
        if (field.closest('#rp_wccf_fb_field_wrapper').children().length < 2) {
            remove_field_wrapper();
            add_no_fields();
        }

        // Remove single field and fix ids
        else {
            field.remove();
            fix_fields();
        }
    }

    /**
     * FORM BUILDER
     * Fix field attributes
     */
    function fix_fields()
    {
        var i = 0;  // Field identifier
        var j = 0;  // Child element identifier (options, conditions etc within a given field)

        // Iterate over fields
        jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper .rp_wccf_fb_field').each(function() {

            var field = jQuery(this);
            var element_wrappers = [];

            // Fix options, conditions etc
            jQuery.each(rp_wccf_child_elements, function(index, type) {

                var type_plural = type + 's';

                // Check if we have elements of this type for this field and handle them
                field.find('.rp_wccf_fb_field_' + type_plural + '_row').each(function() {

                    element_wrappers.push(jQuery(this));

                    // Iterate over elements of this type of current field
                    jQuery(this).find('.rp_wccf_fb_' + type + '_wrapper .rp_wccf_fb_' + type).each(function() {

                        // Iterate over all field elements of current element
                        jQuery(this).find('input, select').each(function() {

                            // Attribute id
                            if (typeof jQuery(this).prop('id') !== 'undefined') {
                                var new_value = jQuery(this).prop('id').replace(/_(\{i\}|\d+)?_/, '_' + i + '_').replace(/(\{j\}|\d+)?$/, j);
                                jQuery(this).prop('id', new_value);
                            }

                            // Attribute name
                            if (typeof jQuery(this).prop('name') !== 'undefined') {
                                var new_value = jQuery(this).prop('name').replace(/^rp_wccf_options\[fb\]\[(\{i\}|\d+)\]?/, 'rp_wccf_options[fb][' + i + ']').replace(new RegExp('\\[' + type_plural + '\\]\\[(\\{j\\}|\\d+)\\]?'), '[' + type_plural + '][' + j + ']');
                                jQuery(this).prop('name', new_value);
                            }
                        });

                        // Increment element identifier
                        j++;
                    });

                    // Reset element identifier
                    j = 0;
                });

            });

            // Iterate over all field elements of this field
            jQuery(this).find('input, select').each(function() {

                var current_form_element = jQuery(this);

                // Do not touch field options (already sorted above)
                if (element_wrappers.length > 0) {

                    var proceed = true;

                    jQuery.each(element_wrappers, function(index, value) {
                        if (jQuery.contains(value[0], current_form_element[0])) {
                            proceed = false;
                            return true;
                        }
                    });

                    if (!proceed) {
                        return true;
                    }
                }

                // Attribute id
                if (typeof jQuery(this).prop('id') !== 'undefined') {
                    var new_value = jQuery(this).prop('id').replace(/(\{i\}|\d+)?$/, i);
                    jQuery(this).prop('id', new_value);
                }

                // Attribute name
                if (typeof jQuery(this).prop('name') !== 'undefined') {
                    var new_value = jQuery(this).prop('name').replace(/^rp_wccf_options\[fb\]\[(\{i\}|\d+)\]?/, 'rp_wccf_options[fb][' + i + ']');
                    jQuery(this).prop('name', new_value);
                }
            });

            // Iterate over all label elements of this field
            jQuery(this).find('label').each(function() {

                var current_form_element = jQuery(this);

                // Do not touch field options (already sorted above)
                if (element_wrappers.length > 0) {

                    var proceed = true;

                    jQuery.each(element_wrappers, function(index, value) {
                        if (jQuery.contains(value[0], current_form_element[0])) {
                            proceed = false;
                        }
                    });

                    if (!proceed) {
                        return true;
                    }
                }

                // Attribute for
                if (typeof jQuery(this).prop('for') !== 'undefined' && jQuery(this).prop('for').length) {
                    var new_value = jQuery(this).prop('for').replace(/(\{i\}|\d+)?$/, i);
                    jQuery(this).prop('for', new_value);
                }
            });

            // Increment field identifier
            i++;
        });
    }

    /**
     * FORM BUILDER
     * Fix field values
     */
    function fix_field_values(is_new)
    {
        var i = 0;  // Field identifier
        var j = 0;  // Child element identifier (options, conditions etc within a given field)

        // Iterate over fields
        jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper .rp_wccf_fb_field').each(function() {

            var field = jQuery(this);
            var element_wrappers = [];

            // Fix options, conditions etc
            jQuery.each(rp_wccf_child_elements, function(index, type) {

                var type_plural = type + 's';

                // Check if we have elements of this type for this field and handle them
                field.find('.rp_wccf_fb_field_' + type_plural + '_row').each(function() {

                    element_wrappers.push(jQuery(this));

                    // Iterate over elements of this type of current field
                    jQuery(this).find('.rp_wccf_fb_' + type + '_wrapper .rp_wccf_fb_' + type).each(function() {

                        // Iterate over all field elements of current element
                        jQuery(this).find('input, select').each(function() {

                            // Get field key
                            var field_key = jQuery(this).prop('id').replace(new RegExp('^rp_wccf_' + type_plural + '_'), '').replace(/^(\d+_)?/, '').replace(/(_\d+)?$/, '');

                            // Select options in select fields
                            if (jQuery(this).is('select')) {
                                if (!is_new && rp_wccf_fb !== false && typeof rp_wccf_fb[i] !== 'undefined' && typeof rp_wccf_fb[i][type_plural] !== 'undefined' && typeof rp_wccf_fb[i][type_plural][j] !== 'undefined' && typeof rp_wccf_fb[i][type_plural][j][field_key] !== 'undefined' && rp_wccf_fb[i][type_plural][j][field_key]) {
                                    if (is_multiselect(jQuery(this))) {
                                        if (typeof rp_wccf_fb_multiselect_options[i] !== 'undefined' && typeof rp_wccf_fb_multiselect_options[i][type_plural] !== 'undefined' && typeof rp_wccf_fb_multiselect_options[i][type_plural][j] !== 'undefined' && typeof rp_wccf_fb_multiselect_options[i][type_plural][j][field_key] === 'object') {
                                            for (var k = 0; k < rp_wccf_fb[i][type_plural][j][field_key].length; k++) {
                                                var all_options = rp_wccf_fb_multiselect_options[i][type_plural][j][field_key];
                                                var current_option_key = rp_wccf_fb[i][type_plural][j][field_key][k];

                                                for (var l = 0; l < all_options.length; l++) {
                                                    if (typeof all_options[l]['id'] !== 'undefined' && typeof all_options[l]['id'] !== 'undefined' && all_options[l]['id'] == current_option_key) {
                                                        var current_option_label = all_options[l]['text'];
                                                        jQuery(this).append(jQuery('<option></option>').attr('value', current_option_key).prop('selected', true).text(current_option_label));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else {
                                        jQuery(this).val(rp_wccf_fb[i][type_plural][j][field_key]);
                                    }
                                }
                            }

                            // Check checkboxes
                            else if (jQuery(this).is(':checkbox')) {
                                if (!is_new && rp_wccf_fb !== false && typeof rp_wccf_fb[i] !== 'undefined' && typeof rp_wccf_fb[i][type_plural] !== 'undefined' && typeof rp_wccf_fb[i][type_plural][j] !== 'undefined' && typeof rp_wccf_fb[i][type_plural][j][field_key] !== 'undefined' && rp_wccf_fb[i][type_plural][j][field_key]) {
                                    jQuery(this).prop('checked', true);
                                }
                            }

                            // Add value for text input fields
                            else if (typeof jQuery(this).prop('value') !== 'undefined' && jQuery(this).prop('value') === '{value}') {
                                if (!is_new && rp_wccf_fb !== false && typeof rp_wccf_fb[i] !== 'undefined' && typeof rp_wccf_fb[i][type_plural] !== 'undefined' && typeof rp_wccf_fb[i][type_plural][j] !== 'undefined' && typeof rp_wccf_fb[i][type_plural][j][field_key] !== 'undefined') {
                                    jQuery(this).prop('value', rp_wccf_fb[i][type_plural][j][field_key]);
                                }
                                else {
                                    jQuery(this).removeAttr('value');
                                }
                            }

                            // Initialize select2
                            if (jQuery(this).hasClass('rp_wccf_fb_select2')) {
                                initialize_select2(jQuery(this));
                            }
                        });

                        // Increment element identifier
                        j++;
                    });

                    // Reset element identifier
                    j = 0;
                });

            });

            // Iterate over all field elements of this field
            jQuery(this).find('input, select').each(function() {

                var current_form_element = jQuery(this);

                // Do not touch field options (already sorted above)
                if (element_wrappers.length > 0) {

                    var proceed = true;

                    jQuery.each(element_wrappers, function(index, value) {
                        if (jQuery.contains(value[0], current_form_element[0])) {
                            proceed = false;
                            return true;
                        }
                    });

                    if (!proceed) {
                        return true;
                    }
                }

                // Get field key
                var field_key = jQuery(this).prop('id').replace(/^rp_wccf_/, '').replace(/(_\d+)?$/, '');

                // Select options in select fields
                if (jQuery(this).is('select')) {
                    if (!is_new && rp_wccf_fb !== false && typeof rp_wccf_fb[i] !== 'undefined' && typeof rp_wccf_fb[i][field_key] !== 'undefined' && rp_wccf_fb[i][field_key]) {
                        jQuery(this).val(rp_wccf_fb[i][field_key]);
                    }
                }

                // Check checkboxes
                else if (jQuery(this).is(':checkbox')) {
                    if (!is_new && rp_wccf_fb !== false && typeof rp_wccf_fb[i] !== 'undefined' && typeof rp_wccf_fb[i][field_key] !== 'undefined' && rp_wccf_fb[i][field_key]) {
                        jQuery(this).prop('checked', true);
                    }
                }

                // Add value for text input fields
                else if (typeof jQuery(this).prop('value') !== 'undefined' && jQuery(this).prop('value') === '{value}') {
                    if (!is_new && rp_wccf_fb !== false && typeof rp_wccf_fb[i] !== 'undefined' && typeof rp_wccf_fb[i][field_key] !== 'undefined') {
                        jQuery(this).prop('value', rp_wccf_fb[i][field_key]);
                    }
                    else {
                        jQuery(this).removeAttr('value');
                    }
                }

                // Initialize select2
                if (jQuery(this).hasClass('rp_wccf_fb_select2')) {
                    initialize_select2(jQuery(this));
                }
            });

            // Toogle price fields for options
            toggle_price_fields(field);

            // Increment field identifier
            i++;
        });
    }

    /**
     * FORM BUILDER
     * Initialize select2 on one element
     */
    function initialize_select2(element)
    {
        // Currently only multiselect fields are converted
        if (!is_multiselect(element)) {
            return;
        }

        // Make sure our Select2 reference is set
        if (typeof RP_Select2 === 'undefined') {
            return;
        }

        // Initialize Select2
        RP_Select2.call(element, {
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url:        rp_wccf.ajaxurl,
                type:       'POST',
                dataType:   'json',
                delay:      250,
                data: function(params) {
                    return {
                        query:      params.term,
                        action:     'rp_wccf_load_multiselect_items',
                        type:       parse_multiselect_subject(element),
                        selected:   element.val()
                    };
                },
                processResults: function(data, page) {
                    return {
                        results: data.results
                    };
                }
            }
        });
    }

    /**
     * FORM BUILDER
     * Parse multiselect field subject
     */
    function parse_multiselect_subject(element)
    {
        var subject = '';

        jQuery.each(element.attr('class').split(/\s+/), function(index, item) {
            if (item.indexOf('rp_wccf_condition_') > -1) {
                subject = item.replace('rp_wccf_condition_', '');
                return;
            }
        });

        return subject;
    }

    /**
     * FORM BUILDER
     * Fix field visibility
     */
    function fix_field_visibility()
    {
        // Iterate over fields
        jQuery('#rp_wccf_fb #rp_wccf_fb_field_wrapper .rp_wccf_fb_field').each(function() {

            var field = jQuery(this);

            // Hide Position on tabs other but Checkout Fields
            if (rp_wccf.tab !== 'checkout') {
                field.find('.rp_wccf_input_position').closest('.rp_wccf_fb_field_setting').hide();
            }

            // Hide Adjust Price on tabs other but Product Fields
            if (rp_wccf.tab !== 'product' && rp_wccf.tab !== 'product_admin') {
                field.find('.rp_wccf_input_price').closest('.rp_wccf_fb_field_setting_checkbox').hide();
            }

            // Hide Public on tabs other but Product Properties and Order Fields
            if (rp_wccf.tab !== 'product_admin' && rp_wccf.tab !== 'order') {
                field.find('.rp_wccf_input_public').closest('.rp_wccf_fb_field_setting_checkbox').hide();
            }

            // Type
            type_changed(field.find('.rp_wccf_input_type').first());
            field.find('.rp_wccf_input_type').first().change(function() {
                type_changed(jQuery(this));
            });

            // Checkboxes
            jQuery.each(['advanced', 'price'], function(index, value) {
                checkbox_changed(field.find('.rp_wccf_input_' + value).first(), value);
                field.find('.rp_wccf_input_' + value).first().change(function() {
                    checkbox_changed(jQuery(this), value);
                });
            });
        });
    }

    /**
     * FORM BUILDER
     * Field type setting value changed
     */
    function type_changed(input)
    {
        var field = input.closest('.rp_wccf_fb_field');

        // Select, multiselect, checkbox and radio field types - show options
        if (jQuery.inArray(input.val(), ['select', 'multiselect', 'checkbox', 'radio']) !== -1) {
            field.find('.rp_wccf_fb_field_options_row').show();
        }
        // Other field types - hide options
        else {
            field.find('.rp_wccf_fb_field_options_row').hide();
        }

        // Toggle character limit field visibility
        input.closest('.rp_wccf_fb_field').find('.rp_wccf_input_character_limit').each(function() {
            if (jQuery.inArray(input.val(), ['text', 'textarea', 'password', 'email', 'number']) !== -1) {
                jQuery(this).prop('disabled', false);
                jQuery(this).closest('.rp_wccf_fb_field_setting').show();
            }
            else {
                jQuery(this).val('');
                jQuery(this).prop('disabled', true);
                jQuery(this).closest('.rp_wccf_fb_field_setting').hide();
            }
        });

        // Toggle per character pricing option visibility
        input.closest('.rp_wccf_fb_field').find('.rp_wccf_price_method').each(function() {

            var option = jQuery(this).find('option[value="surcharge_per_character"]');

            if (jQuery.inArray(input.val(), ['text', 'textarea', 'password', 'email', 'number']) !== -1) {
                option.prop('disabled', false);
                option.show();
            }
            else {

                // Reset option if per character is selected
                if (jQuery(this).val() === 'surcharge_per_character') {
                    jQuery(this).prop('selectedIndex', 0);
                }

                option.prop('disabled', true);
                option.hide();
            }
        });

        // Toggle price fields
        toggle_price_fields(field);
    }

    /**
     * FORM BUILDER
     * Checkbox value changed
     */
    function checkbox_changed(input, key)
    {
        var field = input.closest('.rp_wccf_fb_field');

        // Toggle price field
        if (key === 'price') {
            toggle_price_fields(field);
        }
        else {
            // Checked
            if (input.is(':checked')) {
                field.find('.rp_wccf_fb_field_' + key + '_row').show();
            }
            // Unchecked
            else {
                field.find('.rp_wccf_fb_field_' + key + '_row').hide();
            }
        }
    }

    /**
     * FORM BUILDER
     * Set up options, conditions etc (var type) for one field
     */
    function set_up(type, field, config)
    {
        var type_singular = type.replace(/s$/, '');

        // No existing children of given type
        if (config === false || typeof config !== 'object' || typeof config[type] !== 'object') {
            add_no(type, field);
        }

        // Set up existing children of given type
        else {
            for (var key in config[type]) {
                add(type_singular, field, config);
            }
        }

        // Bind click action
        field.find('.rp_wccf_fb_add_' + type_singular + ' button').click(function() {
            add(type_singular, field, false);
        });
    }

    /**
     * FORM BUILDER
     * Add one option, condition etc (var type)
     */
    function add(type, field, config)
    {
        // Add wrapper
        add_wrapper(type, field);

        // Make sure we don't have the No Options, No Conditions etc notice
        remove_no(type + 's', field);

        // Add element
        append(field.find('.rp_wccf_fb_' + type + '_wrapper'), type, null);

        // Fix identifiers, values and visibility on newly added item
        if (config === false) {

            // Fix fields
            fix_fields();
            fix_field_values(true);
            fix_field_visibility();

            // Fix elements of current condition
            if (type === 'condition') {
                fix_condition(field.find('.rp_wccf_fb_condition').last());
            }
        }

        // Handle delete action
        field.find('.rp_wccf_fb_' + type + '_remove_handle').last().click(function() {
            remove(type, jQuery(this).closest('.rp_wccf_fb_' + type));
        });
    }

    /**
     * FORM BUILDER
     * Fix condition
     */
    function fix_condition(element)
    {
        // Condition type
        element.find('.rp_wccf_condition_type').change(function() {
            toggle_condition_fields(element);
        });
        toggle_condition_fields(element);

        // Other custom field condition
        element.find('.rp_wccf_condition_method').change(function() {
            fix_other_custom_field(element);
        });
        fix_other_custom_field(element);
    }

    /**
     * FORM BUILDER
     * Remove one option, condition etc (var type)
     */
    function remove(type, element)
    {
        var field = element.closest('.rp_wccf_fb_field');

        // Last element? Remove the entire wrapper and add No Options, No Conditions etc wrapper
        if (field.find('.rp_wccf_fb_' + type + '_wrapper').children().length < 2) {
            remove_wrapper(type, field);
            add_no(type + 's', field);
        }

        // Remove single element and fix ids
        else {
            element.remove();
            fix_fields();
        }
    }

    /**
     * FORM BUILDER
     * Add wrapper for options, conditions etc (var type)
     */
    function add_wrapper(type, field)
    {
        // Make sure we don't have one yet before proceeding
        if (field.find('.rp_wccf_fb_' + type + '_wrapper').length === 0) {

            // Add wrapper
            prepend(field.find('.rp_wccf_fb_field_' + type + 's_row .rp_wccf_fb_field_inner_wrapper'), type + '_wrapper', null);

            // Maybe show price fields for type "option"
            if (type === 'option' && rp_wccf.tab === 'product') {
                toggle_price_fields(field);
            }

            // Make it sortable
            field.find('.rp_wccf_fb_' + type + '_wrapper').sortable({
                axis:       'y',
                handle:     '.rp_wccf_fb_' + type + '_sort_handle',
                opacity:    0.7,
                stop: function(event, ui) {

                    // Remove styles added by jQuery UI
                    jQuery(this).find('.rp_wccf_fb_' + type).each(function() {
                        jQuery(this).removeAttr('style');
                    });

                    // Fix ids, names etc
                    fix_fields();
                }
            });
        }
    }

    /**
     * FORM BUILDER
     * Remove option, condition etc (var type) wrapper
     */
    function remove_wrapper(type, field)
    {
        field.find('.rp_wccf_fb_' + type + '_header').remove();
        field.find('.rp_wccf_fb_' + type + '_wrapper').remove();
    }

    /**
     * FORM BUILDER
     * Add no options, conditions etc (var type) notice
     */
    function add_no(type, field)
    {
        prepend(field.find('.rp_wccf_fb_field_' + type + '_row .rp_wccf_fb_field_inner_wrapper'), 'no_' + type);
    }

    /**
     * FORM BUILDER
     * Remove No Options, No Conditions etc (var type) notice
     */
    function remove_no(type, field)
    {
        field.find('.rp_wccf_fb_no_' + type).remove();
    }

    /**
     * FORM BUILDER
     * Toggle visibility of option price fields
     */
    function toggle_price_fields(field)
    {
        // Field type has options?
        if (jQuery.inArray(field.find('.rp_wccf_input_type').val(), ['select', 'multiselect', 'checkbox', 'radio']) !== -1) {

            // Hide general pricing fields
            field.find('.rp_wccf_fb_field_price_row').hide();

            // Pricing enabled?
            if (field.find('.rp_wccf_input_price').is(':checked')) {

                // Resize other fields
                field.find('.rp_wccf_fb_option_resize').css('width', '31%');

                // Show pricing fields
                field.find('.rp_wccf_fb_option_price').show();
            }
            else {

                // Hide pricing fields
                field.find('.rp_wccf_fb_option_price').hide();

                // Resize other fields
                field.find('.rp_wccf_fb_option_resize').css('width', '46.5%');
            }
        }
        // Field type doesn't have options
        else {

            // Pricing enabled?
            if (field.find('.rp_wccf_input_price').is(':checked')) {
                field.find('.rp_wccf_fb_field_price_row').show();
            }
            else {
                field.find('.rp_wccf_fb_field_price_row').hide();
            }
        }
    }

    /**
     * FORM BUILDER
     * Toggle visibility of condition fields
     */
    function toggle_condition_fields(element)
    {
        // Get current condition type
        var current_type = element.find('.rp_wccf_condition_type').val();

        // Show only fields related to current type
        element.find('.rp_wccf_fb_condition_setting_fields').each(function() {

            // Show or hide fields
            var display = jQuery(this).hasClass('rp_wccf_fb_condition_setting_fields_' + current_type) ? 'block' : 'none';
            jQuery(this).css('display', display);

            // Empty field values
            if (display === 'none') {
                jQuery(this).find('input, select').each(function() {
                    clear_field_value(jQuery(this));
                });
            }
        });
    }

    /**
     * FORM BUILDER
     * Fix fields of other_custom_field condition
     */
    function fix_other_custom_field(element)
    {
        // Get current method
        var current_method = element.find('.rp_wccf_fb_condition_setting_fields_custom_field_other_custom_field .rp_wccf_condition_method').val();

        // Proceed depending on current method
        if (jQuery.inArray(current_method, ['is_empty', 'is_not_empty', 'is_checked', 'is_not_checked']) !== -1) {
            element.find('.rp_wccf_fb_condition_setting_fields_custom_field_other_custom_field').find('input, select').parent().removeClass('rp_wccf_fb_condition_setting_fields_single').addClass('rp_wccf_fb_condition_setting_fields_double');
            element.find('.rp_wccf_fb_condition_setting_fields_custom_field_other_custom_field .rp_wccf_conditions_text').parent().css('display', 'none');
            clear_field_value(element.find('.rp_wccf_fb_condition_setting_fields_custom_field_other_custom_field .rp_wccf_conditions_text'));
        }
        else {
            element.find('.rp_wccf_fb_condition_setting_fields_custom_field_other_custom_field').find('input, select').parent().removeClass('rp_wccf_fb_condition_setting_fields_double').addClass('rp_wccf_fb_condition_setting_fields_single');
            element.find('.rp_wccf_fb_condition_setting_fields_custom_field_other_custom_field .rp_wccf_conditions_text').parent().css('display', 'block');
        }
    }

    /**
     * FORM BUILDER
     * Clear field value
     */
    function clear_field_value(field)
    {
        if (field.is('select')) {
            field.prop('selectedIndex', 0);
        }
        else if (field.is(':radio, :checkbox')) {
            field.removeAttr('checked');
        }
        else {
            field.val('');
        }
    }

    /**
     * FORM BUILDER
     * Check if HTML element is multiselect field
     */
    function is_multiselect(element)
    {
        return (element.is('select') && typeof element.attr('multiple') !== 'undefined' && element.attr('multiple') !== false);
    }

    /**
     * VALIDATION
     * jQuery Validate
     */
    jQuery('form:has(.wccf)').each(function() {

        // Initialize validator
        jQuery(this).validate({
            ignore: ':not(.wccf)',
        });

        // Email
        jQuery(this).find('.wccf_email').each(function() {
            jQuery(this).rules('add', {
                email: true
            });
        });

        // Number
        jQuery(this).find('.wccf_number').each(function() {
            jQuery(this).rules('add', {
                number: true
            });
        });

        // File - remove validation if file already exists
        jQuery(this).find('.wccf_file').each(function() {
            if (jQuery(this).closest('.wccf_meta_box_field_container').has('.wccf_current_file').length) {
                jQuery(this).rules('add', {
                    required: false
                });
            }
        });

        // On submit
        jQuery(this).submit(function(e) {

            // Validate form now
            if (!jQuery(this).valid()) {
                e.preventDefault();
            }
        });
    });

    /**
     * FORM BUILDER
     * Display notice if at least one field in product or checkout field list is of type file
     */
    function check_frontend_file_upload()
    {
        // Only proceed if working with product or checkout fields
        if (rp_wccf.tab !== 'product' && rp_wccf.tab !== 'checkout') {
            return;
        }

        // Track if at least one field is of type file
        var file_fields = false;

        // Iterate over fields
        jQuery('#rp_wccf_fb .rp_wccf_input_type').each(function() {
            if (jQuery(this).val() === 'file') {
                file_fields = true;
                return;
            }
        });

        // Show notice
        if (file_fields) {
            jQuery('.rp_wccf_file_warning').show();
        }
        // Hide notice
        else {
            jQuery('.rp_wccf_file_warning').hide();
        }
    }





    /**
     * HELPER
     * Append template with values to selected element's content
     */
    function append(selector, template, values)
    {
        var html = get_template(template, values);

        if (typeof selector === 'object') {
            selector.append(html);
        }
        else {
            jQuery(selector).append(html);
        }
    }

    /**
     * HELPER
     * Prepend template with values to selected element's content
     */
    function prepend(selector, template, values)
    {
        var html = get_template(template, values);

        if (typeof selector === 'object') {
            selector.prepend(html);
        }
        else {
            jQuery(selector).prepend(html);
        }
    }

    /**
     * HELPER
     * Get template's html code
     */
    function get_template(template, values)
    {
        return populate_template(jQuery('#rp_wccf_fb_template_' + template).html(), values);
    }

    /**
     * HELPER
     * Populate template with values
     */
    function populate_template(template, values)
    {
        for (var key in values) {
            template = replace_macro(template, key, values[key]);
        }

        return template;
    }

    /**
     * HELPER
     * Replace all instances of macro in string
     */
    function replace_macro(string, macro, value)
    {
        var macro = '{' + macro + '}';
        var regex = new RegExp(macro, 'g');
        return string.replace(regex, value);
    }

});