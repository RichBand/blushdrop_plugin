<?php
add_filter('media_upload_tabs', 'my_media_upload_tabs_filter');

function my_media_upload_tabs_filter($tabs) {
  //unset($tabs["type_url"]);
  //unset($tabs['library']);
  $newtab = array('ell_insert_gmap_tab' => __('Google Map', 'insertgmap'));

  return array_merge($tabs, $newtab);
}

add_action('media_upload_ell_insert_gmap_tab', 'media_upload_ell_gmap_tab');

function media_upload_ell_gmap_tab() {
  return wp_iframe('media_upload_ell_gmap_form', $errors);
}

function media_upload_ell_gmap_form() {
  ?>
  <h2>HTML Form</h2>

  <?php
}
