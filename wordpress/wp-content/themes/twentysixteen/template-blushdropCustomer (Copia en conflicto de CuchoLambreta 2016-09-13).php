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

	<?php

    $params = array(
        'post_type' => 'product',
        'product_cat' => 'music'
    );
	$wc_query = new WP_Query($params);
    if ($wc_query->have_posts()):
        ?>
    <div id="bdpCustomerWrapper">
        <div id="bdpCustomerOptions" class="">
            <div id="bdpCustomerMusic" class="bdpContents">
                <p>Choose your song, it's free!</p>
                <select id="bdpCustomerMusicOptions" class="bdpContents">
                <?php
                while($wc_query->have_posts()):
			        $wc_query->the_post();
					$thisID = $wc_query->post->ID;
					$tempDis = isInCart($thisID);
					$disabled = $tempDis? " disabled " : "" ?>
					<option value = "<?php $thisID ?>" <?php echo $disabled ?>>
						<?php the_title() ?>
					</option>
		        <?php
                endwhile;
		        wp_reset_postdata();?>
                </select>
            </div>
    <?php
    else:?>
		<p><?php _e( 'No Products' );?></p>
    <?php
    endif;?>
		<div id="bdpCustomerDiscs" class="bdpContents">
			<p>Do you want to add copies of your discs?</p>
		</div>
		<div id="bdpCustomerRawFootage" class="bdpContents">
			<p>Do you want the raw footage?</p>
			<select id="bdpCustomerRawFootageOptions" class="bdpContents">
				<option value="0">Yes, I want to keep a copy of my raw footage</option>
				<option value="1">No, I don't think I need it</option>
			</select>
		</div>
			</div>
	<div id="bdpCustomerWidget" class="bdpContents">
		<div id="bdpCustomerAmount" class="bdpContents">
			<?php echo $totalTime = showTotalTime($wp_query);?>
		</div>
		<div id="bdpCustomerSubmit" class="bdpContents">
			button submit
		</div>
	</div>

</div>
</div><!-- .content-area -->
<?php get_footer(); ?>
