<?php

require_once 'OutoftheBox_Processor.php';

// Load Dropbox SDK
// Check if Dropbox autoloader is already defined
if (!function_exists('Dropbox\autoload')) {
  try {
    require_once "dropbox-sdk/Dropbox/autoload.php";
  } catch (Exception $ex) {
    return new WP_Error('broke', __('Something went wrong... See settings page', 'outofthebox'));
  }
}

use \Dropbox as dbx;

class OutoftheBox_Dropbox extends OutoftheBox_Processor {

  /** @var string */
  private $accessToken = null;
  private $appKey = 'm3n3zyvyr59cdjb';
  private $appSecret = 'eu73x5upk7ehes4';
  private $client = null;

  /** @var string */
  public $redirectUri;

  /** @var array */
  private $appInfo;
  protected $cache;
  protected $_cache_handle = null;

  /*
   * Try to load prestored token
   *
   * @return boolean|WP_Error
   */

  public function loadToken() {
    if (empty($this->settings['dropbox_app_token'])) {
      return new WP_Error('broke', '<strong>' . __("Out-of-the-Box needs your help!", 'outofthebox') . '</strong> ' . __('Authorize the plugin. If you would like to use your own Dropbox App, enter your App key &amp; secret and re-authorize', 'outofthebox') . '.');
    } else {
      $this->accessToken = $this->settings['dropbox_app_token'];
    }

    return true;
  }

  /*
   * Revoke token
   *
   * @return boolean|WP_Error
   */

  public function revokeToken() {
    $this->client->disableAccessToken();
    $this->accessToken = '';
    $this->settings['dropbox_app_token'] = '';
    update_option('out_of_the_box_settings', $this->settings);
    return true;
  }

  /*
   * Read Dropbox app key and secret
   */

  function setAppConfig() {
    if ((!empty($this->settings['dropbox_app_key'])) && (!empty($this->settings['dropbox_app_secret']))) {
      $this->appKey = $this->settings['dropbox_app_key'];
      $this->appSecret = $this->settings['dropbox_app_secret'];
    }

    $_appinfo = array('key' => $this->appKey, 'secret' => $this->appSecret);
    try {
      $appInfo = dbx\AppInfo::loadFromJson($_appinfo);
    } catch (dbx\AppInfoLoadException $ex) {
      return new WP_Error('broke', __("Your Dropbox App key or token seems to be invalid: ", 'outofthebox') . $ex->getMessage());
    }

    $clientIdentifier = "Out-of-the-Box(Wordpress)/" . OUTOFTHEBOX_VERSION;
    $userLocale = 'en';

    $this->appInfo = array('appInfo' => $appInfo, 'clientIdentifier' => $clientIdentifier, 'userLocale' => $userLocale);
    return $this->appInfo;
  }

  /*
   * Start Dropbox API Client with token
   *
   * @return WP_Error|dbx\Client
   */

  public function startClient() {
    if ($this->accessToken === false)
      die();

    try {
      $accessToken = $this->accessToken;
      $dbxClient = new dbx\Client($accessToken, $this->appInfo['clientIdentifier'], $this->appInfo['userLocale'], $this->appInfo['appInfo']->getHost());
    } catch (Exception $e) {
      return new WP_Error('broke', __("Error in getClient: ", 'outofthebox') . $e->getMessage());
    }

    $this->client = $dbxClient;

    return $this->client;
  }

  /*
   * Get AccountInfo
   *
   * @return mixed|WP_Error
   */

  function getAccountInfo() {
    if ($this->client === null)
      return false;

    try {
      $accountInfo = $this->client->getAccountInfo();
    } catch (Exception $ex) {
      return new WP_Error('broke', $ex->getMessage());
    }

    return $accountInfo;
  }

  /*
   * Gets a $authorizeUrl
   *
   * @return string|WP_Error
   * The URL to redirect the user to.
   */

  public function startWebAuth() {
    try {
      $authorizeUrl = $this->getWebAuth()->start();
    } catch (Exception $ex) {
      return new WP_Error('broke', __("/dropbox-auth-start: could not start authorization: ", 'outofthebox') . $ex->getMessage());
    }
    update_option('out_of_the_box_settings', $this->settings);
    return $authorizeUrl;
  }

  /*
   * Creates token after the user has visited the authorize URL, approved the app,
   * and was redirected to your redirect URI.
   *
   * @return WP_Error|true
   */

  public function createToken() {
    if (!empty($_GET['code'])) {
      try {
        list($accessToken, $userId, $urlState) = $this->getWebAuth()->finish($_GET);
        $this->accessToken = $accessToken;
        $this->settings['dropbox_app_token'] = $accessToken;
        $this->settings['dropbox_csrf-token'] = '';
      } catch (dbx\WebAuthException_BadRequest $ex) {
        $this->settings['dropbox_csrf-token'] = '';
        update_option('out_of_the_box_settings', $this->settings);
        return new WP_Error('broke', __("/dropbox-auth-finish: bad request: ", 'outofthebox') . $ex->getMessage());
      } catch (dbx\WebAuthException_BadState $ex) {
// Auth session expired.  Restart the auth process.
        header('Location: ' . $this->redirectUri);
        die();
      } catch (dbx\WebAuthException_Csrf $ex) {
        $this->settings['dropbox_csrf-token'] = '';
        update_option('out_of_the_box_settings', $this->settings);
        return new WP_Error('broke', __("/dropbox-auth-finish: CSRF mismatch: ", 'outofthebox') . $ex->getMessage());
      } catch (dbx\WebAuthException_NotApproved $ex) {
        $this->settings['dropbox_csrf-token'] = '';
        update_option('out_of_the_box_settings', $this->settings);
        return new WP_Error('broke', __("/dropbox-auth-finish: not approved: ", 'outofthebox') . $ex->getMessage());
      } catch (dbx\WebAuthException_Provider $ex) {
        $this->settings['dropbox_csrf-token'] = '';
        update_option('out_of_the_box_settings', $this->settings);
        return new WP_Error('broke', __("/dropbox-auth-finish: error redirect from Dropbox: ", 'outofthebox') . $ex->getMessage());
      } catch (dbx\Exception $ex) {
        $this->settings['dropbox_csrf-token'] = '';
        update_option('out_of_the_box_settings', $this->settings);
        return new WP_Error('broke', __("/dropbox-auth-finish: error communicating with Dropbox API: ", 'outofthebox') . $ex->getMessage());
      }

      update_option('out_of_the_box_settings', $this->settings);

      return true;
    }
  }

  /*
   * Get folders and files or download a file
   *
   * @return false|array
   * array['path'] = current path
   * array['filelist'] = files and folders in current path
   */

  public function getFolder() {
    if ($this->client === null)
      return false;

    /* Get requested path */
    if (isset($_REQUEST['OutoftheBoxpath']) && $_REQUEST['OutoftheBoxpath'] != '') {

      /* Check if requested path is allowed */
      if (!$this->isEntryAuthorized($this->_requestedPath)) {
        /* Gotcha, this path isn't allowed, return to root or die() if downloadfile */
        $this->setLastPath('');
        unset($_REQUEST['OutoftheBoxpath']);
        return $this->getFolder();
      }

      $this->setLastPath($this->_requestedPath);
    }

//Check if $requested_path is al valid path
    $pathError = dbx\Path::findError($this->_requestedCompletePath);
    if ($pathError !== null) {
      return false;
    } else {

      /* Get folder meta data */
      $entry = $this->client->getMetadataWithChildren($this->_requestedCompletePath);

      /* No valid folder, return to root */
      if ($entry === null) {
        if ($this->_requestedPath === '') {
          return false; // root directory doesn't exist
        }
        $this->setLastPath('');
        unset($_REQUEST['OutoftheBoxpath']);
        return $this->getFolder();
      } else {
        $this->setLastPath($this->_requestedPath);
      }

      /* Sort contents */
      if (count($entry['contents']) > 0) {
        $entry['contents'] = $this->sortFilelist($entry['contents']);
      }

      return $entry;
    }

    return false;
  }

  public function getFolderImages($path) {
    $entry = $this->client->getMetadataWithChildren($path);

    if (($entry === null) || (!isset($entry['contents']))) {
      return array();
    } else {
      return $entry['contents'];
    }
  }

  public function searchByName($query) {
    if ($this->options['searchfrom'] === 'parent') {
      $searchedfolder = $this->_requestedCompletePath;
    } else {
      $searchedfolder = $this->_rootFolder;
    }

    try {
      $result = $this->client->searchFileNames($searchedfolder, $query);
    } catch (Exception $ex) {
      return array();
    }

    if (count($result) > 0) {
      return $result;
    } else {
      return array();
    }
  }

  /*
   * Uploads file to server
   * After upload send file to Dropbox
   * and delete files from tempdir
   */

  function uploadFile() {
    /* Upload File to server */
    require('jquery-file-upload/server/UploadHandler.php');
    $accept_file_types = '/.(' . $this->options['upload_ext'] . ')$/i';
    $post_max_size_bytes = min(OutoftheBox_return_bytes(ini_get('post_max_size')), OutoftheBox_return_bytes(ini_get('upload_max_filesize')));
    $max_file_size = ($this->options['maxfilesize'] !== '0') ? $this->options['maxfilesize'] : $post_max_size_bytes;

    $uploadir = wp_upload_dir();

    $options = array(
      'upload_dir' => $uploadir['path'] . '/',
      'upload_url' => $uploadir['url'] . '/',
      'access_control_allow_methods' => array('POST', 'PUT'),
      'accept_file_types' => $accept_file_types,
      'inline_file_types' => '/\.____$/i',
      'orient_image' => false,
      'image_versions' => array(),
      'max_file_size' => $max_file_size,
      'print_response' => false
    );

    if ($this->options['demo'] === '1') {
      $options['accept_file_types'] = '/\.____$/i';
    }

    $error_messages = array(
      1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'outofthebox'),
      2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'outofthebox'),
      3 => __('The uploaded file was only partially uploaded', 'outofthebox'),
      4 => __('No file was uploaded', 'outofthebox'),
      6 => __('Missing a temporary folder', 'outofthebox'),
      7 => __('Failed to write file to disk', 'outofthebox'),
      8 => __('A PHP extension stopped the file upload', 'outofthebox'),
      'post_max_size' => __('The uploaded file exceeds the post_max_size directive in php.ini', 'outofthebox'),
      'max_file_size' => __('File is too big', 'outofthebox'),
      'min_file_size' => __('File is too small', 'outofthebox'),
      'accept_file_types' => __('Filetype not allowed', 'outofthebox'),
      'max_number_of_files' => __('Maximum number of files exceeded', 'outofthebox'),
      'max_width' => __('Image exceeds maximum width', 'outofthebox'),
      'min_width' => __('Image requires a minimum width', 'outofthebox'),
      'max_height' => __('Image exceeds maximum height', 'outofthebox'),
      'min_height' => __('Image requires a minimum height', 'outofthebox')
    );

    $this->upload_handler = new UploadHandler($options, false, $error_messages);
    $response = @$this->upload_handler->post(false);
    $hash = $_REQUEST['hash'];

    /* Upload files to Dropbox */
    foreach ($response['files'] as &$file) {
      /* Set return Object */
      $file->listtoken = $this->listtoken;
      $file->hash = $hash;

      if (!isset($file->error)) {
        $return = array('file' => $file, 'status' => array('bytes_down_so_far' => 0, 'total_bytes_down_expected' => $file->size, 'percentage' => 0, 'progress' => 'starting'));
        set_transient('outofthebox_upload_' . substr($hash, 0, 40), $return, HOUR_IN_SECONDS);

        /* Check if $dropboxPath is al valid path */
        $pathError = dbx\Path::findError($this->_requestedCompletePath);
        if ($pathError !== null) {
          $file->error = __('Invalid <dropbox-path>', 'outofthebox');
          $return['status']['progress'] = 'failed';
        } else {

          /* Check if file already exists */
          $file_exists = $this->client->getMetadata($this->_requestedCompletePath . '/' . $file->name);
          if ($file_exists !== null) {
            $file_rev = $file_exists['rev'];
          }

          /* Add or update file? */
          if ($this->options['overwrite'] === '1' && isset($file_rev)) {
            $writemode = dbx\WriteMode::update($file_rev);
          } else {
            $writemode = dbx\WriteMode::add();
          }

          /* Write file */
          $filePath = $file->tmp_path;
          $fp = fopen($filePath, "rb");
          try {
            $file->completepath = $this->_requestedCompletePath . '/' . $file->name;
            $file->filesize = OutoftheBox_bytesToSize1024($file->size);
            $metadata = $this->client->uploadFileChunked($this->_requestedCompletePath . '/' . $file->name, $writemode, $fp, $file->size);
            $file->fileid = md5($metadata['path']);
            $file->link = false; // Currently no direct link available
          } catch (Exception $ex) {
            $file->error = __('Not uploaded to Dropbox ', 'outofthebox') . $ex->getMessage();
          }
          fclose($fp);

          /* Send email if needed */
          if ($this->options['notificationupload'] === '1') {
            $this->sendNotificationEmail('upload', array($metadata));
          }

          /* Upload Hook */
          do_action('useyourdrive_upload', $metadata, $file);

          $return['status']['progress'] = 'finished';
          $return['status']['percentage'] = '100';
        }
      } else {
        $return['status']['progress'] = 'failed';
        if ($this->options['debug'] === '1') {
          $file->error = __('Uploading failed', 'outofthebox') . ': ' . $file->error;
        } else {
          $file->error = __('Uploading failed', 'outofthebox');
        }
      }
    }

    $return['file'] = $file;
    set_transient('outofthebox_upload_' . substr($hash, 0, 40), $return, HOUR_IN_SECONDS);

    /* Create response */
    echo json_encode($return);
    die();
  }

  /* Monitor upload to cloud */

  function getUploadStatus() {
    $hash = $_REQUEST['hash'];

    /* Try to get the upload status of the file */
    for ($_try = 1; $_try < 6; $_try++) {
      $result = get_transient('outofthebox_upload_' . substr($hash, 0, 40));

      if ($result !== false) {

        if ($result['status']['progress'] === 'failed' || $result['status']['progress'] === 'finished') {
          delete_transient('outofthebox_upload_' . substr($hash, 0, 40));
        }

        break;
      }

      /* Wait a moment, perhaps the upload still needs to start */
      usleep(500000 * $_try);
    }

    if ($result === false) {
      $result = array('file' => false, 'status' => array('bytes_down_so_far' => 0, 'total_bytes_down_expected' => 0, 'percentage' => 0, 'progress' => 'failed'));
    }

    echo json_encode($result);
    die();
  }

  public function uploadGravityFormPdf($pdffile, $dropboxpath) {
    $authorized = $this->_IsAuthorized('hook');

    if (strpos($dropboxpath, '%user_folder%') !== false) {
      $this->options['root'] = $dropboxpath;
      $dropboxpath = $this->createUserFolder();
    }

    $path_parts = OutoftheBox_mbPathinfo($pdffile);
    $dropboxpath = str_replace('//', '/', '/' . $dropboxpath . '/' . $path_parts['basename']);
    $pathError = dbx\Path::findError($dropboxpath);

    if (!$authorized || $pathError) {
      return false;
    }



    /* Check if file already exists */
    $file_exists = $this->client->getMetadata($dropboxpath);
    if ($file_exists !== null) {
      $writemode = dbx\WriteMode::update($file_exists['rev']);
    } else {
      $writemode = dbx\WriteMode::add();
    }

    /* Open file and upload to Dropbox */
    $fp = fopen($pdffile, "rb");

    try {
      $metadata = $this->client->uploadFileChunked($dropboxpath, $writemode, $fp, filesize($pdffile));
    } catch (Exception $ex) {
      fclose($fp);
      return false;
    }
    fclose($fp);

    return true;
  }

  /*
   * Delete entry from Dropbox
   */

  function deleteEntry($requested_entry = null, $multiple = false) {

    if ($this->options['demo'] === '1') {
      return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
    }

    if ($requested_entry === null) {
      $requested_entry = $this->_requestedCompletePath;
    }

//Check if requested path is allowed
    if (!$this->isEntryAuthorized($requested_entry)) {
//Gotcha, this path isn't allowed
      return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
    }

    if (!$this->isExtensionAuthorized($requested_entry, $this->options['ext'], '.')) {
//Gotcha, this extension isn't allowed
      return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
    }

//Check if $dropboxPath is a valid path
    $pathError = dbx\Path::findError($requested_entry);
    if ($pathError !== null) {
      if ($this->options['debug'] === '1') {
        return new WP_Error('broke', __('Invalid <dropbox-path>', 'outofthebox') . ' ' . $pathError);
      } else {
        return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
      }
    } else {
      try {
        $pathdata = $this->client->getMetadata($requested_entry);

        if ($pathdata['is_dir']) {
          if (!$this->checkUserRole($this->options['deletefolders_role'])) {
            return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
          }
        } else {
          if (!$this->checkUserRole($this->options['deletefiles_role'])) {
            return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
          }
        }

        $meta_entry = $this->client->delete($requested_entry);

        if (($this->options['notificationdeletion'] === '1') && ($multiple === false)) {
          $this->sendNotificationEmail('deletion', array($meta_entry));
        }
      } catch (Exception $ex) {
        if ($this->options['debug'] === '1') {
          return new WP_Error('broke', $ex->getMessage());
        } else {
          return new WP_Error('broke', __('Failed to delete entry', 'outofthebox'));
        }
      }
    }

    return $meta_entry;
  }

  public function deleteEntries() {
    $deleted_entries = array();

    foreach ($_REQUEST['entries'] as $entry) {
      $deleted_entries[$entry] = $this->deleteEntry($this->_requestedCompletePath . '/' . rawurldecode($entry), 'multiple');
    }

    /* Send email if needed */
    if ($this->options['notificationdeletion'] === '1' && (!is_wp_error($entry))) {
      $this->sendNotificationEmail('deletion_multiple', $deleted_entries);
    }

    return $deleted_entries;
  }

  /*
   * Rename entry from Dropbox
   */

  function renameEntry($new_filename = null) {

    if ($this->options['demo'] === '1') {
      return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
    }

    if ($new_filename === null && $this->options['debug'] === '1') {
      return new WP_Error('broke', __('No new name set', 'outofthebox'));
    }

    /* Check if requested path is allowed */
    if (!$this->isEntryAuthorized($this->_requestedCompletePath)) {
      /* Gotcha, this path isn't allowed */
      return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
    }

    /* Check if extensions is allowed */
    if (!$this->isExtensionAuthorized($this->_requestedCompletePath, $this->options['ext'], '.')) {
      /* Gotcha, this extension isn't allowed */
      return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
    }

    /* Check if $dropboxPath is a valid path */
    $pathError = dbx\Path::findError($this->_requestedCompletePath);
    if ($pathError !== null) {
      if ($this->options['debug'] === '1') {
        return new WP_Error('broke', __('Invalid <dropbox-path>', 'outofthebox') . ' ' . $pathError);
      } else {
        return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
      }
    } else {

      $pathdata = $this->client->getMetadata($this->_requestedCompletePath);

      if ($pathdata['is_dir']) {
        if (!$this->checkUserRole($this->options['renamefolders_role'])) {
          return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
        }
      } else {
        if (!$this->checkUserRole($this->options['renamefiles_role'])) {
          return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
        }
      }

      //Set new entry path
      $pathinfo = OutoftheBox_mbPathinfo($this->_requestedCompletePath);
      $extension = (isset($pathinfo['extension']) && !$pathdata['is_dir']) ? '.' . $pathinfo['extension'] : '';
      $newdropboxPath = dirname($this->_requestedCompletePath) . '/' . $new_filename . strtolower($extension);


      try {
        $meta_entry = $this->client->move($this->_requestedCompletePath, $newdropboxPath);
      } catch (Exception $ex) {
        if ($this->options['debug'] === '1') {
          return new WP_Error('broke', $ex->getMessage());
        } else {
          return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
        }
      }
    }

    return $meta_entry;
  }

  /*
   * Move entry 
   */

  function moveEntry($target = null, $copy = false) {
    if ($this->options['demo'] === '1') {
      return new WP_Error('broke', __('Failed to move entry', 'outofthebox'));
    }

    if ($target === null && $this->options['debug'] === '1') {
      return new WP_Error('broke', __('Failed to move entry', 'outofthebox'));
    }

    /* Check if requested path is allowed */
    if (!$this->isEntryAuthorized($this->_requestedCompletePath) || !$this->isEntryAuthorized($target)) {
      /* Gotcha, this path isn't allowed */
      return new WP_Error('broke', __('Failed to move entry', 'outofthebox'));
    }

    /* Check if extensions is allowed */
    if (!$this->isExtensionAuthorized($this->_requestedCompletePath, $this->options['ext'], '.')) {
      /* Gotcha, this extension isn't allowed */
      return new WP_Error('broke', __('Failed to move entry', 'outofthebox'));
    }

    /* Check if $dropboxPath is a valid path */
    $pathError = dbx\Path::findError($this->_requestedCompletePath);
    $targetError = dbx\Path::findError($target);

    if ($pathError !== null || $targetError !== null) {
      if ($this->options['debug'] === '1') {
        return new WP_Error('broke', __('Invalid <dropbox-path>', 'outofthebox') . ' ' . $pathError);
      } else {
        return new WP_Error('broke', __('Failed to move entry', 'outofthebox'));
      }
    } else {

      $pathdata = $this->client->getMetadata($this->_requestedCompletePath);
      $targetpathdata = $this->client->getMetadata($target);

      if (!$this->checkUserRole($this->options['move_role'])) {
        return new WP_Error('broke', __('Failed to move entry', 'outofthebox'));
      }

      try {
        $entryname = ($pathdata['is_dir']) ? $this->_requestedDir : '/' . $this->_requestedFile;

        $meta_entry = $this->client->move($this->_requestedCompletePath, $targetpathdata['path'] . $entryname);
      } catch (Exception $ex) {
        if ($this->options['debug'] === '1') {
          return new WP_Error('broke', $ex->getMessage());
        } else {
          return new WP_Error('broke', __('Failed to rename entry', 'outofthebox'));
        }
      }
    }

    return $meta_entry;
  }

  /*
   * Add directory to Dropbox
   */

  function addFolder($new_folder = null) {

    if ($this->options['demo'] === '1') {
      return new WP_Error('broke', __('Failed to add folder', 'outofthebox'));
    }

    if ($new_folder === null && $this->options['debug'] === '1') {
      return new WP_Error('broke', __('No new foldername set', 'outofthebox'));
    }

//Check if requested path is allowed
    if (!$this->isEntryAuthorized($this->_requestedCompletePath)) {
//Gotcha, this path isn't allowed
      return new WP_Error('broke', __('Failed to add folder', 'outofthebox'));
    }

//Set new entry path
    $newdropboxPath = $this->_requestedCompletePath . '/' . $new_folder;

//Check if $dropboxPath is a valid path
    $pathError = dbx\Path::findError($this->_requestedCompletePath);
    if ($pathError !== null) {
      if ($this->options['debug'] === '1') {
        return new WP_Error('broke', __('Invalid <dropbox-path>', 'outofthebox') . ' ' . $pathError);
      } else {
        return new WP_Error('broke', __('Failed to add folder', 'outofthebox'));
      }
    } else {
      try {
        $meta_entry = $this->client->createFolder($newdropboxPath);
      } catch (Exception $ex) {
        if ($this->options['debug'] === '1') {
          return new WP_Error('broke', $ex->getMessage());
        } else {
          return new WP_Error('broke', __('Failed to add folder', 'outofthebox'));
        }
      }
    }

    if ($meta_entry === null) {
      return new WP_Error('broke', __('Folder', 'outofthebox') . " '$new_folder' " . __('already exists', 'outofthebox'));
    } else {

      $this->setLastPath($this->_requestedPath . '/' . $new_folder);
    }

    return $meta_entry;
  }

  function addUserFolder() {

    $pathError = dbx\Path::findError($this->_requestedCompletePath);
    if ($pathError !== null) {
      if ($this->options['debug'] === '1') {
        return new WP_Error('broke', __('Invalid <dropbox-path>', 'outofthebox') . ' ' . $pathError);
      } else {
        return new WP_Error('broke', __('Failed to add user folder', 'outofthebox'));
      }
    } else {
      $pathdata = $this->client->getMetadata($this->_requestedCompletePath);

      if ($pathdata === null) {
        try {
          /* Copy file from template folder if needed */
          if ($this->options['user_template_dir'] !== '') {
            $this->client->copy($this->options['user_template_dir'], $this->_requestedCompletePath);
          } else {
            /* Else just create a new folder */
            $this->client->createFolder($this->_requestedCompletePath);
          }
        } catch (Exception $ex) {
          if ($this->options['debug'] === '1') {
            return new WP_Error('broke', $ex->getMessage());
          } else {
            return new WP_Error('broke', __('Failed to add folder', 'outofthebox'));
          }
        }
      } else {
        return false;
      }
    }

    return true;
  }

  function updateUserFolder($listoptions, $userfoldername, $olduserfoldername = false, $delete = false) {
    $pathError = dbx\Path::findError($listoptions['root']);
    if ($pathError === null) {
      if ($olduserfoldername === false && $delete === false) {
        $pathdata = $this->client->getMetadata($listoptions['root']);
      } elseif ($delete === true) {
        $pathdata = $this->client->getMetadata($listoptions['root'] . '/' . $userfoldername);
      } else {
        $pathdata = $this->client->getMetadata($listoptions['root'] . '/' . $olduserfoldername);
      }

      if ($pathdata !== null) {
        try {
          if ($olduserfoldername === false && $delete === false) {
            /* Copy file from template folder if needed */
            if ($listoptions['user_template_dir'] !== '') {
              $this->client->copy($listoptions['user_template_dir'], $listoptions['root'] . '/' . $userfoldername);
            } else {
              /* Else just create a new folder */
              $this->client->createFolder($listoptions['root'] . '/' . $userfoldername);
            }
          } elseif ($delete === true) {
            $this->client->delete($listoptions['root'] . '/' . $userfoldername);
          } else {
            $this->client->move($listoptions['root'] . '/' . $olduserfoldername, $rootfolder . '/' . $userfoldername);
          }
        } catch (Exception $ex) {
          
        }
      } else {
        return false;
      }
    } else {
      return false;
    }

    return true;
  }

  /*
   * OAuth 2 "authorization code" flow
   */

  function getWebAuth() {
    if (isset($this->settings['dropbox_csrf-token'])) {
      $this->settings['dropbox_csrf-token'] = '';
    }
    $csrfTokenStore = new dbx\ArrayEntryStore($this->settings, 'dropbox-auth-csrf-token');
    return new dbx\WebAuth($this->appInfo['appInfo'], $this->appInfo['clientIdentifier'], $this->redirectUri, $csrfTokenStore);
  }

  function setRedirectUri($url) {
    $this->redirectUri = $url;
    return $this->redirectUri;
  }

  /*
   * Set Filelist token
   */

  public function setListToken($listtoken) {
    $this->listtoken = $listtoken;
    return $this->listtoken;
  }

  /* Get Preview of file */

  function getPreview() {
    $path_parts = OutoftheBox_mbPathinfo($this->_requestedCompletePath);
    if ((isset($path_parts['extension']) && !in_array(strtolower($path_parts['extension']), $this->options['ext'])) && $this->options['ext'][0] != '*') {
      die();
    }

    if (!$this->isEntryAuthorized($this->_requestedCompletePath)) {
      die();
    }

    $previewsupport = array('pdf', 'doc', 'docx', 'docm', 'ppt', 'pps', 'ppsx', 'ppsm', 'pptx', 'pptm', 'xls', 'xlsx', 'xlsm', 'rtf');
    if (!in_array(strtolower($path_parts['extension']), $previewsupport)) {
      die();
    }


    if (in_array($path_parts['extension'], array('xls', 'xlsx', 'xlsm'))) {
      header('Content-Type: text/html');
    } else {
      header('Content-Disposition: inline; filename="' . $path_parts['filename'] . '.pdf"');
      header('Content-Description: "' . $path_parts['filename'] . '"');
      header('Content-Type: application/pdf');
    }

    /* Enables Preview for PDF files. Requires 'pdf' in array $previewsupport */
    if ($path_parts['extension'] === 'pdf') {
      $entry = $this->client->getMetadata($this->_requestedCompletePath);
      $link = $this->_mediaFromCache($entry['path'], 'files');
      $link = $link['cache'];
      $link['url'] = str_replace('dl.dropboxusercontent.com/1/view/', 'dropbox.com/s/', $link['url']);
      header('Location: ' . $link['url'] . '?raw=1');
      die();
    }

    $tmp_handle = fopen('php://output', "w");
    $preview = $this->client->getPreview($this->_requestedCompletePath, $tmp_handle);
    fclose($tmp_handle);

    die();
  }

  /*
   * Download file
   */

  function downloadFile() {

    if (isset($_REQUEST['media'])) {
      $this->redirecttoMediaFile();
      die();
    }

    //only allowed files
    $path_parts = OutoftheBox_mbPathinfo($this->_requestedCompletePath);
    if ((isset($path_parts['extension']) && !in_array(strtolower($path_parts['extension']), $this->options['ext'])) && $this->options['ext'][0] != '*') {
      die();
    }

    if (!$this->isEntryAuthorized($this->_requestedCompletePath)) {
      die();
    }

    //Get folder meta data
    $entry = $this->client->getMetadata($this->_requestedCompletePath);

    //No valid folder, return to root
    if ($entry !== null) {

      //Get Link
      $link = $this->_mediaFromCache($entry['path'], 'files');
      $link = $link['cache'];

      /* Send email if needed */
      if ($this->options['notificationdownload'] === '1') {
        $this->sendNotificationEmail('download', array($entry));
      }

      if ($this->options['forcedownload'] === '1' || ((isset($_REQUEST['dl'])) && ($_REQUEST['dl'] == 1))) {
        header('Location: ' . $link['url'] . '?dl=1');
      } else if ((isset($_REQUEST['raw'])) && ($_REQUEST['raw'] == 1)) {
        //Do some magic with the url to make it possible getting the raw format
        $link['url'] = str_replace('dl.dropboxusercontent.com/1/view/', 'dropbox.com/s/', $link['url']);
        header('Location: ' . $link['url'] . '?raw=1');
      } else {
        header('Location: ' . $link['url']);
      }

      /* Download Hook */
      do_action('outofthebox_download', $entry, $link['url']);
    }

    die();
  }

  public function redirecttoMediaFile() {
    if (isset($_REQUEST['media']) && isset($_REQUEST['token'])) {
      $cachename = $_REQUEST['media'];
      $token = $_REQUEST['token'];
      $this->_readCache('close');


      if ((isset($this->cache['media'][$cachename])) && (($this->cache['media'][$cachename]['expires']) > time()) && (isset($this->cache['media'][$cachename]['tokens']))) {

        if (isset($this->cache['media'][$cachename]['tokens'][$token]) && ($this->cache['media'][$cachename]['tokens'][$token] > time())) {
          header('Location: ' . $this->cache['media'][$cachename]['url'] . '?dl=1');

          /* Send email if needed */
          if ($this->options['notificationdownload'] === '1') {
            $entry = array(
              'path' => urldecode($this->cache['media'][$cachename]['url']),
              'size' => '?'
            );
            $this->sendNotificationEmail('download', array($entry));
          }
        } elseif (isset($this->cache['media'][$cachename]['tokens'][$token]) && $this->cache['media'][$cachename]['tokens'][$token] <= time()) {
          $this->_lockCache(LOCK_EX);
          unset($this->cache['media'][$cachename]['tokens'][$token]);
          $this->_setCache();
        } else {
          header('Location: ' . $this->cache['media'][$cachename]['url'] . '?dl=1');
        }
      } elseif ((($this->cache['media'][$cachename]['expires']) <= time()) || (isset($this->cache['media'][$cachename]['tokens']))) {
        header('Location: ' . $this->cache['media'][$cachename]['url'] . '?dl=1');
        $this->_lockCache(LOCK_EX);
        unset($this->cache['media'][$cachename]);
        $this->_setCache();
      }
    }

    die();
  }

  /*
   * Create zipfile
   */

  public function createZip() {
//Check if requested path is allowed
    if (!$this->isEntryAuthorized($this->_requestedCompletePath)) {
//Gotcha, this path isn't allowed
      return new WP_Error('broke', __("Requested directory isn't allowed", 'outofthebox'));
    }

    $zip_filename = '_zip_' . basename($this->_requestedCompletePath) . '_' . uniqid() . '.zip';

    $json_options = 0;
    if (defined('JSON_PRETTY_PRINT')) {
      $json_options |= JSON_PRETTY_PRINT;  // Supported in PHP 5.4+
    }

    if (isset($_REQUEST['files'])) {
      $dirlisting = array('folders' => array(), 'files' => array(), 'bytes' => 0, 'bytes_total' => 0);

      foreach ($_REQUEST['files'] as $file) {
        $entry = str_replace('//', '/', $this->_requestedCompletePath . '/' . rawurldecode($file));
        $data = $this->_getRecursiveFiles($entry, '', true);
        $dirlisting['files'] = array_merge($dirlisting['files'], $data['files']);
        $dirlisting['folders'] = array_merge($dirlisting['folders'], $data['folders']);
        $dirlisting['bytes_total'] += $data['bytes_total'];
      }
    } else {
      $dirlisting = $this->_getRecursiveFiles($this->_requestedCompletePath);
    }

    if (count($dirlisting['folders']) > 0 || count($dirlisting['files']) > 0) {

      /* Create zip file */
      if (!function_exists('PHPZip\autoload')) {
        try {
          require_once "PHPZip/autoload.php";
        } catch (Exception $ex) {
          return new WP_Error('broke', __('Something went wrong... See settings page', 'letsbox'));
        }
      }
      $zip = new PHPZip\Zip\Stream\ZipStream($zip_filename);

      /* Add folders */
      if (count($dirlisting['folders']) > 0) {

        foreach ($dirlisting['folders'] as $key => $folder) {
          $zip->addDirectory($folder);
          unset($dirlisting['folders'][$key]);
        }
      }

      /* Add files */
      if (count($dirlisting['files']) > 0) {

        $downloadedfiles = array();

        foreach ($dirlisting['files'] as $key => $file) {
          @set_time_limit(60);

          /* get file */
          ob_flush();
          $stream = fopen('php://memory', 'r+');
          $metadata = $this->client->getFile($this->_requestedCompletePath . '/' . $file['path'], $stream);
          rewind($stream);

          try {
            $zip->addLargeFile($stream, $file['path']);
          } catch (Exception $ex) {
            
          }

          fclose($stream);

          $dirlisting['bytes'] += $file['metadata']['bytes'];
          unset($dirlisting['files'][$key]);

          $downloadedfiles[] = $metadata;
        }
      }

      /* Close zip */
      $result = $zip->finalize();

      /* Send email if needed */
      if ($this->options['notificationdownload'] === '1') {
        $this->sendNotificationEmail('download', $downloadedfiles);
      }

      die();
    } else {
      die('No files or folders selected');
    }
  }

  private function _getRecursiveFiles($path, $currentpath = '', $selection = false, &$dirlisting = array('folders' => array(), 'files' => array(), 'bytes' => 0, 'bytes_total' => 0)) {
    //Get folder meta data
    $entry = $this->client->getMetadataWithChildren($path);

    if ($entry !== null) {

      //First add Current Folder/File
      if ($selection) {
        $path_parts = OutoftheBox_mbPathinfo($entry['path']);

        $continue = true;
        //Only add allowed files to array
        if ($entry['is_dir'] === false && (isset($path_parts['extension']) && !in_array(strtolower($path_parts['extension']), $this->options['ext'])) && $this->options['ext'][0] != '*') {
          $continue = false;
        }

        if (!$this->isEntryAuthorized($entry['path'])) {
          $continue = false;
        }

        if ($continue) {
          $location = $path_parts['basename'];

          if ($entry['is_dir'] === false) {
            $dirlisting['files'][] = array('path' => $location, 'metadata' => $entry);
            $dirlisting['bytes_total'] += $entry['bytes'];
          } else {
            $dirlisting['folders'][] = $location;
            $currentpath = $location;
          }
        }
      }

      //If Folder add all children
      if (isset($entry['contents']) && count($entry['contents']) > 0) {

        foreach ($entry['contents'] as $child) {

          $path_parts = OutoftheBox_mbPathinfo($child['path']);

          //Only add allowed files to array
          if ($child['is_dir'] === false && (isset($path_parts['extension']) && !in_array(strtolower($path_parts['extension']), $this->options['ext'])) && $this->options['ext'][0] != '*') {
            continue;
          }

          if (!$this->isEntryAuthorized($child['path'])) {
            continue;
          }

          $location = ($currentpath === '') ? $path_parts['basename'] : $currentpath . '/' . $path_parts['basename'];

          if ($child['is_dir'] === false) {
            $dirlisting['files'][] = array('path' => $location, 'metadata' => $entry);
            $dirlisting['bytes_total'] += $child['bytes'];
          } else {
            $dirlisting['folders'][] = $location;
            $this->_getRecursiveFiles($child['path'], $location, false, $dirlisting);
          }
        }
      }
    }

    return $dirlisting;
  }

  public function createLink($requested_entry = false) {
    $link = false;
    $error = false;

    if ($requested_entry === false) {
      $requested_entry = $this->_requestedCompletePath;
    }

    if ($this->isEntryAuthorized($this->_requestedPath)) {

      $pathError = dbx\Path::findError($requested_entry);
      if ($pathError === null) {
        $entry = $this->client->getMetadata($requested_entry);

        if ($entry !== false) {
          $this->_readCache('close');
          $cachename = sha1($entry['path']);

          if (!isset($this->cache['files'])) {
            $this->cache['files'] = array();
          }

          if (!empty($this->cache['files'][$cachename]['url'])) {
            $link = $this->cache['files'][$cachename];
          } else {
            $url = $this->client->createShareableLink($requested_entry);
            $url = str_replace('?dl=0', '', $url);
            $this->_lockCache(LOCK_EX);
            $this->_readCache();
            $this->cache['files'][$cachename] = array('url' => $url, 'shortened' => 0);
            $link = $this->cache['files'][$cachename];
            $this->_setCache();
          }
        }
      }
    }

    if (!$link) {
      $error = __("Can't create link", 'outofthebox');
    }

    if (!isset($link['shortened'])) {
      $link['shortened'] = 0;
    }

    //If Short url is requested
    if (($this->settings['shortlinks'] === 'Bitly') && $link['shortened'] === 0) {
      require_once 'bitly/bitly.php';

      try {
        $this->bitly = new Bitly($this->settings['bitly_login'], $this->settings['bitly_apikey']);

        $response = $this->bitly->shorten($link['url'] . '?dl=1');

        if ($response) {
          $url = $response['url'];
          $this->_lockCache(LOCK_EX);
          $this->_readCache();
          $this->cache['files'][$cachename] = array('url' => $url, 'shortened' => 1);
          $link = $this->cache['files'][$cachename];
          $this->_setCache();
        }
      } catch (Exception $ex) {
        
      }
    } else {
      $link['url'] .= '?dl=1';
    }

    $embedlink = 'https://docs.google.com/viewer?embedded=true&url=' . rawurlencode($link['url']);

    $path_parts = OutoftheBox_mbPathinfo($entry['path']);
    $resultdata = array(
      'name' => $path_parts['filename'],
      'extension' => ((isset($path_parts['extension'])) ? '.' . $path_parts['extension'] : ''),
      'link' => $link['url'],
      'embeddedlink' => $embedlink,
      'size' => $entry['size'],
      'error' => $error
    );

    return $resultdata;
  }

  public function createLinks() {
    $links = array('links' => array());

    foreach ($_REQUEST['entries'] as $entry) {
      $links['links'][] = $this->createLink($this->_requestedPath . '/' . rawurldecode($entry));
    }

    return $links;
  }

  /*
   * Check if $entry is allowed
   */

  public function isEntryAuthorized($entry) {
    if (strtolower($entry) === strtolower($this->_rootFolder)) {
      return true;
    }

    $_path = str_ireplace($this->_rootFolder . '/', '', $entry);
    $_path = strtolower($_path);
    $subs = array_filter(explode('/', $_path));

    if ($this->options['exclude'][0] != '*') {
      if (count($subs) === 1) {
        $found = false;

        foreach ($subs as $sub) {
          if (in_array($sub, $this->options['exclude'])) {
            $found = true;
            continue;
          }
        }
        if ($found) {
          return false;
        }
      } elseif (count($subs) > 1) {
        $found = false;

        foreach ($subs as $sub) {
          if (in_array($sub, $this->options['exclude'])) {
            $found = true;
            continue;
          }
        }
        if ($found) {
          return false;
        }
      }
    }

    /* only allow included folders and files */

    if ($this->options['include'][0] != '*') {
      if (count($subs) === 1) {
        $found = false;

        foreach ($subs as $sub) {
          if (in_array($sub, $this->options['include'])) {
            $found = true;
            continue;
          }
        }
        if (!$found) {
          return false;
        }
      } elseif (count($subs) > 1) {
        $found = false;

        foreach ($subs as $sub) {
          if (in_array($sub, $this->options['include'])) {
            $found = true;
            continue;
          }
        }
        if (!$found) {
          return false;
        }
      }
    }
    //if ($this->options['include'][0] != '*') {
    //  foreach ($this->options['include'] as $includedentry) {
    //    if (stripos($entry, '/' . $includedentry) === false) {
    //      return false;
    //    }
    //  }
    //}
    return true;
  }

  /*
   * Check if $extensions array has $entry
   */

  public function isExtensionAuthorized($entry, $extensions, $prefix = '.') {
    if ($extensions[0] != '*') {

      $pathinfo = OutoftheBox_mbPathinfo($entry);
      if (!isset($pathinfo['extension'])) {
        return true;
      }

      foreach ($extensions as $allowedextensions) {
        if (stripos($entry, $prefix . $allowedextensions) !== false) {
          return true;
        }
      }
    } else {
      return true;
    }
    return false;
  }

  protected function _imageFromCache($entry, $folderthumb = false) {
    $h = ($folderthumb === false) ? $this->options['targetheight'] * 1.5 : $this->options['targetheight'] * 1.1;
    $c = ($folderthumb === false) ? ($this->options['crop'] === '1') : true;
    $c = ($c === true) ? '1' : '0';
    $f = 'jpeg';

    $cachename = sha1($entry['path'] . $entry['modified']);

    if (empty($this->cache)) {
      $this->_readCache('close');
    }

    if (!isset($this->cache['images'])) {
      $this->cache['images'] = array();
    }

    if (!isset($this->cache['images'][$cachename])) {
      return $this->loadImageThumb($entry, $cachename, $h, $c, $f);
    }

    if (!isset($this->cache['images'][$cachename][$h . '_' . $c])) {
      return $this->loadImageThumb($entry, $cachename, $h, $c, $f);
    }

    if (($this->cache['images'][$cachename]['modified'] !== strtotime($entry['modified']))) {
      unset($this->cache['images'][$cachename]);
      return $this->loadImageThumb($entry, $cachename, $h, $c, $f);
    }

    if ((!file_exists(OUTOFTHEBOX_CACHEDIR . $this->cache['images'][$cachename][$h . '_' . $c]['thumb']))) {
      unset($this->cache['images'][$cachename][$h . '_' . $c]);
      return $this->loadImageThumb($entry, $cachename, $h, $c, $f);
    }

    return array('image' => $this->cache['images'][$cachename], 'cache' => $this->cache['images'][$cachename][$h . '_' . $c]);
  }

  public function loadImageThumb($entry, $cachename, $h, $c, $f) {

    if ((isset($this->cache['images'][$cachename][$h . '_' . $c]) && ($this->cache['images'][$cachename][$h . '_' . $c]['height'] === $h))) {
      return $this->cache['images'][$cachename][$h . '_' . $c];
    } else {
      $this->_lockCache(LOCK_EX);
      $this->_readCache();
      $this->cache['images'][$cachename]['modified'] = strtotime($entry['modified']);
      if (!isset($this->cache['images'][$cachename]['entry'])) {
        $this->cache['images'][$cachename]['entry'] = $entry['path'];
      }
      if (!isset($this->cache['images'][$cachename]['url'])) {
        $this->cache['images'][$cachename]['url'] = '';
      }

      $this->cache['images'][$cachename][$h . '_' . $c] = array(
        'load_thumb' => '',
        'create_thumb_url' => admin_url('admin-ajax.php') . '?action=outofthebox-thumbnail&src=' . $cachename . '&i=' . $h . '_' . $c,
        'thumb' => '',
        'width' => $h,
        'height' => $h,
        'crop' => $c);

      $this->_setCache();

      return array('image' => $this->cache['images'][$cachename], 'cache' => $this->cache['images'][$cachename][$h . '_' . $c]);
    }
  }

  public function createThumb() {

    $authorized = false;

    //Creating images can take a while
    @set_time_limit(15);

    if ((!empty($_REQUEST['src'])) && (!empty($_REQUEST['i']))) {
      $cachename = $_REQUEST['src'];
      $index = $_REQUEST['i'];

      $this->_readCache('close');

      if (!class_exists('phpthumb')) {
        try {
          require_once('phpThumb/phpthumb.class.php');
        } catch (Exception $ex) {
          die();
        }
      }

      if (empty($this->cache['images'])) {
        die();
      }

      if (!empty($this->cache['images'][$cachename][$index]['thumb'])) {
        if (file_exists(OUTOFTHEBOX_CACHEDIR . $this->cache['images'][$cachename][$index]['thumb'])) {
          header('Location: ' . OUTOFTHEBOX_CACHEURL . $this->cache['images'][$cachename][$index]['thumb']);
          die();
        }
      }

      //Create direct URL if needed
      try {
        $filecachename = sha1($this->cache['images'][$cachename]['entry']);
        if (empty($this->cache['files'][$filecachename]['url'])) {
          if ($this->_isAuthorized()) {
            $authorized = true;
            $link = $this->client->createShareableLink($this->cache['images'][$cachename]['entry']);
            $link = str_replace('?dl=0', '', $link);
            if ($link) {
              $this->_lockCache(LOCK_EX);
              $this->_readCache();
              $this->cache['images'][$cachename]['url'] = $link;
              $this->cache['files'][$filecachename] = array('url' => $link, 'shortened' => 0);
              $this->_setCache();
            } else {
              return false;
            }
          } else {
            return false;
          }
        }

        $imagedata = false;
        if ($this->settings['thumbnails'] === 'Out-of-the-Box' && (ini_get('allow_url_fopen'))) {
          $imagedata = file_get_contents($this->cache['images'][$cachename]['url'] . '?dl=1');
        }

        if ($this->settings['thumbnails'] === 'Dropbox' || $imagedata === false) {

          if (!$authorized) {
            if ($this->_isAuthorized()) {
              
            } else {
              return false;
            }
          }

          $sizes = array('l' => array('width' => '640', 'height' => '480'), 'm' => array('width' => '128', 'height' => '128'), 's' => array('width' => '64', 'height' => '64'), 'sx' => array('width' => '32', 'height' => '32'));
          $thumbsize = null;
          foreach ($sizes as $size => $dimension) {
            $thumbnail = $this->client->getThumbnail($this->cache['images'][$cachename]['entry'], 'jpeg', $size);

            if ($thumbnail !== null) {
              $imagedata = $thumbnail[1];
              $thumbsize = $dimension;
              break;
            }
          }
        }

        if ($imagedata === false) {
          die(__('Cannot get image'));
        }

        // create phpThumb object
        $phpThumb = new phpThumb();
        $phpThumb->setParameter('config_temp_directory', OUTOFTHEBOX_CACHEDIR);
        $phpThumb->setParameter('config_cache_directory', OUTOFTHEBOX_CACHEDIR);
        $phpThumb->setSourceData($imagedata);
        $phpThumb->setParameter('config_output_format', 'jpeg');

        //First create best thumbnail for gallery
        $phpThumb->setParameter('q', 92);
        if ($this->cache['images'][$cachename][$index]['crop'] === '1') {
          $phpThumb->setParameter('zc', 1);
          $phpThumb->setParameter('w', $this->cache['images'][$cachename][$index]['height']);
        }
        $phpThumb->setParameter('h', $this->cache['images'][$cachename][$index]['height']);

        if ($phpThumb->GenerateThumbnail() && ($this->settings['thumbnails'] === 'Out-of-the-Box')) {
          $phpThumb->CalculateThumbnailDimensions();
          $phpThumb->SetCacheFilename();
          $phpThumb->RenderToFile($phpThumb->cache_filename);

          $_cachefile_location = $phpThumb->cache_filename;
          $_locations = (explode(DIRECTORY_SEPARATOR, $_cachefile_location));

          $this->_lockCache(LOCK_EX);
          $this->_readCache();
          $this->cache['images'][$cachename][$index]['thumb'] = end($_locations);
          $this->cache['images'][$cachename][$index]['width'] = $phpThumb->thumbnail_width;
          $this->cache['images'][$cachename][$index]['height'] = $phpThumb->thumbnail_height;
          $this->_setCache();
        } else {

          if ($imagedata === null) {
            return false;
          }

          $file = file_put_contents(OUTOFTHEBOX_CACHEDIR . $cachename . '.jpg', $imagedata);

          //Try to get the correct size
          $thumbnail = wp_get_image_editor(OUTOFTHEBOX_CACHEDIR . $cachename . '.jpg');
          $thumb_file = $cachename . '.jpg';

          if (!is_wp_error($thumbnail)) {
            $thumbsize = $thumbnail->get_size();

            if (($thumbsize['width'] !== $thumbsize['height']) && $c === true) {
              $thumbnail->resize($thumbsize['height'], $thumbsize['height'], true);
              $thumbnail->save(OUTOFTHEBOX_CACHEDIR . $cachename . '.jpg', 'image/jpeg');
              $thumbnail->save(OUTOFTHEBOX_CACHEDIR . $cachename . '-thumb.jpg', 'image/jpeg');
              $thumb_file = $cachename . '-thumb.jpg';
            } else {
              $thumbnail->resize(NULL, $thumbsize['height'], false);
              $thumbnail->save(OUTOFTHEBOX_CACHEDIR . $cachename . '-thumb.jpg', 'image/jpeg');
              $thumb_file = $cachename . '-thumb.jpg';
            }
          }

          $this->_lockCache(LOCK_EX);
          $this->_readCache();

          $this->cache['images'][$cachename][$index]['crop'] = '1';
          $this->cache['images'][$cachename][$index]['thumb'] = $cachename . '.jpg';
          $this->cache['images'][$cachename][$index]['load_thumb'] = $thumb_file;
          $this->cache['images'][$cachename][$index]['width'] = $thumbsize['width'];
          $this->cache['images'][$cachename][$index]['height'] = $thumbsize['height'];

          $this->_setCache();

          header('Location: ' . OUTOFTHEBOX_CACHEURL . $cachename . '.jpg');
          die();
        }

        $phpThumb->resetObject();
        //Create cropped thumbnail for first run
        $phpThumb = new phpThumb();
        $phpThumb->setParameter('config_temp_directory', OUTOFTHEBOX_CACHEDIR);
        $phpThumb->setParameter('config_cache_directory', OUTOFTHEBOX_CACHEDIR);
        $phpThumb->setSourceData($imagedata);
        $phpThumb->setParameter('config_output_format', 'jpeg');

        $phpThumb->setParameter('q', 92);
        $phpThumb->setParameter('zc', 1);
        $phpThumb->setParameter('h', $this->cache['images'][$cachename][$index]['height']);
        $phpThumb->setParameter('w', $this->cache['images'][$cachename][$index]['height']);


        if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
          $phpThumb->SetCacheFilename();
          $phpThumb->RenderToFile($phpThumb->cache_filename);

          $_cachefile_location = $phpThumb->cache_filename;
          $_locations = (explode(DIRECTORY_SEPARATOR, $_cachefile_location));

          $this->_lockCache(LOCK_EX);
          $this->_readCache();
          $this->cache['images'][$cachename][$index]['load_thumb'] = end($_locations);
          $this->_setCache();

          header('Location: ' . OUTOFTHEBOX_CACHEURL . end($_locations));
          die();
        } else {
          die();
        }
      } catch (Exception $e) {
        die();
      }
    } else {
      die();
    }
  }

  protected function _mediaFromCache($entry, $type = 'media') {

    if (!isset($this->cache[$type])) {
      $this->cache[$type] = array();
    }

    $cachename = sha1($entry);

    if ((!isset($this->cache[$type][$cachename])) || (!isset($this->cache[$type][$cachename]['expires'])) || (($this->cache[$type][$cachename]['expires']) < time()) || (!isset($this->cache[$type][$cachename]['tokens']))) {
      list($link, $expires) = $this->client->createTemporaryDirectLink($entry);

      if ($link !== null) {

        $expires->setTimezone(new DateTimeZone(date_default_timezone_get()));
        $expiresstr = $expires->getTimestamp();

        $this->_lockCache(LOCK_EX);
        $this->_readCache();
        $this->cache[$type][$cachename] = array(
          'url' => $link,
          'expires' => $expiresstr,
          'tokens' => array()
        );
        $this->_setCache();
      }
    }

    if ($type === 'media') {
      $this->_lockCache(LOCK_EX);
      $this->_readCache();
      //Remove old media tokens
      foreach ($this->cache[$type][$cachename]['tokens'] as $token => $expires) {
        if ($expires < time()) {
          unset($this->cache[$type][$cachename]['tokens'][$token]);
        }
      }

      //Create new media token for 15 minutes
      $this->cache[$type][$cachename]['tokens'][uniqid(rand())] = time() + 15 * 60;
      $this->_setCache();
    }


    return array('cachename' => $cachename, 'cache' => $this->cache[$type][$cachename]);
  }

  protected function _setCache() {
    $json_options = 0;
    if (defined('JSON_PRETTY_PRINT')) {
      $json_options |= JSON_PRETTY_PRINT;  // Supported in PHP 5.4+
    }

    $data = json_encode($this->cache, $json_options);
    ftruncate($this->_cache_handle, 0);
    rewind($this->_cache_handle);
    $result = fwrite($this->_cache_handle, $data);

    $this->_unlockCache();
    return true;
  }

  protected function _lockCache($type) {
    /*  Check if file exists */

    if (!file_exists(OUTOFTHEBOX_CACHEDIR . 'index')) {
      @file_put_contents(OUTOFTHEBOX_CACHEDIR . 'index', json_encode(array()));

      if (!is_writable(OUTOFTHEBOX_CACHEDIR . 'index')) {
        die('Cache file / directory not writable');
      }
    }
    /* Check if the file is more than 1 minute old. */
    $requires_unlock = ((filemtime(OUTOFTHEBOX_CACHEDIR . 'index') + 60) < (time()));

    /* Check if file is already opened and locked in this process */
    if (empty($this->_cache_handle)) {
      $this->_cache_handle = fopen(OUTOFTHEBOX_CACHEDIR . 'index', 'c+');
    }

    @set_time_limit(60);
    if (!flock($this->_cache_handle, $type | LOCK_NB)) {
      // if the file cannot be unlocked and the last time it was modified was 1 minute, assume that the previous process died and unlock the file manually
      if ($requires_unlock) {
        $this->_unlockCache();
      }
      //Try to lock the file again
      $result = flock($this->_cache_handle, $type);
    }
    @set_time_limit(60);

    return true;
  }

  protected function _unlockCache() {
    if (!empty($this->_cache_handle)) {
      flock($this->_cache_handle, LOCK_UN);
      fclose($this->_cache_handle);
      $this->_cache_handle = null;
    }

    $this->_cleanCache();
    clearstatcache();
    return true;
  }

  protected function _readCache($close = false) {

    if (empty($this->_cache_handle)) {
      $this->_lockCache(LOCK_SH);
    }

    clearstatcache();
    rewind($this->_cache_handle);

    $data = fread($this->_cache_handle, filesize(OUTOFTHEBOX_CACHEDIR . 'index'));
    if ($close !== false) {
      $this->_unlockCache();
    }
    $cache = json_decode($data, true);

    if ($cache === null) {
      $cache = array();
    }

    $this->cache = $cache;

    return $this->cache;
  }

  /*
   * Clean Cache
   */

  private function _cleanCache() {
    $cachefiles = scandir(OUTOFTHEBOX_CACHEDIR);

    $cachefiles = array_filter($cachefiles, function($value) {
      return (strpos($value, '.tmp') !== false || (strpos($value, 'pThumb') !== false ));
    });

    foreach ($cachefiles as $cachefile) {
      @unlink(OUTOFTHEBOX_CACHEDIR . '/' . $cachefile);
    }
  }

}
