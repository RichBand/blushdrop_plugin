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
    //TODO create a method to build the path to dropbox, for admins in the current state, just the user can call it own metadata
    $totalTime = showTotalTime($wp_query);
    //echo $totalTime;
    ?>
<div id="bdpCustomerControls">
	<div id="bdpCustomerOptions">
		<select id="bdpCustomerOptions">
		<option value="1">Song 1</option>
 		<option value="2">Song 2</option>
 		<option value="3">Song 3</option>
 		<option value="4">Song 4</option>
		</select>
	</div>
	<div id="bdpCustomerWidget"></div>
	<div id="bdpCustomerSubmit"></div>
</div>
</div><!-- .content-area -->
<?php get_footer(); ?>
