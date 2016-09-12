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
		public function isBought($productID, $user)
		{
			return wc_customer_bought_product( $user->user_email, $user->ID, $productID );
		}

		public function isInCart($productID)
		{
			$result = array(
				"ok"=>0,
			);
			if ( sizeof( WC()->cart->get_cart() ) > 0 )
			{
				foreach ( WC()->cart->get_cart() as $cart_item_key => $values )
				{
					$id = $values['product_id'];
					$qty = $values['quantity'];
					if ($id == $productID)
					{
						$result['ok'] = 1;
						$result['id'] = $id;
						$result['qty'] = $qty;
						break;
					}
				}
			}
			return $result;
		}

		public function getProduct($productID, $user)
		{
			$product = null;
			//TODO, check whiy the validation of the active plugin woocommerce is not working
			//if ( is_plugin_active('woocommerce/woocommerce.php') ){
				$WC_product = wc_get_product( $productID);
				$product = $WC_product->post;
			//}
			$product->price =  floatval($WC_product->get_price());
			$product->reg_price = floatval($WC_product->get_regular_price());
			$product->sale_price = floatval($WC_product->get_sale_price());
			$product->isBought = ($this->isBought($productID, $user)? 1: 0);
			$productInCart = $this->isInCart($productID);
			$product->isInCart = ($productInCart['ok']? 1: 0);
			$product->quantityInCart = $productInCart['quantity'];
			return json_encode($product);
		}

		public function getMusic($category, $user)
		{
			$music = array();
			$params = array(
				'post_type' => 'product',
				'product_cat' => $category
			);
			$wc_query = new WP_Query($params);
			if ($wc_query->have_posts())
			{
				while($wc_query->have_posts())
				{
					$wc_query->the_post();
					$thisProductID = $wc_query->post->ID;
					$x = $this->getProduct($thisProductID, $user);
					$music[] = $x;
				}
			}
			wp_reset_postdata();
			return json_encode($music);
		}

		public function add_arrayToCart($items){
			//$xexe = '[{"id":2, "qty":1}, {"id":3, "qty":1}, {"id":4, "qty":2}]';
			$res = array();
			$added = 0;
			foreach ($items as $order)
			{
				$id = absint($order['id']);
				$qty = absint($order['qty']);
				$inCart = $this->isInCart($id);
				if(!$inCart['ok'])
				{
					$added = WC()->cart->add_to_cart($id, $qty);
				};
				$order['added'] = ($added)? $added : 0 ;
				array_push($res,$order);
			}
			unset ($order);
			return $res;
		}
	}
}