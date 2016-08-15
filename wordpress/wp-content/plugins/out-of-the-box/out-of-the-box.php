<?php

/*
  Plugin Name: Out-of-the-Box
  Plugin URI: http://www.florisdeleeuw.nl/wordpress-demo/
  Description: Integrates your Dropbox in WordPress
  Version: 1.7.4
  Author: F. de Leeuw
  Author URI:
  Text Domain: outofthebox
 */

/* * ***********SYSTEM SETTINGS****************** */
define('OUTOFTHEBOX_VERSION', '1.7.4');
define('OUTOFTHEBOX_ROOTPATH', plugins_url('', __FILE__));
define('OUTOFTHEBOX_ROOTDIR', __DIR__);
define('OUTOFTHEBOX_CACHEDIR', __DIR__ . '/cache/');
define('OUTOFTHEBOX_CACHEURL', OUTOFTHEBOX_ROOTPATH . '/cache/');

if (!class_exists('OutoftheBox')) {

  class OutoftheBox {

    public $settings = false;

    /**
     * Construct the plugin object
     */
    public function __construct() {

      $this->LoadDefaultValues();

      add_action('init', array(&$this, 'Init'));

      if (is_admin() && !defined('DOING_AJAX')) {
        require_once(sprintf("%s/admin_page.php", dirname(__FILE__)));
        $OutoftheBox_settings = new OutoftheBox_settings($this);
      }

      add_action('wp_head', array(&$this, 'LoadIEstyles'));

      $priority = add_filter('out-of-the-box_enqueue_priority', 10);
      add_action('wp_enqueue_scripts', array(&$this, 'LoadScripts'), $priority);
      add_action('wp_enqueue_scripts', array(&$this, 'LoadLastScripts'), 99999);
      add_action('wp_enqueue_scripts', array(&$this, 'LoadStyles'));

      add_action('plugins_loaded', array(&$this, 'GravityFormsAddon'), 100);

      /* Shortcodes */
      add_shortcode('outofthebox', array(&$this, 'CreateTemplate'));

      /* Add user folder if needed */
      if (isset($this->settings['userfolder_oncreation']) && $this->settings['userfolder_oncreation'] === 'Yes') {
        add_action('user_register', array(&$this, 'UpdateUserfolder'));
      }
      if (isset($this->settings['userfolder_update']) && $this->settings['userfolder_update'] === 'Yes') {
        add_action('profile_update', array(&$this, 'UpdateUserfolder'), 100, 2);
      }
      if (isset($this->settings['userfolder_remove']) && $this->settings['userfolder_remove'] === 'Yes') {
        add_action('delete_user', array(&$this, 'DeleteUserfolder'));
      }

      add_action('wp_head', array(&$this, 'CustomCss'), 100);

      /* Ajax calls */
      add_action('wp_ajax_nopriv_outofthebox-get-filelist', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-get-filelist', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-search', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-search', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-get-gallery', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-get-gallery', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-upload-file', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-upload-file', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-delete-entry', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-delete-entry', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-delete-entries', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-delete-entries', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-rename-entry', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-rename-entry', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-move-entry', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-move-entry', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-add-folder', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-add-folder', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-get-playlist', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-get-playlist', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-create-zip', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-create-zip', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-thumbnail', array(&$this, 'GenerateThumbnail'));
      add_action('wp_ajax_outofthebox-thumbnail', array(&$this, 'GenerateThumbnail'));

      add_action('wp_ajax_nopriv_outofthebox-create-link', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-create-link', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-embedded', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-embedded', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-download', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-download', array(&$this, 'StartProcess'));

      add_action('wp_ajax_nopriv_outofthebox-preview', array(&$this, 'StartProcess'));
      add_action('wp_ajax_outofthebox-preview', array(&$this, 'StartProcess'));

      add_action('wp_ajax_outofthebox-revoke', array(&$this, 'StartProcess'));

      add_action('wp_ajax_outofthebox-getpopup', array(&$this, 'GetPopup'));

      add_action('wp_ajax_outofthebox-linkusertofolder', array(&$this, 'LinkUserToFolder'));
      add_action('wp_ajax_outofthebox-unlinkusertofolder', array(&$this, 'UnlinkUserToFolder'));

      /* add settings link on plugin page */
      add_filter('plugin_row_meta', array(&$this, 'AddSettingsLink'), 10, 2);
    }

    public function Init() {
      /* Localize */
      $i18n_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
      load_plugin_textdomain('outofthebox', false, $i18n_dir);
    }

    public function LoadDefaultValues() {

      $this->settings = get_option('out_of_the_box_settings', array(
          'purcasecode' => '',
          'dropbox_app_key' => '',
          'dropbox_app_secret' => '',
          'dropbox_app_token' => '',
          'dropbox-auth-csrf-token' => '',
          'shortlinks' => 'Dropbox',
          'bitly_login' => '',
          'bitly_apikey' => '',
          'thumbnails' => 'Out-of-the-Box',
          'google_analytics' => 'No',
          'lightbox_skin' => 'metro-black',
          'lightbox_path' => 'horizontal',
          'mediaplayer_skin' => 'default',
          'userfolder_name' => '%user_login% (%user_email%)',
          'userfolder_oncreation' => 'Yes',
          'userfolder_onfirstvisit' => 'No',
          'userfolder_update' => 'Yes',
          'userfolder_remove' => 'Yes'
      ));

      if ($this->settings === false) {
        return;
      }
      /* Remove 'advancedsettings' option of versions before 1.6.2 */
      $advancedsettings = get_option('out_of_the_box_advancedsettings');
      if ($advancedsettings !== false && $this->settings !== false) {
        $this->settings = array_merge($this->settings, $advancedsettings);
        delete_option('out_of_the_box_advancedsettings');
        $this->settings = get_option('out_of_the_box_settings');
      }

      /* Set default values */
      if (empty($this->settings['google_analytics'])) {
        $this->settings['google_analytics'] = 'No';
      }

      if (empty($this->settings['download_template'])) {
        $this->settings['download_template'] = 'Hi!

%visitor% has downloaded the following files from your site: 

<ul>%filelist%</ul>';
      }
      if (empty($this->settings['upload_template'])) {
        $this->settings['upload_template'] = 'Hi!

%visitor% has uploaded the following file(s) to your Google Drive:

<ul>%filelist%</ul>';
      }
      if (empty($this->settings['delete_template'])) {
        $this->settings['delete_template'] = 'Hi!

%visitor% has deleted the following file(s) on your Google Drive:

<ul>%filelist%</ul>';
      }

      if (empty($this->settings['filelist_template'])) {
        $this->settings['filelist_template'] = '<li><a href="%fileurl%" title-"%filename%">%filesafepath%<a/> (%filesize%)</li>';
      }

      if (empty($this->settings['mediaplayer_skin'])) {
        $this->settings['mediaplayer_skin'] = 'default';
      }

      if (empty($this->settings['lightbox_skin'])) {
        $this->settings['lightbox_skin'] = 'metro-black';
      }
      if (empty($this->settings['lightbox_path'])) {
        $this->settings['lightbox_path'] = 'horizontal';
      }

      update_option('out_of_the_box_settings', $this->settings);
    }

    public function AddSettingsLink($links, $file) {
      $plugin = plugin_basename(__FILE__);

      /* create link */
      if ($file == $plugin && !is_network_admin()) {
        return array_merge(
                $links, array(sprintf('<a href="options-general.php?page=%s">%s</a>', 'OutoftheBox_settings', __('Settings', 'outofthebox')))
        );
      }

      return $links;
    }

    public function LoadScripts() {
      wp_register_script('jquery.requestAnimationFrame', plugins_url('includes/iLightBox/js/jquery.requestAnimationFrame.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'includes/iLightBox/js/jquery.requestAnimationFrame.js'));
      wp_register_script('jquery.mousewheel', plugins_url('includes/iLightBox/js/jquery.mousewheel.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'includes/iLightBox/js/jquery.mousewheel.js'));
      wp_register_script('ilightbox', plugins_url('includes/iLightBox/js/ilightbox.packed.js', __FILE__), array('jquery', 'jquery.requestAnimationFrame', 'jquery.mousewheel'), filemtime(plugin_dir_path(__FILE__) . 'includes/iLightBox/js/ilightbox.packed.js'));

      wp_register_script('collagePlus', plugins_url('includes/collagePlus/jquery.collagePlus.min.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'includes/collagePlus/jquery.collagePlus.min.js'));
      wp_register_script('removeWhitespace', plugins_url('includes/collagePlus/extras/jquery.removeWhitespace.min.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'includes/collagePlus/extras/jquery.removeWhitespace.min.js'));
      wp_register_script('unveil', plugins_url('includes/jquery-unveil/jquery.unveil.min.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-widget'));

      $skin = $this->settings['mediaplayer_skin'];
      if ((!file_exists(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/OutoftheBox_Media.js")) ||
              (!file_exists(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/css/style.css")) ||
              (!file_exists(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/player.php"))) {
        $skin = 'default';
      }

      wp_register_style('OutoftheBox.Media', plugins_url("/skins/$skin/css/style.css", __FILE__), false, (filemtime(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/css/style.css")));
      wp_register_script('jQuery.jplayer', plugins_url("/skins/$skin/jquery.jplayer/jplayer.playlist.min.js", __FILE__), array('jquery'));
      wp_register_script('jQuery.jplayer.playlist', plugins_url("/skins/$skin/jquery.jplayer/jquery.jplayer.min.js", __FILE__), array('jquery'));

      wp_register_script('OutoftheBox.Media', plugins_url("/skins/$skin/OutoftheBox_Media.js", __FILE__), array('jquery'), false, true);

      /* load in footer */
      wp_register_script('jQuery.iframe-transport', plugins_url('includes/jquery-file-upload/js/jquery.iframe-transport.js', __FILE__), array('jquery'), false, true);
      wp_register_script('jQuery.fileupload', plugins_url('includes/jquery-file-upload/js/jquery.fileupload.js', __FILE__), array('jquery'), false, true);
      wp_register_script('jQuery.fileupload-process', plugins_url('includes/jquery-file-upload/js/jquery.fileupload-process.js', __FILE__), array('jquery'), false, true);

      wp_register_script('OutoftheBox', plugins_url('includes/OutoftheBox.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'includes/OutoftheBox.js'), true);

      wp_enqueue_script('unveil');

      $post_max_size_bytes = min(OutoftheBox_return_bytes(ini_get('post_max_size')), OutoftheBox_return_bytes(ini_get('upload_max_filesize')));

      $localize = array(
          'plugin_ver' => OUTOFTHEBOX_VERSION,
          'plugin_url' => plugins_url('', __FILE__),
          'ajax_url' => admin_url('admin-ajax.php'),
          'js_url' => plugins_url('/skins/' . $this->settings['mediaplayer_skin'] . '/jquery.jplayer', __FILE__),
          'is_mobile' => wp_is_mobile(),
          'lightbox_skin' => $this->settings['lightbox_skin'],
          'lightbox_path' => $this->settings['lightbox_path'],
          'post_max_size' => $post_max_size_bytes,
          'google_analytics' => (($this->settings['google_analytics'] === 'Yes') ? 1 : 0),
          'refresh_nonce' => wp_create_nonce("outofthebox-get-filelist"),
          'gallery_nonce' => wp_create_nonce("outofthebox-get-gallery"),
          'upload_nonce' => wp_create_nonce("outofthebox-upload-file"),
          'delete_nonce' => wp_create_nonce("outofthebox-delete-entry"),
          'rename_nonce' => wp_create_nonce("outofthebox-rename-entry"),
          'move_nonce' => wp_create_nonce("outofthebox-move-entry"),
          'addfolder_nonce' => wp_create_nonce("outofthebox-add-folder"),
          'getplaylist_nonce' => wp_create_nonce("outofthebox-get-playlist"),
          'createzip_nonce' => wp_create_nonce("outofthebox-create-zip"),
          'createlink_nonce' => wp_create_nonce("outofthebox-create-link"),
          'str_success' => __('Success', 'outofthebox'),
          'str_error' => __('Error', 'outofthebox'),
          'str_inqueue' => __('In queue', 'outofthebox'),
          'str_uploading_local' => __('Uploading to Server', 'outofthebox'),
          'str_uploading_cloud' => __('Uploading to Cloud', 'outofthebox'),
          'str_error_title' => __('Error', 'outofthebox'),
          'str_close_title' => __('Close', 'outofthebox'),
          'str_start_title' => __('Start', 'outofthebox'),
          'str_cancel_title' => __('Cancel', 'outofthebox'),
          'str_delete_title' => __('Delete', 'outofthebox'),
          'str_zip_title' => __('Create zip file', 'outofthebox'),
          'str_delete' => __('Do you really want to delete:', 'outofthebox'),
          'str_delete_multiple' => __('Do you really want to delete these files?', 'outofthebox'),
          'str_rename_title' => __('Rename', 'outofthebox'),
          'str_rename' => __('Rename to:', 'outofthebox'),
          'str_no_filelist' => __("Can't receive filelist", 'outofthebox'),
          'str_addfolder_title' => __('Add folder', 'outofthebox'),
          'str_addfolder' => __('New folder', 'outofthebox'),
          'str_zip_nofiles' => __('No files found or selected', 'outofthebox'),
          'str_zip_createzip' => __('Creating zip file', 'outofthebox'),
          'str_share_link' => __('Share file', 'outofthebox'),
          'str_create_shared_link' => __('Creating shared link...', 'outofthebox'),
          'str_previous_title' => __('Previous', 'outofthebox'),
          'str_next_title' => __('Next', 'outofthebox'),
          'str_xhrError_title' => __('This content failed to load', 'outofthebox'),
          'str_imgError_title' => __('This image failed to load', 'outofthebox'),
          'str_startslideshow' => __('Start slideshow', 'outofthebox'),
          'str_stopslideshow' => __('Stop slideshow', 'outofthebox'),
          'str_nolink' => __('Not yet linked to a folder', 'outofthebox'),
          'maxNumberOfFiles' => __('Maximum number of files exceeded', 'outofthebox'),
          'acceptFileTypes' => __('File type not allowed', 'outofthebox'),
          'maxFileSize' => __('File is too large', 'outofthebox'),
          'minFileSize' => __('File is too small', 'outofthebox'),
          'str_iframe_loggedin' => "<div class='empty_iframe'><h1>" . __('Still Waiting?', 'outofthebox') . "</h1><span>" . __("If the document doesn't open, you are probably trying to access a protected file which requires you to be logged in on Dropbox.", 'outofthebox') . " <strong>" . __('Try to open the file in a new window.', 'outofthebox') . "</strong></span></div>"
      );

      wp_localize_script('OutoftheBox', 'OutoftheBox_vars', $localize);
    }

    public function LoadLastScripts() {
      /* Load scripts as last to support themes with Isotope */
      wp_register_script('imagesloaded', plugins_url('includes/jquery-qTip/imagesloaded.pkgd.min.js', __FILE__), null, false, true);
      wp_register_script('qtip', plugins_url('includes/jquery-qTip/jquery.qtip.min.js', __FILE__), array('jquery', 'imagesloaded'), false, true);
    }

    public function LoadStyles() {
      /* First looks in theme/template directories for the stylesheet, falling back to plugin directory */
      $cssfile = 'outofthebox.css';
      if (file_exists(get_stylesheet_directory() . '/' . $cssfile)) {
        $stylesheet = get_stylesheet_directory_uri() . '/' . $cssfile;
      } elseif (file_exists(get_template_directory() . '/' . $cssfile)) {
        $stylesheet = get_template_directory_uri() . '/' . $cssfile;
      } else {
        $stylesheet = plugins_url('css/' . $cssfile, __FILE__);
      }

      wp_register_style('OutoftheBox-fileupload-jquery-ui', plugins_url('includes/jquery-file-upload/css', __FILE__) . '/jquery.fileupload-ui.css');

      $skin = $this->settings['lightbox_skin'];
      wp_register_style('ilightbox', plugins_url('includes/iLightBox/css/ilightbox.css', __FILE__), false, (filemtime(__DIR__ . "/includes/iLightBox/css/ilightbox.css")));
      wp_register_style('ilightbox-skin', plugins_url('includes/iLightBox/' . $skin . '-skin/skin.css', __FILE__), false, (filemtime(__DIR__ . "/includes/iLightBox/" . $skin . "-skin/skin.css")));

      wp_register_style('qtip', plugins_url('includes/jquery-qTip/jquery.qtip.min.css', __FILE__), null, false);
      wp_register_style('OutoftheBox-dialogs', plugins_url('css', __FILE__) . '/jquery-ui-1.10.3.custom.css');

      wp_register_style('Awesome-Font-css', plugins_url('includes/font-awesome/css/font-awesome.min.css', __FILE__), false, (filemtime(__DIR__ . "/includes/font-awesome/css/font-awesome.min.css")));
      wp_enqueue_style('Awesome-Font-css');

      wp_register_style('OutoftheBox', $stylesheet, false, filemtime(__FILE__));
      wp_enqueue_style('OutoftheBox');
    }

    public function LoadIEstyles() {

      echo "<!--[if IE]>\n";
      echo "<link rel='stylesheet' type='text/css' href='" . plugins_url('css/outofthebox-skin-ie.css', __FILE__) . "' />\n";
      echo "<![endif]-->\n";
    }

    public function GravityFormsAddon() {
      require_once 'includes/OutoftheBox_GravityForms.php';
    }

    public function StartProcess() {
      if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
          case 'outofthebox-get-filelist':
            require_once 'includes/OutoftheBox_Filebrowser.php';
            $processor = new OutoftheBox_Filebrowser;
            $processor->startProcess();
            break;
          case 'outofthebox-download':
          case 'outofthebox-preview':
          case 'outofthebox-create-zip':
          case 'outofthebox-create-link':
          case 'outofthebox-embedded':
          case 'outofthebox-revoke':
            require_once(ABSPATH . 'wp-includes/pluggable.php');
            require_once 'includes/OutoftheBox_Dropbox.php';
            $processor = new OutoftheBox_Dropbox;
            $processor->startProcess();
            break;

          case 'outofthebox-get-gallery':
            require_once 'includes/OutoftheBox_Gallery.php';
            $processor = new OutoftheBox_Gallery;
            $processor->startProcess();
            break;

          case 'outofthebox-upload-file':
          case 'outofthebox-delete-entry':
          case 'outofthebox-delete-entries':
          case 'outofthebox-rename-entry':
          case 'outofthebox-move-entry':
          case 'outofthebox-add-folder':
            require_once 'includes/OutoftheBox_Dropbox.php';
            $processor = new OutoftheBox_Dropbox;
            $processor->startProcess();
            break;

          case 'outofthebox-get-playlist':
            require_once 'includes/OutoftheBox_Mediaplayer.php';
            $processor = new OutoftheBox_Mediaplayer;
            $processor->startProcess();
            break;
        }
      }
    }

    public function CustomCss() {
      if (!empty($this->settings['custom_css'])) {
        echo '<!-- Custom OutoftheBox CSS Styles -->' . "\n";
        echo '<style type="text/css" media="screen">' . "\n";
        echo $this->settings['custom_css'] . "\n";
        echo '</style>' . "\n";
      }
    }

    public function CreateTemplate($atts = array()) {
      require_once 'includes/OutoftheBox_Dropbox.php';
      $processor = new OutoftheBox_Dropbox;
      return $processor->createFromShortcode($atts);
    }

    public function GenerateThumbnail() {
      require_once 'includes/OutoftheBox_Dropbox.php';
      $processor = new OutoftheBox_Dropbox;
      return $processor->createThumb();
    }

    public function GetPopup() {
      include OUTOFTHEBOX_ROOTDIR . '/templates/tinymce_popup.php';
      die();
    }

    public function UnlinkUserToFolder() {
      check_ajax_referer('outofthebox-create-link');

      if (current_user_can('manage_options')) {
        if ($_REQUEST['userid'] === 'GUEST') {
          $result = delete_site_option('out_of_the_box_guestlinkedto');
        } else {
          $result = delete_user_option($_REQUEST['userid'], 'out_of_the_box_linkedto', false);
        }

        if ($result !== false) {
          die('1');
        }
      }
      die('-1');
    }

    public function LinkUserToFolder() {
      check_ajax_referer('outofthebox-create-link');

      if (current_user_can('manage_options')) {
        $linkedto = array('folderid' => rawurldecode($_REQUEST['id']), 'foldertext' => rawurldecode($_REQUEST['id']));

        if ($_REQUEST['userid'] === 'GUEST') {
          $result = update_site_option('out_of_the_box_guestlinkedto', $linkedto);
        } else {
          $result = update_user_option($_REQUEST['userid'], 'out_of_the_box_linkedto', $linkedto, false);
        }

        if ($result !== false) {
          die('1');
        }
      }
      die('-1');
    }

    public function UpdateUserfolder($user_id, $old_user_data = false) {
      $outoftheboxlists = get_option('out_of_the_box_lists', array());
      $updatelists = array();

      foreach ($outoftheboxlists as $list) {

        if (isset($list['user_upload_folders']) && $list['user_upload_folders'] === 'auto') {
          $updatelists[] = $list;
        }
      }


      if (count($updatelists) > 0) {
        require_once 'includes/OutoftheBox_Dropbox.php';
        $processor = new OutoftheBox_Dropbox;

        foreach ($updatelists as $listoptions) {

          $oldfoldername = false;
          if ($old_user_data !== false) {
            $oldfoldername = strtr($processor->settings['userfolder_name'], array(
                "%user_login%" => $old_user_data->user_login,
                "%user_email%" => $old_user_data->user_email,
                "%user_firstname%" => isset($old_user_data->user_firstname) ? $old_user_data->user_firstname : '?',
                "%user_lastname%" => isset($old_user_data->user_lastname) ? $old_user_data->user_lastname : '?',
                "%display_name%" => $old_user_data->display_name,
                "%ID%" => $old_user_data->ID
            ));
          }

          $new_user = get_user_by('id', $user_id);

          $userfoldername = strtr($processor->settings['userfolder_name'], array(
              "%user_login%" => $new_user->user_login,
              "%user_email%" => $new_user->user_email,
              "%user_firstname%" => isset($new_user->user_firstname) ? $new_user->user_firstname : '?',
              "%user_lastname%" => isset($new_user->user_lastname) ? $new_user->user_lastname : '?',
              "%display_name%" => $new_user->display_name,
              "%ID%" => $new_user->ID
          ));


          if ($oldfoldername === false || ($oldfoldername !== $userfoldername)) {
            $processor->userChangeFolder($listoptions, $userfoldername, $oldfoldername, false);
          }
        }
      }
    }

    public function DeleteUserfolder($user_id) {
      $outoftheboxlists = get_option('out_of_the_box_lists', array());
      $updatelists = array();

      foreach ($outoftheboxlists as $list) {

        if (isset($list['user_upload_folders']) && $list['user_upload_folders'] === 'auto') {
          $updatelists[] = $list;
        }
      }

      if (count($updatelists) > 0) {
        require_once 'includes/OutoftheBox_Dropbox.php';
        $processor = new OutoftheBox_Dropbox;

        foreach ($updatelists as $listoptions) {

          $deleted_user = get_user_by('id', $user_id);

          $userfoldername = strtr($processor->settings['userfolder_name'], array(
              "%user_login%" => $deleted_user->user_login,
              "%user_email%" => $deleted_user->user_email,
              "%display_name%" => $deleted_user->display_name,
              "%ID%" => $deleted_user->ID
          ));

          $processor->userChangeFolder($listoptions, $userfoldername, false, true);
        }
      }
    }

  }

}

if (class_exists('OutoftheBox')) {
  /* Installation and uninstallation hooks */
  register_activation_hook(__FILE__, 'OutoftheBox_Network_Activate');
  register_deactivation_hook(__FILE__, 'OutoftheBox_Network_Deactivate');

  $OutoftheBox = new OutoftheBox();
}

/* Activation & Deactivation */

/**
 * Activate the plugin on network
 */
function OutoftheBox_Network_Activate($network_wide) {
  if (is_multisite() && $network_wide) { // See if being activated on the entire network or one blog
    global $wpdb;

    /* Get this so we can switch back to it later */
    $current_blog = $wpdb->blogid;
    /* For storing the list of activated blogs */
    $activated = array();

    /* Get all blogs in the network and activate plugin on each one */
    $sql = "SELECT blog_id FROM %d";
    $blog_ids = $wpdb->get_col($wpdb->prepare($sql, $wpdb->blogs));
    foreach ($blog_ids as $blog_id) {
      switch_to_blog($blog_id);
      OutoftheBox_Activate(); // The normal activation function
      $activated[] = $blog_id;
    }

    /* Switch back to the current blog */
    switch_to_blog($current_blog);

    /* Store the array for a later function */
    update_site_option('out_of_the_box_activated', $activated);
  } else { // Running on a single blog
    OutoftheBox_Activate(); // The normal activation function
  }
}

/**
 * Activate the plugin
 */
function OutoftheBox_Activate() {
  add_option('out_of_the_box_settings', array(
      'dropbox_app_key' => '',
      'dropbox_app_secret' => '',
      'dropbox_app_token' => '',
      'dropbox-auth-csrf-token' => '',
      'purcasecode' => '',
      'custom_css' => '',
      'shortlinks' => 'Dropbox',
      'google_analytics' => 'No',
      'lightbox_skin' => 'metro-black',
      'lightbox_path' => 'horizontal',
      'mediaplayer_skin' => 'default',
      'bitly_login' => '', 'bitly_apikey' => '',
      'thumbnails' => 'Out-of-the-Box',
      'userfolder_name' => '%user_login% (%user_email%)',
      'userfolder_oncreation' => 'Yes',
      'userfolder_onfirstvisit' => 'No',
      'userfolder_update' => 'Yes',
      'userfolder_remove' => 'Yes',
      'download_template' => '',
      'upload_template' => '',
      'delete_template' => '',
      'filelist_template' => '')
  );

  update_option('out_of_the_box_lists', array());
  @unlink(OUTOFTHEBOX_CACHEDIR . '/index');
}

/**
 * Deactivate the plugin on network
 */
function OutoftheBox_Network_Deactivate($network_wide) {
  if (is_multisite() && $network_wide) { // See if being activated on the entire network or one blog
    global $wpdb;

    // Get this so we can switch back to it later
    $current_blog = $wpdb->blogid;

    // If the option does not exist, plugin was not set to be network active
    if (get_site_option('out_of_the_box_activated') === false) {
      return false;
    }

    // Get all blogs in the network
    $activated = get_site_option('out_of_the_box_activated');

    $sql = "SELECT blog_id FROM %d";
    $blog_ids = $wpdb->get_col($wpdb->prepare($sql, $wpdb->blogs));
    foreach ($blog_ids as $blog_id) {
      if (!in_array($blog_id, $activated)) { // Plugin is not activated on that blog
        switch_to_blog($blog_id);
        OutoftheBox_Deactivate();
      }
    }

    // Switch back to the current blog
    switch_to_blog($current_blog);

    // Store the array for a later function
    update_site_option('out_of_the_box_activated', $activated);
  } else { // Running on a single blog
    OutoftheBox_Deactivate();
  }
}

/**
 * Deactivate the plugin
 */
function OutoftheBox_Deactivate() {
  update_option('out_of_the_box_lists', array());

  $wp_upload_dir = wp_upload_dir();
  $uploaddir = $wp_upload_dir['basedir'] . '/outofthebox';
  $uploadfiles = @scandir($uploaddir);

  if ($uploadfiles !== FALSE) {
    foreach ($uploadfiles as $uploadfile) {
      @unlink($uploaddir . '/' . $uploadfile);
    }
    @rmdir($uploaddir);
  }
}

/**
 * Deactivate the plugin on network
 */
function OutoftheBox_Network_Uninstall($network_wide) {
  if (is_multisite() && $network_wide) { // See if being activated on the entire network or one blog
    global $wpdb;

    // Get this so we can switch back to it later
    $current_blog = $wpdb->blogid;

    // If the option does not exist, plugin was not set to be network active
    if (get_site_option('out_of_the_box_activated') === false) {
      return false;
    }

    // Get all blogs in the network
    $activated = get_site_option('out_of_the_box_activated');

    $sql = "SELECT blog_id FROM %d";
    $blog_ids = $wpdb->get_col($wpdb->prepare($sql, $wpdb->blogs));
    foreach ($blog_ids as $blog_id) {
      if (!in_array($blog_id, $activated)) { // Plugin is not activated on that blog
        switch_to_blog($blog_id);
        OutoftheBox_Uninstall();
      }
    }

    // Switch back to the current blog
    switch_to_blog($current_blog);

    // Store the array for a later function
    update_site_option('out_of_the_box_activated', $activated);
  } else { // Running on a single blog
    OutoftheBox_Uninstall();
  }
}

/**
 * Deactivate the plugin
 */
function OutoftheBox_Uninstall() {
  //delete_option('out_of_the_box_settings');
  delete_option('out_of_the_box_lists');
  delete_option('out_of_the_box_activated');
  delete_site_option('out_of_the_box_guestlinkedto');

  $cachefiles = @scandir(OUTOFTHEBOX_CACHEDIR);

  if ($cachefiles !== FALSE) {
    $cachefiles = array_diff($cachefiles, array('..', '.', '.htaccess'));
    foreach ($cachefiles as $cachefile) {
      @unlink(OUTOFTHEBOX_CACHEDIR . '/' . $cachefile);
    }
  }
}

//Helpers

function OutoftheBox_return_bytes($size_str) {
  switch (substr($size_str, -1)) {
    case 'M': case 'm': return (int) $size_str * 1048576;
    case 'K': case 'k': return (int) $size_str * 1024;
    case 'G': case 'g': return (int) $size_str * 1073741824;
    default: return $size_str;
  }
}

function OutoftheBox_bytesToSize1024($bytes, $precision = 2) {
// human readable format -- powers of 1024
  $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
  return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision) . ' ' . $unit[$i];
}

function OutoftheBox_mbPathinfo($filepath) {
  preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $filepath, $m);
  if (isset($m[1]))
    $ret['dirname'] = $m[1];
  if (isset($m[2]))
    $ret['basename'] = $m[2];
  if (isset($m[5]))
    $ret['extension'] = $m[5];
  if (isset($m[3]))
    $ret['filename'] = $m[3];

  if (substr($filepath, -1) === '.') {
    $ret['basename'] .= '.';
    unset($ret['extension']);
  }

  return $ret;
}
