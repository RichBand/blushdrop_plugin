<?php
if (!current_user_can('edit_pages')) {
  die();
}

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'default';

if (!function_exists('shortcode_exists')) {

  function shortcode_exists($shortcode = false) {
    global $shortcode_tags;

    if (!$shortcode)
      return false;

    if (array_key_exists($shortcode, $shortcode_tags))
      return true;

    return false;
  }

}

function wp_roles_checkbox($name, $selected = array()) {
  global $wp_roles;
  if (!isset($wp_roles)) {
    $wp_roles = new WP_Roles();
  }

  $roles = $wp_roles->get_names();


  foreach ($roles as $role_value => $role_name) {
    if (in_array($role_value, $selected) || $selected[0] == 'all') {
      $checked = 'checked="checked"';
    } else {
      $checked = '';
    }
    echo '<input class="simple" type="checkbox" name="' . $name . '[]" value="' . $role_value . '" ' . $checked . '>' . $role_name . '<br/>';
  }
  if (in_array('guest', $selected) || $selected[0] == 'all') {
    $checked = 'checked="checked"';
  } else {
    $checked = '';
  }
  echo '<input class="simple" type="checkbox" name="' . $name . '[]" value="guest" ' . $checked . '>' . __('Guest', 'outofthebox');
}

wp_register_script('collagePlus', OUTOFTHEBOX_ROOTPATH . '/includes/collagePlus/jquery.collagePlus.min.js', array('jquery'), filemtime(OUTOFTHEBOX_ROOTDIR . '/includes/collagePlus/jquery.collagePlus.min.js'));
wp_register_script('removeWhitespace', OUTOFTHEBOX_ROOTPATH . '/includes/collagePlus/extras/jquery.removeWhitespace.min.js', array('jquery'), filemtime(OUTOFTHEBOX_ROOTDIR . '/includes/collagePlus/extras/jquery.removeWhitespace.min.js'));
wp_register_script('Radiobuttons', OUTOFTHEBOX_ROOTPATH . '/includes/jquery-radiobutton/jquery-radiobutton-2.0.js', array('jquery'), filemtime(OUTOFTHEBOX_ROOTDIR . '/includes/jquery-radiobutton/jquery-radiobutton-2.0.js'));
wp_register_script('imagesloaded', OUTOFTHEBOX_ROOTPATH . '/includes/jquery-qTip/imagesloaded.pkgd.min.js', null, false, true);
wp_register_script('qtip', OUTOFTHEBOX_ROOTPATH . '/includes/jquery-qTip/jquery.qtip.min.js', array('jquery', 'imagesloaded'), false, true);
wp_register_script('unveil', OUTOFTHEBOX_ROOTPATH . '/includes/jquery-unveil/jquery.unveil.min.js', array('jquery'), false, true);

wp_register_script('OutoftheBox', OUTOFTHEBOX_ROOTPATH . '/includes/OutoftheBox.js', array('jquery'), filemtime(OUTOFTHEBOX_ROOTDIR . '/includes/OutoftheBox.js'), true);
wp_register_script('OutoftheBox.tinymce', OUTOFTHEBOX_ROOTPATH . '/includes/OutoftheBox_tinymce_popup.js', array('jquery'), filemtime(OUTOFTHEBOX_ROOTDIR . '/includes/OutoftheBox_tinymce_popup.js'), true);

function OutoftheBox_remove_all_scripts() {
  global $wp_scripts;
  $wp_scripts->queue = array();

  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-tabs');
  wp_enqueue_script('jquery-ui-tooltip');
  wp_enqueue_script('jquery-ui-widget');
  wp_enqueue_script('jquery-ui-position');
  wp_enqueue_script('jquery-effects-fade');
  wp_enqueue_script('jquery');

  wp_enqueue_script('collagePlus');
  wp_enqueue_script('removeWhitespace');

  wp_enqueue_script('Radiobuttons');
  wp_enqueue_script('imagesloaded');
  wp_enqueue_script('qtip');
  wp_enqueue_script('unveil');

  wp_enqueue_script('OutoftheBox');
  wp_enqueue_script('OutoftheBox.tinymce');
}

add_action('wp_print_scripts', 'OutoftheBox_remove_all_scripts', 100);

$post_max_size_bytes = min(OutoftheBox_return_bytes(ini_get('post_max_size')), OutoftheBox_return_bytes(ini_get('upload_max_filesize')));

$localize = array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'plugin_url' => OUTOFTHEBOX_ROOTPATH,
    'js_url' => OUTOFTHEBOX_ROOTPATH . '/includes/jQuery.jPlayer',
    'post_max_size' => $post_max_size_bytes,
    'refresh_nonce' => wp_create_nonce("outofthebox-get-filelist"),
    'gallery_nonce' => wp_create_nonce("outofthebox-get-gallery"),
    'upload_nonce' => wp_create_nonce("outofthebox-upload-file"),
    'delete_nonce' => wp_create_nonce("outofthebox-delete-entry"),
    'rename_nonce' => wp_create_nonce("outofthebox-rename-entry"),
    'addfolder_nonce' => wp_create_nonce("outofthebox-add-folder"),
    'getplaylist_nonce' => wp_create_nonce("outofthebox-get-playlist"),
    'createzip_nonce' => wp_create_nonce("outofthebox-create-zip"),
    'createlink_nonce' => wp_create_nonce("outofthebox-create-link"),
    'str_success' => __('Success', 'outofthebox'),
    'str_error' => __('Error', 'outofthebox'),
    'str_inqueue' => __('In queue', 'outofthebox'),
    'str_uploading' => __('Uploading', 'outofthebox'),
    'str_error_title' => __('Error', 'outofthebox'),
    'str_close_title' => __('Close', 'outofthebox'),
    'str_start_title' => __('Start', 'outofthebox'),
    'str_cancel_title' => __('Cancel', 'outofthebox'),
    'str_delete_title' => __('Delete', 'outofthebox'),
    'str_zip_title' => __('Create zip file', 'outofthebox'),
    'str_delete' => __('Do you really want to delete:', 'outofthebox'),
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
    'maxNumberOfFiles' => __('Maximum number of files exceeded', 'outofthebox'),
    'acceptFileTypes' => __('File type not allowed', 'outofthebox'),
    'maxFileSize' => __('File is too large', 'outofthebox'),
    'minFileSize' => __('File is too small', 'outofthebox')
);

wp_localize_script('OutoftheBox', 'OutoftheBox_vars', $localize);
/* Initialize shortcode vars */
$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : 'files';
?>
<html>
  <head>
    <title>
      <?php
      if ($type === 'default') {
        _e('Create Shortcode', 'outofthebox');
        $mcepopup = 'shortcode';
      } else if ($type === 'links') {
        _e('Insert direct links to files or folders', 'outofthebox');
        $mcepopup = 'links';
      } else if ($type === 'embedded') {
        _e('Embed files', 'outofthebox');
        $mcepopup = 'embedded';
      } else if ($type === 'gravityforms') {
        _e('Create Shortcode', 'outofthebox');
        $mcepopup = 'shortcode';
      }
      ?>
    </title>
    <?php if ($type !== 'gravityforms') { ?>
      <script type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
      <script type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
      <script type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
    <?php } ?>
    <base target="_self" />
    <?php wp_print_scripts(); ?>
    <link rel='stylesheet' id='OutoftheBox-jquery-css'  href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/jquery-ui-1.10.3.custom.css?ver=<?php echo (filemtime(OUTOFTHEBOX_ROOTDIR . "/css/jquery-ui-1.10.3.custom.css")); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='OutoftheBox-css'  href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/outofthebox.css?ver=<?php echo (filemtime(OUTOFTHEBOX_ROOTDIR . "/css/outofthebox.css")); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='OutoftheBox-tinymce-css'  href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/outofthebox_tinymce.css?ver=<?php echo (filemtime(OUTOFTHEBOX_ROOTDIR . "/css/outofthebox_tinymce.css")); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='Awesome-Font-css'  href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/includes/font-awesome/css/font-awesome.min.css?ver=<?php echo (filemtime(OUTOFTHEBOX_ROOTDIR . "/includes/font-awesome/css/font-awesome.min.css")); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='qTip'  href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/includes/jquery-qTip/jquery.qtip.min.css?ver=<?php echo (filemtime(OUTOFTHEBOX_ROOTDIR . "/includes/jquery-qTip/jquery.qtip.min.css")); ?>' type='text/css' media='all' />
  </head>
  <body class="OutoftheBox <?php echo $type; ?>">
    <div class='OutoftheBox list-container loadingshortcode'>
      <img class="preloading" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/clouds/cloud_loading_128.gif" data-src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/clouds/cloud_loading_128.gif" data-src-retina="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/clouds/cloud_loading_256.gif">
      <h2><?php echo __("Loading Shortcode Generator", 'outofthebox') ?></h2>
    </div>
    <form id="OutoftheBox_addshortce_form" action="#" class="OutoftheBox jsdisabled">

      <div class="wrap">

        <?php
        if ($type === 'links' || $type === 'embedded') {

          if ($type === 'embedded') {
            echo "<p>" . __('Out-of-the-Box uses Google Doc Viewer to embed your files.', 'outofthebox') . "&nbsp" .
            __('A list of supported file types can be found', 'outofthebox') . '&nbsp;<a href="http://support.google.com/docs/?hl=en&p=docs_viewer" target="_blank">' . __('here', 'outofthebox') . "</a></p>";
          }
          ?>
          <?php
          $atts = array(
              'mode' => 'files',
              'showfiles' => '1',
              'upload' => '0',
              'delete' => '0',
              'rename' => '0',
              'addfolder' => '0',
              'showcolumnnames' => '0',
              'viewrole' => 'all',
              'candownloadzip' => '0',
              'showsharelink' => '0',
              'previewinline' => '0',
              'mcepopup' => $mcepopup
          );

          echo $this->CreateTemplate($atts);
          ?>
          <?php
        } else {
          ?>

          <div id="tabs">
            <ul>
              <li><a href="#settings_general"><span>General</span></a></li>
              <li id="settings_userfolders_tab"><a href="#settings_userfolders"><span>User Folders</span></a></li>
              <li id="settings_mediafiles_tab" class="hidden"><a href="#settings_mediafiles"><span>Media files</span></a></li>
              <li><a href="#settings_layout"><span>Layout</span></a></li>
              <li><a href="#settings_sorting"><span>Sorting</span></a></li>
              <li id="settings_advanced_tab"><a href="#settings_advanced"><span>Advanced</span></a></li>
              <li><a href="#settings_exclusions"><span>Exclusions</span></a></li>
              <li id="settings_upload_tab"><a href="#settings_upload"><span>Upload Form</span></a></li>
              <li id="settings_notifications_tab"><a href="#settings_notifications"><span>Notifications</span></a></li>
              <li id="settings_manipulation_tab"><a href="#settings_manipulation"><span>File Manipulation</span></a></li>
              <li><a href="#settings_permissions"><span>User Permissions</span></a></li>
            </ul>
            <!-- General Tab -->
            <div id="settings_general">
              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Use plugin as', 'outofthebox'); ?>
                  <span class="help" title="<p>Select how you want to use Out-of-the-Box in your post or page</p>">?</span>
                </h4>
                <div class="section">
                  <div class="radiobuttons-container">
                    <div class="radiobutton">
                      <input type="radio" id="files" name="mode" <?php echo (($mode === 'files') ? 'checked="checked"' : ''); ?> value="files" class="mode"/><label for="files"><?php _e('File browser', 'outofthebox'); ?></label>
                    </div>
                    <?php if ($type !== 'gravityforms') { ?>
                      <div class="radiobutton">
                        <input type="radio" id="gallery" name="mode" <?php echo (($mode === 'gallery') ? 'checked="checked"' : ''); ?> value="gallery" class="mode"/><label for="gallery"><?php _e('Photo gallery', 'outofthebox'); ?></label>
                      </div>
                      <div class="radiobutton">
                        <input type="radio" id="audio" name="mode" <?php echo (($mode === 'audio') ? 'checked="checked"' : ''); ?> value="audio" class="mode"/><label for="audio"><?php _e('Audio player', 'outofthebox'); ?></label>
                      </div>
                      <div class="radiobutton">
                        <input type="radio" id="video" name="mode" <?php echo (($mode === 'video') ? 'checked="checked"' : ''); ?> value="video" class="mode"/><label for="video"><?php _e('Video player', 'outofthebox'); ?></label>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Select root folder', 'outofthebox'); ?>
                  <span class="help" title="<p>What should be the start folder of the plugin? The user can not browse below this folder</p>">?</span>
                </h4>
                <div class="section">
                  <div class="root-folder">
                    <?php
                    $atts = array(
                        'mode' => 'files',
                        'showfiles' => '1',
                        'filesize' => '0',
                        'filedate' => '0',
                        'upload' => '0',
                        'delete' => '0',
                        'rename' => '0',
                        'addfolder' => '0',
                        'showbreadcrumb' => '0',
                        'showcolumnnames' => '0',
                        'viewrole' => 'administrator|editor|author|contributor',
                        'downloadrole' => 'none',
                        'candownloadzip' => '0',
                        'showsharelink' => '0',
                        'previewinline' => '0',
                        'mcepopup' => $mcepopup
                    );

                    if (isset($_REQUEST['dir'])) {
                      $atts['startpath'] = $_REQUEST['dir'];
                    }

                    echo $this->CreateTemplate($atts);
                    ?>
                  </div>
                  <div class="no-root-folder hidden">
                    <?php _e("You are using User-Linked user folders. You can't select a root folder.", 'outofthebox'); ?>
                  </div>
                </div>
              </div>
            </div>
            <!-- End General Tab -->
            <!-- User Folders Tab -->
            <div id="settings_userfolders">
              <div class="option option-help forfilebrowser forgallery">
                <h4><?php _e('User folders', 'outofthebox'); ?>
                </h4>
                <div class="section">
                  <div class="description">
                    <?php _e('User folders can be useful in some situations, for example', 'outofthebox'); ?>:
                    <ul>
                      <li><?php _e('Each user should only be able to access their own files in their own folder', 'outofthebox'); ?></li>
                      <li><?php _e('Users and guests on your site want to upload files to their own folder', 'outofthebox'); ?></li>
                      <li><?php _e('Your clients should get their own personal folder if they register, already filled with some files from template folder', 'outofthebox'); ?></li>
                    </ul>
                    <?php _e('You can use the plugin in two ways to create folders that are linked to the users', 'outofthebox'); ?>. 
                    <?php _e('You can let the plugin automatically create user folders in the, by you selected, root folder', 'outofthebox'); ?>. 
                    <?php _e('Or you can link each user to their own folder via the plugin settings menu', 'outofthebox'); ?>. 
                    (<a href="<?php echo admin_url('admin.php?page=OutoftheBox_settings_linkusers'); ?>" target="_blank"><?php _e('here', 'outofthebox'); ?></a>)
                  </div>
                </div>
              </div>  

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Use user specific folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Let users only browser through the their own folder (automatically or were you have linked them to)', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_linkedfolders" id="OutoftheBox_linkedfolders"  <?php echo (isset($_REQUEST['userfolders'])) ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option option-userfolders forfilebrowser forgallery <?php echo (isset($_REQUEST['userfolders'])) ? '' : 'hidden'; ?>">
                <h4><?php _e('Method', 'outofthebox'); ?>
                  <span class="help" title="<p><strong><?php _e('Select the method that should be used', 'outofthebox'); ?></strong></br>
                        <?php _e('Use the user-folder link that you have created via the plugin settings menu', 'outofthebox'); ?>.
                        <?php _e('Or let the plugin automatically create the user folders', 'outofthebox'); ?>
                        </p>">?</span>
                </h4>
                <?php
                $userfolders = (!isset($_REQUEST['userfolders']) || (isset($_REQUEST['userfolders']) && ($_REQUEST['userfolders'] === 'auto'))) ? 'auto' : 'manual';
                ?>
                <div class="section">
                  <div class="radiobuttons-container">
                    <div class="radiobutton">
                      <input type="radio" id="userfolders_method_manual" name="OutoftheBox_userfolders_method" <?php echo ($userfolders === 'manual') ? 'checked="checked"' : ''; ?> value="manual"/><label for="file_layout_grid"><?php _e('Use my own created User-Folder link', 'outofthebox'); ?></label>
                    </div>
                    <div class="radiobutton">
                      <input type="radio" id="userfolders_method_auto" name="OutoftheBox_userfolders_method" <?php echo ($userfolders === 'auto') ? 'checked="checked"' : ''; ?> value="auto"/><label for="file_layout_list"><?php _e('Automatically create the user folders', 'outofthebox'); ?></label>
                    </div>
                    <div class="option option-userfolders_auto <?php echo ($userfolders === 'auto') ? '' : 'hidden'; ?>">
                      <i><?php echo __('By default guests (not logged in users) will also get their own folder', 'outofthebox'); ?>. 
                        <?php echo __("Remove 'Guest' from View Roles on the 'User Permissions' tab to prevent guests to use the plugin", 'outofthebox'); ?>.
                      </i>
                    </div>
                  </div>
                </div>

                <div class="option option-userfolders_auto forgallery <?php echo ($userfolders === 'auto') ? '' : 'hidden'; ?>">
                  <h4><?php _e('Use a template folder', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('If you would like to create the user folders based on another folder. The content of the template folder will be copied to the user folder', 'outofthebox'); ?></p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <input type="checkbox" name="OutoftheBox_userfolders_template" id="OutoftheBox_userfolders_template" <?php echo (isset($_REQUEST['usertemplatedir'])) ? 'checked="checked"' : ''; ?> data-div-toggle="option-userfolders-template"/>
                    </div>
                  </div>

                  <div class="option option-userfolders-template forfilebrowser forgallery <?php echo (isset($_REQUEST['usertemplatedir'])) ? '' : 'hidden'; ?>">
                    <h4><?php _e('Template folder', 'outofthebox'); ?>
                      <span class="help" title="<p><?php _e('Select the template folder', 'outofthebox'); ?>.</p>">?</span>
                    </h4>
                    <div class="section">
                      <div class="template-folder">
                        <?php
                        $atts = array(
                            'mode' => 'files',
                            'filelayout' => 'list',
                            'showfiles' => '1',
                            'filesize' => '0',
                            'filedate' => '0',
                            'upload' => '0',
                            'delete' => '0',
                            'rename' => '0',
                            'addfolder' => '0',
                            'showbreadcrumb' => '0',
                            'showcolumnnames' => '0',
                            'viewrole' => 'administrator|editor|author|contributor',
                            'downloadrole' => 'none',
                            'candownloadzip' => '0',
                            'showsharelink' => '0',
                            'mcepopup' => $mcepopup,
                            '_random' => time() + 10
                        );

                        if (isset($_REQUEST['usertemplatedir'])) {
                          $atts['startpath'] = $_REQUEST['usertemplatedir'];
                        }

                        echo $this->CreateTemplate($atts);
                        ?>
                      </div>
                    </div>
                  </div>

                  <h4><?php _e('Who can access all user folders', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can browse through all folders and access all files', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['viewuserfoldersrole'])) ? explode('|', $_REQUEST['viewuserfoldersrole']) : array('administrator');
                      wp_roles_checkbox('OutoftheBox_view_user_folders_role', $selected);
                      ?>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <!-- End User Folders Tab -->
            <!-- Media Files Tab -->
            <div id="settings_mediafiles">
              <div class="option option-help foraudio forvideo">
                <h4><?php _e('Media Files', 'outofthebox'); ?>
                </h4>
                <div class="section">
                  <div class="description">
                    <?php _e('The mediaplayer will decided, based on the provided formats, if the user will have a HTML5 player or a Flash Player', 'outofthebox'); ?>. <?php _e('You may provide the same file with different extensions to increase cross-browser support', 'outofthebox'); ?>.<br/> <?php _e('Do always supply a mp3 (audio) or m4v/mp4 (video)file to support all browsers', 'outofthebox'); ?>.
                  </div>
                </div>
              </div>        

              <div class="option foraudio">
                <h4 class="mediaextensions"><?php _e('Provided formats', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Select which sort of media files you will provide', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <?php
                $mediaextensions = (!isset($_REQUEST['mediaextensions']) || ($mode !== 'audio')) ? array() : explode('|', $_REQUEST['mediaextensions']);
                ?>
                <div class="section">
                  <div class="checkbox">
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('mp3', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='mp3'/>mp3&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('mp4', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='mp4'/>mp4&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('m4a', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='m4a'/>m4a&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('ogg', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='ogg'/>ogg&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('oga', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='oga'/>oga&nbsp&nbsp
                  </div>
                </div>
              </div>

              <div class="option forvideo">
                <h4 class="mediaextensions"><?php _e('Provided formats', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Select which sort of media files you will provide', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <?php
                $mediaextensions = (!isset($_REQUEST['mediaextensions']) || ($mode !== 'video')) ? array() : explode('|', $_REQUEST['mediaextensions']);
                ?>
                <div class="section">
                  <div class="checkbox">
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('mp4', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='mp4'/>mp4&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('m4v', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='m4v'/>m4v&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('ogg', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='ogg'/>ogg&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('ogv', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='ogv'/>ogv&nbsp&nbsp
                    <input class="simple" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('webmv', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='webmv'/>webmv&nbsp&nbsp
                  </div>
                </div>
              </div>

              <div class="option foraudio forvideo">
                <h4><?php _e('Automatically start playing', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Autoplay - Automatically start playing', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_autoplay" id="OutoftheBox_autoplay" <?php echo (isset($_REQUEST['autoplay']) && $_REQUEST['autoplay'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option foraudio forvideo">
                <h4><?php _e('Allow download', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Show direct download link to media file in the playlist', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_linktomedia" id="OutoftheBox_linktomedia" <?php echo (isset($_REQUEST['linktomedia']) && $_REQUEST['linktomedia'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option foraudio forvideo">
                <h4><?php _e('Allow purchase', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Show link to webshop in the playlist', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_mediapurchase" id="OutoftheBox_mediapurchase" <?php echo (isset($_REQUEST['linktoshop'])) ? 'checked="checked"' : ''; ?> data-div-toggle='webshop-options'/>
                  </div>
                </div>
              </div>

              <div class="option webshop-options <?php echo (isset($_REQUEST['linktoshop'])) ? '' : 'hidden'; ?>">
                <h4><?php _e('Link to webshop', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Insert link to your webshop here', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_linktoshop" id="OutoftheBox_linktoshop" placeholder="https://www.yourwebshop.com/" value="<?php echo (isset($_REQUEST['linktoshop'])) ? $_REQUEST['linktoshop'] : ''; ?>"/>
                </div>
              </div>

            </div>
            <!-- End Media Files Tab -->
            <!-- Layout Tab -->
            <div id="settings_layout">

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Container width', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Set max width for the Out-of-the-Box container", "outofthebox"); ?>. <?php _e("You can use pixels or percentages, eg '360px', '480px', '70%'", "outofthebox"); ?>. <?php echo __('Leave empty for default value', 'outofthebox'); ?> (100%).</p>">?</span>
                </h4>
                <div class="section smallinput">
                  <input type="text" name="OutoftheBox_max_width" id="OutoftheBox_max_width" placeholder="100%" value="<?php echo (isset($_REQUEST['maxwidth'])) ? $_REQUEST['maxwidth'] : ''; ?>"/>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Container height', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Set max height for the Out-of-the-Box container", "outofthebox"); ?>. <?php _e("You can use pixels or percentages, eg '360px', '480px', '70%'", "outofthebox"); ?>. <?php echo __('Leave empty for default value', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section smallinput">
                  <input type="text" name="OutoftheBox_max_height" id="OutoftheBox_max_height" placeholder="" value="<?php echo (isset($_REQUEST['maxheight'])) ? $_REQUEST['maxheight'] : ''; ?>"/>
                </div>
              </div>

              <div class="option foraudio forvideo <?php echo (in_array($mode, array('audio', 'video'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Hide playlist on start', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Would you like to hide the playlist', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_hideplaylist" id="OutoftheBox_hideplaylist" <?php echo (isset($_REQUEST['hideplaylist']) && $_REQUEST['hideplaylist'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery <?php echo (in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display breadcrumb', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display a breadcrumb in the file browser?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_breadcrumb" id="OutoftheBox_breadcrumb" <?php echo (isset($_REQUEST['showbreadcrumb']) && $_REQUEST['showbreadcrumb'] === '0') ? '' : 'checked="checked"'; ?> data-div-toggle="breadcrumb-options"/>
                  </div>
                </div>


                <div class="option breadcrumb-options <?php echo (isset($_REQUEST['showbreadcrumb']) && $_REQUEST['showbreadcrumb'] === '0') ? 'hidden' : ''; ?>">
                  <h4><?php _e('Root breadcrumb title', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('What should be the breadcrumb title of the root folder? Leave empty for default value.', 'outofthebox'); ?></p>">?</span>
                  </h4>
                  <div class="section largeinput">
                    <input type="text" name="OutoftheBox_roottext" id="OutoftheBox_roottext" placeholder="Start" value="<?php echo (isset($_REQUEST['roottext'])) ? $_REQUEST['roottext'] : ''; ?>"/>
                  </div>
                </div>
                <div class="option breadcrumb-options">
                  <h4><?php _e('Display parents in breadcrumb', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Should the plugin show all the parents of the root folder in breadcrumb?', 'outofthebox'); ?></p>">?</span>
                  </h4>
                  <div class="section ">
                    <div class="checkbox"> 
                      <input type="checkbox" name="OutoftheBox_rootname" id="OutoftheBox_rootname"/>
                    </div>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser <?php echo (in_array($mode, array('files'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display columnnames', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display the columnnames of the date and filesize?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_showcolumnnames" id="OutoftheBox_showcolumnnames" <?php echo (isset($_REQUEST['showcolumnnames']) && $_REQUEST['showcolumnnames'] === '0') ? '' : 'checked="checked"'; ?> />
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery <?php echo (in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display refresh button', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display a refresh button so users can update the file list and refresh the cache?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_showrefreshbutton" id="OutoftheBox_showrefreshbutton" <?php echo (isset($_REQUEST['showrefreshbutton']) && $_REQUEST['showrefreshbutton'] === '0') ? '' : 'checked="checked"'; ?>/>
                  </div>
                </div>
              </div>


              <div class="option forfilebrowser <?php echo (in_array($mode, array('files'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display files in folder', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display files in the folder so the user can preview and download them?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_showfiles" id="OutoftheBox_showfiles" <?php echo (isset($_REQUEST['showfiles']) && $_REQUEST['showfiles'] === '0') ? '' : 'checked="checked"'; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser <?php echo (in_array($mode, array('files'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display child folders in folder', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display the child folders in the selected root folder?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_showfolders" id="OutoftheBox_showfolders" <?php echo (isset($_REQUEST['showfolders']) && $_REQUEST['showfolders'] === '0') ? '' : 'checked="checked"'; ?>/>
                  </div>
                </div>
              </div>

              <div class="option option-filesize forfilebrowser <?php echo (in_array($mode, array('files'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display file size', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display a column with the file size?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_filesize" id="OutoftheBox_filesize" <?php echo (isset($_REQUEST['filesize']) && $_REQUEST['filesize'] === '0') ? '' : 'checked="checked"'; ?> />
                  </div>
                </div>
              </div>

              <div class="option option-filedate forfilebrowser <?php echo (in_array($mode, array('files'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display date last modified', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display a column with the last modified date?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_filedate" id="OutoftheBox_filedate" <?php echo (isset($_REQUEST['filedate']) && $_REQUEST['filedate'] === '0') ? '' : 'checked="checked"'; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser <?php echo (in_array($mode, array('files'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Display file extension', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Do you want to display the file extensions (.pdf, .txt)?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_showext" id="OutoftheBox_showext" <?php echo (isset($_REQUEST['showext']) && $_REQUEST['showext'] === '0') ? '' : 'checked="checked"'; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forgallery <?php echo (in_array($mode, array('gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Slideshow in Lightbox', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Do you want to enable the slideshow in the lightbox", 'outofthebox'); ?>. <?php _e("Set to 0 to load all images at once", 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_slideshow" id="OutoftheBox_slideshow" <?php echo (isset($_REQUEST['slideshow']) && $_REQUEST['slideshow'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="slideshow-options"/>
                  </div>
                </div>
              </div>

              <div class="option slideshow-options forgallery <?php echo (in_array($mode, array('gallery'))) ? '' : 'hidden'; ?> ">
                <h4><?php _e('Delay between cycles (ms)', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Delay between cycles in milliseconds, the default is 5000", 'outofthebox'); ?></p>.">?</span>
                </h4>
                <div class="section smallinput">
                  <input type="text" name="OutoftheBox_pausetime" id="OutoftheBox_pausetime" placeholder="5000" value="<?php echo (isset($_REQUEST['OutoftheBox_pausetime'])) ? $_REQUEST['OutoftheBox_pausetime'] : ''; ?>"/>
                </div>
              </div>

              <div class="option forgallery <?php echo (in_array($mode, array('gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Number of images', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Number of images to be loaded each time", 'outofthebox'); ?>. <?php _e("Set to 0 to load all images at once", 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section smallinput">
                  <input type="text" name="OutoftheBox_maximage" id="OutoftheBox_maximage" placeholder="25" value="<?php echo (isset($_REQUEST['maximages'])) ? $_REQUEST['maximages'] : ''; ?>"/>
                </div>
              </div>

              <div class="option forgallery <?php echo (in_array($mode, array('gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Gallery row height', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("The ideal height you want your grid rows to be", 'outofthebox'); ?>. <?php _e("It won't set it exactly to this as plugin adjusts the row height to get the correct width", 'outofthebox'); ?>. <?php echo __('Leave empty for default value', 'outofthebox'); ?> (150).</p>">?</span>
                </h4>
                <div class="section smallinput">
                  <input type="text" name="OutoftheBox_targetHeight" id="OutoftheBox_targetHeight" placeholder="150" value="<?php echo (isset($_REQUEST['targetheight'])) ? $_REQUEST['targetheight'] : ''; ?>"/>
                </div>
              </div>

              <div class="option forgallery <?php echo (in_array($mode, array('gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Crop images to squares', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Crop images for a squared grid", 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_crop" id="OutoftheBox_crop" <?php echo (isset($_REQUEST['crop']) && $_REQUEST['crop'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

            </div>
            <!-- End Layout Tab -->
            <!-- Sorting Tab -->
            <div id="settings_sorting">
              <div class="option  forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Sort by', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Sort files and folders by their properties', 'outofthebox'); ?></p>">?</span>
                </h4>
                <?php
                $sortfield = (!isset($_REQUEST['sortfield'])) ? 'name' : $_REQUEST['sortfield'];
                ?>
                <div class="section">
                  <div class="radiobuttons-container sort_fields">
                    <div class="radiobutton">
                      <input type="radio" id="name" name="sort_field" <?php echo ($sortfield === 'name') ? 'checked="checked"' : ''; ?> value="name" class="mode"/><label for="name"><?php _e('Name', 'outofthebox'); ?></label>
                    </div>
                    <div class="radiobutton">
                      <input type="radio" id="size" name="sort_field" <?php echo ($sortfield === 'size') ? 'checked="checked"' : ''; ?> value="size" class="mode"/><label for="size"><?php _e('Size', 'outofthebox'); ?></label>
                    </div>
                    <div class="radiobutton">
                      <input type="radio" id="modified" name="sort_field" <?php echo ($sortfield === 'modified') ? 'checked="checked"' : ''; ?> value="modified" class="mode"/><label for="modified"><?php _e('Date modified', 'outofthebox'); ?></label>
                    </div>
                    <div class="radiobutton">
                      <input type="radio" id="shuffle" name="sort_field" <?php echo ($sortfield === 'shuffle') ? 'checked="checked"' : ''; ?> value="shuffle" class="mode"/><label for="shuffle"><?php _e('Shuffle/Random', 'outofthebox'); ?></label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="option option-sort-field forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Sort order', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Sort order: ascending or descending', 'outofthebox'); ?></p>">?</span>
                </h4>
                <?php
                $sortorder = (isset($_REQUEST['sortorder']) && $_REQUEST['sortorder'] === 'desc') ? 'desc' : 'asc';
                ?>
                <div class="section">
                  <div class="radiobuttons-container sort_fields">
                    <div class="radiobutton">
                      <input type="radio" id="asc" name="sort_order" <?php echo ($sortorder === 'asc') ? 'checked="checked"' : ''; ?> value="asc" class="mode"/><label for="files"><?php _e('Ascending', 'outofthebox'); ?></label>
                    </div>
                    <div class="radiobutton">
                      <input type="radio" id="desc" name="sort_order" <?php echo ($sortorder === 'desc') ? 'checked="checked"' : ''; ?> value="desc" class="mode"/><label for="gallery"><?php _e('Descending', 'outofthebox'); ?></label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Sorting Tab -->
            <!-- Advanced Tab -->
            <div id="settings_advanced">
              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Enable search', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should users be able to use the search function', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_search" id="OutoftheBox_search" <?php echo (isset($_REQUEST['search']) && $_REQUEST['search'] === '0') ? '' : 'checked="checked"'; ?> data-div-toggle="search-options"/>
                  </div>
                </div>
              </div>

              <div class="option search-options forfilebrowser forgallery <?php echo (isset($_REQUEST['search']) && $_REQUEST['search'] === '0') ? 'hidden' : ''; ?>"">
                <h4><?php _e('Search from selected root', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Search only in the current folder or search from the selected root folder  ', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_searchfrom" id="OutoftheBox_searchfrom" <?php echo (isset($_REQUEST['searchfrom']) && $_REQUEST['searchfrom'] === 'selectedroot') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Enable link sharing', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should users be able to generate permanent direct links to the files?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_showsharelink" id="OutoftheBox_showsharelink" <?php echo (isset($_REQUEST['showsharelink']) && $_REQUEST['showsharelink'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e("Open preview inline", 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Do you want to open the preview in an inline popup or should it open in a new window", 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_previewinline" id="OutoftheBox_previewinline" <?php echo (isset($_REQUEST['previewinline']) && $_REQUEST['previewinline'] === '0') ? '' : 'checked="checked"'; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e("Force a 'Save as'", 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e("Force a 'Save as' Dialog on downloading file", 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_forcedownload" id="OutoftheBox_forcedownload" <?php echo (isset($_REQUEST['forcedownload']) && $_REQUEST['forcedownload'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Enable ZIP-download', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should users be able to use download multiple files as zip?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_candownloadzip" id="OutoftheBox_candownloadzip" <?php echo (isset($_REQUEST['candownloadzip']) && $_REQUEST['candownloadzip'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Advanced Tab -->
            <!-- Exclusions Tab -->
            <div id="settings_exclusions">
              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Show only files with these extensions', 'outofthebox'); ?>
                  <span class="help" title="<p><?php echo __('Add extensions separated with | e.g. (jpg|png|gif)', 'outofthebox') . '. ' . __('Leave empty to show all files', 'outofthebox', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_ext" id="OutoftheBox_ext" value="<?php echo (isset($_REQUEST['ext'])) ? $_REQUEST['ext'] : ''; ?>"/>
                </div>
              </div> 

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Show only these files and folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php echo __('Add files or folders separated with | e.g. (file1.jpg|long folder name)', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_include" id="OutoftheBox_include" value="<?php echo (isset($_REQUEST['include'])) ? $_REQUEST['include'] : ''; ?>"/>
                </div>
              </div> 

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Hide these files and folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php echo __('Add files or folders separated with | e.g. (file1.jpg|long folder name)', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_exclude" id="OutoftheBox_exclude" value="<?php echo (isset($_REQUEST['exclude'])) ? $_REQUEST['exclude'] : ''; ?>"/>
                </div>
              </div> 

            </div>
            <!-- End Exclusions Tab -->
            <!-- Upload Tab -->
            <div id="settings_upload">
              <div class="option forfilebrowser forgallery <?php echo (in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Include upload form', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should users be able to upload files? You can manage the permissions under \'User Permissions\'', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_upload" id="OutoftheBox_upload" data-div-toggle="upload-options" <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option upload-options forfilebrowser forgallery <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Overwrite existing files', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should the plugin overwrite existing files?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_overwrite" id="OutoftheBox_overwrite" <?php echo (isset($_REQUEST['overwrite']) && $_REQUEST['overwrite'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option upload-options forfilebrowser forgallery <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <h4><?php _e('Limit upload by extension', 'outofthebox'); ?>
                  <span class="help" title="<p><?php echo __('Add extensions separated with | e.g. (jpg|png|gif)', 'outofthebox') . ' ' . __('Leave empty for no restricion', 'outofthebox', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_upload_ext" id="OutoftheBox_upload_ext" value="<?php echo (isset($_REQUEST['uploadext'])) ? $_REQUEST['uploadext'] : ''; ?>"/>
                </div>
              </div>

              <div class="option upload-options forfilebrowser forgallery <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <?php $max_size_bytes = min(OutoftheBox_return_bytes(ini_get('post_max_size')), OutoftheBox_return_bytes(ini_get('upload_max_filesize'))); ?>
                <h4><?php _e('Max. upload size', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Max filesize for uploading in bytes', 'outofthebox'); ?>. <?php echo __('Leave empty for server maximum ', 'outofthebox'); ?> (<?php echo $max_size_bytes; ?> bytes). <a href='http://www.google.nl/#q=1mb+in+bytes' target='_blank'><?php echo __('How to calculate?', 'outofthebox'); ?></a></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_maxfilesize" id="OutoftheBox_maxfilesize" value="<?php echo (isset($_REQUEST['maxfilesize'])) ? $_REQUEST['maxfilesize'] : ''; ?>"/>
                </div>
              </div>
            </div>

            <!-- End Upload Tab -->

            <!-- Notifications Tab -->
            <div id="settings_notifications">
              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Download notification', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Would you like to receive a notification email when someone downloads a file?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_notificationdownload" id="OutoftheBox_notificationdownload" <?php echo (isset($_REQUEST['notificationdownload']) && $_REQUEST['notificationdownload'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Upload notification', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Would you like to receive a notification email when someone uploads a file?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_notificationupload" id="OutoftheBox_notificationupload" <?php echo (isset($_REQUEST['notificationupload']) && $_REQUEST['notificationupload'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Delete notification', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Would you like to receive a notification email when someone deletes a file?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_notificationdeletion" id="OutoftheBox_notificationdeletion" <?php echo (isset($_REQUEST['notificationdeletion']) && $_REQUEST['notificationdeletion'] === '1') ? 'checked="checked"' : ''; ?>/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery">
                <h4><?php _e('Send notification to', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('On which email address would you like to receive the notification? You can use %admin_email% and %user_email%.', 'outofthebox'); ?>. <?php echo __('Default value is:', 'outofthebox') . ' ' . get_site_option('admin_email'); ?></p>">?</span>
                </h4>
                <div class="section largeinput">
                  <input type="text" name="OutoftheBox_notification_email" id="OutoftheBox_notification_email" placeholder="<?php echo get_site_option('admin_email'); ?>" value="<?php echo (isset($_REQUEST['notificationemail'])) ? $_REQUEST['notificationemail'] : ''; ?>" />
                </div>
              </div>

            </div>
            <!-- End Notifications Tab -->
            <!-- Manipulation Tab -->
            <div id="settings_manipulation">
              <div class="option option-help forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('File Manipulation', 'outofthebox'); ?>
                </h4>
                <div class="section">
                  <div class="description">
                    <?php _e('Out-of-the-Box uses Wordpress Roles to determine how an user can use the plugin', 'outofthebox'); ?>. 
                    <?php _e("You set these under 'User Permissions'", 'outofthebox'); ?>.
                  </div>
                </div>
              </div>    

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Rename files and folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should it be possible to rename files and folders?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_rename" id="OutoftheBox_rename" <?php echo (isset($_REQUEST['rename']) && $_REQUEST['rename'] === '1') ? 'checked="checked"' : ''; ?>  data-div-toggle="rename-options"/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Move files and folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should it be possible to move files and folders?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_move" id="OutoftheBox_move" <?php echo (isset($_REQUEST['move']) && $_REQUEST['move'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="move-options"/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Delete files and folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should it be possible to delete files and folders?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_delete" id="OutoftheBox_delete" <?php echo (isset($_REQUEST['delete']) && $_REQUEST['delete'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="delete-options"/>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Create new folders', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Should it be possible to create new folders?', 'outofthebox'); ?></p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <input type="checkbox" name="OutoftheBox_addfolder" id="OutoftheBox_addfolder"  <?php echo (isset($_REQUEST['addfolder']) && $_REQUEST['addfolder'] === '1') ? 'checked="checked"' : ''; ?>  data-div-toggle="addfolder-options"/>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Manipulation Tab -->
            <!-- Permissions Tab -->
            <div id="settings_permissions">
              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Who can view', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Select which WordPress Roles can view and use the plugin', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <?php
                    $selected = (isset($_REQUEST['viewrole'])) ? explode('|', $_REQUEST['viewrole']) : array('administrator', 'author', 'contributor', 'editor', 'subscriber', 'pending', 'guest');
                    wp_roles_checkbox('OutoftheBox_view_role', $selected);
                    ?>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery foraudio forvideo">
                <h4><?php _e('Who can download', 'outofthebox'); ?>
                  <span class="help" title="<p><?php _e('Select which WordPress Roles can download files', 'outofthebox'); ?>.</p>">?</span>
                </h4>
                <div class="section">
                  <div class="checkbox">
                    <?php
                    $selected = (isset($_REQUEST['downloadrole'])) ? explode('|', $_REQUEST['downloadrole']) : array('administrator', 'author', 'contributor', 'editor', 'subscriber', 'pending', 'guest');
                    wp_roles_checkbox('OutoftheBox_download_role', $selected);
                    ?>
                  </div>
                </div>
              </div>

              <div class="option forfilebrowser forgallery <?php echo (in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                <div class="option upload-options <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can upload', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can upload files', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['uploadrole'])) ? explode('|', $_REQUEST['uploadrole']) : array('administrator', 'author', 'contributor', 'editor', 'subscriber');
                      wp_roles_checkbox('OutoftheBox_upload_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>

                <div class="option rename-options  <?php echo (isset($_REQUEST['rename']) && $_REQUEST['rename'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can rename files', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can rename files', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['renamefilesrole'])) ? explode('|', $_REQUEST['renamefilesrole']) : array('administrator', 'author', 'contributor', 'editor');
                      wp_roles_checkbox('OutoftheBox_renamefiles_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>

                <div class="option rename-options  <?php echo (isset($_REQUEST['rename']) && $_REQUEST['rename'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can rename folders', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can rename folders', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['renamefoldersrole'])) ? explode('|', $_REQUEST['renamefoldersrole']) : array('administrator', 'author', 'contributor', 'editor');
                      wp_roles_checkbox('OutoftheBox_renamefolders_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>

                <div class="option move-options  <?php echo (isset($_REQUEST['move']) && $_REQUEST['move'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can move files and folders', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can move files and folders', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['moverole'])) ? explode('|', $_REQUEST['moverole']) : array('administrator', 'editor');
                      wp_roles_checkbox('OutoftheBox_move_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>

                <div class="option delete-options  <?php echo (isset($_REQUEST['delete']) && $_REQUEST['delete'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can delete files', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can delete files', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['deletefilesrole'])) ? explode('|', $_REQUEST['deletefilesrole']) : array('administrator', 'author', 'contributor', 'editor');
                      wp_roles_checkbox('OutoftheBox_deletefiles_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>

                <div class="option delete-options  <?php echo (isset($_REQUEST['delete']) && $_REQUEST['delete'] === '1' && in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can delete folders', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can delete folders', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['deletefoldersrole'])) ? explode('|', $_REQUEST['deletefoldersrole']) : array('administrator', 'author', 'contributor', 'editor');
                      wp_roles_checkbox('OutoftheBox_deletefolders_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>

                <div class="option addfolder-options  <?php echo (in_array($mode, array('files', 'gallery'))) ? '' : 'hidden'; ?>">
                  <h4><?php _e('Who can create new folders', 'outofthebox'); ?>
                    <span class="help" title="<p><?php _e('Select which WordPress Roles can create new folders', 'outofthebox'); ?>.</p>">?</span>
                  </h4>
                  <div class="section">
                    <div class="checkbox">
                      <?php
                      $selected = (isset($_REQUEST['addfolderrole'])) ? explode('|', $_REQUEST['addfolderrole']) : array('administrator', 'author', 'contributor', 'editor');
                      wp_roles_checkbox('OutoftheBox_addfolder_role', $selected);
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Permissions Tab -->

          </div>
          <?php
        }
        ?>

        <div class="footer">
          <div style="float: right; margin-left:10px">
            <?php if ($type === 'default') { ?>
              <input type="submit" id="insert"  class="insert_shortcode button-primary" name="insert" value="<?php _e("Insert", 'outofthebox'); ?>" />
            <?php } elseif ($type === 'links') { ?>
              <input type="submit" id="insert" class="insert_links button-primary" name="insert" value="<?php _e("Insert links", 'outofthebox'); ?>" />
            <?php } elseif ($type === 'embedded') { ?>
              <input type="submit" id="insert" class="insert_embedded button-primary" name="insert" value="<?php _e("Embed", 'outofthebox'); ?>" />
            <?php } elseif ($type === 'gravityforms') { ?>
              <input type="submit" id="insert" class="insert_shortcode_gf button-primary" name="insert" value="<?php _e("Insert", 'outofthebox'); ?>" />
            <?php } ?>
          </div>
          <div style="float: right">
            <?php if ($type === 'gravityforms') { ?>
              <input type="button" id="cancel" class="button-secondary" name="cancel" value="<?php _e("Cancel", 'outofthebox'); ?>" onclick="parent.tb_remove();" />
            <?php } else { ?>
              <input type="button" id="cancel" class="button-secondary" name="cancel" value="<?php _e("Cancel", 'outofthebox'); ?>" onclick="tinyMCEPopup.close();" />
            <?php } ?>
          </div>
        </div>
      </div>
    </form>

    <?php if ($type === 'gravityforms') { ?>
      <script>
        jQuery(document).ready(function ($) {
          $("#tabs").disableTab(2, true);
          $("#tabs").disableTab(4, true);
          $("#tabs").disableTab(5, true);
          $("#tabs").disableTab(6, true);
          $("#tabs").disableTab(8, true);
        });
      </script>
    <?php } ?>
  </body>
</html>