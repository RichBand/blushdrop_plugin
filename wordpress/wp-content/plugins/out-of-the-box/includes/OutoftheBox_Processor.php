<?php

class OutoftheBox_Processor {

	public $options = array();
	protected $lists = array();
	protected $listtoken = '';
	protected $_requestedFile;
	protected $_requestedDir;
	protected $_requestedPath;
	protected $_requestedCompletePath;
	protected $_lastPath = '/';
	protected $_userFolder = false;
	public $mobile = false;
	protected $_loadscripts = array('general' => false, 'files' => false, 'upload' => false, 'mediaplayer' => false, 'qtip' => false);

	/**
	 * Construct the plugin object
	 */
	public function __construct() {
		$this->settings = get_option('out_of_the_box_settings');
		$this->lists = get_option('out_of_the_box_lists', array());

		if (isset($_REQUEST['mobile']) && ($_REQUEST['mobile'] === 'true')) {
			$this->mobile = true;
		}
	}

	public function startProcess() {
		if (isset($_REQUEST['action'])) {

			$authorized = $this->_IsAuthorized();

			if (($authorized === true) && ($_REQUEST['action'] === 'outofthebox-revoke')) {
				$this->revokeToken();
				die();
			}

			if ((!isset($_REQUEST['listtoken']))) {
				die();
			}

			$this->listtoken = $_REQUEST['listtoken'];
			if (!isset($this->lists[$this->listtoken])) {
				die();
			}

			$this->options = $this->lists[$this->listtoken];

			/* Set rootFolder */
			if ($this->options['user_upload_folders'] === 'manual') {
				$userfolder = get_user_option('out_of_the_box_linkedto');
				if (is_array($userfolder) && isset($userfolder['foldertext'])) {
					$this->_rootFolder = $userfolder['folderid'];
				} else {
					$defaultuserfolder = get_site_option('out_of_the_box_guestlinkedto');
					if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
						$this->_rootFolder = $defaultuserfolder['folderid'];
					} else {
						die();
					}
				}
			} else if (($this->options['user_upload_folders'] === 'auto') && !$this->checkUserRole($this->options['view_user_folders_role'])) {
				$this->_rootFolder = $this->createUserFolder();
			} else if (($this->options['user_upload_folders'] === 'auto')) {
				$this->_rootFolder = str_replace('/%user_folder%', '', $this->options['root']);
			} else {
				$this->_rootFolder = $this->options['root'];
			}

			$this->_rootFolder = html_entity_decode($this->_rootFolder);
			$this->_rootFolder = str_replace('//', '/', $this->_rootFolder);

			if (!$this->checkUserRole($this->options['view_role'])) {
				die();
			}

			if (isset($_REQUEST['lastpath'])) {
				$this->_lastPath = rawurldecode($_REQUEST['lastpath']);
			}

			if (isset($_REQUEST['OutoftheBoxpath']) && $_REQUEST['OutoftheBoxpath'] != '') {
				$a = rawurldecode($_REQUEST['OutoftheBoxpath']);
				$this->_setRequestedPath($a);
			} else {
				$this->_setRequestedPath();
			}

			switch ($_REQUEST['action']) {
				case 'outofthebox-get-filelist':
					if (is_wp_error($authorized)) {
// No valid token is set
						echo json_encode(array('lastpath' => $this->_lastPath, 'path' => '', 'folder' => '', 'html' => ''));
						die();
					}

					if (isset($_REQUEST['query']) && $this->options['search'] === '1') { // Search files
						$filelist = $this->searchFiles();
					} else {
						$filelist = $this->getFilesList(); // Read folder
					}

					break;

				case 'outofthebox-preview':
					$preview = $this->getPreview();
					break;

				case 'outofthebox-download':
				case 'outofthebox-create-zip':
				case 'outofthebox-create-link':
				case 'outofthebox-embedded':
					if (!$this->checkUserRole($this->options['download_role'])) {
						die();
					}

					if (is_wp_error($authorized)) {
						die();
					}

					if ($_REQUEST['action'] === 'outofthebox-download') {
						$file = $this->downloadFile();
					} elseif ($_REQUEST['action'] === 'outofthebox-create-zip') {
						$file = $this->createZip();
					} else {
						if (isset($_REQUEST['entries'])) {
							$links = $this->createLinks();
							echo json_encode($links);
						} else {
							$link = $this->createLink();
							echo json_encode($link);
						}

						die();
					}

					break;
				case 'outofthebox-get-gallery':
					if (is_wp_error($authorized)) {
// No valid token is set
						echo json_encode(array('lastpath' => $this->_lastPath, 'folder' => '', 'html' => ''));
						die();
					}

					if (isset($_REQUEST['query']) && $this->options['search'] === '1') { // Search files
						$imagelist = $this->searchImageFiles();
					} else {
						$imagelist = $this->getImagesList(); // Read folder
					}

					break;

				case 'outofthebox-upload-file':
					$user_can_upload = false;
					if ($this->options['upload'] === '1') {
						if ($this->checkUserRole($this->options['upload_role'])) {
							$user_can_upload = true;
						}
					}

					if (is_wp_error($authorized) || $user_can_upload === false) {
						die();
					}

					switch ($_REQUEST['type']) {
						case 'do-upload':
							$upload = $this->uploadFile();
							break;
						case 'get-status':
							$status = $this->getUploadStatus();
							break;
					}

					die();
					break;

				case 'outofthebox-delete-entry':
				case 'outofthebox-delete-entries':
//Check if user is allowed to delete entry
					$user_can_delete = false;
					if ($this->options['delete'] === '1') {
						if ($this->checkUserRole($this->options['deletefiles_role']) || $this->checkUserRole($this->options['deletefolders_role'])) {
							$user_can_delete = true;
						}
					}

					if (is_wp_error($authorized) || $user_can_delete === false) {
						echo json_encode(array('result' => '-1', 'msg' => __('Failed to delete entry', 'outofthebox')));
						die();
					}

					if ($_REQUEST['action'] === 'outofthebox-delete-entries') {
						$entries = $this->deleteEntries();

						foreach ($entries as $entry) {
							if (is_wp_error($entry)) {
								echo json_encode(array('result' => '-1', 'msg' => __('Not all entries could be deleted', 'outofthebox')));
								die();
							}
						}
						echo json_encode(array('result' => '1', 'msg' => __('Entry was deleted', 'outofthebox')));
					} else {
						$file = $this->deleteEntry();
						if (is_wp_error($file)) {
							echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
						} else {
							echo json_encode(array('result' => '1', 'msg' => __('Entry was deleted', 'outofthebox')));
						}
					}
					die();
					break;

				case 'outofthebox-rename-entry':
					/* Check if user is allowed to rename entry */
					$user_can_rename = false;
					if ($this->options['rename'] === '1') {
						if ($this->checkUserRole($this->options['renamefiles_role']) || $this->checkUserRole($this->options['renamefolders_role'])) {
							$user_can_rename = true;
						}
					}

					if (is_wp_error($authorized) || $user_can_rename === false) {
						echo json_encode(array('result' => '-1', 'msg' => __('Failed to rename entry', 'outofthebox')));
						die();
					}

					/* Strip unsafe characters */
					$newname = rawurldecode($_REQUEST['newname']);
					$special_chars = array("?", "/", "\\", "<", ">", ":", "\"", "*");
					$newname = str_replace($special_chars, '', $newname);

					$file = $this->renameEntry($newname);

					if (is_wp_error($file)) {
						echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
					} else {
						echo json_encode(array('result' => '1', 'msg' => __('Entry was renamed', 'outofthebox')));
					}

					die();
					break;

				case 'outofthebox-move-entry':
					/* Check if user is allowed to move entry */
					$user_can_move = false;
					if ($this->options['move'] === '1') {
						if ($this->checkUserRole($this->options['movefiles_role'])) {
							$user_can_move = true;
						}
					}

					if (is_wp_error($authorized) || $user_can_move === false) {
						echo json_encode(array('result' => '-1', 'msg' => __('Failed to move entry', 'outofthebox')));
						die();
					}


					$file = $this->moveEntry(rawurldecode($this->_rootFolder . $_REQUEST['target']));

					if (is_wp_error($file)) {
						echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
					} else {
						echo json_encode(array('result' => '1', 'msg' => __('Entry was moved', 'outofthebox')));
					}

					die();
					break;


				case 'outofthebox-add-folder':

//Check if user is allowed to add folder
					$user_can_addfolder = false;
					if ($this->options['addfolder'] === '1') {
						if ($this->checkUserRole($this->options['addfolder_role'])) {
							$user_can_addfolder = true;
						}
					}

					if (is_wp_error($authorized) || $user_can_addfolder === false) {
						echo json_encode(array('result' => '-1', 'msg' => __('Failed to add folder', 'outofthebox')));
						die();
					}

//Strip unsafe characters
					$newfolder = rawurldecode($_REQUEST['newfolder']);
					$special_chars = array("?", "/", "\\", "<", ">", ":", "\"", "*");
					$newfolder = str_replace($special_chars, '', $newfolder);

					$file = $this->addFolder($newfolder);

					if (is_wp_error($file)) {
						echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
					} else {
						echo json_encode(array('result' => '1', 'msg' => __('Folder', 'outofthebox') . ' ' . $newfolder . ' ' . __('was added', 'outofthebox'), 'lastpath' => $this->_lastPath,));
					}
					die();
					break;

				case 'outofthebox-get-playlist':
					if (is_wp_error($authorized)) {
						die();
					}

					$playlist = $this->getMediaList();

					break;

				default:
					die();
			}
		} else {
			die();
		}
	}

	public function createFromShortcode($atts) {


		$atts = (is_string($atts)) ? array() : $atts;
		$atts = $this->removeDeprecatedOptions($atts);

		//Create a unique identifier
		$this->listtoken = md5(OUTOFTHEBOX_VERSION . serialize($atts));

//Read shortcode
		extract(shortcode_atts(array(
			'dir' => '/',
			'startpath' => false,
			'mode' => 'files',
			'userfolders' => '0',
			'usertemplatedir' => '',
			'viewuserfoldersrole' => 'administrator',
			'ext' => '*',
			'showfiles' => '1',
			'showfolders' => '1',
			'filesize' => '1',
			'filedate' => '1',
			'showcolumnnames' => '1',
			'showext' => '1',
			'showroot' => '0',
			'sortfield' => 'name',
			'sortorder' => 'asc',
			'showbreadcrumb' => '1',
			'candownloadzip' => '0',
			'showsharelink' => '0',
			'showrefreshbutton' => '1',
			'roottext' => __('Start', 'outofthebox'),
			'search' => '1',
			'searchfrom' => 'parent',
			'include' => '*',
			'exclude' => '*',
			'maxwidth' => '100%',
			'maxheight' => '',
			'viewrole' => 'administrator|editor|author|contributor|subscriber|pending|guest',
			'downloadrole' => 'administrator|editor|author|contributor|subscriber|pending|guest',
			'previewinline' => '1',
			'forcedownload' => '0',
			'maximages' => '25',
			'crop' => '0',
			'quality' => '90',
			'slideshow' => '0',
			'pausetime' => '5000',
			'targetheight' => '150',
			'mediaextensions' => '',
			'autoplay' => '0',
			'hideplaylist' => '0',
			'linktomedia' => '0',
			'linktoshop' => '',
			'notificationupload' => '0',
			'notificationdownload' => '0',
			'notificationdeletion' => '0',
			'notificationemail' => '%admin_email%',
			'upload' => '0',
			'overwrite' => '0',
			'uploadext' => '.',
			'uploadrole' => 'administrator|editor|author|contributor|subscriber',
			'maxfilesize' => '0',
			'delete' => '0',
			'deletefilesrole' => 'administrator|editor',
			'deletefoldersrole' => 'administrator|editor',
			'rename' => '0',
			'renamefilesrole' => 'administrator|editor',
			'renamefoldersrole' => 'administrator|editor',
			'move' => '0',
			'moverole' => 'administrator|editor',
			'addfolder' => '0',
			'addfolderrole' => 'administrator|editor',
			'mcepopup' => '0',
			'debug' => '0',
			'demo' => '0'
		), $atts));

		if (!isset($this->lists[$this->listtoken])) {

			$authorized = $this->_isAuthorized();

			if (is_wp_error($authorized)) {
				if ($debug === '1') {
					return "<div id='message' class='error'><p>" . $authorized->get_error_message() . "</p></div>";
				}
				return "";
			}

			$this->lists[$this->listtoken] = array();

//Set Session Data
			switch ($mode) {
				case 'audio':
				case 'video':
					$mediaextensions = explode('|', $mediaextensions);
					break;
				case 'gallery':
					$ext = ($ext == '*') ? 'gif|jpg|jpeg|png|bmp' : $ext;
					$uploadext = ($uploadext == '.') ? 'gif|jpg|jpeg|png|bmp' : $uploadext;
				default:
					$mediaextensions = '';
					break;
			}

			//Force $candownloadzip = 0 if we can't use ZipArchive
			if (!class_exists('ZipArchive')) {
				$candownloadzip = '0';
			}

			$dir = rtrim($dir, "/");
			$dir = ($dir == '') ? '/' : $dir;
			if (substr($dir, 0, 1) !== '/') {
				$dir = '/' . $dir;
			}

			// Explode roles
			$viewrole = explode('|', $viewrole);
			$downloadrole = explode('|', $downloadrole);
			$uploadrole = explode('|', $uploadrole);
			$deletefilesrole = explode('|', $deletefilesrole);
			$deletefoldersrole = explode('|', $deletefoldersrole);
			$renamefilesrole = explode('|', $renamefilesrole);
			$renamefoldersrole = explode('|', $renamefoldersrole);
			$moverole = explode('|', $moverole);
			$addfolderrole = explode('|', $addfolderrole);
			$viewuserfoldersrole = explode('|', $viewuserfoldersrole);

			$this->options = array(
				'root' => htmlspecialchars_decode($dir),
				'startpath' => $startpath,
				'mode' => $mode,
				'user_upload_folders' => $userfolders,
				'user_template_dir' => htmlspecialchars_decode($usertemplatedir),
				'view_user_folders_role' => $viewuserfoldersrole,
				'media_extensions' => $mediaextensions,
				'autoplay' => $autoplay,
				'hideplaylist' => $hideplaylist,
				'linktomedia' => $linktomedia,
				'linktoshop' => $linktoshop,
				'ext' => explode('|', strtolower($ext)),
				'show_files' => $showfiles,
				'show_folders' => $showfolders,
				'show_filesize' => $filesize,
				'show_filedate' => $filedate,
				'show_columnnames' => $showcolumnnames,
				'show_ext' => $showext,
				'show_root' => $showroot,
				'sort_field' => $sortfield,
				'sort_order' => $sortorder,
				'show_breadcrumb' => $showbreadcrumb,
				'can_download_zip' => $candownloadzip,
				'show_sharelink' => $showsharelink,
				'show_refreshbutton' => $showrefreshbutton,
				'root_text' => $roottext,
				'search' => $search,
				'searchfrom' => $searchfrom,
				'include' => explode('|', strtolower(htmlspecialchars_decode($include))),
				'exclude' => explode('|', strtolower(htmlspecialchars_decode($exclude))),
				'maxwidth' => $maxwidth,
				'maxheight' => $maxheight,
				'view_role' => $viewrole,
				'download_role' => $downloadrole,
				'previewinline' => $previewinline,
				'forcedownload' => $forcedownload,
				'maximages' => $maximages,
				'notificationupload' => $notificationupload,
				'notificationdownload' => $notificationdownload,
				'notificationdeletion' => $notificationdeletion,
				'notificationemail' => $notificationemail,
				'upload' => $upload,
				'overwrite' => $overwrite,
				'upload_ext' => strtolower($uploadext),
				'upload_role' => $uploadrole,
				'maxfilesize' => $maxfilesize,
				'delete' => $delete,
				'deletefiles_role' => $deletefilesrole,
				'deletefolders_role' => $deletefoldersrole,
				'rename' => $rename,
				'renamefiles_role' => $renamefilesrole,
				'renamefolders_role' => $renamefoldersrole,
				'move' => $move,
				'move_role' => $moverole,
				'addfolder' => $addfolder,
				'addfolder_role' => $addfolderrole,
				'crop' => $crop,
				'quality' => $quality,
				'targetheight' => $targetheight,
				'slideshow' => $slideshow,
				'pausetime' => $pausetime,
				'mcepopup' => $mcepopup,
				'debug' => $debug,
				'demo' => $demo,
				'expire' => strtotime('+1 weeks'),
				'listtoken' => $this->listtoken);

			$this->updateLists();

			//Create userfolders if needed

			if (($this->options['user_upload_folders'] === 'auto')) {
				if ($this->settings['userfolder_onfirstvisit'] === 'Yes') {

					$allusers = array();
					$roles = array_diff($this->options['view_role'], $this->options['view_user_folders_role']);

					foreach ($roles as $role) {
						$users_query = new WP_User_Query(array(
							'fields' => 'all_with_meta',
							'role' => $role,
							'orderby' => 'display_name'
						));
						$results = $users_query->get_results();
						if ($results) {
							$allusers = array_merge($allusers, $results);
						}
					}

					foreach ($allusers as $user) {
						$requestedCompletePath = $this->createUserFolder($user);
						$this->_requestedCompletePath = str_replace('//', '/', $requestedCompletePath);
						//Creating folders can take a while, max 20 second per user
						set_time_limit(20);
						$this->addUserFolder();
					}
				}
			}
		} else {
			$this->options = $this->lists[$this->listtoken];
			$this->updateLists();
		}

		ob_start();
		$this->renderTemplate();

		return ob_get_clean();
	}

	public function renderTemplate() {

// Render the  template
		if ($this->checkUserRole($this->options['view_role'])) {


			$rootfolder = ''; //(($this->options['user_upload_folders'] !== '0') && !$this->checkUserRole($this->options['view_user_folders_role'])) ? '' : '';

			/* if ($this->options['user_upload_folders'] === 'manual') {
				$userfolder = get_user_option('out_of_the_box_linkedto');
				if (is_array($userfolder) && isset($userfolder['foldertext'])) {
				$rootfolder = $userfolder['folderid'];
				} else {
				$defaultuserfolder = get_site_option('out_of_the_box_guestlinkedto');
				if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
				$rootfolder = $defaultuserfolder['folderid'];
				} else {
				include(sprintf("%s/templates/noaccess.php", OUTOFTHEBOX_ROOTDIR));
				return;
				}
				}
				} */

			$rootfolder = ($this->options['startpath'] !== false) ? $this->options['startpath'] : $rootfolder;

			echo "<div id='OutoftheBox'>";
			echo "<noscript><div class='OutoftheBox-nojsmessage'>" . __('To view the Dropbox folders, you need to have JavaScript enabled in your browser', 'outofthebox') . ".<br/>";
			echo "<a href='http://www.enable-javascript.com/' target='_blank'>" . __('To do so, please follow these instructions', 'outofthebox') . "</a>.</div></noscript>";

			switch ($this->options['mode']) {
				case 'files':

					$this->loadScripts('files');

					echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox files oftb-list jsdisabled' data-list='files' data-token='$this->listtoken' data-path='" . rawurlencode($rootfolder) . "' data-org-path='" . rawurlencode($this->_lastPath) . "' data-sort='" . $this->options['sort_field'] . ":" . $this->options['sort_order'] . "' data-deeplink='" . ((!empty($_REQUEST['file'])) ? $_REQUEST['file'] : '') . "' data-layout='list'>";

					if ($this->options['mcepopup'] === 'shortcode') {
						echo "<div class='selected-folder'><strong>" . __('Selected folder', 'outofthebox') . ": </strong><span class='current-folder-raw'></span></div>";
					}

					include(sprintf("%s/templates/frontend.php", OUTOFTHEBOX_ROOTDIR));
					$this->renderUploadform();
					echo "</div>";
					break;

				case 'gallery':

					$this->loadScripts('files');

					$nextimages = '';
					if (($this->options['maximages'] !== '0')) {
						$nextimages = "data-loadimages='" . $this->options['maximages'] . "'";
					}

					echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox gridgallery jsdisabled' data-list='gallery' data-token='$this->listtoken' data-path='" . rawurlencode($this->_lastPath) . "' data-org-path='" . rawurlencode($this->_lastPath) . "' data-sort='" . $this->options['sort_field'] . ":" . $this->options['sort_order'] . "'  data-targetheight='" . $this->options['targetheight'] . "' data-deeplink='" . ((!empty($_REQUEST['image'])) ? $_REQUEST['image'] : '') . "' data-slideshow='" . $this->options['slideshow'] . "' data-pausetime='" . $this->options['pausetime'] . "' $nextimages>";
					include(sprintf("%s/templates/gallery.php", OUTOFTHEBOX_ROOTDIR));
					$this->renderUploadform();
					echo "</div>";
					break;

				case 'video':
				case 'audio':
					$skin = $this->settings['mediaplayer_skin'];
					$mp4key = array_search('mp4', $this->options['media_extensions']);
					if ($mp4key !== false) {
						unset($this->options['media_extensions'][$mp4key]);
						if ($this->options['mode'] === 'video') {
							if (!in_array('m4v', $this->options['media_extensions'])) {
								$this->options['media_extensions'][] = 'm4v';
							}
						} else {
							if (!in_array('m4a', $this->options['media_extensions'])) {
								$this->options['media_extensions'][] = 'm4a';
							}
						}
					}

					$oggkey = array_search('ogg', $this->options['media_extensions']);
					if ($oggkey !== false) {
						unset($this->options['media_extensions'][$oggkey]);
						if ($this->options['mode'] === 'video') {
							if (!in_array('ogv', $this->options['media_extensions'])) {
								$this->options['media_extensions'][] = 'ogv';
							}
						} else {
							if (!in_array('oga', $this->options['media_extensions'])) {
								$this->options['media_extensions'][] = 'oga';
							}
						}
					}

					$this->loadScripts('mediaplayer');

					$extensions = join(',', $this->options['media_extensions']);
					if ($extensions !== '') {
						echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox media " . $this->options['mode'] . " jsdisabled' data-list='media' data-token='$this->listtoken' data-extensions='" . $extensions . "' data-path='$this->_lastPath' data-sort='" . $this->options['sort_field'] . ":" . $this->options['sort_order'] . "' data-deeplink='' data-autoplay='" . $this->options['autoplay'] . "'>";
						include(sprintf("%s/skins/%s/player.php", OUTOFTHEBOX_ROOTDIR, $skin));
						echo "</div>";
					} else {
						echo '<strong>Out-of-the-Box:</strong>' . __('Please update your mediaplayer shortcode', 'outofthebox');
					}

					break;
			}
			echo "</div>";

			$this->loadScripts('general');
		}
	}

	public function renderUploadform() {
		$user_can_upload = false;
		if ($this->checkUserRole($this->options['upload_role'])) {
			$user_can_upload = true;
		}

		if ($this->options['upload'] === '1' && $user_can_upload) {
			$post_max_size_bytes = min(OutoftheBox_return_bytes(ini_get('post_max_size')), OutoftheBox_return_bytes(ini_get('upload_max_filesize')));
			$max_file_size = ($this->options['maxfilesize'] !== '0') ? $this->options['maxfilesize'] : $post_max_size_bytes;
			$post_max_size_str = OutoftheBox_bytesToSize1024($post_max_size_bytes);
			$acceptfiletypes = '.(' . $this->options['upload_ext'] . ')$';

			$this->loadScripts('upload');
			include(sprintf("%s/templates/uploadform.php", OUTOFTHEBOX_ROOTDIR));
		}
	}

	private function _setRequestedPath($path = '') {

		if ($path === '') {
			if ($this->_lastPath !== '') {
				$path = $this->_lastPath;
			} else {
				$path = '/';
			}
		}

		$special_chars = array("?", "\\", "=", "<", ">", ":", "\"", "*", "|");
		$path = str_replace($special_chars, '', $path);

		$path = rtrim($path, "/");
		if (($path !== '') && (substr($path, 0, 1) !== '/')) {
			$path = '/' . $path;
		}

		$path = str_replace(array('\\', '//'), '/', $path);

		$path_parts = OutoftheBox_mbPathinfo($path);

		$this->_requestedDir = '';
		$this->_requestedFile = '';

		if (isset($path_parts['extension'])) {
//it's a file
			$this->_requestedFile = $path_parts['basename'];
			$this->_requestedDir = str_replace('\\', '/', $path_parts['dirname']);
			$requestedDir = ($this->_requestedDir === '/') ? '/' : $this->_requestedDir . '/';
			$this->_requestedPath = $requestedDir . $this->_requestedFile;
		} else {
//it's a dir
			$this->_requestedDir = str_replace('\\', '/', $path);
			$this->_requestedFile = '';
			$this->_requestedPath = $this->_requestedDir;
		}

		$requestedCompletePath = $this->_rootFolder;
		if ($this->_rootFolder !== $this->_requestedPath) {
			$requestedCompletePath = html_entity_decode($this->_rootFolder . $this->_requestedPath);
		}

		$this->_requestedCompletePath = str_replace('//', '/', $requestedCompletePath);

//Create user folder if need and doesn't exists
		if (($this->options['user_upload_folders'] === 'auto') && !$this->checkUserRole($this->options['view_user_folders_role'])) {
			$this->addUserFolder();
		}
	}

	protected function loadScripts($template) {
		if ($this->_loadscripts[$template] === true) {
			return false;
		}

		switch ($template) {
			case 'general':
				wp_enqueue_script('OutoftheBox');
				break;
			case 'files':
				wp_enqueue_style('qtip');
				wp_enqueue_style('OutoftheBox-dialogs');
				wp_enqueue_script('json2');
				wp_enqueue_script('jquery-ui-mouse');

				if ((($this->options['delete'] === '1') && ($this->checkUserRole($this->options['deletefiles_role']) || $this->checkUserRole($this->options['deletefolders_role']))) ||
					(($this->options['addfolder'] === '1') && ($this->checkUserRole($this->options['addfolder_role']))) ||
					(($this->options['rename'] === '1') && ($this->checkUserRole($this->options['renamefiles_role']) || $this->checkUserRole($this->options['renamefolders_role']))) ||
					($this->options['show_sharelink'] === '1')) {
					wp_enqueue_script('jquery-ui-button');
					wp_enqueue_script('jquery-ui-position');
					wp_enqueue_script('jquery-ui-dialog');
				}

				if ($this->options['move'] === '1' && $this->checkUserRole($this->options['move_role'])) {
					wp_enqueue_script('jquery-ui-droppable');
					wp_enqueue_script('jquery-ui-draggable');
				}

				wp_enqueue_script('jquery-effects-core');
				wp_enqueue_script('jquery-effects-fade');
				wp_enqueue_script('collagePlus');
				wp_enqueue_script('removeWhitespace');
				wp_enqueue_style('ilightbox');
				wp_enqueue_style('ilightbox-skin');
				wp_enqueue_script('jquery.requestAnimationFrame');
				wp_enqueue_script('jquery.mousewheel');
				wp_enqueue_script('ilightbox');
				wp_enqueue_script('imagesloaded');
				wp_enqueue_script('qtip');
				break;
			case 'mediaplayer':
				wp_enqueue_script('imagesloaded');
				wp_enqueue_style('OutoftheBox.Media');
				wp_enqueue_script('jQuery.jplayer');
				wp_enqueue_script('jQuery.jplayer.playlist');
				wp_enqueue_script('OutoftheBox.Media');
				break;
			case 'upload':
				wp_enqueue_style('OutoftheBox-fileupload-jquery-ui');
				wp_enqueue_script('jquery-ui-droppable');
				wp_enqueue_script('jquery-ui-button');
				wp_enqueue_script('jquery-ui-progressbar');
				wp_enqueue_script('jQuery.iframe-transport');
				wp_enqueue_script('jQuery.fileupload');
				wp_enqueue_script('jQuery.fileupload-process');
				break;
		}

		$this->_loadscripts[$template] = true;
	}

	protected function setLastPath($path) {
		$this->_lastPath = $path;
		if ($this->_lastPath === '') {
			$this->_lastPath = '/';
		}
		$this->_setRequestedPath();
		return $this->_lastPath;
	}

	protected function removeDeprecatedOptions($options = array()) {
		/* Deprecated Shuffle */
		if (isset($options['shuffle'])) {
			unset($options['shuffle']);
			$options['sortfield'] = 'shuffle';
		}
		/* Changed Userfolders */
		if (isset($options['userfolders']) && $options['userfolders'] === '1') {
			$options['userfolders'] = 'auto';
		}

		if (isset($options['partiallastrow'])) {
			unset($options['partiallastrow']);
		}

		return $options;
	}

	protected function updateLists() {
		$this->lists[$this->listtoken] = $this->options;
		$this->_cleanLists();
		update_option('out_of_the_box_lists', $this->lists);
	}

	protected function sortFilelist($foldercontents) {
		if (count($foldercontents) > 0) {
// Sort Filelist, folders first
			$sort = array();

			$sort_field = 'path';
			$sort_order = SORT_ASC;

			if (isset($_REQUEST['sort'])) {
				$sort_options = explode(':', $_REQUEST['sort']);

				if ($sort_options[0] === 'shuffle') {
					shuffle($foldercontents);
					return $foldercontents;
				}

				if (count($sort_options) === 2) {

					switch ($sort_options[0]) {
						case 'name':
							$sort_field = 'path';
							break;
						case 'size':
							$sort_field = 'bytes';
							break;
						case 'modified':
							$sort_field = 'modified';
							break;
					}

					switch ($sort_options[1]) {
						case 'asc':
							$sort_order = SORT_ASC;
							break;
						case 'desc':
							$sort_order = SORT_DESC;
							break;
					}
				}
			}

			foreach ($foldercontents as $k => $v) {
				$sort['is_dir'][$k] = $v['is_dir'];

				if ($sort_field === 'modified') {
					if (isset($v['client_mtime']) && (strtotime($v['client_mtime']) > strtotime($v['modified']))) {
						$sort['sort'][$k] = strtotime($v['client_mtime']);
					} else {
						$sort['sort'][$k] = strtotime($v['modified']);
					}
				} else {
					$sort['sort'][$k] = strtolower($v[$sort_field]);
				}
			}

// Sort by dir desc and then by name asc
			if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
				@array_multisort($sort['is_dir'], SORT_DESC, SORT_REGULAR, $sort['sort'], $sort_order, SORT_NATURAL, $foldercontents, SORT_ASC, SORT_NATURAL);
			} else {
				array_multisort($sort['is_dir'], SORT_DESC, $sort['sort'], $sort_order, $foldercontents);
			}
		}
		return $foldercontents;
	}

	protected function createUserFolder($user = false) {
// Create unique user path
// Needed if $userfolders is set
		$userfoldername = '';

		if (is_user_logged_in() && $user === false) {
			$current_user = wp_get_current_user();

			$userfoldersname = strtr($this->settings['userfolder_name'], array(
				"%user_login%" => $current_user->user_login,
				"%user_email%" => $current_user->user_email,
				"%user_firstname%" => $current_user->user_firstname,
				"%user_lastname%" => $current_user->user_lastname,
				"%display_name%" => $current_user->display_name,
				"%ID%" => $current_user->ID,
			));

			$userfoldername = '/' . $userfoldersname;
			$this->_userFolder = $userfoldersname;
		} elseif ($user !== false) {

			$userfoldersname = strtr($this->settings['userfolder_name'], array(
				"%user_login%" => $user->user_login,
				"%user_email%" => $user->user_email,
				"%user_firstname%" => $user->user_firstname,
				"%user_lastname%" => $user->user_lastname,
				"%display_name%" => $user->display_name,
				"%ID%" => $user->ID,
			));

			$userfoldername = '/' . $userfoldersname;
			$this->_userFolder = $userfoldersname;
		} else {
			$userfolder = uniqid();
			if (!isset($_COOKIE['OftB-ID'])) {
				$expire = time() + 60 * 60 * 24 * 7;
				setcookie('OftB-ID', $userfolder, $expire, '/');
			} else {
				$userfolder = $_COOKIE['OftB-ID'];
			}

			$userhash = md5($userfolder);
			$userfoldername = '/' . __('Guests', 'outofthebox') . '/' . $userhash;
			$this->_userFolder = __('Guest', 'outofthebox');
		}


		if (strpos($this->options['root'], '%user_folder%') !== false) {
			return str_replace('%user_folder%', $userfoldername, $this->options['root']);
		} else {
			return $this->options['root'] . $userfoldername;
		}
	}

	public function userChangeFolder($listoptions, $userfoldername, $oldfoldername, $delete = false) {
		if ($this->_isAuthorized(true) === true) {
			if ($userfoldername !== '' && $oldfoldername !== '') {
				$this->updateUserFolder($listoptions, $userfoldername, $oldfoldername, $delete);
			}
		}
	}

	protected function sendNotificationEmail($emailtype = false, $entries = array()) {

		if ($emailtype === false) {
			return;
		}

		/* Get emailaddress */
		$recipients = strtr(trim($this->options['notificationemail']), array(
			"%admin_email%" => get_site_option('admin_email')
		));

		/* Current site url */
		$currenturl = $_SERVER['HTTP_REFERER'];

		/* Vistor name and email */
		$visitor = __('A guest', 'outofthebox');
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$visitor = $current_user->display_name;

			$recipients = strtr($recipients, array(
				"%user_email%" => $current_user->user_email
			));
		}

		/* User IP */
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$ip = apply_filters('wpb_get_ip', $ip);

		/* Subject */
		$subject = get_bloginfo();

		/* Create FileList */
		$_filelisttemplate = trim($this->settings['filelist_template']);
		$filelist = '';
		foreach ($entries as $entry) {
			$path_parts = OutoftheBox_mbPathinfo($entry['path']);
			$pathreg = str_replace('/', '\/', $this->_rootFolder);
			$location = preg_replace('/' . $pathreg . '/i', '', $path_parts['dirname'], 1) . '/' . $path_parts['basename'];
			$fileline = strtr($_filelisttemplate, array(
				"%filename%" => $path_parts['basename'],
				"%filesize%" => $entry['size'],
				"%fileurl%" => admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($location) . "&lastpath=" . rawurlencode($this->_lastPath) . "&dl=1&listtoken=" . $this->listtoken,
				"%filepath%" => $entry['path'],
				"%filesafepath%" => $location
			));
			$filelist .= $fileline;
		}

		/* Create Message */
		switch ($emailtype) {
			case 'download':
				if (count($entries) === 1) {
					$subject .= ' | ' . __('File downloaded', 'outofthebox') . ': ' . $location;
				} else {
					$subject .= ' | ' . __('Files downloaded', 'outofthebox') . ' (' . count($entries) . ')';
				}
				$message = trim($this->settings['download_template']);
				break;
			case 'upload':
				$subject .= ' | ' . __('New file(s) on your Dropbox', 'outofthebox');
				$message = trim($this->settings['upload_template']);
				break;
			case 'deletion':
			case 'deletion_multiple':
				if (count($entries) === 1) {
					$subject .= ' | ' . __('File deleted on your Dropbox', 'outofthebox');
				} else {
					$subject .= ' | ' . __('Files deleted on your Dropbox', 'outofthebox') . ' (' . count($entries) . ')';
				}

				$message = trim($this->settings['delete_template']);
				break;
		}

		/* Geo location if required */
		$location = '-location unknown-';
		if (strpos($message, '%location%') !== false) {
			try {
				$geolocation = (unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip)));

				if ($geolocation !== false && $geolocation['geoplugin_status'] === 200) {
					$location = $geolocation['geoplugin_city'] . ', ' . $geolocation['geoplugin_region'] . ', ' . $geolocation['geoplugin_countryName'];
				} else {
					$location = '-location unknown-';
				}
			} catch (Exception $ex) {
				$location = '-location unknown-';
			}
		}

		/* Replace filters */
		$message = strtr($message, array(
			"%visitor%" => $visitor,
			"%currenturl%" => $currenturl,
			"%filelist%" => $filelist,
			"%ip%" => $ip,
			"%location%" => $location
		));

		$recipients = explode(',', $recipients);

		/* Create Notifaction variable for hook */
		$notification = array(
			'type' => $emailtype,
			'recipients' => $recipients,
			'subject' => $subject,
			'message' => $message,
			'files' => $entries
		);

		/* Executes hook */
		$notification = apply_filters('outofthebox_notification', $notification);

		/* Send mail */
		try {
			$headers = array('Content-Type: text/html; charset=UTF-8');
			$htmlmessage = nl2br($notification['message']);

			foreach ($notification['recipients'] as $recipient) {
				$result = wp_mail($recipient, $notification['subject'], $htmlmessage, $headers);
			}
		} catch (Exception $ex) {

		}
	}

	private function _cleanLists() {
		$now = time();
		foreach ($this->lists as $token => $list) {

			if (!isset($list['expire']) || ($list['expire']) < $now) {
				unset($this->lists[$token]);
			}
		}
	}

	protected function _isAuthorized($hook = false) {
		if (isset($_REQUEST['action']) && ($hook === false)) {
			switch ($_REQUEST['action']) {

				case 'outofthebox-get-filelist':
				case 'outofthebox-get-gallery':
				case 'outofthebox-get-playlist':
				case 'outofthebox-rename-entry':
				case 'outofthebox-move-entry':
				case 'outofthebox-upload-file':
				case 'outofthebox-add-folder':
				case 'outofthebox-create-zip':
					check_ajax_referer($_REQUEST['action']);
					break;

				case 'outofthebox-delete-entry':
				case 'outofthebox-delete-entries':
					check_ajax_referer('outofthebox-delete-entry');
					break;

				case 'outofthebox-create-link':
				case 'outofthebox-embedded':
					check_ajax_referer('outofthebox-create-link');
					break;
				case 'outofthebox-download':
				case 'outofthebox-getpopup':
				case 'outofthebox-thumbnail':
				case 'outofthebox-preview':
				case 'outofthebox-revoke':
					break;
				default:
					die();
			}
		}

		$hasToken = $this->loadToken();

		if (is_wp_error($hasToken)) {
			return $hasToken;
		}

		if (is_wp_error($appInfo = $this->setAppConfig())) {
			return $appInfo;
		}

		$client = $this->startClient();

		return true;
	}

	/**
	 * Checks if a particular user has a role.
	 * Returns true if a match was found.
	 *
	 * @param array $roles Roles array.
	 * @return bool
	 */
	public function checkUserRole($roles_to_check = array()) {

		if (in_array('all', $roles_to_check)) {
			return true;
		}

		if (in_array('none', $roles_to_check)) {
			return false;
		}

		if (in_array('guest', $roles_to_check)) {
			return true;
		}

		if (is_super_admin()) {
			return true;
		}

		if (!is_user_logged_in()) {
			return false;
		}

		$user = wp_get_current_user();

		if (empty($user) || (!($user instanceof WP_User))) {
			return false;
		}

		foreach ($user->roles as $role) {
			if (in_array($role, $roles_to_check)) {
				return true;
			}
		}

		return false;
	}

	public function removeElementWithValue($array, $key, $value) {
		foreach ($array as $subKey => $subArray) {
			if ($subArray[$key] == $value) {
				unset($array[$subKey]);
			}
		}

		return $array;
	}

}