<?php
/**
 * Created by PhpStorm.
 * User: ricardobandala
 * Date: 2016-08-25
 * Time: 22:38
 */
//require_once 'connectDropbox.php';
add_action( 'wp_loaded', 'loadModel');

function loadModel(){
    if ( isCustomer() || is_admin() ) { ?>
        <script>
            model.abspath = '<?php echo get_site_url(); ?>';
            model.currentTotalTime = 0<?php echo $totalTime = showTotalTime();?>;
            <?php $url = get_site_url(); ?>;
            model.url = "<?php echo $url ?>";
            model.main = <?php echo getProduct('base, main')?>;
            model.dvd = <?php echo getProduct('base, disc')?>;
            model.raw = <?php echo getProduct('base, raw')?>;
            model.music =
            <?php $current_user = wp_get_current_user();
                $ifBought = isBoughted($current_user, 51) ? "1" : "0"?>
                model.isBoughtMainProduct = <?php echo $ifBought ?>;
        </script>
        <?php
    }
};
function getMusic(){
    $params = array(
        'post_type' => 'product',
        'product_cat' => 'music'
    );
    $wc_query = new WP_Query($params);
    if ($wc_query->have_posts()):
        while($wc_query->have_posts()):
            $wc_query->the_post();
            $thisProductID = $wc_query->post->ID;
            $_product = wc_get_product( $thisProductID );?>
            var track = {
            'id': <?php echo $thisProductID ?>,
            'title':'<?php the_title() ?>',
            <?php
            $inCart = isInCart($thisProductID)? "1" : "0";
            $ifBought = isBoughted($current_user, $thisProductID)? "1" : "0"?>
            'inCart': <?php echo $inCart ?>,
            'excerpt':'<?php echo $wc_query->post->post_excerpt;
            $wewe = $_product->get_price();?>',
            'price':'<?php echo $_product->get_price(); ?>',
            'reg-price':'<?php echo $_product->get_regular_price(); ?>',
            'sale-price':'<?php echo $_product->get_sale_price(); ?>'
            }
            model.musicTrack.push(track);
            console.log(track.title);
            <?php
        endwhile;
    endif;
    wp_reset_postdata();
 }
function getProduct($productTag){
    $res = "";
    $params = array(
        'post_type' => 'product',
        'product_cat' => $productTag
    );
    $wc_query = new WP_Query($params);
    if ($wc_query->have_posts()):
        while($wc_query->have_posts()):
            $wc_query->the_post();
            $thisProductID = $wc_query->post->ID;
            $_product = wc_get_product( $thisProductID );
            $inCart = isInCart($thisProductID)? "1" : "0";

            $res .=  '{id:'.$thisProductID.', '
                .'title:'.the_title().','
                .'inCart:'.$inCart.','
                .'image:'.$_product->get_image_id().','
                .'excerpt:'.$wc_query->post->post_excerpt.','
                .'price:'.$_product->get_price().','
                .'reg-price:'.$_product->get_regular_price().','
                .'sale-price:'.$_product->get_sale_price()
                .'};';
        endwhile;
    endif;
    wp_reset_postdata();
    return $res;
}


