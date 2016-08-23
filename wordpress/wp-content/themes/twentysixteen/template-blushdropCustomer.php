<?php
/**
 * Template Name: template-blushdropCustomer
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
			// Include the page content template.
			get_template_part( 'template-parts/content', 'page' );
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			// End of the loop.
		endwhile;
		?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<script>
	model.abspath = '<?php echo get_site_url(); ?>';
	model.currentTotalTime = <?php echo $totalTime = showTotalTime($wp_query);?>;
	<?php $current_user = wp_get_current_user();
	$ifBought = isBoughted($current_user, 51)? "1" : "0"?>
	model.isBoughtMainProduct = <?php echo $ifBought ?>;
	<?php
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
			//TODO, RB check the business logic to assign just the main product or any other product that is already bought

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
	$params = array(
			'post_type' => 'product',
		'product_cat' => 'base'
	);
	$wc_query = new WP_Query($params);
	if ($wc_query->have_posts()):
	while($wc_query->have_posts()):
	$wc_query->the_post();
	$thisProductID = $wc_query->post->ID;
	$_product = wc_get_product( $thisProductID );
	?>
	var baseProduct = {
		'id':<?php echo $thisProductID ?>,
		'title':'<?php the_title() ?>',
		<?php
		$inCart = isInCart($thisProductID)? "1" : "0"; ?>
		'inCart': <?php echo $inCart ?>,
		'image':'<?php  $_product->get_image_id(); ?>',
		'excerpt':'<?php echo $wc_query->post->post_excerpt ?>',
		'price':'<?php echo $_product->get_price(); ?>',
		'reg-price':'<?php echo $_product->get_regular_price(); ?>',
		'sale-price':'<?php echo $_product->get_sale_price(); ?>'
	};
	model.baseProducts.push(baseProduct);
	console.log(" baseProduct: "+baseProduct.title);
	<?php
	endwhile;
	endif;
	wp_reset_postdata();?>
</script>
<?php get_footer(); ?>