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
if ( ! defined( 'ABSPATH' ) ) {
//	exit; // Exit if accessed directly
}
if (!class_exists('Blushdrop')) {
	require_once 'blushdrop_dropbox.php';
	require_once 'blushdrop_woocommerce.php';
	class Blushdrop
	{
		private $bdp_dpx = null;
		private $bdp_wcm = null;
		private $path = "";
		private $settings = null;
		function __construct($args)
		{
			$this->setConfigValues();
			$this->settings = $this->loadSettings();
			$this->path = $this->setPath($args);
			$this->bdp_dpx = new Blushdrop_dropbox($this->path);
			$this->bdp_wcm = new Blushdrop_woocommerce();
			add_action('wp_login', array(&$this, 'redirectIfCustomer'), 11, 2);
			add_action('user_register', array(&$this, 'whenNewCustomer'), 10, 1);
			add_shortcode('blushdrop_products', array(&$this, 'blushdrop_loadMinutes'));
			add_action('wp_ajax_getMinutes', array(&$this, 'ajax_getMinutes'));
			add_action('wp_ajax_addOrderToCart', array(&$this, 'ajax_addOrderToCart'));
		}
		public function ajax_addOrderToCart()
		{
			$order = $_REQUEST['order'];
			$wcm = $this->bdp_wcm;
			$res = $wcm->add_arrayToCart($order);
			header('Content-Type: application/json');
			echo json_encode($res);
			exit;
		}

		public function ajax_getMinutes()
		{
			$userID     = absint( $_REQUEST['userID'] );
			$user = get_userdata($userID);
			$path = $this->path.$user->user_login;
			$minutes = $this->bdp_dpx->getVideoMinutes($path);
			header('Content-Type: text/plain');
			echo $minutes;
			exit;
		}

		public function blushdrop_loadMinutes()
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

		public function createPageCustomer($user, $path)
		{
			$oob = '[outofthebox dir="'.$path.'" mode="files"'
				.' viewrole="administrator|editor|author|contributor|subscriber|customer|guest"'
				.' downloadrole="administrator|editor|author|contributor|subscriber" upload="1" overwrite="1" rename="1"'
				.' renamefilesrole="administrator|editor|author|contributor|customer"'
				.' renamefoldersrole="administrator|editor|author|contributor|customer" move="1" delete="1"'
				.' deletefilesrole="administrator|editor|author|contributor|customer"'
				.' deletefoldersrole="administrator|editor|author|contributor|customer"'
				.' addfolder="1" addfolderrole="administrator|editor|author|contributor|customer|guest"]';
			$oob .="[blushdrop_products]";
			$page['post_type'] = 'page';
			$page['post_content'] = $oob;
			$page['post_parent'] = 0;
			$page['post_author'] = $user->ID;
			$page['post_status'] = 'publish';
			$page['post_title'] = $username = $this->sanitizeUserName($user->user_login);
			$pageid = wp_insert_post($page);
			if ($pageid == 0) {
				//TODO find what to do with the error, maybe a suggestion to reload?
			}
		}
		/**
		 * @return Blushdrop_dropbox|null
		 */
		public function getBdpDpx()
		{
			return $this->bdp_dpx;
		}
		/**
		 * @return Blushdrop_woocommerce|null
		 */
		public function getBdpWcm()
		{
			return $this->bdp_wcm;
		}
		/**
		 * @return $this->settings|null
		 */
		public function getSettings()
		{
			return $this->settings;
		}
		/**
		 * @return string|Blushdrop
		 */
		public function getPath()
		{
			return $this->path;
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

		public function loadSettings()
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

		public function redirectIfCustomer($user_login, $user)
		{
			$myID = $user->ID;
			if ($this->isCustomer($myID)) {
				$thisSite = get_site_url() . "/";
				wp_redirect($thisSite . $user->user_login, 302);
				exit;
			};
		}

		public function sanitizeUserName($username)
		{
			//TODO improove with regex;
			$username = wp_strip_all_tags( $username );
			$username = remove_accents( $username );
			$username = str_replace("@","_at_",$username);
			$username = str_replace(" ","_",$username);
			return $username;
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

		private function setPath($args)
		{
			$path = ($args['path'] == null || $args['path'] == "")
				?$this->settings['dropbox_path']
				: $args['path'];
			return $path;
		}

		public function whenNewCustomer($user_id)
		{
			if ($this->isCustomer($user_id)){
				$newUser = get_userdata($user_id);
				$username = $this->sanitizeUserName($newUser->user_login);
				$path = $this->path.$username;
				$this->bdp_dpx->createFolder($path);
				$this->createPageCustomer($newUser, $path);
			}
		}
	}//end of class
}//end, If class exist