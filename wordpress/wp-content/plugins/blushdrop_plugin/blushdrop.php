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
 * @version  1.1
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
			$this->setConfigValues($args);
			$this->settings = $this->loadSettings();
			$this->path = $this->settings['dropbox_path'];
			$this->bdp_dpx = new Blushdrop_dropbox(['dropbox_path' => $args['dropbox_path'], 'dropbox_appInfo' =>$args['dropbox_appInfo']]);
			$this->bdp_wcm = new Blushdrop_woocommerce();
			add_action('init', array(&$this, 'register_CustomerFiles'));
			add_action('user_register', array(&$this, 'whenNewCustomer'), 10, 1);
			add_action('wp_ajax_getMinutes', array(&$this, 'ajax_getMinutes'));
			add_action('wp_ajax_addOrderToCart', array(&$this, 'ajax_addOrderToCart'));
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_CustomerFiles'));
			add_action('wp_login', array(&$this, 'redirectIfCustomer'), 11, 2);
			add_shortcode('blushdrop_ClientControls', array(&$this, 'loadClientControls'));
			add_shortcode('blushdrop_ClientModel', array(&$this, 'loadClientModel'));
			add_shortcode('blushdrop_ClientCartRules', array(&$this, 'loadClientCartRules'));
		}
		private function createPageCustomer($user, $path)
		{
			$oob = '[outofthebox dir="'.$path.'"'
				.'mode="files"'
				.'viewrole="administrator|author|customer|guest"'
				.'downloadrole="administrator|author|subscriber|customer"'
				.'upload="1" rename="1" '
				.'renamefilesrole="administrator|editor|author|contributor|customer" '
				.'renamefoldersrole="administrator|editor|author|customer" '
				.'move="1" delete="1" deletefilesrole="administrator|editor|author|customer"'
				.'deletefoldersrole="administrator|editor|author|customer" addfolder="1" '
				.'addfolderrole="administrator|editor|author|contributor|customer" '
				.'uploadrole="administrator|editor|author|contributor|subscriber|customer|guest"]';
			$oob .=" [blushdrop_ClientModel][blushdrop_ClientControls]";
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
		/*
		* Create an array with the products filtered applying
		* the following business logic
		*#replace in cart if: product is Music
		*#Just one per cart if: product is Raw or editing
		*#as many in the cart if: product is Discs or Minutes
		*#Discard if: product is url
		*The minutes follow this logic:
		 * If the new qty is equal, ignore
		 * If the new qty is > then just add the extra
		 * If the new qty is < remove the difference of minutes in cart vs qty
		 * *
	 **/
		private function filterProductsToAdd($orders)
		{
			$wcm = $this->bdp_wcm;
			$settings = $this->settings;
			$musicIDs = $wcm->getMusicIDs($settings['prodCat_Music']);
			$res = Array();
			$regProdIDs = Array(
				$settings['prodID_Disc'],
				$settings['prodID_ExtraMinute'],
				$settings['prodID_RawMaterial'],
				$settings['prodID_EditingPacakage']
			);
			foreach ($orders as $order){
				$id = absint($order['id']);
				$qty = intval($order['qty']);
				$inCart = $wcm->isInCart($id);

				if(in_array($id, $regProdIDs)){
					if($inCart['qty'] == $qty){
						$order['added'] = 0;
						array_push($res, $order);
						continue;
					}
					if($inCart['ok']) {
						$order['added'] = $wcm->setQuantityInCart($inCart['key'], $qty);
					}else{
						$order['added'] = $wcm->addToCart($id, $qty);
					};
					array_push($res, $order);
					continue;
				};
				//***Just One per car, different ID's : MUSIC***
				if(in_array($id, $musicIDs)){
					$authorID = get_the_author_meta('ID');
					$musicInCart = $wcm->thereIsMusicInCart($settings['prodCat_Music'], $authorID);
					if($musicInCart) {
						$deleted = $wcm->removeMusicFromCart($settings['prodCat_Music']);
					}
					$order['added'] = $wcm->addToCart($id, $qty);
					array_push($res,$order);
					continue;
				};
			}
			unset ($order);
			return $res;
		}

		private function isAuthorOrAdmin()
		{
			$userID = wp_get_current_user()->ID;
			$authorID = get_the_author_meta('ID');
			$istheAuthor = ($userID == $authorID)? 1 : 0;
			$isAuthorized = ($this->isCustomer($userID))? $istheAuthor: 0;
			$isAdmin = current_user_can('administrator');
			if($isAuthorized ||$isAdmin ){
				return true;
			}
			return false;
		}

		private function isCustomer($ID = null)
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

		private function loadSettings()
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
		private function sanitizeUserName($username)
		{
			//TODO improove with regex;
			$username = wp_strip_all_tags( $username );
			$username = remove_accents( $username );
			$username = str_replace("@","_at_",$username);
			$username = str_replace(" ","_",$username);
			return $username;
		}

		private function setConfigValues($args)
		{
			add_option('blushdrop_settings', $args);
		}
		/**Public functions ***********************************/
		public function ajax_addOrderToCart()
		{
			$orders = $_REQUEST['order'];
			$res = $this->filterProductsToAdd($orders);
			header('Content-Type:./ application/json');
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

		public function enqueue_CustomerFiles()
		{
			$user_login = wp_get_current_user()->user_login;
			$isAdmin = current_user_can('administrator');
			if(is_page($user_login) || $isAdmin){
				wp_enqueue_style('mdl_css');
				wp_enqueue_style('custom_css');
				wp_enqueue_script('mdl_js');
				wp_enqueue_script('blushdrop_dashboard_js');
			}
			if(is_page('cart')){
				wp_enqueue_script('blushdrop_cart_js');
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
		public function loadClientCartRules()
		{
			$page = get_page_by_title( 'Cart' );
			if( $this->isCustomer() && is_page($page->ID)){
				if(file_exists(WP_PLUGIN_DIR . "/blushdrop_plugin/client_cartRules.php"))
				{
					include_once(WP_PLUGIN_DIR . "/blushdrop_plugin/client_cartRules.php");
				}
				else
				{
					return 'An error has occurred, please reload the page, if the problem
					persist, please get in contact with customer service';
				}
			}
		}
		public function loadClientControls()
		{
			if( $this->isAuthorOrAdmin()) {
				if(file_exists(WP_PLUGIN_DIR . "/blushdrop_plugin/client_dashboardControls.html")) {
					return file_get_contents(WP_PLUGIN_DIR . "/blushdrop_plugin/client_dashboardControls.html");
				}
				else {
					return 'An error has occurred, please reload the page, if the problem
				persist, please get in contact with customer service';
				}
			}
		}
		public function loadClientModel()
		{
			if( $this->isAuthorOrAdmin()){
				if(file_exists(WP_PLUGIN_DIR . "/blushdrop_plugin/client_dashboardModel.php"))
				{
					include_once(WP_PLUGIN_DIR . "/blushdrop_plugin/client_dashboardModel.php");
					$authorID = get_the_author_meta('ID');
					loadData($this, $authorID);
				}
				else
				{
					return 'An error has occurred, please reload the page, if the problem
					persist, please get in contact with customer service';
				}
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
		public function register_CustomerFiles()
		{
			wp_register_style('mdl_css', plugins_url('/mdl/material.css', __FILE__), false, null, 'all');
			wp_register_style('custom_css', plugins_url('/css/CustomerTemplateStyle.css', __FILE__), false, null, 'all');
			wp_register_script('mdl_js', plugins_url('/mdl/material.js', __FILE__));
			wp_register_script('blushdrop_dashboard_js', plugins_url('/js/blushdrop_dashboard.js', __FILE__));
			wp_register_script('blushdrop_cart_js', plugins_url('/js/blushdrop_cart.js', __FILE__));
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
