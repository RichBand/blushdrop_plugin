<?php

/**
 * Order Field Data Frontend Display
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<header>
    <h2><?php echo apply_filters('wccf_context_label', RP_WCCF::option('alias_order'), 'order', 'frontend'); ?></h2>
</header>

<?php do_action('wccf_before_order_fields'); ?>

<table class="shop_table shop_table_responsive">
    <tbody>

        <?php foreach ($fields as $field): ?>

            <tr>
                <th><?php echo $field['label']; ?></th>
                <td><?php echo $field['value_to_display']; ?></td>
            </tr>

        <?php endforeach; ?>

    </tbody>
</table>

<?php do_action('wccf_after_order_fields'); ?>