<?php

/**
 * View for Settings page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap woocommerce rp_wccf_settings">
    <div class="rp_wccf_settings_container">
        <form method="post" action="options.php" enctype="multipart/form-data">
            <input type="hidden" name="current_tab" value="<?php echo $current_tab; ?>" />
            <?php settings_fields('rp_wccf_opt_group_' . $current_tab); ?>
            <?php do_settings_sections('rp_wccf-admin-' . str_replace('_', '-', $current_tab)); ?>
            <div></div>
            <?php submit_button(); ?>
        </form>
    </div>
</div>