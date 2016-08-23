<?php

/**
 * View for Settings page header (tabs)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="rp_wccf_tabs_container">
    <h2 class="nav-tab-wrapper">
        <?php foreach ($this->settings as $tab_key => $tab): ?>
            <a class="nav-tab <?php echo ($tab_key == $current_tab ? 'nav-tab-active' : ''); ?>" href="admin.php?page=woocommerce_custom_fields&tab=<?php echo $tab_key; ?>"><?php echo $tab['title']; ?></a>
        <?php endforeach; ?>
    </h2>
</div>