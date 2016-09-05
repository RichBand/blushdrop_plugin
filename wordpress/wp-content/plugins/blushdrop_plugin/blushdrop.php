<?php
/**
 * Created by PhpStorm.
 * User: ricardobandala
 * Date: 2016-08-30
 * Time: 22:05
 */
/**
 * Main Blushdrop Class.
 *
 * @class Blushdrop
 * @version  1.0
 */

/*
 *
 * */
require_once 'blushdrop_dropbox.php';
if (!class_exists('Blushdrop')) {
	class Blushdrop
	{
		private $bdp_dpx = null;
		private $path = "";
		private $settings = null;

		function __construct($args)
		{
			$this->setConfigValues();
			$this->settings = $this->getSettings();
			$this->path = $this->getPath($args);
			$this->bdp_dpx = new Blushdrop_dropbox($this->path);
			add_action('wp_login', array(&$this, 'redirectIfCustomer'), 11, 2);
			add_action('user_register', array(&$this, 'whenNewCustomer'), 10, 1);
			add_shortcode('blushdrop_products', array(&$this, 'blushdrop_LoadMinutes'));
		}

		private function getPath($args)
	{
		$path = ($args['path'] == null || $args['path'] == "")
			?$this->settings['dropbox_path']
			: $args['path'];
		return $path;
	}

		private function getSettings()
		{
			$settings = get_option('blushdrop_settings', array(
				'dropbox_path' => '',
				'prodCat_Music' => '',
				'prodID_Disc' => '',
				'prodID_EditingPacakage' => '',
				'prodID_ExtraMinute' => '',
				'prodID_RawMaterial' => '',
				'prodID_URL' => '',
			));
			return $settings;
		}

		public function setConfigValues()
		{
			add_option('blushdrop_settings', array(
				'dropbox_path' => '/blushdrop/',
				'prodCat_Music' => 'music',
				'prodID_Disc' => '31',
				'prodID_EditingPacakage' => '51',
				'prodID_ExtraMinute' => '79',
				'prodID_RawMaterial' => '67',
				'prodID_URL' => '94',
			));
		}

		public function blushdrop_LoadMinutes()
		{
			$userID = wp_get_current_user()->ID;
			$authorID = get_the_author_meta('ID');
			$istheAuthor = ($userID == $authorID)? 1 : 0;
			$isAuthorized = ($this->isCustomer($userID))? $istheAuthor: 0;
			$isAdmin = current_user_can('administrator');
			if( $isAuthorized || $isAdmin)
			{
				$path_base = ABSPATH . "wp-content/plugins/blushdrop_plugin/";
				if(file_exists(WP_PLUGIN_DIR . "/blushdrop_plugin/setModel.php"))
				{
					include_once(WP_PLUGIN_DIR . "/blushdrop_plugin/setModel.php");
					loadData($this, $userID);
				}
				else
				{
					echo 'An error has occurred, please reload the page, if the problem
				persist, please get in contact with customer service';
				}
			}
		}

		public function createPageCustomer($newUser, $path)
		{//TODO, check if its neccessary to add a / after $path
			//TODO check construction of shortode of $oob
			$oob = '[outofthebox 
	    dir="' + $path + '" 
	    mode="files" 
	    viewrole="administrator|author|customer|guest" 
	    downloadrole="administrator|author|subscriber|customer" 
	    upload="1" 
	    rename="1" 
	    renamefilesrole="administrator|editor|author|contributor" 
	    renamefoldersrole="administrator|editor|author|contributor" 
	    move="1" 
	    delete="1" 
	    deletefilesrole="administrator|editor|author|contributor" 
	    deletefoldersrole="administrator|editor|author|contributor" 
	    addfolder="1" 
	    addfolderrole="administrator|editor|author|contributor"]';
			$oob .="[blushdrop_products]";
			$page['post_type'] = 'page';
			$page['post_content'] = $oob;
			$page['post_parent'] = 0;
			$page['post_author'] = $newUser->ID;
			$page['post_status'] = 'publish';
			$page['post_title'] = $newUser->user_login;
			//$page = apply_filters('yourplugin_add_new_page', $page, 'teams');
			$pageid = wp_insert_post($page);
			if ($pageid == 0) {
				//TODO find what to do with the error, maybe a suggestion to reload?
			}
		}

		public function isCustomer($ID)
		{
			$user = ($ID == null)? wp_get_current_user() : get_userdata($ID);
			if ($user != null)
			{
				$user_roles = $user->roles;
				if (in_array("Customer", $user_roles) || in_array("customer", $user_roles))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function redirectIfCustomer($user_login, $user)
		{
			$myID = $user->ID;
			if ($this->isCustomer($myID)) {
				$thisSite = get_site_url() . "/";
				wp_redirect($thisSite . $user->user_login, 302);
				exit;
			};
		}

		public function whenNewCustomer($user_id)
		{
			if ($this->isCustomer($user_id)) {
				$newUser = get_userdata($user_id);
				$path = $this->path . $newUser->user_login;
				$this->bdp_dpx->createFolder($path);
				$this->createPageCustomer($newUser, $path);
			}
		}
	}//end, of class
}//end, If class exist