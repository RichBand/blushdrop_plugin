<?php
global $wpdb;

/* get the users from the database ordered by user nicename */
$user_ids = get_users();

/* add object for guest user */
$guest = new stdClass();
$guest->ID = 'GUEST';
$guest->user_email = 'guest@example.com';
$guest->display_name = 'Default folder for Guests and non-linked Users';
//$user_ids[] = $guest;

$html = '';
?>
<div class="wrap adminfilebrowser">
  <h2><?php _e('Link users to folder', 'outofthebox'); ?></h2>
  <div id='OutoftheBox-UserToFolder'>

    <?php
    $html .= getUserListing($guest);

    //loop through each user
    foreach ($user_ids as $user) {
      // Get user data
      $html .= getUserListing($user->data);
    }
    echo $html;
    ?>
  </div>
  <div id='oftb-embedded' style='clear:both;display:none'>
    <?php
    echo $this->OutoftheBox_Dropbox->createFromShortcode(
            array('mode' => 'files',
                'showfiles' => '1',
                'filesize' => '0',
                'filedate' => '0',
                'upload' => '0',
                'delete' => '0',
                'rename' => '0',
                'addfolder' => '0',
                'showbreadcrumb' => '0',
                'showcolumnnames' => '0',
                'showfiles' => '0',
                'viewrole' => 'administrator|editor|author|contributor',
                'downloadrole' => 'none',
                'candownloadzip' => '0',
                'showsharelink' => '0',
                'mcepopup' => 'linkto',
                'search' => '0'));
    ?>
  </div>
</div>
<?php

function getUserListing($curuser) {
  $html = '<div class="oftb-user ' . (($curuser->ID === 'GUEST') ? 'guest' : '' ) . '">';

  /* Gravatar */
  if (function_exists('get_wp_user_avatar')) {
    $display_gravatar = get_wp_user_avatar($curuser->user_email, 32);
  } else {
    $display_gravatar = get_avatar($curuser->user_email, 32);
    if ($display_gravatar === false) {
      //Gravatar is disabled, show default image.
      $display_gravatar = '<img src="' . OUTOFTHEBOX_ROOTPATH . '/css/images/usericon.png"/>';
    }
  }

  $html .= "<div class=\"oftb-avatar\"><a title=\"$curuser->display_name\">$display_gravatar</a></div>\n";

  $html .= "<div class=\"oftb-userinfo\" data-userid=\"" . $curuser->ID . "\">";

  /* name */
  $html .= "<div class=\"oftb-name\"><a href=\"" . (($curuser->ID === 'GUEST') ? '#' : get_edit_user_link($curuser->ID)) . "\"title=\"$curuser->display_name\">$curuser->display_name</a></div>\n";

  /* Current link */
  if ($curuser->ID === 'GUEST') {
    $curfolder = get_site_option('out_of_the_box_guestlinkedto');
  } else {
    $curfolder = get_user_option('out_of_the_box_linkedto', $curuser->ID);
  }
  $nolink = true;
  if (empty($curfolder) || !is_array($curfolder) || !isset($curfolder['foldertext'])) {
    $curfolder = __('Not yet linked to a folder', 'outofthebox');
  } else {
    $curfolder = $curfolder['foldertext'];
    $nolink = false;
  }

  $html .= "<div class=\"oftb-linkedto\">$curfolder</div>\n";
  $html .= "<input class='oftb-linkbutton button-primary' type='submit' title='" . __('Link to folder', 'outofthebox') . "' value='" . __('Link to folder', 'outofthebox') . "'>";
  $html .= "<input class='oftb-unlinkbutton button-secondary " . ($nolink ? 'disabled' : '') . "' type='submit' title='" . __('Remove link', 'outofthebox') . "' value='" . __('Remove link', 'outofthebox') . "'>";

  $html .= "</div>";

  $html .= '</div>';
  return $html;
}
