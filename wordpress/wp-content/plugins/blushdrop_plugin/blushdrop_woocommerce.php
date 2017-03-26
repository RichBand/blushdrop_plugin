<?php
/**
 * Created by PhpStorm.
 * User: ricardobandala
 * Date: 2016-09-04
 * Time: 17:07
 */
if (!class_exists('Blushdrop_woocommerce')) {
	class Blushdrop_woocommerce
	{
	    private $musicCat = null;

	    function __construct($args)
        {
            $this->musicCat = $args['musicCat'];
        }

        public function add_arrayToCart($items){
			//$xexe = '[{"id":2, "qty":1}, {"id":3, "qty":1}, {"id":4, "qty":2}]';
			$res = array();
			$added = 0;
			foreach ($items as $order)
			{
				$id = absint($order['id']);
				$qty = absint($order['qty']);
				$added = WC()->cart->add_to_cart($id, $qty);
				$order['added'] = ($added)? $added : 0 ;
				array_push($res,$order);
			}
			unset ($order);
			return $res;
		}

		public function addToCart($id, $qty){
			$res = WC()->cart->add_to_cart($id, $qty);
			return $res;
		}

		public function isBought($productID, $user)
		{
			return wc_customer_bought_product( $user->user_email, $user->ID, $productID );
		}

		public function isInCart($productID)
		{
			$result = array(
				"ok"=>0,
				"qty"=>0,
				"key"=>0
			);
			$cart = WC()->cart->get_cart();
			if ( sizeof( $cart ) > 0 )
			{
				foreach ( $cart as $cart_item_key => $values )
				{
					$id = $values['product_id'];
					if ($id == $productID)
					{
						$result['ok'] = 1;
						$result['key'] = $cart_item_key;
						$result['qty'] = $values['quantity'];;
						break;
					}
				}
			}
			return $result;
		}
		/**
		 * @param $productID
		 * @param $user
		 * @return mixed|string|void
		 */
		public function getProduct($productID, $user=0)
		{
			$product = null;
			//TODO, check why the validation of the active plugin woocommerce is not working
//			if ( is_plugin_active('woocommerce/woocommerce.php') ){
				$WC_product = wc_get_product( $productID);
				$product = $WC_product->post;
//			}
            if(isset($user->ID)){
                $product->isBought = $this->isBought($productID, $user)? 1: 0;
            }
			$product->isInCart = $this->isInCart($productID);
			$product->price =  floatval($WC_product->get_price());
			$product->reg_price = floatval($WC_product->get_regular_price());
			$product->sale_price = floatval($WC_product->get_sale_price());
			return $product;
		}
        public function getMusic($user=0)
        {
            $music = array();
            $params = array(
                'post_type' => 'product',
                'product_cat' => $this->musicCat
            );
            $wc_query = new WP_Query($params);
            if ($wc_query->have_posts())
            {
                while($wc_query->have_posts())
                {
                    $wc_query->the_post();
                    $thisProductID = $wc_query->post->ID;
                    $music[] = $this->getProduct($thisProductID, $user);
                }
            }
            wp_reset_postdata();
            return $music;
        }

		public function getMusicIDs()
		{
			$res = [];
			$music = $this->getMusic();
			for($i = 0, $j = count($music); $i<$j; $i++){
				array_push($res, $music[$i]->ID);
			}
			return $res;
		}

		public function getMusicInCart()
        {
            $res = array();
            $songs = $this->getMusic();
            $res = array_filter($songs, function($song){
                return !empty($song->isInCart['ok']);
            });
            if (!empty($res)){
                $res =  array_values($res)[0];
            }
            return $res;
        }

		public function removeMusicFromCart()
		{
			$res = array();
			$songs = $this->getMusic();
			foreach($songs as $song){
			    $inCart = $song->isInCart;
			    if($inCart['ok']){
			    	$res[] = $this->setQuantityInCart($inCart['key'], -1);
			    }
            }
			return $res;
		}
        private function setFeaturedImage( $image_url, $post_id){
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);
            if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
            else                                    $file = $upload_dir['basedir'] . '/' . $filename;
            file_put_contents($file, $image_data);

            $wp_filetype = wp_check_filetype($filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
            $res2= set_post_thumbnail( $post_id, $attach_id );
            return ['attachement'=>$res1, 'thumbnail'=>$res2];
        }
		public function setQuantityInCart($key, $qty)
		{
			$res = WC()->cart->set_quantity($key, $qty, 1);
			return $res;
		}

        public function setNewSong($songID, $WPMusicCapsule, $data)
        {
            $musicCat = $this->musicCat;
            $term = get_term_by('name', $this->musicCat,'product_cat');
            $post_id = wp_insert_post([
                'import_id'         =>  $songID + $WPMusicCapsule,
                'comment_status'    =>  'open',
                'post_content'      =>  $songID,
                'post_name'         =>  $data['title'].' ('.$data['author'].')'  ,
                'post_status'       =>  'publish',
                'post_title'        =>  $data['title'].' ('.$data['author'].')',
                'post_type'         =>  'product'
            ]);
            wp_set_object_terms($post_id, $term->term_id, 'product_cat');
            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, '_stock_status', 'instock');
            update_post_meta( $post_id, 'total_sales', '0');
            update_post_meta( $post_id, '_downloadable', 'no');
            update_post_meta( $post_id, '_virtual', 'yes');
            update_post_meta( $post_id, '_regular_price', "0" );
            update_post_meta( $post_id, '_sale_price', "" );
            update_post_meta( $post_id, '_purchase_note', "" );
            update_post_meta( $post_id, '_featured', "no" );
            update_post_meta( $post_id, '_weight', "" );
            update_post_meta( $post_id, '_length', "" );
            update_post_meta( $post_id, '_width', "" );
            update_post_meta( $post_id, '_height', "" );
            update_post_meta( $post_id, '_sku', "");
            update_post_meta( $post_id, '_product_attributes', array());
            update_post_meta( $post_id, '_sale_price_dates_from', "" );
            update_post_meta( $post_id, '_sale_price_dates_to', "" );
            update_post_meta( $post_id, '_price', "0" );
            update_post_meta( $post_id, '_sold_individually', "yes" );
            update_post_meta( $post_id, '_manage_stock', "no" );
            update_post_meta( $post_id, '_backorders', "no" );
            update_post_meta( $post_id, '_stock', "" );
            $this->setFeaturedImage($data['image'], $post_id);
            return $post_id;
        }

	}
}