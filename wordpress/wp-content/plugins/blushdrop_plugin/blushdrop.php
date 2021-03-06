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
            $this->bdp_dpx = new Blushdrop_dropbox($args['dropbox']);
            $this->path = $args['dropbox']['path'];
            unset($args['dropbox']);
            $this->setConfigValues($args);
            $this->settings = $this->loadSettings();
			$this->bdp_wcm = new Blushdrop_woocommerce(['musicCat'=>$this->settings['prodCat_Music']]);
            add_action('init', array(&$this, 'register_CustomerFiles'));
            add_action('user_register', array(&$this, 'whenNewCustomer'), 10, 1);
            add_action('wp_ajax_addOrderToCart', array(&$this, 'ajax_addOrderToCart'));
            add_action('wp_ajax_getMinutes', array(&$this, 'ajax_getMinutes'));
            add_action('wp_ajax_getSongData', array(&$this, 'ajax_getSongData'));
            add_action('wp_ajax_getTrackList', array(&$this, 'ajax_getTrackList'));
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_CustomerFiles'));
            add_action('wp_login', array(&$this, 'redirectIfCustomer'), 11, 2);
			add_shortcode('blushdrop_CustomerDashboardControls', array(&$this, 'loadCustomerDashboardControls'));
			add_shortcode('blushdrop_CustomerCartRules', array(&$this, 'loadCustomerCartRules'));
            add_filter('woocommerce_email_order_meta_fields', 'admin_email_order_meta_fields', 10, 3 );
		}

		private function admin_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
            $fields['extra_fields'] = array(
                'label' => __( 'Wedding Date' ),
                'value' => get_post_meta( $order->id, 'weddate', true ),
            );
            return $fields;
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
        private function applyCartRulesToDashboard($orders)
        {
            $bdp_wcm = $this->bdp_wcm;
            $settings = $this->settings;
            $res = Array();
            $regProdIDs = [
                $settings['prodID_Disc'],
                $settings['prodID_ExtraMinute'],
                $settings['prodID_RawMaterial'],
                $settings['prodID_EditingPacakage']
            ];
            //always check that base editing is on the cart

            $orders[] =  [ 'id'=> $settings['prodID_EditingPacakage'], 'qty'=>1];
            
            foreach ($orders as $order) {
                $id = absint($order['id']);
                $qty = intval($order['qty']);

                //if music, Add
                if($id == $settings['prodID_MusicCapsule']){
                    $bdpSongCode = intval($id) + intval($order['songID']);
                    if(!get_post_status($bdpSongCode)){
                        $data = $this->getSongData(absint($order['songID']));
                        $bdp_wcm->setNewSong(intval($order['songID']), $settings['prodID_MusicCapsule'], $data);
                    }
                    $bdp_wcm->removeMusicFromCart($settings['prodCat_Music']);
                    $regProdIDs[] = $bdpSongCode;
                    $id = $bdpSongCode;
                }

                $inCart = $bdp_wcm->isInCart($id);

                if(in_array($id, $regProdIDs)){
                    if($inCart['qty'] == $qty){
                        $order['added'] = 0;
                        array_push($res, $order);
                        continue;
                    }
                    if($inCart['ok']) {
                        $order['added'] = $bdp_wcm->setQuantityInCart($inCart['key'], $qty);
                    }else{
                        $order['added'] = $bdp_wcm->addToCart($id, $qty);
                    };
                    array_push($res, $order);
                    continue;
                };
            }
            return $res;
        }

        private function createPageCustomer($user, $path)
		{
			$oob = '[outofthebox dir="'.$path.'" mode="files" '
                .'viewrole="administrator|author|customer|guest"'
                .'downloadrole="administrator|author|subscriber|customer" '
                .'upload="1" rename="1" '
                .'renamefilesrole="administrator|editor|author|contributor|customer"'
                .' renamefoldersrole="administrator|editor|author|customer"'
                .' move="1" delete="1" deletefilesrole="administrator|editor|author|customer" '
                .'deletefoldersrole="administrator|editor|author|customer" '
                .'addfolder="1" addfolderrole="administrator|editor|author|contributor|customer"'
                .' uploadrole="administrator|editor|author|contributor|subscriber|customer|guest"]';
			$oob .=" [blushdrop_CustomerDashboardControls]";
			try {
                $page['post_type'] = 'page';
                $page['post_content'] = $oob;
                $page['post_parent'] = 0;
                $page['post_author'] = $user->ID;
                $page['post_status'] = 'publish';
                $page['post_title'] = $this->sanitizeUserName($user->user_login);
                $pageid = wp_insert_post($page);
            }
            catch(Exception $e){
                error_log("Caught $e while trying to create the page for the user ".$user->ID);
            }
		}

		private function getSongData($songID){
            $url = "https://soundstripe-test-api.herokuapp.com/v1/songs/".intval($songID);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $contents = curl_exec($curl);
            curl_close($curl);
            $contents = json_decode($contents);
            if($contents) {
                $result = [
                    'title'=>$contents->data->attributes->title,
                    'author'=>$contents->data->relationships->artist->data->name,
                    'image'=>$contents->data->relationships->artist->data->pic->url,
                    'src'=>$contents->data->relationships->audio_files->data[0]->audio_file->versions->mp3->url,
                    'duration'=>$contents->data->attributes->duration,
                ];
            }else {
                //TODO: predeterminated media names?
                $result = [
                    'title'=>'Generic title',
                    'author'=>'Generic Author',
                    'image'=>'',
                    'src'=>'',
                    'duration'=>''
                ];
            }
            return $result;
        }
		private function isAuthorOrAdmin()
		{
			$userID = wp_get_current_user()->ID;
			$authorID = get_the_author_meta('ID');
			$isTheAuthor = ($userID == $authorID)? 1 : 0;
			$isAuthorized = ($this->isCustomer($userID))? $isTheAuthor: 0;
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
			$settings = get_option('blushdrop_settings', [
				'dropbox_path' => '',
				'prodCat_Music' => '',
				'prodID_Disc' => '',
				'prodID_EditingPacakage' => '',
				'prodID_ExtraMinute' => '',
				'prodID_RawMaterial' => '',
				'prodID_URL' => '',
			]);
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
            if(get_option('blushdrop_settings', $default=false)){
                update_option('blushdrop_settings', $args);
            }
            else{
                add_option('blushdrop_settings', $args);
            }

        }
		/**Public functions ***********************************/
		public function ajax_addOrderToCart()
		{
			$orders = $_REQUEST['order'];
			$res = $this->applyCartRulesToDashboard($orders);
			header('Content-Type:./ application/json');
			echo json_encode($res);
			exit;
		}

		public function ajax_getMinutes()
		{
			$current_user = wp_get_current_user();
			$path = $this->path.$current_user->user_login;
			$minutes = $this->bdp_dpx->getVideoMinutes($path);
			header('Content-Type: text/plain');
			echo $minutes;
			exit();
		}
        public function ajax_getSongData()
        {
            $index = intval($_REQUEST['index']);
            $contents = $this->getSongData($index);
            if($contents) {
                header('Content-Type:./ application/json');
                echo json_encode($contents);
            }else {
                var_dump(http_response_code(204));
            }
            exit();
        }
        public function ajax_getTrackList()
                {
                    $index = intval($_REQUEST['index']);
                    $url = "https://soundstripe-test-api.herokuapp.com/v1/playlists/43/songs?page=$index";
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    $contents = curl_exec($curl);
                    curl_close($curl);
                    if(!empty($contents)) {
                        echo $contents;
                    }else {
                        var_dump(http_response_code(204));
                    }
                    exit;
                }

		public function enqueue_CustomerFiles()
		{
            wp_enqueue_script('jquery');
            if(is_page('cart')){
                wp_enqueue_style('mdl_css');
                wp_enqueue_style('custom_css');
                wp_enqueue_script('mdl_js');
                wp_enqueue_script('blushdrop_cart_js');
                return;
            }
			$user_login = wp_get_current_user()->user_login;
			if($user_login && is_page($user_login) || current_user_can('administrator')){
				wp_enqueue_style('mdl_css');
				wp_enqueue_style('custom_css');
                wp_enqueue_style('songsPlaylist_css');
				wp_enqueue_script('mdl_js');
				wp_enqueue_script('blushdrop_dashboard_js');
                wp_enqueue_script('blushdrop_songsPlaylist_js');
                return;
			}
            else{
			    wp_enqueue_script('blushdrop_override_OOB_js');
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

        public function loadCustomerCartRules()
        {
            $settings = $this->getSettings();
            $onePerCart = $settings['cartRules']['onePerCart']; //=>['prodID_EditingPacakage','prodID_URL'],
            $noModifyQuantity = $settings['cartRules']['noModifyQuantity']; //=>['minutes'],
            $file = WP_PLUGIN_DIR . "/blushdrop_plugin/customerCartRules.php";
            if (file_exists($file)) {
                ob_start();
                include($file);
                return ob_get_clean();
            }
            return '';
        }

        public function loadCustomerDashboardControls()
		{
            if( $this->isAuthorOrAdmin()) {
                $settings = $this->getSettings();
                $wcm = $this->getBdpWcm();
                $dpx = $this->getBdpDpx();
                $author = get_user_by('id', get_the_author_meta('ID'));
                $path = $this->getPath() . $author->user_login;
                $currentTotalMinutes = $dpx->getVideoMinutes($path);
                $products = [
                    'disc' => $wcm->getProduct($settings['prodID_Disc'], $author),
                    'main' => $wcm->getProduct($settings['prodID_EditingPacakage'], $author),
                    'minute' => $wcm->getProduct($settings['prodID_ExtraMinute'], $author),
                    'music' => $wcm->getProduct($settings['prodID_MusicCapsule'], $author),
                    'musicInCart' => $wcm->getMusicInCart(),
                    'raw' => $wcm->getProduct($settings['prodID_RawMaterial'], $author),
                    'url' => $wcm->getProduct($settings['prodID_URL'], $author),
                ];
                $file = WP_PLUGIN_DIR . "/blushdrop_plugin/customerDashboardControls.php";
                if (file_exists($file)) {
                    ob_start();
                    include($file);
                    return ob_get_clean();
                }
            }
            return '';
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
			wp_register_style('mdl_css', plugins_url('/mdl/material.min.css', __FILE__), false, null, 'all');
			wp_register_style('custom_css', plugins_url('/css/CustomerTemplateStyle.css', __FILE__), false, null, 'all');
			wp_register_script('mdl_js', plugins_url('/mdl/material.js', __FILE__));

			wp_register_style('songsPlaylist_css', plugins_url('/css/songsPlaylist.css', __FILE__), false, null, 'all');
            wp_register_script('blushdrop_songsPlaylist_js', plugins_url('/js/blushdrop_songsPlaylist.js', __FILE__));

			wp_register_script('blushdrop_dashboard_js', plugins_url('/js/blushdrop_dashboard.js', __FILE__));
		 	wp_register_script('blushdrop_cart_js', plugins_url('/js/blushdrop_cart.js', __FILE__));

            wp_register_script('blushdrop_override_OOB_js', plugins_url('/js/blushdrop_override_OOB.js', __FILE__));
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
