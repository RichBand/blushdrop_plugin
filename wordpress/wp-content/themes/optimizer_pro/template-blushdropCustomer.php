<?php
/**
 * Template Name: template-blushdropCustomer
 */

global $optimizer;?>

<?php get_header(); ?>


    <div class="page_wrap layer_wrapper">

        <!--CUSTOM PAGE HEADER STARTS-->
        <?php $show_pgheader = get_post_meta( $post->ID, 'show_page_header', true); if (empty($show_pgheader)){ ?>
        	<?php get_template_part('framework/core','pageheader'); ?>
        <?php }else{ ?>
		<?php } ?>
        <!--CUSTOM PAGE HEADER ENDS-->


        <div id="content">
            <div class="center">
				<?php
				//NO SIDEBAR LOGIC
                $nosidebar ='';
                $hidesidebar = get_post_meta($post->ID, 'hide_sidebar', true);
				$sidebar = get_post_meta($post->ID, 'sidebar', true);

                if (!empty( $hidesidebar )){
                        $nosidebar = 'no_sidebar';
                }else{
                        if(!empty( $sidebar ) && is_active_sidebar( $sidebar )){
                            $nosidebar = '';
						}elseif(!empty( $sidebar ) && !is_active_sidebar( $sidebar )){
							$nosidebar = 'no_sidebar';
                        }elseif(!is_active_sidebar( 'sidebar' ) ){
                            $nosidebar = 'no_sidebar';
                 		}
                } ?>
                <div class="single_wrap <?php echo $nosidebar; ?>">
                    <div class="single_post">
                        <?php if(have_posts()): ?><?php while(have_posts()): ?><?php the_post(); ?>
                        <div <?php post_class(); ?> id="post-<?php the_ID(); ?>">

                        <!--EDIT BUTTON START-->
                            <?php if ( is_user_logged_in() && is_admin() ) { ?>
                                    <div class="edit_wrap">
                            			<a href="<?php echo get_edit_post_link(); ?>">
                            				<?php _e('Edit','optimizer'); ?>
                                		</a>
                            		</div>
                            <?php } ?>
                        <!--EDIT BUTTON END-->

                        <!--PAGE CONTENT START-->
                        <div class="single_post_content">
                        	<?php if(empty($optimizer['pageheader_switch'])){ ?>
                            	<?php do_action('optimizer_before_title'); ?>
                        			<h1 class="postitle"><?php the_title(); ?></h1>
								<?php do_action('optimizer_after_title'); ?>
							<?php } ?>

                            <!--SOCIAL SHARE POSTS START-->
                            <?php if (!empty ($optimizer['social_page_id']) || is_customize_preview()) { ?>
                                <div class="share_foot share_pos_<?php echo $optimizer['share_position']; ?> <?php if (empty($optimizer['social_page_id'])){ echo 'hide_share'; }?>">
									<?php get_template_part('framework/core','share_this'); ?>
                                </div>
                             <?php } ?>
                            <!--SOCIAL SHARE POSTS END-->

                                <!--THE CONTENT START-->
                                    <div class="thn_post_wrap" <?php optimizer_schema_prop('content'); ?>>
										<?php do_action('optimizer_before_content'); ?>
                                            <?php the_content(); ?>
                                        <?php do_action('optimizer_after_content'); ?>
                                    </div>
                                        <div style="clear:both"></div>
                                    <div class="thn_post_wrap wp_link_pages">
                                        <?php wp_link_pages('<p class="pages"><strong>'.__('Pages:', 'optimizer').'</strong> ', '</p>', 'number'); ?>
                                    </div>
                                <!--THE CONTENT END-->
                        </div>
												<?php $minutes = showTotalTime($wp_query);
												echo $minutes." Minutes";
												echo "$".($minutes-10)*10;
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
												<div id="bdpCustomerWidget">

												</div>
												<div id="bdpCustomerSubmit"></div>
												</div>


                        <!--PAGE CONTENT END-->


                        </div>

                   <?php endwhile ?>
                    </div>


                  <!--COMMENT START: Calling the Comment Section. If you want to hide comments from your posts, remove the line below-->
                  <?php if (!empty ($optimizer['post_comments_id'])) { ?>
                      <div class="comments_template">
                          <?php comments_template('',true); ?>
                      </div>
                  <?php }?>
                  <!--COMMENT END-->

                  <?php endif ?>

                    </div>

                <!--PAGE END-->


                <!--SIDEBAR LEFT OR RIGHT-->
                	<?php /* Sidebar Variables */?>
					<?php $hide_sidebar = get_post_meta( $post->ID, 'hide_sidebar', true);
						if(empty($hide_sidebar)){	get_sidebar(); 	}
					?>
                <!--SIDEBAR LEFT OR RIGHT END-->

                    </div>
            </div><!--#content END-->



			<?php optimizer_full_sidebar(); ?>



    </div><!--layer_wrapper class END-->

<?php get_footer(); ?>