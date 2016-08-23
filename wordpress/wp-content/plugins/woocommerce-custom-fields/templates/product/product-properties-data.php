<?php

/**
 * Product Properties Data Frontend Display In Custom Tab
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<h2><?php echo apply_filters('wccf_context_label', RP_WCCF::option('alias_product_admin'), 'product_admin', 'frontend'); ?></h2>

<?php do_action('wccf_before_order_fields'); ?>

<table class="shop_attributes">
    <tbody>

        <?php $class = ''; ?>
        <?php foreach ($fields as $field): ?>

            <tr class="<?php echo $class; ?>">
                <th><?php echo $field['label']; ?></th>
                <td><p><?php echo $field['value_to_display']; ?></p></td>
            </tr>

            <?php $class = $class === '' ? 'alt' : ''; ?>

        <?php endforeach; ?>

    </tbody>
</table>

<?php do_action('wccf_after_order_fields'); ?>