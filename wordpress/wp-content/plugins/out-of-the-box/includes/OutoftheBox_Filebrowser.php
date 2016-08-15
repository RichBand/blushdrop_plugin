<?php

require_once 'OutoftheBox_Dropbox.php';

class OutoftheBox_Filebrowser extends OutoftheBox_Dropbox {

  private $_search = false;

  public function getFilesList() {

    $this->_folder = $this->getFolder();

    if (($this->_folder !== false)) {
      $this->filesarray = $this->createFilesArray();

      $this->renderFilelist();
    }
  }

  public function searchFiles() {
    $this->_search = true;
    $input = mb_strtolower($_REQUEST['query'], 'UTF-8');
    $this->_folder = array();
    $this->_folder['contents'] = $this->searchByName($input);

    if (($this->_folder !== false)) {
      $this->filesarray = $this->createFilesArray();

      $this->renderFilelist();
    }
  }

  public function renderFilelist() {

    /* Create HTML Filelist */
    $filelist_html = "";

    if (count($this->filesarray) > 0) {
      $hasfilesorfolders = false;

      foreach ($this->filesarray as $item) {
        /* Render folder div */
        if ($item['is_dir']) {
          $filelist_html .= $this->renderDir($item);


          if ($item['parentfolder'] === false) {
            $hasfilesorfolders = true;
          }
        }
      }
    }

    $filelist_html .= $this->renderNewFolder();

    if (count($this->filesarray) > 0) {
      foreach ($this->filesarray as $item) {
        /* Render files div */
        if (!$item['is_dir']) {
          $filelist_html .= $this->renderFile($item);
          $hasfilesorfolders = true;
        }
      }

      if ($hasfilesorfolders === false) {
        if ($this->options['show_files'] === '1') {
          $filelist_html .= $this->renderNoResults();
        }
      }
    } else {
      if ($this->options['show_files'] === '1' || $this->_search === true) {
        $filelist_html .= $this->renderNoResults();
      }
    }

    /* Create HTML Filelist title */
    $spacer = ' &raquo; ';

    $breadcrumbelements = array_filter(explode('/', $this->_requestedPath));

    $location = '';
    foreach ($breadcrumbelements as &$element) {
      $location .= '/' . $element;
      $class = 'folder';
      if (basename($this->_requestedPath) == $element) {
        $class .= ' current_folder';
      }
      $element = "<a href='javascript:void(0)' class='" . $class . "' data-url='" . rawurlencode($location) . "'>" . $element . "</a>";
    }

    if (($this->options['show_root'] === '1') && ($this->_rootFolder != '/')) {
      $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . ltrim($this->_rootFolder, '/') . "</a>";
      array_unshift($breadcrumbelements, $startelement);
    } else {
      if ($this->_userFolder !== false) {
        $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . $this->_userFolder . "</a>";
      } else {
        $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . $this->options['root_text'] . "</a>";
      }

      array_unshift($breadcrumbelements, $startelement);
    }

    $filepath = implode($spacer, $breadcrumbelements);

    $raw_path = '';
    if ((current_user_can('edit_posts') || current_user_can('edit_pages')) && get_user_option('rich_editing') == 'true') {
      $raw_path = $this->_requestedCompletePath;
    }


    if ($this->_search === true) {
      $expires = 0;
    } else {
      $expires = time() + 60 * 5;
    }

    echo json_encode(array(
      'lastpath' => rawurlencode($this->_lastPath),
      'rawpath' => $raw_path,
      'breadcrumb' => $filepath,
      'html' => $filelist_html,
      'expires' => $expires));

    die();
  }

  public function renderNoResults() {

    $html = '<div class="entry folder">
<div class="entry_icon">
<img src="' . OUTOFTHEBOX_ROOTPATH . '/css/clouds/cloud_status_16.png" ></div>
<div class="entry_name"><a class="entry_link">' . __('No files or folders found', 'outofthebox') . '</a></div></div>
';

    return $html;
  }

  public function renderDir($item) {
    $return = '';
    $classmoveable = (($this->options['move'] === '1' && $this->checkUserRole($this->options['move_role']))) ? 'moveable' : '';
    $style = $item['style'];

    $return .= "<div class='entry folder $classmoveable $style' data-url='" . rawurlencode($item['path']) . "' data-name='" . $item['basename'] . "'>\n";
    $return .= "<div class='entry_icon' data-url='" . rawurlencode($item['path']) . "'><img src='" . $item['icon'] . "'/></div>\n";

    if ($item['parentfolder'] === false) {

      if ($this->options['mcepopup'] === 'linkto') {
        $return .= "<div class='entry_linkto'>\n";
        $return .= "<span>" . "<input class='button-secondary' type='submit' title='" . __('Select folder', 'outofthebox') . "' value='" . __('Select folder', 'outofthebox') . "'>" . '</span>';
        $return .= "</div>";
      }

      if ((($this->options['can_download_zip'] === '1') && ($this->checkUserRole($this->options['download_role']))) ||
              (($this->options['delete'] === '1') && ($this->checkUserRole($this->options['deletefiles_role']) || $this->checkUserRole($this->options['deletefolders_role'])))) {
        $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item['basename']) . "'/></div>";
      }

      if ($this->options['mcepopup'] === 'links') {
        $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item['basename']) . "'/></div>";
      }

      $return .= "<div class='entry_edit'>";
      $return .= $this->renderEditItem($item);
      $return .= "</div>";

      $return .= "<div class='entry_name'><a class='entry_link'>" . $item['basename'] . "</a></div>";
    } else {
      $return .= "<div class='entry_name'><a class='entry_link'>" . $item['name'] . "</a></div>";
    }

    $return .= "</div>\n";
    return $return;
  }

  public function renderFile($item) {
    $return = '';
    $classmoveable = (($this->options['move'] === '1' && $this->checkUserRole($this->options['move_role']))) ? 'moveable' : '';

    $return .= "<div class='entry file $classmoveable' data-url='" . rawurlencode($item['path']) . "' data-name='" . $item['name'] . "'>\n";
    $return .= "<div class='entry_icon'><img src='" . $item['icon'] . "'/></div>";

    $link = $this->renderFileNameLink($item);
    $title = $link['filename'] . ((($this->options['show_filesize'] === '1') && ($item['size'] > 0)) ? ' (' . OutoftheBox_bytesToSize1024($item['size']) . ')' : '&nbsp;');

    if ((($this->options['can_download_zip'] === '1') && ($this->checkUserRole($this->options['download_role']))) ||
            (($this->options['delete'] === '1') && ($this->checkUserRole($this->options['deletefiles_role']) || $this->checkUserRole($this->options['deletefolders_role'])))) {
      $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item['basename']) . "'/></div>";
    }

    if (in_array($this->options['mcepopup'], array('links', 'embedded'))) {
      $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item['basename']) . "'/></div>";
    }

    $return .= "<div class='entry_edit_placheholder'><div class='entry_edit'>";
    $return .= $this->renderEditItem($item);
    $return .= "</div></div>";

    $return .= "<a " . $link['url'] . " " . $link['target'] . " class='" . $link['class'] . "' title='$title' " . $link['lightbox'] . " " . $link['onclick'] . " data-filename='" . $link['filename'] . "'>";

    if ($this->options['show_filesize'] === '1') {
      $return .= "<div class='entry_size'>" . $item['size'] . "</div>";
    }

    if ($this->options['show_filedate'] === '1') {
      $edited = date_i18n(get_option('date_format') . ' H:s', strtotime($item['edited']));
      $return .= "<div class='entry_lastedit'>" . $edited . "</div>";
    }


    $return .= "<div class='entry_name'>" . $link['filename'];

    if ($this->_search === true) {
      $return .= "<div class='entry_foundpath'>" . $item['path'] . "</div>";
    }

    $return .= "</div>";
    $return .= "</a>";
    $return .= "</div>\n";

    return $return;
  }

  public function renderFileNameLink($item) {
    $class = '';
    $url = '';
    $target = '';
    $onclick = '';
    $lightbox = '';
    $datatype = 'iframe';

    /* Check if user is allowed to download file */
    if (($this->options['mcepopup'] === '0') && ($this->checkUserRole($this->options['download_role']))) {
      if ($this->options['forcedownload'] === '1') {
        $url = admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;
        $class = 'entry_action_download';
      } else {

        $class = 'entry_action_download';
        $url = admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;

        if ($this->options['previewinline'] === '0' || ($this->mobile)) {
          $onclick = "sendGooglePageView('Preview (new window)', '" . $item['name'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
          $class = 'entry_action_external_view';
          $target = "_blank";
          if ($item['openwithdropbox']) {
            $onclick = "sendGooglePageView('Preview', '" . $item['name'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
            $url = admin_url('admin-ajax.php') . "?action=outofthebox-preview&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;
          }
          if (in_array($item['extension'], array('pdf'))) {
            $url .= "&raw=1";
          }
        } else {

          if (in_array($item['extension'], array('jpg', 'jpeg', 'gif', 'png'))) {
            $class = 'entry_link ilightbox-group';
            $onclick = "sendGooglePageView('Preview', '" . $item['name'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
            $url = $item['url']; //"&dl=1";
            $datatype = 'image';
          } else if ($item['openwithdropbox']) {
            $class = 'entry_link ilightbox-group';
            $onclick = "sendGooglePageView('Preview', '" . $item['name'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
            $url = admin_url('admin-ajax.php') . "?action=outofthebox-preview&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;
            $onclick = "sendGooglePageView('Preview', '" . $item['basename'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
          } else if (in_array($item['extension'], array('pdf'))) {
            $class = 'entry_link ilightbox-group';
            $onclick = "sendGooglePageView('Preview', '" . $item['name'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
            $url .= "&raw=1";
          }
        }
      }
    }

    $filename = $item['name'];
    $filename .= (($this->options['show_ext'] === '1' && !empty($item['extension'])) ? '.' . $item['extension'] : '');

    if (!empty($url)) {
      $url = "href='" . $url . "'";
    };
    if (!empty($target)) {
      $target = "target='" . $target . "'";
    };
    if (!empty($onclick)) {
      $onclick = 'onclick="' . $onclick . '"';
    };

    /* Lightbox Settings */
    if (strpos($class, 'ilightbox-group') !== false) {
      $lightbox = "rel='ilightbox[" . $this->listtoken . "]' ";
      if ($datatype === 'image') {
        $lightbox .= 'data-type="image"';
        $lightbox .= ' data-options="thumbnail: \'' . $item['thumb'] . '\'"';
      } else {
        $lightbox .= 'data-type="iframe"';
        $lightbox .= ' data-options="mousewheel: false, width: \'85%\', height: \'80%\', thumbnail: \'' . str_replace('32x32', '128x128', $item['icon']) . '\'"';
      }
    }


    return array('filename' => $filename, 'class' => $class, 'url' => $url, 'lightbox' => $lightbox, 'target' => $target, 'onclick' => $onclick);
  }

  public function renderEditItem($item) {
    $html = '';

    $usercandownload = $this->checkUserRole($this->options['download_role']);
    
    if ($item['is_dir']) {
      $usercanrename = ($this->checkUserRole($this->options['renamefolders_role']));
      $usercandelete = ($this->checkUserRole($this->options['deletefolders_role']));
    } else {
      $usercanrename = ($this->checkUserRole($this->options['renamefiles_role']));
      $usercandelete = ($this->checkUserRole($this->options['deletefiles_role']));
    }

    $filename = $item['name'];
    $filename .= (($this->options['show_ext'] === '1' && !empty($item['extension'])) ? '.' . $item['extension'] : '');

    /* View */
    if ($this->options['forcedownload'] !== '1' && (!$item['is_dir'])) {

      if (in_array($item['extension'], array('pdf', 'jpg', 'jpeg', 'gif', 'png')) && $this->options['previewinline'] === '1') {
        $html .= "<li><a class='entry_action_view' title='" . __('Preview', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview', 'outofthebox') . "</a></li>";
      } else if ($item['openwithdropbox']) {
        $previewurl = admin_url('admin-ajax.php') . "?action=outofthebox-preview&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;
        $onclick = "sendGooglePageView('Preview', '" . $item['basename'] . ((!empty($item['extension'])) ? '.' . $item['extension'] : '') . "');";
        $html .= "<li><a class='entry_action_view' title='" . __('Preview', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview', 'outofthebox') . "</a></li>";
        $html .= "<li><a href='$previewurl' target='_blank' class='entry_action_external_view' onclick=\"$onclick\" title='" . __('Preview (new window)', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview (new window)', 'outofthebox') . "</a></li>";
      }
    }

    /* Download */
    if ((!$item['is_dir']) && ($usercandownload)){
      $html .= "<li><a href='" . admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken . "&dl=1' data-filename='" . $filename . "' class='entry_action_download' title='" . __('Download file', 'outofthebox') . "'><i class='fa fa-cloud-download fa-lg'></i>&nbsp;" . __('Download file', 'outofthebox') . "</a></li>";
    }

    /* Shortlink */
    if (!$item['is_dir']) {
      if (($this->options['show_sharelink'] === '1') && ($this->checkUserRole($this->options['download_role']))) {
        $html .= "<li><a class='entry_action_shortlink' title='" . __('Sharing link', 'outofthebox') . "'><i class='fa fa-group fa-lg'></i>&nbsp;" . __('Sharing link', 'outofthebox') . "</a></li>";
      }
    }

    /* Rename */
    if (($this->options['rename'] === '1') && ($usercanrename)) {
      $html .= "<li><a class='entry_action_rename' title='" . __('Rename', 'outofthebox') . "'><i class='fa fa-tag fa-lg'></i>&nbsp;" . __('Rename', 'outofthebox') . "</a></li>";
    }

    /* Delete */
    if (($this->options['delete'] === '1') && ($usercandelete)) {
      $html .= "<li><a class='entry_action_delete' title='" . __('Delete', 'outofthebox') . "'><i class='fa fa-times-circle fa-lg'></i>&nbsp;" . __('Delete', 'outofthebox') . "</a></li>";
    }

    if ($html !== '') {
      return "<a class='entry_edit_menu'><i class='fa fa-chevron-circle-down fa-lg'></i></a><div id='menu-" . $item['id'] . "' class='oftb-dropdown-menu'><ul data-path='" . rawurlencode($item['path']) . "' data-name='" . $item['basename'] . "'>" . $html . "</ul></div>\n";
    }

    return $html;
  }

  public function renderNewFolder() {
    $html = '';
    if (($this->_search === false) && ($this->options['addfolder'] === '1')) {
      $user_can_add_folder = $this->checkUserRole($this->options['addfolder_role']);

      if ($user_can_add_folder) {
        $html .= "<div class='entry folder newfolder'>";
        $html .= "<div class='entry_icon'><img src='" . OUTOFTHEBOX_ROOTPATH . "/css/icons/32x32/folder-new.png'/></div>";
        $html .= "<div class='entry_name'>" . __('Add folder', 'outofthebox') . "</div>";
        $html .= "<div class='entry_description'>" . __('Add a new folder in this directory', 'outofthebox') . "</div>";
        $html .= "</div>";
      }
    }
    return $html;
  }

  public function createFilesArray() {
    $filesarray = array();

// Add 'back to Previous folder' if needed

    if (($this->_search === false) && (strtolower($this->_folder['path']) !== strtolower($this->_rootFolder))) {
      $foldername = basename($this->_folder['path']);
      $location = str_replace('\\', '/', (dirname($this->_requestedPath)));
      array_push($filesarray, array(
        'id' => md5($location),
        'name' => ' <strong>' . __('Previous folder', 'outofthebox') . '</strong>',
        'basename' => $foldername,
        'path' => $location,
        'icon' => OUTOFTHEBOX_ROOTPATH . '/css/icons/32x32/folder-grey.png',
        'is_dir' => true,
        'openwithdropbox' => false,
        'parentfolder' => true,
        'style' => 'previous'
      ));
    }

//Add folders and files to filelist
    if (count($this->_folder['contents']) > 0) {

      foreach ($this->_folder['contents'] as $child) {

//Skip entry if its a file, and we dont want to show files
        if (($child['is_dir'] === false) && ($this->options['show_files'] === '0')) {
          continue;
        }
//Skip entry if its a folder, and we dont want to show folders
        if (($child['is_dir'] === true) && ($this->options['show_folders'] === '0')) {
          continue;
        }

        $path = $child['path'];

        $path_parts = OutoftheBox_mbPathinfo($path);

        if ((!$child['is_dir']) && isset($path_parts['extension'])) {
          include_once 'mime-types/mime-types.php';
          $extension = $path_parts['extension'];
          $mimetype = getMimeType(strtolower($path_parts['extension']));
        }

//Only add allowed files to array
        if ($child['is_dir'] === false && (isset($path_parts['extension']) && !in_array(strtolower($path_parts['extension']), $this->options['ext'])) && $this->options['ext'][0] != '*') {
          continue;
        }

        if (!$this->isEntryAuthorized($path)) {
          continue;
        }

        if ($this->_search === true) {
          if ($this->_rootFolder !== '/') {
            $pathreg = str_replace('/', '\/', $this->_rootFolder);
            $location = preg_replace('/' . $pathreg . '/i', '', $path_parts['dirname'], 1);
          } else {
            $location = $path_parts['dirname'];
          }
          $location = $location . '/' . $path_parts['basename'];
        } else {
          $location = ($this->_lastPath == '/') ? '/' . $path_parts['basename'] : $this->_lastPath . '/' . $path_parts['basename'];
        }

        $extension = (isset($path_parts['extension'])) ? strtolower($path_parts['extension']) : '';

        /* Can File be previewed via Dropbox? 
         * https://www.dropbox.com/developers/core/docs#thumbnails
         */
        $previewsupport = array('doc', 'docx', 'docm', 'ppt', 'pps', 'ppsx', 'ppsm', 'pptx', 'pptm', 'xls', 'xlsx', 'xlsm', 'rtf');
        $openwithdropbox = (in_array($extension, $previewsupport));
        //
        //add files with thumbnails
        $image = false;
        $thumb = false;
        $url = false;
        if ($child['thumb_exists'] === true) {
          $image = $this->_imageFromCache($child);
          $thumb = (!empty($image['cache']['thumb'])) ? OUTOFTHEBOX_CACHEURL . $image['cache']['thumb'] : $image['cache']['create_thumb_url'];
          $url = (empty($image['image']['url'])) ? $image['cache']['create_thumb_url'] : $image['image']['url'] . '?dl=1';
        }

        $thumbnailicon = ($child['is_dir']) ? 'folder.png' : $this->fileIcon($mimetype);

        array_push($filesarray, array(
          'id' => md5($location),
          'name' => $path_parts['filename'],
          'basename' => $path_parts['basename'],
          'extension' => $extension,
          'originalpath' => $child['path'],
          'path' => $location,
          'icon' => OUTOFTHEBOX_ROOTPATH . '/css/icons/32x32/' . $thumbnailicon,
          'is_dir' => $child['is_dir'],
          'size' => $child['size'],
          'edited' => (isset($child['client_mtime']) && (strtotime($child['client_mtime']) > strtotime($child['modified']))) ? $child['client_mtime'] : $child['modified'],
          'openwithdropbox' => $openwithdropbox,
          'parentfolder' => false,
          'url' => $url,
          'thumb' => $thumb,
          'style' => ''
        ));
      }
    }

    return $filesarray;
  }

  public function fileIcon($mimetype, $iconsize = 'large') {

    $icon = 'unknown';

    if (strpos($mimetype, 'word') !== false) {
      $icon = 'application-msword';
    } else if (strpos($mimetype, 'excel') !== false || strpos($mimetype, 'spreadsheet') !== false) {
      $icon = 'application-vnd.ms-excel';
    } else if (strpos($mimetype, 'powerpoint') !== false || strpos($mimetype, 'presentation') !== false) {
      $icon = 'application-vnd.ms-powerpoint';
    } else if (strpos($mimetype, 'access') !== false || strpos($mimetype, 'mdb') !== false) {
      $icon = 'application-vnd.ms-access';
    } else if (strpos($mimetype, 'image') !== false) {
      $icon = 'image-x-generic';
    } else if (strpos($mimetype, 'audio') !== false) {
      $icon = 'audio-x-generic';
    } else if (strpos($mimetype, 'video') !== false) {
      $icon = 'video-x-generic';
    } else if (strpos($mimetype, 'pdf') !== false) {
      $icon = 'application-pdf';
    } else if (strpos($mimetype, 'zip') !== false ||
            strpos($mimetype, 'archive') !== false ||
            strpos($mimetype, 'tar') !== false ||
            strpos($mimetype, 'compressed') !== false
    ) {
      $icon = 'application-zip';
    } else if (strpos($mimetype, 'html') !== false) {
      $icon = 'text-xml';
    } else if (strpos($mimetype, 'application/exe') !== false ||
            strpos($mimetype, 'application/x-msdownload') !== false ||
            strpos($mimetype, 'application/x-exe') !== false ||
            strpos($mimetype, 'application/x-winexe') !== false ||
            strpos($mimetype, 'application/msdos-windows') !== false ||
            strpos($mimetype, 'application/x-executable') !== false
    ) {
      $icon = 'application-x-executable';
    } else if (strpos($mimetype, 'text') !== false) {
      $icon = 'text-x-generic';
    }

    return $icon . '.png';
  }

}
