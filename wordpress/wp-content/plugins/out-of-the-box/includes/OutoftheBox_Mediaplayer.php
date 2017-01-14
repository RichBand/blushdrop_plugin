<?php

require_once 'OutoftheBox_Dropbox.php';

class OutoftheBox_Mediaplayer extends OutoftheBox_Dropbox {

  public function getMediaList() {

    $this->_folder = $this->getFolder();

    if (($this->_folder !== false)) {
      //Create Gallery array
      $this->mediaarray = $this->createMediaArray();

      if (count($this->mediaarray) > 0) {
        echo json_encode($this->mediaarray);
      }
    }

    die();
  }

  public function createMediaArray() {
    $playlist = array();

    //Create Filelist array
    if (count($this->_folder['contents']) > 0) {

      $this->_folder['contents'] = $this->sortFilelist($this->_folder['contents']);

      $files = array();
      foreach ($this->_folder['contents'] as $child) {

        //Skip entry if its a file, and we dont want to show files
        if (($child['is_dir'] === true)) {
          continue;
        }

        $path = $child['path'];
        $path_parts = OutoftheBox_mbPathinfo($path);

        if (isset($path_parts['extension'])) {
          $path_parts['extension'] = strtolower($path_parts['extension']);
        }

        //Only add allowed files to array
        if (!isset($path_parts['extension']) || (!in_array($path_parts['extension'], $this->options['media_extensions']))) {
          continue;
        }

        if (!$this->isEntryAuthorized($path)) {
          continue;
        }

        $allowedextensions = array('mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga');
        if (empty($path_parts['extension']) || !in_array($path_parts['extension'], $allowedextensions)) {
          continue;
        }


        // combine same files with different extensions
        if (!isset($files[$path_parts['filename']])) {

          $files[$path_parts['filename']] = array(
            'title' => $path_parts['filename'],
            'name' => $path_parts['filename'],
            'artist' => '',
            'path' => $path,
            'file' => $path_parts['dirname'] . '/' . $path_parts['filename'],
            'poster' => OUTOFTHEBOX_ROOTPATH . '/css/images/play.png',
            'extensions' => array(),
            'download' => false,
            'linktoshop' => ($this->options['linktoshop'] !== '') ? $this->options['linktoshop'] : false
          );
        }

        array_push($files[$path_parts['filename']]['extensions'], strtolower($path_parts['extension']));
      }

      foreach ($files as $file) {

        $song = $file;

        foreach ($file['extensions'] as $song_extension) {
          $media = $this->_mediaFromCache($file['path']);
          end($media['cache']['tokens']);
          $mediatoken = key($media['cache']['tokens']);


          if ($media !== null) {
            //Can play mp4 but need to give m4v or m4a
            if ($song_extension === 'mp4') {
              $song_extension = ($this->options['mode'] === 'audio') ? 'm4a' : 'm4v';
            }
            if ($song_extension === 'ogg') {
              $song_extension = ($this->options['mode'] === 'audio') ? 'oga' : 'ogv';
            }

            $song[$song_extension] = admin_url('admin-ajax.php') . '?action=outofthebox-download&listtoken=' . $this->listtoken . '&media=' . $media['cachename'] . '&token=' . $mediatoken;
            if ($this->options['linktomedia'] === '1') {
              $song['download'] = $song[$song_extension];
            }
          }
        }

        unset($song['path']);
        array_push($playlist, $song);
      }
    }

    return $playlist;
  }

}
