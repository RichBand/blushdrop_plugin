<?php
if (!class_exists('OutoftheBox_Settings')) {

  class OutoftheBox_settings {

    private $settings_key = 'out_of_the_box_settings';
    private $plugin_options_key = 'OutoftheBox_settings';
    private $plugin_network_options_key = 'OutoftheBox_network_settings';
    private $plugin_id = 5529125;

    /**
     * Construct the plugin object
     */
    public function __construct() {

      /* Check if plugin can be used */
      if ((version_compare(PHP_VERSION, '5.3.0') < 0) || (!function_exists('curl_init'))) {
        add_action('admin_notices', array(&$this, 'AdminNotice'));
        return;
      } else {

        /* Init */
        add_action('init', array(&$this, 'LoadSettings'));
        add_action('admin_init', array(&$this, 'RegisterSettings'));
        add_action('admin_init', array(&$this, 'CheckForUpdates'));
        add_action('admin_enqueue_scripts', array(&$this, 'LoadAdmin'));

        /* add TinyMCE button */
        /* Depends on the theme were to load.... */
        add_action('init', array(&$this, 'ShortcodeButtonInit'));
        add_action('admin_head', array(&$this, 'ShortcodeButtonInit'));

        /* Add menu's */
        add_action('admin_menu', array(&$this, 'AddMenu'));
        add_action('network_admin_menu', array(&$this, 'AddNetworkMenu'));

        /* Network save settings call */
        add_action('network_admin_edit_' . $this->plugin_network_options_key, array($this, 'SaveNetworkSettings'));
      }
    }

    public function LoadAdmin($hook) {

      if (!isset($this->settingspage) && !isset($this->filebrowserpage)) {
        return;
      } elseif ($hook == $this->settingspage || $hook == $this->filebrowserpage || $hook == $this->userpage) {
        require_once 'includes/OutoftheBox_Processor.php';
        require_once 'includes/OutoftheBox_Dropbox.php';
        $this->OutoftheBox_Dropbox = new OutoftheBox_Dropbox;
      }

      if ($hook == $this->filebrowserpage || $hook == $this->userpage) {
        global $OutoftheBox;
        $OutoftheBox->LoadScripts();
        $OutoftheBox->LoadLastScripts();
        $OutoftheBox->LoadStyles();
      }

      if ($hook == $this->userpage) {
        add_thickbox();
      }

      if ($hook == $this->settingspage) {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-position');
        wp_enqueue_script('jquery-effects-fade');
        wp_enqueue_script('jquery');

        wp_register_script('ddslick', OUTOFTHEBOX_ROOTPATH . '/includes/jquery-ddslick/jquery.ddslick.min.js', array('jquery'), false, true);
        wp_enqueue_script('ddslick');

        wp_register_script('Radiobuttons', OUTOFTHEBOX_ROOTPATH . '/includes/jquery-radiobutton/jquery-radiobutton-2.0.js', array('jquery'), false, true);
        wp_enqueue_script('Radiobuttons');

        wp_register_script('qtip', plugins_url('includes/jquery-qTip/jquery.qtip.min.js', __FILE__), array('jquery'), false, true);
        wp_enqueue_script('qtip');

        wp_register_script('unveil', plugins_url('includes/jquery-unveil/jquery.unveil.min.js', __FILE__), array('jquery'), false, true);
        wp_enqueue_script('unveil');

        wp_register_script('OutoftheBox.tinymce', OUTOFTHEBOX_ROOTPATH . '/includes/OutoftheBox_tinymce_popup.js', array('jquery'), filemtime(OUTOFTHEBOX_ROOTDIR . '/includes/OutoftheBox_tinymce_popup.js'), true);
        wp_enqueue_script('OutoftheBox.tinymce');

        wp_register_style('qtip', plugins_url('includes/jquery-qTip/jquery.qtip.min.css', __FILE__), null, false);
        wp_enqueue_style('qtip');

        wp_register_style('OutoftheBox.tinymce', plugins_url('css/outofthebox_tinymce.css', __FILE__), null, false);
        wp_enqueue_style('OutoftheBox.tinymce');

        wp_register_style('OutoftheBox-dialogs', plugins_url('css', __FILE__) . '/jquery-ui-1.10.3.custom.css');
        wp_enqueue_style('OutoftheBox-dialogs');
      }
    }

    /**
     * add a menu
     */
    public function AddMenu() {
      // Add a page to manage this plugin's settings
      add_menu_page('Out-of-the-Box', 'Out-of-the-Box', 'manage_options', $this->plugin_options_key, array(&$this, 'SettingsPage'), plugin_dir_url(__FILE__) . 'css/images/dropbox_logo_blue_small.png');
      $this->settingspage = add_submenu_page($this->plugin_options_key, 'Out-of-the-Box ' . __('Settings'), __('Settings'), 'manage_options', $this->plugin_options_key, array(&$this, 'SettingsPage'));
      $this->userpage = add_submenu_page($this->plugin_options_key, __('Link users to folder', 'outofthebox'), __('Link users to folder', 'outofthebox'), 'manage_options', $this->plugin_options_key . '_linkusers', array(&$this, 'LinkUsers'));
      $this->filebrowserpage = add_submenu_page($this->plugin_options_key, __('File browser', 'outofthebox'), __('File browser', 'outofthebox'), 'manage_options', $this->plugin_options_key . '_filebrowser', array(&$this, 'Filebrowser'));
    }

    public function AddNetworkMenu() {
      add_menu_page('Out-of-the-Box', 'Out-of-the-Box', 'manage_options', $this->plugin_network_options_key, array(&$this, 'NetworkSettingsPage'), plugin_dir_url(__FILE__) . 'css/images/dropbox_logo_blue_small.png');
    }

    public function RegisterSettings() {
      register_setting($this->settings_key, $this->settings_key);
    }

    function LoadSettings() {
      $this->settings = (array) get_option($this->settings_key);
    }

    public function SettingsPage() {
      if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'outofthebox'));
      }

      if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        update_option('out_of_the_box_lists', array());
      }
      include(sprintf("%s/templates/admin.php", OUTOFTHEBOX_ROOTDIR));
    }

    public function NetworkSettingsPage() {
      $outofthebox_purchaseid = get_site_option('outofthebox_purchaseid');
      ?>
      <div class="wrap">
        <div class='left' style="min-width:400px; max-width:650px; padding: 0 20px 0 0; float:left">
          <?php if ($_GET['updated']) { ?>
            <div id="message" class="updated"><p><?php _e('Saved!', 'outofthebox'); ?></p></div>
          <?php } ?>
          <form action="<?php echo network_admin_url('edit.php?action=' . $this->plugin_network_options_key); ?>" method="post">
            <?php
            echo __('If you would like to receive updates, please insert your Purchase code', 'outofthebox') . '. ' .
            '<a href="http://support.envato.com/index.php?/Knowledgebase/Article/View/506/54/where-can-i-find-my-purchase-code">' .
            __('Where do I find the purchase code?', 'outofthebox') . '</a>.';
            ?>
            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <th scope="row"><?php _e('Purchase Code', 'outofthebox'); ?></th>
                  <td><input type="text" name="outofthebox_purchaseid" id="outofthebox_purchaseid" value="<?php echo $outofthebox_purchaseid; ?>" placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX" maxlength="37" style="width:90%"/></td>
                </tr>
              </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
          </form>
        </div>
      </div>
      <?php
    }

    public function SaveNetworkSettings() {
      if (current_user_can('manage_network_options')) {
        update_site_option('outofthebox_purchaseid', $_POST['outofthebox_purchaseid']);
      }

      wp_redirect(
              add_query_arg(
                      array('page' => $this->plugin_network_options_key, 'updated' => 'true'), network_admin_url('admin.php')
              )
      );
      exit;
    }

    function Filebrowser() {
      ?>
      <div class="wrap adminfilebrowser">
        <?php
        screen_icon('outofthebox');
        echo '<h2>' . __('File browser', 'outofthebox') . '</h2>';
        echo $this->OutoftheBox_Dropbox->createFromShortcode(
                array('mode' => 'files',
                  'viewrole' => 'all',
                  'downloadrole' => 'all',
                  'uploadrole' => 'all',
                  'upload' => '1',
                  'rename' => '1',
                  'delete' => '1',
                  'addfolder' => '1')
        );
        ?>
      </div>
      <?php
    }

    function LinkUsers() {
      require_once 'includes/OutoftheBox_LinkUsers.php';
    }

    public function CheckDropboxApp() {
      $page = isset($_GET["page"]) ? '?page=' . $_GET["page"] : '';
      $location = get_admin_url(null, 'admin.php' . $page);

      /* Check if Auto-update is being activated */
      if (isset($_REQUEST['purchase_code']) && $_REQUEST['plugin_id'] && ((int) $_REQUEST['plugin_id'] === $this->plugin_id)) {
        $this->settings['purcasecode'] = sanitize_key($_REQUEST['purchase_code']);
        update_option($this->settings_key, $this->settings);
        $this->LoadSettings();
        $this->OutoftheBox_Dropbox->settings = get_option($this->settings_key);
        echo '<script type="text/javascript">window.opener.parent.location.href = "' . $location . '"; window.close();</script>';
      } elseif (!empty($this->settings['purcasecode'])) {
        echo "<div id='message' class='updated'><p>" . __('The plugin is <strong>Activated</strong> and the <strong>Auto-Updater</strong> enabled', 'outofthebox') . ". " . __('Your purchasecode', 'outofthebox') . ": " . esc_attr($this->settings['purcasecode']) . " </p></div>";
      } else {
        echo "<div id='message' class='error'><p>" . __('The plugin is <strong>Not Activated</strong> and the <strong>Auto-Updater</strong> disabled', 'outofthebox') . ". " . __('Please activate the plugin', 'outofthebox') . ".</p><p><input id='updater_button' type='button' value='Enable Auto-Updater' class='button-primary'/></p></div>";
      }

      /* Do Authorization stuff */
      $authorize = true;
      $current_url = parse_url(admin_url('admin.php?page=OutoftheBox_settings'));
      $can_do_own_auth = ($current_url['scheme'] === 'https' || $current_url['host'] === 'localhost') ? true : false;
      $use_own_app = ((!empty($this->settings['dropbox_app_key'])) && (!empty($this->settings['dropbox_app_secret']))) ? true : false;

      $appInfo = $this->OutoftheBox_Dropbox->setAppConfig();
      if (is_wp_error($appInfo)) {
        echo "<div id='message' class='error'><p>" . $appInfo->get_error_message() . "</p></div>";
        return false;
      }


      $redirectUrl = $this->OutoftheBox_Dropbox->setRedirectUri($location);
      $redirectMsg = '';
      if ($use_own_app) {
        if ($can_do_own_auth) {
          $redirectMsg = "<ul>
            <li>Add <strong><em>$redirectUrl</em></strong> to the <strong>OAuth redirect URIs</strong> in the <a href='https://www.dropbox.com/developers/apps/' target='_blank'>App Console</a></li>
          </ul>";
        } else {
          $redirectMsg = "<p>Because you don't mind using Out-of-the-Box without a SSL certificate, we will direct you via our site.</p><p><ul>
            <li>Add <strong><em>https://www.florisdeleeuw.nl:443/out-of-the-box/index.php</em></strong> to the <strong>OAuth redirect URIs</strong> in the <a href='https://www.dropbox.com/developers/apps/' target='_blank'>App Console</a></li>
          </ul></p>";
        }
      } else {
        $redirectMsg = "<p>We will direct you to Dropbox via our site.</p>";
      }
      $authorizebutton = "<input id='authorizeDropbox_button' type='button' value='" . __('(Re) Authorize the Plugin!', 'outofthebox') . "' class='button-primary'/>";
      $revokebutton = "<input id='revokeDropbox_button' type='button' value='" . __('Revoke authorization', 'outofthebox') . "' class='button-secondary'/>&nbsp;";

      // are we coming from dropbox's auth page?
      if (!empty($_GET['code'])) {
        $createToken = $this->OutoftheBox_Dropbox->createToken();

        if (is_wp_error($createToken)) {
          echo "<div id='message' class='error'><p>" . $createToken->get_error_message() . '</p><p>' . $redirectMsg . $authorizebutton . "</p></div>";
        } else {
          echo "<script type='text/javascript'>window.location.href = '" . $location . "';</script>";
        }

        $this->LoadSettings();
      } elseif (!empty($_GET['_token'])) {

        $newtoken = $_GET['_token'];
        $this->settings['dropbox_app_token'] = $newtoken;
        update_option($this->settings_key, $this->settings);

        $this->LoadSettings();
        $this->OutoftheBox_Dropbox->settings = get_option($this->settings_key);
        echo "<script type='text/javascript'>window.location.href = '" . $location . "';</script>";
      }

      $authUrl = $this->OutoftheBox_Dropbox->startWebAuth();

      if ($use_own_app && $can_do_own_auth) {
        $authUrl = $this->OutoftheBox_Dropbox->startWebAuth();
      } else {
        $encodedredirect = strtr(base64_encode($location), '+/=', '-_~');
        $authUrl = 'https://www.florisdeleeuw.nl:443/out-of-the-box/index.php?app_key=' . $appInfo['appInfo']->getKey() . '&app_secret=' . $appInfo['appInfo']->getSecret() . '&wp_redirect=' . $encodedredirect;
      }

      $hasToken = $this->OutoftheBox_Dropbox->loadToken();

      if (is_wp_error($hasToken)) {
        echo "<div id='message' class='error'><p>" . $hasToken->get_error_message() . '</p><p>' . $redirectMsg . $authorizebutton . "</p></div>";
      } else {

        $client = $this->OutoftheBox_Dropbox->startClient();
        $accountInfo = $this->OutoftheBox_Dropbox->getAccountInfo();

        if ($accountInfo === false) {
          $error = new WP_Error('broke', __("Plugin isn't linked to your Dropbox anymore... Please Reauthorize!", 'outofthebox'));
          echo "<div id='message' class='error'><p>" . $error->get_error_message() . '</p><p>' . $redirectMsg . $authorizebutton . "</p></div>";
        } else if (is_wp_error($accountInfo)) {
          $error = $accountInfo;
          echo "<div id='message' class='error'><p>" . $error->get_error_message() . '</p><p>' . $redirectMsg . $authorizebutton . "</p></div>";
        } else {
          $user = $accountInfo['display_name'];
          $email = $accountInfo['email'];
          $quotaused = OutoftheBox_bytesToSize1024($accountInfo['quota_info']['normal'] + $accountInfo['quota_info']['shared']);
          $quotatotal = OutoftheBox_bytesToSize1024($accountInfo['quota_info']['quota']);
          $authorize = false;
          echo "<div id='message' class='updated'>
        <p>" . __('Out-of-the-Box is succesfully authorized and linked with dropbox account:', 'outofthebox') . "<br/><strong>$user ($email - $quotaused/$quotatotal)</strong></p><p>" . $revokebutton . $authorizebutton . "</p></div>";
        }
      }
      ?>
      <script type="text/javascript" >
        jQuery(document).ready(function ($) {
          $('#authorizeDropbox_button').click(function () {
            window.location = '<?php echo $authUrl; ?>';
          });

          $('#revokeDropbox_button').click(function () {
            $.ajax({type: "POST",
              url: '<?php echo admin_url('admin-ajax.php'); ?>',
              data: {
                action: 'outofthebox-revoke'
              },
              success: function (response) {
                location.reload(true)
              },
              dataType: 'json'
            });
          });

          $('#updater_button').click(function () {
            popup = window.open('https://www.florisdeleeuw.nl/updates/activate.php?init=1&client_url=<?php echo strtr(base64_encode($location), '+/=', '-_~'); ?>&plugin_id=<?php echo $this->plugin_id; ?>', "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=700");
          });
        });
      </script>
      <?php
    }

    public function AdminNotice() {
      global $pagenow;
      if ($pagenow == 'index.php' || $pagenow == 'plugins.php') {
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
          echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>' . __('You need at least PHP 5.3 if you want to use Out-of-the-Box', 'outofthebox') . '. ' .
          __('You are using:', 'outofthebox') . ' <u>' . phpversion() . '</u></p></div>';
        } elseif (!function_exists('curl_init')) {
          echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>' . __("You don't have the cURL PHP extension installed (couldn't find function \"curl_init\"), please enable or install this extension", 'outofthebox') . '. ' .
          '</p></div>';
        }
      }
    }

    public function CheckForUpdates() {
      /* Updater */
      $purchasecode = false;

      $plugin = dirname(plugin_basename(__FILE__)) . '/out-of-the-box.php';
      if (is_multisite() && is_plugin_active_for_network($plugin)) {
        $purchasecode = get_site_option('outofthebox_purchaseid');
      } else {
        $purchasecode = $this->settings['purcasecode'];
      }

      if (!empty($purchasecode)) {
        require_once 'includes/plugin-update-checker/plugin-update-checker.php';
        $updatechecker = PucFactory::buildUpdateChecker('https://www.florisdeleeuw.nl/updates/?action=get_metadata&slug=' . dirname(plugin_basename(__FILE__)) . '&purchase_code=' . $purchasecode . '&plugin_id=' . $this->plugin_id, dirname(__FILE__) . '/out-of-the-box.php');
      }
    }

    public function checkDependencies() {
      $check = array();

      array_push($check, array('success' => true, 'warning' => false, 'value' => __('WordPress version', 'outofthebox'), 'description' => get_bloginfo('version')));
      array_push($check, array('success' => true, 'warning' => false, 'value' => __('Plugin version', 'outofthebox'), 'description' => OUTOFTHEBOX_VERSION));

      if (version_compare(PHP_VERSION, '5.3.0') < 0) {
        array_push($check, array('success' => false, 'warning' => false, 'value' => __('PHP version', 'outofthebox'), 'description' => phpversion() . ' ' . __('You need at least PHP 5.3 if you want to use Out-of-the-Box', 'outofthebox')));
      } else {
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('PHP version', 'outofthebox'), 'description' => phpversion()));
      }

      //Check if we can use oAuth 2 authentication, we need SSL or localhost
      $current_url = parse_url(admin_url('admin.php?page=OutoftheBox_settings'));
      $can_do_auth = ($current_url['scheme'] === 'https' || $current_url['host'] === 'localhost') ? true : false;
      if ($can_do_auth) {
        if ($current_url['scheme'] === 'https')
          array_push($check, array('success' => true, 'warning' => false, 'value' => __('Using SSL', 'outofthebox'), 'description' => __('You are using a secure connection', 'outofthebox')));

        if ($current_url['host'] === 'localhost')
          array_push($check, array('success' => true, 'warning' => true, 'value' => __('Using SSL', 'outofthebox'), 'description' => __("You are using running a server on 'localhost'.", 'outofthebox') . ' ' . __("If your using this Plugin somewhere else and would like to have a secure connection, you probably need a SSL certificate.", 'outofthebox')));
      } else {
        array_push($check, array('success' => false, 'warning' => true, 'value' => __('Using SSL', 'outofthebox'), 'description' => __('SSL is required for authentication with the Dropbox API.', 'outofthebox') . " <a href='http://goo.gl/FxM4QN' title='Out of the Box documentation' target='_blank'>" . __('See our documentation how to obtain a SSL certificate or use Out-of-the-Box without one.', 'outofthebox') . "</a>"));
      }

      //Check if we can use CURL
      if (function_exists('curl_init')) {
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('cURL PHP extension', 'outofthebox'), 'description' => __('You have the cURL PHP extension installed', 'outofthebox')));
      } else {
        array_push($check, array('success' => false, 'warning' => false, 'value' => __('cURL PHP extension', 'outofthebox'), 'description' => __("You don't have the cURL PHP extension installed (couldn't find function \"curl_init\"), please enable or install this extension", 'outofthebox')));
      }

      //Check if temp dir is writeable
      $uploadir = wp_upload_dir();

      if (!is_writable($uploadir['path'])) {
        array_push($check, array('success' => false, 'warning' => false, 'value' => __('Is TMP directory writable?', 'outofthebox'), 'description' => __('TMP directory', 'outofthebox') . ' \'' . $uploadir['path'] . '\' ' . __('isn\'t writable. You are not able to upload files to Dropbox.', 'outofthebox') . ' ' . __('Make sure TMP directory is writable', 'outofthebox')));
      } else {
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is TMP directory writable?', 'outofthebox'), 'description' => __('TMP directory is writable', 'outofthebox')));
      }

      //Check if cache dir is writeable
      if (!file_exists(OUTOFTHEBOX_CACHEDIR)) {
        @mkdir(OUTOFTHEBOX_CACHEDIR, 0755);
      }

      if (!is_writable(OUTOFTHEBOX_CACHEDIR)) {
        @chmod(OUTOFTHEBOX_CACHEDIR, 0755);

        if (!is_writable(OUTOFTHEBOX_CACHEDIR)) {
          array_push($check, array('success' => false, 'warning' => false, 'value' => __('Is CACHE directory writable?', 'outofthebox'), 'description' => __('CACHE directory', 'outofthebox') . ' \'' . OUTOFTHEBOX_CACHEDIR . '\' ' . __('isn\'t writable. The gallery will load very slowly.', 'outofthebox') . ' ' . __('Make sure CACHE directory is writable', 'outofthebox')));
        } else {
          array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE directory writable?', 'outofthebox'), 'description' => __('CACHE directory is now writable', 'outofthebox')));
        }
      } else {
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE directory writable?', 'outofthebox'), 'description' => __('CACHE directory is writable', 'outofthebox')));
      }

      //Check if cache index-file is writeable
      if (!is_readable(OUTOFTHEBOX_CACHEDIR . 'index')) {
        @file_put_contents(OUTOFTHEBOX_CACHEDIR . 'index', json_encode(array()));

        if (!is_readable(OUTOFTHEBOX_CACHEDIR . 'index')) {
          array_push($check, array('success' => false, 'warning' => false, 'value' => __('Is CACHE-index file writable?', 'outofthebox'), 'description' => __('-index file', 'outofthebox') . ' \'' . OUTOFTHEBOX_CACHEDIR . 'index' . '\' ' . __('isn\'t writable. The gallery will load very slowly.', 'outofthebox') . ' ' . __('Make sure CACHE-index file is writable', 'outofthebox')));
        } else {
          array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE-index file writable?', 'outofthebox'), 'description' => __('CACHE-index file is now writable', 'outofthebox')));
        }
      } else {
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE-index file writable?', 'outofthebox'), 'description' => __('CACHE-index file is writable', 'outofthebox')));
      }

      // Supported images
      $mime_types = array('image/jpeg', 'image/png', 'image/bmp', 'image/gif');
      $supported = '';
      $success = true;

      foreach ($mime_types as $mime_type) {
        $arg = array('mime_type' => $mime_type, 'methods' => array('resize', 'save'));
        $img_editor_test = false;

        if (function_exists('wp_image_editor_supports')) {
          $img_editor_test = wp_image_editor_supports($arg);
        }

        if ($img_editor_test === true) {
          $success = false;
        }

        $supported .= $mime_type . ': ' . (($img_editor_test === true) ? 'Yes' : 'No') . '<br/>';
      }

      array_push($check, array('success' => $success, 'warning' => true, 'value' => __('Can resize the following images', 'outofthebox'), 'description' => $supported . '<br/>' . __("If your server doesn't support resizing an image type, we try to use Dropbox own thumbnails", 'outofthebox')));

      //Check if we can use ZIP class
      if (class_exists('ZipArchive')) {
        $message = __("You can use the ZIP function", 'outofthebox');
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('Download files as ZIP', 'outofthebox'), 'description' => $message));
      } else {
        $message = __("You cannot download files as ZIP", 'outofthebox');
        array_push($check, array('success' => true, 'warning' => true, 'value' => __('Download files as ZIP', 'outofthebox'), 'description' => $message));
      }

      if (!extension_loaded('mbstring')) {
        array_push($check, array('success' => false, 'warning' => false, 'value' => __('md_string extension enabled?', 'outofthebox'), 'description' => __('The required md_string extension is not enabled on your server. Please enable this extension.', 'outofthebox')));
      }

      /* Check if Gravity Forms is installed and can be used */
      if (class_exists("GFForms")) {
        $is_correct_version = false;
        if (class_exists('GFCommon')) {
          $is_correct_version = version_compare(GFCommon::$version, '1.9', '>=');
        }
        if ($is_correct_version) {
          $message = __("You can use Out-of-the-Box in Gravity Forms (" . GFCommon::$version . ")", 'outofthebox');
          array_push($check, array('success' => true, 'warning' => false, 'value' => __('Gravity Forms integration', 'outofthebox'), 'description' => $message));
        } else {
          $message = __("You have Gravity Forms (" . GFCommon::$version . ") installed, but versions before 1.9 are not supported. Please update Gravity Forms if you want to use this plugin in combination with Gravity Forms", 'outofthebox');
          array_push($check, array('success' => false, 'warning' => true, 'value' => __('Gravity Forms integration', 'outofthebox'), 'description' => $message));
        }
      }

      // Create Table
      $html = '<table border="0" cellspacing="0" cellpadding="0">';

      foreach ($check as $row) {

        $color = ($row['success']) ? 'green' : 'red';
        $color = ($row['warning']) ? 'orange' : $color;

        $html .= '<tr style="vertical-align:top;"><td width="200" style="padding: 5px; color:' . $color . '"><strong>' . $row['value'] . '</strong></td><td style="padding: 5px;">' . $row['description'] . '</td></tr>';
      }

      $html .= '</table>';

      return $html;
    }

    /*
     * Add MCE buttons and script
     */

    public function ShortcodeButtonInit() {

      //Abort early if the user will never see TinyMCE
      if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
        return;
      }

      global $pagenow;
      if (!in_array($pagenow, array('post.php', 'post-new.php')))
        return;

      //Add a callback to regiser our tinymce plugin
      add_filter("mce_external_plugins", array(&$this, "RegisterTinymcePlugin"));

      // Add a callback to add our button to the TinyMCE toolbar
      add_filter('mce_buttons', array(&$this, 'AddTinymceButton'));

      /* Add custom CSs for placeholders */
      add_editor_style(OUTOFTHEBOX_ROOTPATH . '/css/outofthebox_tinymce_editor.css');
    }

    //This callback registers our plug-in
    function RegisterTinymcePlugin($plugin_array) {
      $plugin_array['outofthebox'] = OUTOFTHEBOX_ROOTPATH . "/includes/OutoftheBox_tinymce.js";
      return $plugin_array;
    }

    //This callback adds our button to the toolbar
    function AddTinymceButton($buttons) {
      //Add the button ID to the $button array
      $buttons[] = "outofthebox";
      $buttons[] = "outofthebox_embedded";
      $buttons[] = "outofthebox_links";
      return $buttons;
    }

  }

}