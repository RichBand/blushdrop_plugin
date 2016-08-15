<div class="OutoftheBox settingspage">
  <form method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?>
    <?php settings_fields('out_of_the_box_settings'); ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="out_of_the_box_settings[dropbox_app_token]" id="dropbox_app_token" value="<?php echo @esc_attr($this->settings['dropbox_app_token']); ?>" >
    <input type="hidden" name="out_of_the_box_settings[purcasecode]" id="purcasecode" value="<?php echo esc_attr($this->settings['purcasecode']); ?>">
    <div class="wrap">
      <h1><?php _e('Out-of-the-Box', 'outofthebox'); ?></h1>
      <div id="tabs"  style="display:none;">
        <ul>
          <li><a href="#settings_general"><span>Authorization</span></a></li>
          <li><a href="#settings_layout"><span>Layout</span></a></li>
          <li><a href="#settings_userfolders"><span>User Folders</span></a></li>
          <li><a href="#settings_notifications"><span>Notifications</span></a></li>
          <li><a href="#settings_shortlinks"><span>Shortlinks</span></a></li>
          <li><a href="#settings_stats"><span>Statistics</span></a></li>
          <li><a href="#settings_system"><span>System information</span></a></li>
          <li><a href="#settings_help"><span><i>Need help?</i></span></a></li>
        </ul>
        <!-- General Tab -->
        <div id="settings_general">
          <div class="option option-help">
            <div class="section">
              <div class="description">
                <?php
                $this->CheckDropboxApp();
                ?>
              </div>
            </div>
          </div>    

          <div class="option option-help">
            <h4><?php _e('Own Dropbox App', 'outofthebox'); ?>
            </h4>
            <div class="section">
              <div class="description">
                If you created your own Dropbox App, please enter your settings below. In the <a href="http://goo.gl/dsT71e" target="_blank">documentation</a> you can find how you can create a Dropbox App.
              </div>
            </div>
          </div>

          <div class="option">
            <h4><?php _e('Dropbox App key', 'outofthebox'); ?>
              <span class="help" title="<p>If you want to use your own Dropbox App, insert a Dropbox App Key. You can find this key on the settings page of Dropbox App Console.</p>">?</span>
            </h4>
            <div class="section largeinput">
              <input type="text" name="out_of_the_box_settings[dropbox_app_key]" id="dropbox_app_key" value="<?php echo esc_attr($this->settings['dropbox_app_key']); ?>" >
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Dropbox App secret', 'outofthebox'); ?>
              <span class="help" title="<p>If you want to use your own Dropbox App, insert a Dropbox App Secret. You can find this secret on the settings page of Dropbox App Console.</p>">?</span>
            </h4>
            <div class="section largeinput">
              <input type="text" name="out_of_the_box_settings[dropbox_app_secret]" id="dropbox_app_secret" value="<?php echo esc_attr($this->settings['dropbox_app_secret']); ?>">
            </div>
          </div>
        </div>
        <!-- End General Tab -->

        <!-- Layout Tab -->
        <div id="settings_layout">
          <div class="option" style='overflow: visible;'>
            <h4><?php _e('Lightbox skin', 'outofthebox'); ?>
              <span class="help" title="<p>Select which skin you want to use for the lightbox</p>">?</span>
            </h4>
            <div class="section">
              <select name="lightbox_skin_selectbox" id="lightbox_skin_selectbox" class="ddslickbox">
                <?php
                foreach (new DirectoryIterator(OUTOFTHEBOX_ROOTDIR . '/includes/iLightBox/') as $fileInfo) {
                  if ($fileInfo->isDir() && !$fileInfo->isDot() && (strpos($fileInfo->getFilename(), 'skin') !== false)) {
                    if (file_exists(OUTOFTHEBOX_ROOTDIR . '/includes/iLightBox/' . $fileInfo->getFilename() . '/skin.css')) {
                      $selected = '';
                      $skinname = str_replace('-skin', '', $fileInfo->getFilename());

                      if ($skinname === $this->settings['lightbox_skin']) {
                        $selected = 'selected="selected"';
                      }

                      $icon = file_exists(OUTOFTHEBOX_ROOTDIR . '/includes/iLightBox/' . $fileInfo->getFilename() . '/thumb.jpg') ? OUTOFTHEBOX_ROOTPATH . '/includes/iLightBox/' . $fileInfo->getFilename() . '/thumb.jpg' : '';
                      echo '<option value="' . $skinname . '" data-imagesrc="' . $icon . '" data-description="" ' . $selected . '>' . $fileInfo->getFilename() . "</option>\n";
                    }
                  }
                }
                ?>
              </select>
              <input type="hidden" name="out_of_the_box_settings[lightbox_skin]" id="lightbox_skin" value="<?php echo esc_attr($this->settings['lightbox_skin']); ?>">
            </div>
          </div>

          <div class="option">
            <h4><?php _e('Scroll horizontal or vertical in Lightbox', 'outofthebox'); ?>
              <span class="help" title="<p>Sets path for switching windows. Possible values are 'vertical' and 'horizontal' and the default is 'vertical'</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[lightbox_path]" id="lightbox_path">
                <option value="horizontal" <?php echo ($this->settings['lightbox_path'] === "horizontal" ? "selected='selected'" : ''); ?>>Horizontal</option>
                <option value="vertical" <?php echo ($this->settings['lightbox_path'] === "vertical" ? "selected='selected'" : ''); ?>>Vertical</option>
              </select>
            </div>
          </div>

          <div class="option" style='height: 120px; overflow: visible;'>
            <h4><?php _e('Media player skin', 'outofthebox'); ?>
              <span class="help" title="<p>Select which skin you want to use for the audio or media player</p>">?</span>
            </h4>
            <div class="section">
              <select name="mediaplayer_skin_selectbox" id="mediaplayer_skin_selectbox"class="ddslickbox">
                <?php
                foreach (new DirectoryIterator(OUTOFTHEBOX_ROOTDIR . '/skins/') as $fileInfo) {
                  if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                    if (file_exists(OUTOFTHEBOX_ROOTDIR . '/skins/' . $fileInfo->getFilename() . '/OutoftheBox_Media.js')) {
                      $selected = '';
                      if ($fileInfo->getFilename() === $this->settings['mediaplayer_skin']) {
                        $selected = 'selected="selected"';
                      }

                      $icon = file_exists(OUTOFTHEBOX_ROOTDIR . '/skins/' . $fileInfo->getFilename() . '/thumb.jpg') ? OUTOFTHEBOX_ROOTPATH . '/skins/' . $fileInfo->getFilename() . '/thumb.jpg' : '';
                      echo '<option value="' . $fileInfo->getFilename() . '" data-imagesrc="' . $icon . '" data-description="" ' . $selected . '>' . $fileInfo->getFilename() . "</option>\n";
                    }
                  }
                }
                ?>
              </select>
              <input type="hidden" name="out_of_the_box_settings[mediaplayer_skin]" id="mediaplayer_skin" value="<?php echo esc_attr($this->settings['mediaplayer_skin']); ?>">
            </div>
          </div>

          <div class="option">
            <h4><?php _e('Gallery Thumbnails', 'outofthebox'); ?>
              <span class="help" title="<p>Select how you want to generate the thumbnails. Out-of-the-Box can create the thumbnails for you, but this isn't always possible. If you can't generate thumbnails on your server, please set it to Dropbox.</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[thumbnails]" id="thumbnails">
                <option value="Dropbox" <?php echo ($this->settings['thumbnails'] === "Dropbox" ? "selected='selected'" : ''); ?>>Dropbox</option>
                <option value="Out-of-the-Box" <?php echo ($this->settings['thumbnails'] === "Out-of-the-Box" ? "selected='selected'" : ''); ?>>Out-of-the-Box</option>
              </select>
            </div>
          </div>

          <div class="option option-help">
            <h4><?php _e('Custom CSS', 'outofthebox'); ?>
            </h4>
            <div class="section">
              <div class="description">
                If you want to modify the looks of the plugin slightly, you can insert here your custom CSS. Don't edit the CSS files itself, because those modifications will be lost during an update.
              </div>
            </div>
          </div>

          <div class="option">
            <h4><?php _e('CSS', 'outofthebox'); ?>
              <span class="help" title="<p>You can insert here your custom CSS</p>">?</span>
            </h4>
            <div class="section largeinput">
              <textarea name="out_of_the_box_settings[custom_css]" id="custom_css" cols="" rows="10"><?php echo esc_attr($this->settings['custom_css']); ?></textarea>
            </div>
          </div>

        </div>
        <!-- End Layout Tab -->

        <!-- UserFolders Tab -->
        <div id="settings_userfolders">
          <div class="option">
            <h4><?php _e('User folder name', 'outofthebox'); ?>
              <span class="help" title="<p>Template name for automatically created user folders. You can use %user_login%, %user_email%, %display_name%, %ID%.</p>">?</span>
            </h4>
            <div class="section largeinput">
              <input type="text" name="out_of_the_box_settings[userfolder_name]" id="userfolder_name" value="<?php echo esc_attr($this->settings['userfolder_name']); ?>">
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Create user folders on registration', 'outofthebox'); ?>
              <span class="help" title="<p>Create the a new user folder automatically after a new user has been created</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[userfolder_oncreation]" id="userfolder_oncreation">
                <option value="Yes" <?php echo ($this->settings['userfolder_oncreation'] === "Yes" ? "selected='selected'" : ''); ?>>Yes</option>
                <option value="No" <?php echo ($this->settings['userfolder_oncreation'] === "No" ? "selected='selected'" : ''); ?>>No</option>
              </select>
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Create all user folders on first visit', 'outofthebox'); ?>
              <span class="help" title="<p>Create all user folders on first visit. This takes around 1 sec per user, so it isn't recommended if you have tons of users.</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[userfolder_onfirstvisit]" id="userfolder_onfirstvisit">
                <option value="Yes" <?php echo ($this->settings['userfolder_onfirstvisit'] === "Yes" ? "selected='selected'" : ''); ?>>Yes</option>
                <option value="No" <?php echo ($this->settings['userfolder_onfirstvisit'] === "No" ? "selected='selected'" : ''); ?>>No</option>
              </select>
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Update user folders after profile update', 'outofthebox'); ?>
              <span class="help" title="<p>Update the folder name of the user after they update their profile.</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[userfolder_update]" id="userfolder_update">
                <option value="Yes" <?php echo ($this->settings['userfolder_update'] === "Yes" ? "selected='selected'" : ''); ?>>Yes</option>
                <option value="No" <?php echo ($this->settings['userfolder_update'] === "No" ? "selected='selected'" : ''); ?>>No</option>
              </select>
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Remove user folders after deletion', 'outofthebox'); ?>
              <span class="help" title="<p>Try to remove user folders after they are deleted.</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[userfolder_remove]" id="userfolder_remove">
                <option value="Yes" <?php echo ($this->settings['userfolder_remove'] === "Yes" ? "selected='selected'" : ''); ?>>Yes</option>
                <option value="No" <?php echo ($this->settings['userfolder_remove'] === "No" ? "selected='selected'" : ''); ?>>No</option>
              </select>
            </div>
          </div>
        </div>
        <!-- End UserFolders Tab -->

        <!-- Notifications Tab -->
        <div id="settings_notifications">
          <div class="option">
            <h4><?php _e('Template download', 'outofthebox'); ?>
              <span class="help" title="<p>Template for email notification</p>">?</span>
            </h4>
            <div class="section largeinput">
              <textarea name="out_of_the_box_settings[download_template]" id="download_template" cols="" rows="6"><?php echo esc_attr($this->settings['download_template']); ?></textarea>
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Template upload', 'outofthebox'); ?>
              <span class="help" title="<p>Template for email notification</p>">?</span>
            </h4>
            <div class="section largeinput">
              <textarea name="out_of_the_box_settings[upload_template]" id="upload_template" cols="" rows="6"><?php echo esc_attr($this->settings['upload_template']); ?></textarea>
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Template deletion', 'outofthebox'); ?>
              <span class="help" title="<p>Template for email notification</p>">?</span>
            </h4>
            <div class="section largeinput">
              <textarea name="out_of_the_box_settings[delete_template]" id="delete_template" cols="" rows="6"><?php echo esc_attr($this->settings['delete_template']); ?></textarea>
            </div>
          </div>
          <div class="option">
            <h4><?php _e('Template files in %filelist%', 'outofthebox'); ?>
              <span class="help" title="<p>Template for File item in File List (%filelist%) in the download/upload/delete template</p>">?</span>
            </h4>
            <div class="section largeinput">
              <textarea name="out_of_the_box_settings[filelist_template]" id="filelist_template" cols="" rows="6"><?php echo esc_attr($this->settings['filelist_template']); ?></textarea>
            </div>
          </div>
        </div>
        <!-- End Notifications Tab -->

        <!--  ShortLinks Tab -->
        <div id="settings_shortlinks">
          <div class="option">
            <h4><?php _e('Shortlinks API', 'outofthebox'); ?>
              <span class="help" title="<p>How do you want to create shortlinks to your files</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[shortlinks]" id="shortlinks">
                <option value="Dropbox"  <?php echo ($this->settings['shortlinks'] === "Dropbox" ? "selected='selected'" : ''); ?>>Dropbox</option>
                <option value="Bitly"  <?php echo ($this->settings['shortlinks'] === "Bitly" ? "selected='selected'" : ''); ?>>Bitly</option>
              </select>
            </div>
          </div>

          <div class="option bitly">
            <p><a href="https://bitly.com/a/sign_up" target="_blank">Sign up by Bitly</a> and <a href="https://bitly.com/a/your_api_key‎" target="_blank">get your apiKey</a></p>
            <h4><?php _e('Bitly login', 'outofthebox'); ?>
            </h4>
            <div class="section largeinput">
              <input type="text" name="out_of_the_box_settings[bitly_login]" id="bitly_login" value="<?php echo esc_attr($this->settings['bitly_login']); ?>">
            </div>
            <h4><?php _e('Bitly apiKey', 'outofthebox'); ?>
            </h4>
            <div class="section largeinput">
              <input type="text" name="out_of_the_box_settings[bitly_apikey]" id="bitly_apikey" value="<?php echo esc_attr($this->settings['bitly_apikey']); ?>" >
            </div>
          </div> 
        </div>
        <!-- End ShortLinks Tab -->

        <!--  Statistics Tab -->
        <div id="settings_stats">
          <div class="option option-help">
            <h4><?php _e('Statistics', 'outofthebox'); ?>
            </h4>
            <div class="section">
              <div class="description">
                Would you like to see some statistics about your files? Out-of-the-Box can send all download/upload events to Google Analytics. 
                If you enable this feature, please make sure you already added your <a href="https://support.google.com/analytics/answer/1008080?hl=en">Google Analytics web tracking</a> code to your site.
              </div>
            </div>
          </div>

          <div class="option">
            <h4><?php _e('Enable Google Analytics', 'outofthebox'); ?>
              <span class="help" title="<p>Enable Google Analytics to track all download/upload/stream events</p>">?</span>
            </h4>
            <div class="section">
              <select type="text" name="out_of_the_box_settings[google_analytics]" id="google_analytics">
                <option value="Yes" <?php echo ($this->settings['google_analytics'] === "Yes" ? "selected='selected'" : ''); ?>>Yes</option>
                <option value="No" <?php echo ($this->settings['google_analytics'] === "No" ? "selected='selected'" : ''); ?>>No</option>
              </select>
            </div>
          </div> 
        </div>
        <!-- End Statistics Tab -->

        <!-- System info Tab -->
        <div id="settings_system">
          <div class="option option-help">
            <h4><?php _e('System information', 'outofthebox'); ?>
            </h4>
            <div class="section">
              <div class="description">
                <?php
                echo $this->checkDependencies();
                ?>
              </div>
            </div>
          </div>
        </div>
        <!-- End System info -->
        <!-- Help Tab -->
        <div id="settings_help">
          <div class="option option-help">
            <h4><?php _e('Support & Documentation', 'outofthebox'); ?></h4>
            <div class="section">
              <div class="description">
                <p><a href='http://goo.gl/FxM4QN' title='Out of the Box documentation' target="_blank"><?php _e('Visit the Out-of-the-Box website', 'outofthebox'); ?></a> <?php _e('for documentation and installation details', 'outofthebox'); ?>.</p>
                <p><?php _e('Discovered a bug or just need some help with the plugin?', 'outofthebox'); ?> <a href='http://goo.gl/rjuqhv' title='Out of the Box support' target="_blank"><?php _e('Visit the support page', 'outofthebox'); ?></a>.</p>
              </div>
            </div>
          </div>
        </div>
        <!-- End Help info -->
      </div>
      <?php submit_button(); ?>
    </div>
  </form>
  <script type="text/javascript" >
    jQuery(document).ready(function ($) {
      $('#shortlinks').change(function () {
        if ($(this).val() === 'Dropbox') {
          $(this).parent().parent().next().hide();
        } else {
          $(this).parent().parent().next().show();
        }
      });
      $('#shortlinks').trigger('change');

      $('#lightbox_skin_selectbox').ddslick({
        width: 330,
        imagePosition: "right",
        background: '#FFFFFF',
        onSelected: function (item) {
          $("#lightbox_skin").val($('#lightbox_skin_selectbox').data('ddslick').selectedData.value);
        }
      });

      $('#mediaplayer_skin_selectbox').ddslick({
        width: 330,
        imagePosition: "right",
        background: '#FFFFFF',
        onSelected: function (item) {
          $("#mediaplayer_skin").val($('#mediaplayer_skin_selectbox').data('ddslick').selectedData.value);
        }
      });
    });
  </script>
</div>