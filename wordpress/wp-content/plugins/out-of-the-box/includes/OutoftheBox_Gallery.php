<?php

require_once 'OutoftheBox_Dropbox.php';

class OutoftheBox_Gallery extends OutoftheBox_Dropbox {

  private $_search = false;

  public function getImagesList() {
    $this->_folder = $this->getFolder();

    if (($this->_folder !== false)) {
      /* Create Gallery array */
      $this->imagesarray = $this->createImageArray();

      $this->renderImagesList();
    }
  }

  public function searchImageFiles() {
    $this->_search = true;
    $input = mb_strtolower($_REQUEST['query'], 'UTF-8');
    $this->_folder = array();
    $this->_folder['contents'] = $this->searchByName($input);

    if (($this->_folder !== false)) {
      /* Create Gallery array */
      $this->imagesarray = $this->createImageArray();

      $this->renderImagesList();
    }
  }

  public function renderImagesList() {

    /* Create HTML Filelist */
    $imageslist_html = "";

    if (count($this->imagesarray) > 0) {
      $imageslist_html = "<div class='images image-collage'>";
      foreach ($this->imagesarray as $item) {
        /* Render folder div */
        if ($item['is_dir']) {
          $imageslist_html .= $this->renderDir($item);
        }
      }
    }

    $imageslist_html .= $this->renderNewFolder();

    if (count($this->imagesarray) > 0) {
      $i = 0;
      foreach ($this->imagesarray as $item) {

        /* Render file div */
        if (!$item['is_dir']) {
          $hidden = (($this->options['maximages'] !== '0') && ($i >= $this->options['maximages']));
          $imageslist_html .= $this->renderFile($item, $hidden);
          $i++;
        }
      }

      $imageslist_html .= "</div>";
    } else {
      if ($this->_search === true) {
        $imageslist_html .= '<div class="no_results">' . __('No files or folders found', 'outofthebox') . '</div>';
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
      $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . $this->options['root_text'] . "</a>";
      array_unshift($breadcrumbelements, $startelement);
    }

    $filepath = implode($spacer, $breadcrumbelements);

    if ($this->_search === true) {
      $expires = 0;
    } else {
      $expires = time() + 60 * 5;
    }

    echo json_encode(array(
      'lastpath' => rawurlencode($this->_lastPath),
      'breadcrumb' => $filepath,
      'html' => $imageslist_html,
      'expires' => $expires));

    die();
  }

  public function renderDir($item) {
    $return = "";
    if ($item['parentfolder'] === true) {
      $return .= "<div class='image-container image-folder' data-url='" . rawurlencode($item['path']) . "' data-name='" . $item['basename'] . "'>";
    } else {
      $classmoveable = (($this->options['move'] === '1' && $this->checkUserRole($this->options['move_role']))) ? 'moveable' : '';
      $return .= "<div class='image-container image-folder entry $classmoveable' data-url='" . rawurlencode($item['path']) . "' data-name='" . $item['basename'] . "'>";

      $return .= "<div class='entry_edit'>";
      $return .= $this->renderEditItem($item);

      if (($this->options['can_download_zip'] === '1') && ($this->checkUserRole($this->options['download_role'])) ||
              ($this->options['delete'] === '1') && ($this->checkUserRole($this->options['deletefolders_role']))) {
        $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item['basename']) . "'/></div>";
      }
      $return .= "</div>";
    }
    $return .= "<a title='" . $item['name'] . "'>";
    $return .= "<img class='preloading image-folder-img' src='" . OUTOFTHEBOX_ROOTPATH . "/css/images/transparant.png' data-src='" . plugins_url('css/images/folder.png', dirname(__FILE__)) . "' width='" . $item['width'] . "' height='" . $item['height'] . "' style='width:" . $item['width'] . "px;height:" . $item['height'] . "px;'/>";

    if (count($item['folderimages']) > 0) {
      $size = $this->options['targetheight'];
      $number = 1;

      foreach (array_reverse($item['folderimages']) as $folderimage) {
        $img = (!empty($folderimage['thumb'])) ? OUTOFTHEBOX_CACHEURL . $folderimage['thumb'] : $folderimage['create_thumb_url'];
        $return .= "<div class='folder-thumb thumb$number' style='width:" . $size . "px;height:" . $size . "px;background-image: url(" . $img . ")'></div>";
        $number++;
      }
    }

    $return .= "<div class='folder-text'>" . $item['name'] . "</div></a>";

    $return .= "</div>\n";

    return $return;
  }

  public function renderFile($item, $hidden = false) {


    $hiddenclass = ($hidden) ? 'hidden' : '';
    $class = $hiddenclass;

    if ((!empty($_REQUEST['deeplink'])) && (md5($item['url']) === $_REQUEST['deeplink'])) {
      $class .= ' deeplink';
    }

    $classmoveable = (($this->options['move'] === '1' && $this->checkUserRole($this->options['move_role']))) ? 'moveable' : '';
    $return = "<div class='image-container $class entry $classmoveable' data-url='" . rawurlencode($item['path']) . "' data-name='" . $item['name'] . "'>";

    $return .= "<div class='entry_edit'>";
    $return .= $this->renderEditItem($item);

    if (($this->options['can_download_zip'] === '1') && ($this->checkUserRole($this->options['download_role'])) ||
            ($this->options['delete'] === '1') && ($this->checkUserRole($this->options['deletefiles_role']))) {
      $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item['basename']) . "'/></div>";
    }
    $return .= "</div>";

    $thumbnail = 'data-options="thumbnail: \'' . $item['thumb'] . '\'"';

    $class = 'ilightbox-group';
    $target = '';

    /* If previewinline attribute is set, open image in new window */
    if ($this->options['previewinline'] === '0') {
      /* Check if a direct link is already provide, if not download file */
      if (strpos($item['url'], 'outofthebox-thumbnail') !== false) {
        $item['url'] = admin_url('admin-ajax.php') . "?action=outofthebox-download&raw=1&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;
      } else {
        $item['url'] = str_replace('?dl=1', '?raw=1', $item['url']);
      }
      $class = '';
      $target = ' target="_blank" ';
    }

    $return .= "<a href='" . $item['url'] . "' title='" . $item['name'] . "' $target class='$class' data-type='image' data-caption='" . $item['name'] . "' $thumbnail rel='ilightbox[" . $this->listtoken . "]'><span class='image-rollover'></span>";
    $return .= "<img class='preloading $hiddenclass' src='" . OUTOFTHEBOX_ROOTPATH . "/css/images/transparant.png' data-src='" . $item['thumb'] . "' width='" . $item['width'] . "' height='" . $item['height'] . "' style='width:" . $item['width'] . "px;height:" . $item['height'] . "px;'/>";
    $return .= "</a>";

    $return .= "</div>\n";
    return $return;
  }

  public function renderEditItem($item) {
    $html = '';

    if ($item['is_dir']) {
      $usercanrename = ($this->checkUserRole($this->options['renamefolders_role']));
      $usercandelete = ($this->checkUserRole($this->options['deletefolders_role']));
    } else {
      $usercanrename = ($this->checkUserRole($this->options['renamefiles_role']));
      $usercandelete = ($this->checkUserRole($this->options['deletefiles_role']));
    }

    /* Download */
    if (!$item['is_dir']) {
      $html .= "<li><a href='" . admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item['path']) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken . "&dl=1' class='entry_action_download' title='" . __('Download file', 'outofthebox') . "'><i class='fa fa-cloud-download fa-lg'></i>&nbsp;" . __('Download file', 'outofthebox') . "</a></li>";
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
        $height = $this->options['targetheight'];
        $html .= "<div class='image-container image-folder image-add-folder grey newfolder'>";
        $html .= "<a title='" . __('Add folder', 'outofthebox') . "'><div class='folder-text'>" . __('Add folder', 'outofthebox') . "</div>";
        $html .= "<img class='preloading' src='" . OUTOFTHEBOX_ROOTPATH . "/css/images/transparant.png' data-src='" . plugins_url('css/images/addfolder.png', dirname(__FILE__)) . "' width='$height' height='$height' style='width:" . $height . "px;height:" . $height . "px;'/>";
        $html .= "</a>";
        $html .= "</div>\n";
      }
    }
    return $html;
  }

  public function createImageArray() {
    $imagearray = array();

    //Add folders and files to filelist


    if (count($this->_folder['contents']) > 0) {

      foreach ($this->_folder['contents'] as $child) {

        $path = $child['path'];
        $path_parts = OutoftheBox_mbPathinfo($path);

        //Only add allowed files to array
        if ($child['is_dir'] === false && (isset($path_parts['extension']) && !in_array(strtolower($path_parts['extension']), $this->options['ext'])) && $this->options['ext'] [0] != '*') {
          continue;
        }

        //Skip entry if its a folder, and we dont want to show folders
        if (($child['is_dir'] === true) && ($this->options['show_folders'] === '0')) {
          continue;
        }

        if (!$this->isEntryAuthorized($path)) {
          continue;
        }

        if ($child['is_dir'] === true) {
          //Read folder for possible images
          $foldercontents = $this->getFolderImages($path);
          $folderimages = array();

          $foldercontents = $this->removeElementWithValue($foldercontents, 'is_dir', true);
          $foldercontents = $this->removeElementWithValue($foldercontents, 'thumb_exists', false);

          if (count($foldercontents) > 0) {
            //Process only the first three
            $foldercontents = array_slice($foldercontents, 0, 3);

            foreach ($foldercontents as $folderimage) {
              $image = $this->_imageFromCache($folderimage, 'folderthumb');
              $folderimages[] = $image['cache'];
            }
          } else {
            //Remove empty folders from the list
            //continue;
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

          array_push($imagearray, array(
            'id' => md5($location),
            'name' => $path_parts['filename'],
            'basename' => $path_parts['basename'],
            'path' => $location,
            'is_dir' => true,
            'thumb' => plugins_url('css/images/folder.png', dirname(__FILE__)),
            'width' => $this->options['targetheight'],
            'height' => $this->options['targetheight'],
            'folderimages' => $folderimages,
            'parentfolder' => false
          ));
          continue;
        }


        //add files with thumbnails
        if ($child['thumb_exists'] === true) {

          $image = $this->_imageFromCache($child);
          $location = ($this->_lastPath == '/') ? '/' . $path_parts['basename'] : $this->_lastPath . '/' . $path_parts['basename'];

          array_push($imagearray, array(
            'id' => md5($location),
            'name' => $path_parts['filename'],
            'basename' => $path_parts['basename'],
            'path' => $location,
            'is_dir' => false,
            'url' => (empty($image['image']['url'])) ? $image['cache']['create_thumb_url'] : $image['image']['url'] . '?dl=1',
            'thumb' => (!empty($image['cache']['thumb'])) ? OUTOFTHEBOX_CACHEURL . $image['cache']['thumb'] : $image['cache']['create_thumb_url'],
            'width' => $image['cache']['width'],
            'height' => $image['cache']['height']
          ));
        }
      }
    }

    return $imagearray;
  }

}
