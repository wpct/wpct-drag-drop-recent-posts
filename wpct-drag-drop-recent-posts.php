<?php
/**
 * Plugin Name: WPCT Drag & Drop Recent Posts
 * Description: Very customizable slider for posts
 * Plugin URI: https://wp-code-tips.com/
 * Author: wp-code-tips.com
 * Author URI: https://wp-code-tips.com/contact/
 * Version: 1.20
 * Text Domain: wpct-drag-drop-recent-posts
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

// TODO:
// posts counter
// first image featured - full width
// Google Fonts select

// ensure is_plugin_active() exists (not on frontend)
if( !function_exists('is_plugin_active') ) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// excerpt limit 
if ( ! function_exists( 'wpctDragAndDropRecentPostsExcerpt' ) ) {
	function wpctDragAndDropRecentPostsExcerpt($count){
	  $excerpt = get_the_excerpt();
	  $excerpt = strip_tags($excerpt);
	  $excerpt = mb_substr($excerpt, 0, $count);
	  $excerpt_final = '<div class="wpct-excerpt-text">'.$excerpt;
	  if($count > 0){
	  	$excerpt_final.= '...';
	  }
	  $excerpt_final.= '</div>';
	  
	  return $excerpt_final;
	}
}

// Set/check session
if ( ! function_exists( 'wpctDragAndDropRecentPostsSession' ) ) {
	function wpctDragAndDropRecentPostsSession() {
	    if(!session_id()) {
	        session_start();
	    }
	}
	add_action('init', 'wpctDragAndDropRecentPostsSession', 1);
}

// count hits of posts
if ( ! function_exists( 'wpctDragAndDropRecentPostsTrackPopularPosts' ) ) {
	function wpctDragAndDropRecentPostsTrackPopularPosts() {
	    // Only run the process for single posts, pages and post types
	    if (is_singular()) {
	        global $post;
	        $custom_field = '_wpct_drag_drop_recent_posts_popular_posts_count';
	        // Only track a one view per post for a single visitor session to avoid duplications
	        if ( !isset( $_SESSION["wpct-drag-drop-recent-posts-popular-posts-count-{$post->ID}"] ) ) {
	            // Update view count
	            $view_count = get_post_meta( $post->ID, $custom_field, true );
	            $stored_count = ( isset($view_count) && !empty($view_count) ) ? ( intval($view_count) + 1 ) : 1;
	            $update_meta = update_post_meta( $post->ID, $custom_field, $stored_count );
	            // Check for errors
	            if ( is_wp_error($update_meta) )
	                error_log( $update_meta->get_error_message(), 0 );
	                // Store session in "viewed" state
	                $_SESSION["wpct-drag-drop-recent-posts-popular-posts-count-{$post->ID}"] = 1;
	        }
	        // uncomment these 3 lines to show views of post (right after <body> tag)
	         /*echo '<p style="color: red; text-align: center; margin: 0; background: #fff; z-index: 9999; position: relative; border: 1px solid #000; padding: 1em;">';
	         echo get_post_meta( $post->ID, $custom_field, true );
	         echo ' views of this post</p>';*/
	    }
	}
	add_action('wp_head', 'wpctDragAndDropRecentPostsTrackPopularPosts');
}

if(!class_exists('WPCTrecentPosts')){
    class WPCTrecentPosts extends WP_Widget {
    	
		public function __construct() {
			$widget_ops = array( 
				'classname' => 'WPCTrecentPosts',
				'description' => __('Very customizable slider for posts.', 'wpct-drag-drop-recent-posts'),
			);
			parent::__construct( 'WPCTrecentPosts', 'WPCT Drag & Drop Recent Posts', $widget_ops );
		}
		
        public function widget($args,  $setup){

            // check if Pro features should be enabled   
            if ( is_plugin_active( 'wpct-drag-drop-recent-posts-pro/wpct-drag-drop-recent-posts-pro.php' ) ) {
                $is_pro = true;
            } else{
                $is_pro = false;
            }
			
			$is_pro_class = 'wpct-free';
			if($is_pro){
				$is_pro_class = 'wpct-pro';
			}
			
            extract($args);
            $post_type = 'post';
            if(isset($setup['post_type'])){
            	$post_type = $setup['post_type'];
            }
			$post_type_category = 'category';
			if(isset($setup['post_type_category'])){
				$post_type_category = $setup['post_type_category'];
			}
			$count_posts = wp_count_posts($post_type);
			$title_show = 1;
			if(isset($setup['title_show'])){
				$title_show = $setup['title_show'];
			}
			$title_linkable = 1;
			if(isset($setup['title_linkable'])){
				$title_linkable = $setup['title_linkable'];
			}
			$readmore = 0;
			if(isset($setup['readmore'])){
				$readmore = $setup['readmore'];
			}
			$header_tag = 5;
			if(isset($setup['header_tag'])){
				$header_tag = $setup['header_tag'];
			}
			$create_date = 0;
			if(isset($setup['create_date'])){
				$create_date = $setup['create_date'];
			}
			$author = 0;
			if(isset($setup['author'])){
				$author = $setup['author'];
			}
			$taxonomy_list = 0;
			if(isset($setup['taxonomy_list'])){
				$taxonomy_list = $setup['taxonomy_list'];
			}
			$taxonomy_link = 0;
			if(isset($setup['taxonomy_link'])){
				$taxonomy_link = $setup['taxonomy_link'];
			}
			$sticky_posts = 0;
			if(isset($setup['sticky_posts'])){
				$sticky_posts = $setup['sticky_posts'];
			}
			$number_of_all_items = 9;
			if(isset($setup['number_of_all_items'])){
				$number_of_all_items = $setup['number_of_all_items'];
			}
			if(!isset($setup['number_of_columns'])){
				$number_of_columns = 1;
			} else {
				$number_of_columns = $setup['number_of_columns'];
			}
			if(!isset($setup['number_of_rows'])){
				$number_of_rows = 1;
			} else {
				$number_of_rows = $setup['number_of_rows'];
			}
			$interval = '0';
			if(!empty($setup['interval'])){
				$interval = $setup['interval'];
			}
			$slider_pause = 'null';
			if(!empty($setup['slider_pause'])){
				$slider_pause = $setup['slider_pause'];
			}
			$grid_spacing = 10;
			if(isset($setup['grid_spacing'])){
				$grid_spacing = $setup['grid_spacing'];
			}
			$elements_order = '';
			if(isset($setup['elements_order'])){
				$elements_order = $setup['elements_order'];
			}
			$theme = 0;
			if(isset($setup['theme'])){
				$theme = $setup['theme'];
			}
			$theme_val = '';
			if($theme == 1){
				$theme_val = 'wpct-theme-1';
			} else if ($theme == 2){
				$theme_val = 'wpct-theme-2';
			} else if ($theme == 3){
			    $theme_val = 'wpct-theme-3';
			}
			
			$load_fa = 1;
			if(isset($setup['load_fa'])){
				$load_fa = $setup['load_fa'];
			}
			$load_animate = 1;
			if(isset($setup['load_animate'])){
				$load_animate = $setup['load_animate'];
			}
			$animate_css_effect = 'fadeInUp';
			if(isset($setup['animate_css_effect']) && $is_pro){
				$animate_css_effect = $setup['animate_css_effect'];
			}
			
			$slide_width = 100 / $number_of_columns;
			$unique_id = $args['widget_id'];
			if ( post_type_exists( $post_type ) ) {
				if ($number_of_all_items > $count_posts->publish){
					$number_of_all_items = $count_posts->publish;
				}
			}
			$order_posts = 'Date';
			if(isset($setup['order_posts'])){
				$order_posts = $setup['order_posts'];
			}
			$meta_key = '';
			if($order_posts == 'meta_value_num'){
				$meta_key = '_wpct_drag_drop_recent_posts_popular_posts_count';
			}
			$order_direction = 'DESC';
			if(isset($setup['order_direction'])){
				$order_direction = $setup['order_direction'];
			}
			$navigation_way = 1;
			if(isset($setup['navigation_way'])){
				$navigation_way = $setup['navigation_way'];
			}
            $title_widget = apply_filters('widget_title', $setup['title']);
            if ( empty($title_widget) ){
            	$title_widget = false;
				$before_title = false;
				$after_title = false;
            }
            echo $before_widget;
            echo $before_title;
            echo $title_widget;
            echo $after_title;
			$desc_limit = 100;
			if(isset($setup['desc_limit'])){
				$desc_limit = $setup['desc_limit'];
			}
			$show_thumbnail = 1;
			if(isset($setup['show_thumbnail'])){
				$show_thumbnail = $setup['show_thumbnail'];
			}
			$thumbnail_linkable = 1;
			if(isset($setup['thumbnail_linkable'])){
				$thumbnail_linkable = $setup['thumbnail_linkable'];
			}
			$image_floating = 'left';
			if(isset($setup['image_floating'])){
				$image_floating = $setup['image_floating'];
			}
			$image_size = 'thumbnail';
			if(isset($setup['image_size'])){
				$image_size = $setup['image_size'];
			}
			$category_id = '';
			if(isset($setup['category_id'])){
				$category_id = $setup['category_id'];
			}
			$even_odd = '';
			if ($number_of_columns % 2){
				$even_odd = 'odd-items-in-row';
			} else{
				$even_odd = 'even-items-in-row';
			}

			// get category for CPT
			$category_id_loop = '';
			$category_id_taxonomy = '';
			$tag_loop = '';
			$tax_query = '';
			$current_taxonomy ='';
			$field_value = '';
			
			// get taxonomies that belongs to $post_type
   			$taxonomy_objects = get_object_taxonomies( $post_type, 'names' );
			if($post_type == 'post' && !empty($category_id)){
				$taxonomy_to_check = get_term($category_id, $post_type_category);
				if(!empty($taxonomy_to_check)){
					$current_taxonomy = $taxonomy_to_check->taxonomy;
				}
			}
			
			// check for post type and post type taxonomy
			if(($post_type == 'post') && ($post_type_category == 'category')){
				if($current_taxonomy == $post_type_category){
					$category_id_loop = $category_id;
				} else {
					$category_id_loop = '';
				}
				$tag_loop = '';
				$tax_query = '';
			} else if(($post_type == 'post') && ($post_type_category == 'post_tag')){
				if($current_taxonomy == $post_type_category){
					$tag_loop = $category_id;
				} else {
					$tag_loop = '';
				}
				$category_id_loop = '';
				$tax_query = '';
			} else if(($post_type == 'post') && ($post_type_category == 'post_format ')){
				if($current_taxonomy == $post_type_category){
					$category_id_loop = $category_id;
				} else {
					$category_id_loop = '';
				}
				$tag_loop = '';
				$tax_query = '';
			} else if(($post_type != 'post')){
				if(!empty($category_id) && in_array($post_type_category, $taxonomy_objects)){
					$tax_query =
						array(
							array(
								'taxonomy' => ''.$post_type_category.'',
								'field'    => 'term_id',
								'terms'    => $category_id,
							),
						);
				} else {
					$tax_query = '';
				}
				$category_id_loop = '';
				$tag_loop = '';
			}
			
			// loop
			$loop = new WP_Query(array(
				'post_type' => ''.$post_type.'', 
				'posts_per_page' => ''.$number_of_all_items.'', 
				'ignore_sticky_posts' => ''.$sticky_posts.'', 
				'meta_key' => ''.$meta_key.'', 
				'orderby'=> ''.$order_posts.'', 
				'order' => ''.$order_direction.'',
				'cat' => $category_id_loop,
				'tax_query' => $tax_query,
				'tag_id' => $tag_loop
			));

			$counter = 0;
			$counter_elements_in_row = 0;
			$counter_bullets = 0;
			while ( $loop->have_posts() ) : $loop->the_post();
				$counter_bullets++;
			endwhile;
			$bullets_on_board = '';
			if (($navigation_way == 1) && ($counter_bullets > ($number_of_columns * $number_of_rows))){
				$bullets_on_board = 'bullets-enabled';
			}
			wp_reset_query();
			
			// check if CPT and category taxonomy exists, if they have relation
			if(post_type_exists( $post_type ) && taxonomy_exists( $post_type_category) && !in_array($post_type_category, $taxonomy_objects) && !empty($post_type_category)){
				echo __('Entered <strong>Post Type Taxonomy</strong> does not belong to <strong>Post Type</strong>.', 'wpct-drag-drop-recent-posts');
			} else if ( !post_type_exists( $post_type ) && (!taxonomy_exists( $post_type_category) && !empty($post_type_category))) {
			   echo __('Entered <strong>Post Type</strong> and <strong>Post Type Taxonomy</strong> does not exist.', 'wpct-drag-drop-recent-posts');
			} else if(!post_type_exists( $post_type )){
				echo __('Entered <strong>Post Type</strong> does not exist.', 'wpct-drag-drop-recent-posts');
			} else if(!taxonomy_exists( $post_type_category) && !empty($post_type_category)){
				echo __('Entered <strong>Post Type Taxonomy</strong> does not exist.', 'wpct-drag-drop-recent-posts');
			} else { ?>
			<div id="WPCTrecentPosts-<?php echo $unique_id; ?>" class="<?php echo $theme_val . ' ' . $is_pro_class; ?> wpct-recent-posts-outer carousel slide <?php if($navigation_way == 3){ echo 'vertical'; } ?> <?php echo $bullets_on_board; ?> columns-<?php echo $number_of_columns.' '.$even_odd; ?>" style="margin-left: -<?php echo $grid_spacing; ?>px;" data-ride="carousel" >
				<div class="carousel-inner image-<?php echo $image_floating; ?>" style="margin-bottom: -<?php echo $grid_spacing; ?>px;">
						<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
								<?php 
								$counter++;
								$post_title = get_the_title();
								if($counter_elements_in_row == $number_of_columns){
									$counter_elements_in_row = 0;
								}
								$counter_elements_in_row++;
								global $post;
								$permalink = get_permalink($post->ID);
								if ($number_of_columns * $number_of_rows == 1){ 
									if ($counter == 1){ ?>
										<div class="carousel-item active wpct-elements-in-row-<?php echo $number_of_columns; ?>">
									<?php } else { ?>
										<div class="carousel-item wpct-elements-in-row-<?php echo $number_of_columns; ?>">
									<?php }?>
								<?php } else{
									if (($counter % ($number_of_columns * $number_of_rows) == 1)){
											if ($counter == 1){ ?>
												<div class="carousel-item active wpct-elements-in-row-<?php echo $number_of_columns; ?>">
											<?php } else { ?>
												<div class="carousel-item wpct-elements-in-row-<?php echo $number_of_columns; ?>">
											<?php } ?>
									<?php }
								} ?>
								<div class="wpct-one-post-container el-<?php echo $counter; ?> wpct-elements-in-row-<?php echo $counter_elements_in_row; ?>" style="width: <?php echo $slide_width; ?>%;">
									<div class="wpct-one-post-container-inside">
										<div class="wpct-one-post-container-inside2" style="padding-left: <?php echo $grid_spacing; ?>px; padding-bottom: <?php echo $grid_spacing; ?>px;">
											<div class="wpct-one-post-container-inside3 clearfix">
												<div class="wpct-one-post-container-inside4 <?php echo $animate_css_effect; ?> animated <?php if ( has_post_thumbnail()){ echo 'image-on'; } ?>">
													
													<?php
														$elements_order_array = str_split($elements_order);
														foreach ( $elements_order_array as $element_order_item ){
															if($element_order_item == 1){
																if ( has_post_thumbnail() && $show_thumbnail == '1'){ // THUMBNAIL
																		$image_id = get_post_thumbnail_id();
																		$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
																		if(!empty($image_alt)){
																			$image_alternative_text = $image_alt;
																		} else {
																			$image_alternative_text = $post_title;
																		} ?>
																		<div class="wpct-thumbnail">
																		<?php if($thumbnail_linkable == 1){ ?>
																			<a class="wpct-thumbnail-link" href="<?php the_permalink(); ?>">
																		<?php } ?>
																				<?php echo the_post_thumbnail($image_size, array(
																					'alt'   => $image_alternative_text
																				)); ?>
																		<?php if($thumbnail_linkable == 1){ ?>
																			</a>
																		<?php } ?>
																		</div>
																<?php }
															} else if($element_order_item == 2){ // TITLE
															if($title_show == 1){ ?>
																	<h<?php echo $header_tag; ?> class="wpct-recent-posts-title-tag">
																		<?php if($title_linkable == 1){ ?>
																			<a href="<?php the_permalink(); ?>">
																		<?php } ?>
																				<?php the_title(); ?>
																		<?php if($title_linkable == 1){ ?>
																			</a>
																		<?php } ?>
																	</h<?php echo $header_tag; ?>>
																<?php }
															} else if($element_order_item == 3){ // DATE
																if($create_date == 1){ ?>
																	<span class="wpct-creation-date"><?php echo get_the_date(); ?></span>
																<?php }
															} else if($element_order_item == 4){ // AUTHOR
																if($author == 1){ ?>
																	<span class="wpct-author"><?php echo __('Created by: ', 'wpct-drag-drop-recent-posts').get_the_author(); ?></span>
																<?php }
															} else if($element_order_item == 5){ // POST CONTENT
																echo wpctDragAndDropRecentPostsExcerpt($desc_limit);
															} else if($element_order_item == 6){ // READMORE
																if($readmore == 1){ ?>
																	<div class="wpct-readmore-container">
																		<a class="readmore wpct-readmore" href="<?php echo $permalink; ?>"><?php echo __('Read more', 'wpct-drag-drop-recent-posts'); ?></a>
																	</div>
																<?php }
															} else if($element_order_item == 7){ // TAXONOMY LIST
																if($taxonomy_list == 1){ ?>
																	<div class="wpct-categories-list-container">
																		<?php echo '<span class="wpct-categories-list-label">'.__('Category: ', 'wpct-drag-drop-recent-posts').'</span>';
																		$terms = get_the_terms( $post->ID , $post_type_category );
																		echo '<ul class="wpct-categories-list">';
														                    foreach ( $terms as $term ) {
														                        $term_link = get_term_link( $term, $post_type_category );
														                        if( is_wp_error( $term_link ) )
														                        continue;
														                    	echo '<li><a href="' . $term_link . '">' . $term->name . '</a><span class="wpct-category-list-sep">, </span></li>';
														                    }
																		echo '</ul>';
																		?>
																	</div>
																<?php } ?>
															<?php } 
														}
													?>
												</div> 
											</div>
										</div>
									</div>
								</div>
								<?php if (($counter % ($number_of_columns * $number_of_rows)) == 0){ ?>
									</div>
								<?php } ?> 	
						<?php endwhile; ?>
						<?php if ((($counter % ($number_of_columns * $number_of_rows)) != 0) && ($counter >= ($number_of_columns * $number_of_rows))){ ?>
							</div>
						<?php } ?> 
						<?php wp_reset_query(); ?>
			</div>
			<?php 
			if($counter < ($number_of_columns * $number_of_rows)){ ?>
			</div>	
			<?php } ?>
			<?php if (($navigation_way == 1) && ($counter > ($number_of_columns * $number_of_rows))){ ?>
	        	<?php $counter2 = 0; ?>
		        <ol class="carousel-indicators" style="padding-left: <?php echo $grid_spacing; ?>px;">
		        	<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		        		<?php $counter2++; ?>
		        	<?php if (($counter2 % ($number_of_columns * $number_of_rows) == 1) || ($number_of_columns * $number_of_rows) == 1){
		        		if ($counter2 == 1){ ?>
	        			<li data-target="#WPCTrecentPosts-<?php echo $unique_id; ?>" data-slide-to="0" class="active" tabindex="0"></li>
					<?php } else { ?>
						<li data-target="#WPCTrecentPosts-<?php echo $unique_id; ?>" data-slide-to="<?php echo ($counter2 -1)/($number_of_columns * $number_of_rows); ?>" tabindex="0"></li>
					<?php } ?>	
				<?php } ?>
	            <?php endwhile; ?>
	            <?php wp_reset_query(); ?>
	        	</ol>  
		        <?php } else if (($navigation_way == 2) && ($counter > ($number_of_columns * $number_of_rows))) { ?>
		        	<div class="wpct-carousel-navigation-container left-right">
		        	
						<a class="carousel-control left" href="#WPCTrecentPosts-<?php echo $unique_id; ?>" data-slide="prev" <?php if($theme == 2 || $theme == 3){ echo 'style="left:' . $grid_spacing . 'px;"'; } ?>><i class="fa fa-chevron-left fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Previous', 'wpct-drag-drop-recent-posts'); ?></span></i></a>
						<a class="carousel-control right" href="#WPCTrecentPosts-<?php echo $unique_id; ?>" data-slide="next" ><i class="fa fa-chevron-right fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Next', 'wpct-drag-drop-recent-posts'); ?></span></i></a>
					</div>
		        <?php } else if (($navigation_way == 3) && ($counter > ($number_of_columns * $number_of_rows))) { ?>
		        	<div class="wpct-carousel-navigation-container up-down">
						<a class="carousel-control up" href="#WPCTrecentPosts-<?php echo $unique_id; ?>" data-slide="prev" ><i class="fa fa-chevron-up fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Previous', 'wpct-drag-drop-recent-posts'); ?></span></i></a>
						<a class="carousel-control down" href="#WPCTrecentPosts-<?php echo $unique_id; ?>" data-slide="next" ><i class="fa fa-chevron-down fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Next', 'wpct-drag-drop-recent-posts'); ?></span></i></a>
					</div>
		        <?php } ?>
		</div>
		<?php if(($taxonomy_link == 1) && (!empty($category_id))){
		    $values = array(
		      'orderby' => 'name',
		      'order' => 'ASC',
		      'taxonomy' => ''.$post_type_category.''
		     );
			$categories = get_categories($values); ?>
			<div class="wpct-more-from-category">
				<?php foreach ( $categories as $category ) { ?>
					<?php if( in_array($category->cat_ID, $category_id) ) { ?>
						<a href="<?php echo get_term_link($category); ?>"><?php echo __('More from', 'wpct-drag-drop-recent-posts'); ?> <?php echo $category->cat_name; ?></a><br />
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>
		<?php }

		// load CSS - frontend - BEGIN
		if (!(wp_style_is( 'animate.css', 'enqueued' )) && $load_animate) {
			wp_enqueue_style( 'animate', plugin_dir_url( __FILE__ ) . 'assets/css/animate.css' );
		}
		if (!(wp_style_is('all.css')) && $load_fa) {
			wp_enqueue_style( 'font-awesome-all',  plugin_dir_url( __FILE__ ) . 'assets/css/font-awesome/all.css', '', '5.10.2' );
		}
		if (!(wp_style_is('v4-shims.css')) && $load_fa) {
			wp_enqueue_style( 'font-awesome-v4-shims',  plugin_dir_url( __FILE__ ) . 'assets/css/font-awesome/v4-shims.css', '', '5.10.2' );
		}
		// load CSS - frontend - END
		
		echo $after_widget; ?>
		<script>
			jQuery(document).ready(
				function($){
					// run carousel
				    $('#WPCTrecentPosts-<?php echo $unique_id; ?>.wpct-recent-posts-outer').carousel({
				    	interval: <?php echo $interval; ?>,
						pause: "<?php echo $slider_pause; ?>"
					})
					$('#WPCTrecentPosts-<?php echo $unique_id; ?>.wpct-recent-posts-outer').on('slid.bs.carousel', function () {
					  jQuery('#WPCTrecentPosts-<?php echo $unique_id; ?> .wpct-one-post-container-inside4').addClass('animated');
					})
					// key ENTER nav
					$('#<?php echo $unique_id; ?> .carousel-indicators li').on('keyup', function(event){
						if(event.which == 13) { // enter key
							jQuery(this).click();
						}
					});
					// add and remove 'animated' class after animation is complete - Animate CSS
					let carouselContainer = document.querySelectorAll('#WPCTrecentPosts-<?php echo $unique_id; ?> .wpct-one-post-container-inside4');
					for (let element of carouselContainer) {
						jQuery(this).addClass('animated');
						element.addEventListener('animationend', function() {
							jQuery(this).removeClass('animated');
						});
					}
					
					if($('#WPCTrecentPosts-<?php echo $unique_id; ?>.wpct-recent-posts-outer').hasClass('wpct-theme-2') || $('#WPCTrecentPosts-<?php echo $unique_id; ?>.wpct-recent-posts-outer').hasClass('wpct-theme-3')){
						$('#WPCTrecentPosts-<?php echo $unique_id; ?>.wpct-recent-posts-outer .wpct-thumbnail').each(function() {
						  $( this ).insertBefore($( this ).parent());
						  //$( this ).after( "<div>" );
						  //$( this ).parent().append( "</div>" );
						  //$( this ).addClass('ccc');
						});
					}
				}
			);
		</script>
		<?php }

        //Admin Form
        public function form($setup)
        {
            // check if Pro features should be enabled   
            if ( is_plugin_active( 'wpct-drag-drop-recent-posts-pro/wpct-drag-drop-recent-posts-pro.php' ) ) {
                $is_pro = true;
            } else{
                $is_pro = false;
            }
            
            $setup = wp_parse_args( (array) $setup, array('title' => __('MISC Posts', 'wpct-drag-drop-recent-posts'),
            	'title_show' => '1',
            	'title_linkable' => '1',
            	'readmore' => '1',
            	'header_tag' => '3',
            	'create_date' => '1',
            	'author' => '1',
            	'taxonomy_list' => '1',
            	'post_type' => 'post',
            	'post_type_category' => 'category',
            	'taxonomy_link' => '0',
            	'sticky_posts' => '0',
                'number_of_all_items' => '9',
                'number_of_columns' => '1',
                'number_of_rows' => '3',
                'order_posts' => 'Date',
                'order_direction' => 'DESC',
                'navigation_way' => '1',
                'title' => __('WPCT Drag & Drop Recent Posts', 'wpct-drag-drop-recent-posts'),
                'desc_limit' => '100',
                'image_floating' => 'left',
                'show_thumbnail' => '1',
                'thumbnail_linkable' => '1',
                'image_size' => 'thumbnail',
                'grid_spacing' => '10',
                'interval' => '5000',
                'slider_pause' => 'null',
                'elements_order' => '1234567',
                'theme' => '0',
                'load_fa' => '1',
                'load_animate' => '1',
                'animate_css_effect' => 'fadeInUp',
                'category_id' => '' ) );
			$title_widget= esc_attr($setup['title']);
			$post_type = $setup['post_type'];
			$post_type_category = $setup['post_type_category'];
			$taxonomy_link = $setup['taxonomy_link'];
			$sticky_posts = $setup['sticky_posts'];
			$title_show = $setup['title_show'];
			$title_linkable = $setup['title_linkable'];
			$readmore = $setup['readmore'];
			$header_tag = $setup['header_tag'];
			$create_date = $setup['create_date'];
			$author = $setup['author'];
			$taxonomy_list = $setup['taxonomy_list'];
			$number_of_all_items = $setup['number_of_all_items'];
            $order_posts = $setup['order_posts'];
			$desc_limit = $setup['desc_limit'];
			$show_thumbnail = $setup['show_thumbnail'];
			$thumbnail_linkable = $setup['thumbnail_linkable'];
			$image_floating = $setup['image_floating'];
			$image_size = $setup['image_size'];
			$category_id = $setup['category_id'];
			$number_of_rows = $setup['number_of_rows'];
			$number_of_columns = $setup['number_of_columns'];
			$grid_spacing = $setup['grid_spacing'];
			$interval = $setup['interval'];
			$slider_pause = $setup['slider_pause'];
			$elements_order = $setup['elements_order'];
			$theme = $setup['theme'];
			$load_fa = $setup['load_fa'];
			$load_animate = $setup['load_animate'];
			$animate_css_effect = $setup['animate_css_effect'];
            $elements_order_array = str_split($elements_order);
            ?>
			<p id="wpct-sortable-console"><?php echo $elements_order; ?></p>

			<p>
			    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title_widget; ?>" />
            </p>

            <p>
                <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" type="text" value="<?php echo $post_type; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('post_type_category'); ?>"><?php _e('Post Type Taxonomy', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('post_type_category'); ?>" name="<?php echo $this->get_field_name('post_type_category'); ?>" type="text" value="<?php echo $post_type_category; ?>" />
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('category_id'); ?>"><?php _e('Taxonomy items (empty items are not displayed)', 'wpct-drag-drop-recent-posts'); ?></label>
				<?php
				    $values = array(
				      'orderby' => 'name',
				      'order' => 'ASC',
				      'taxonomy' => ''.$post_type_category.''
				     );
					$categories = get_categories($values); 
					if( !empty($categories) ) :
						?>
							<?php
								echo '<div style="max-height:150px; overflow:auto; border:1px solid #dfdfdf; padding:5px; margin-bottom:5px;">';
								echo '<ul class="wpct-recent-posts-id-list categories-id-list">';
								foreach ( $categories as $category ) {
									if( $category_id ) {
										$checked = in_array($category->cat_ID, $category_id) ? ' checked="checked"' : '';
									} else {
										$checked = '';
									}
									$option = '<li><input type="checkbox" name="' . $this->get_field_name('category_id') . '[]" id="page-' . $category->cat_ID . '" value="' . $category->cat_ID . '" ' . $checked . '>';
									$option .= '<span>(ID: ' . $category->cat_ID . ') ' . $category->cat_name . '</span></li>';
									echo $option;
								}
								echo '</ul>';
								echo '</div>';
							?>
				<?php endif; ?>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('taxonomy_link'); ?>"><?php _e('Archive link at the bottom (only if taxonomy was selected)', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('taxonomy_link'); ?>" id="<?php echo $this->get_field_id('taxonomy_link'); ?>">
                    <option value="0"<?php selected( $setup['taxonomy_link'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['taxonomy_link'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('sticky_posts'); ?>"><?php _e('Force display sticky posts (only for posts)', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('sticky_posts'); ?>" id="<?php echo $this->get_field_id('sticky_posts'); ?>">
                    <option value="0"<?php selected( $setup['sticky_posts'], '0' ); ?>><?php _e('Yes', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['sticky_posts'], '1' ); ?>><?php _e('No', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('number_of_columns'); ?>"><?php _e('Number of items in row', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('number_of_columns'); ?>" name="<?php echo $this->get_field_name('number_of_columns'); ?>" type="number" min="1" value="<?php echo $number_of_columns; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('number_of_rows'); ?>"><?php _e('Number of rows', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('number_of_rows'); ?>" name="<?php echo $this->get_field_name('number_of_rows'); ?>" type="number" min="1" value="<?php echo $number_of_rows; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('number_of_all_items'); ?>"><?php _e('Number of all items', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('number_of_all_items'); ?>" name="<?php echo $this->get_field_name('number_of_all_items'); ?>" type="number" min="1" value="<?php echo $number_of_all_items; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('title_show'); ?>"><?php _e('Post Title', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('title_show'); ?>" id="<?php echo $this->get_field_id('title_show'); ?>">
                    <option value="0"<?php selected( $setup['title_show'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['title_show'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('title_linkable'); ?>"><?php _e('Post Title Clickable', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('title_linkable'); ?>" id="<?php echo $this->get_field_id('title_linkable'); ?>">
                    <option value="0"<?php selected( $setup['title_linkable'], '0' ); ?>><?php _e('No', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['title_linkable'], '1' ); ?>><?php _e('Yes', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('header_tag'); ?>"><?php _e('Header Tag For Title', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('header_tag'); ?>" id="<?php echo $this->get_field_id('header_tag'); ?>">
                    <option value="1"<?php selected( $setup['header_tag'], '1' ); ?>><?php _e('H1', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="2"<?php selected( $setup['header_tag'], '2' ); ?>><?php _e('H2', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="3"<?php selected( $setup['header_tag'], '3' ); ?>><?php _e('H3', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="4"<?php selected( $setup['header_tag'], '4' ); ?>><?php _e('H4', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="5"<?php selected( $setup['header_tag'], '5' ); ?>><?php _e('H5', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="6"<?php selected( $setup['header_tag'], '6' ); ?>><?php _e('H6', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('create_date'); ?>"><?php _e('Creation Date', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('create_date'); ?>" id="<?php echo $this->get_field_id('create_date'); ?>">
                    <option value="0"<?php selected( $setup['create_date'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['create_date'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('author'); ?>"><?php _e('Author', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('author'); ?>" id="<?php echo $this->get_field_id('author'); ?>">
                    <option value="0"<?php selected( $setup['author'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['author'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('taxonomy_list'); ?>"><?php _e('Post type taxonomies', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('taxonomy_list'); ?>" id="<?php echo $this->get_field_id('taxonomy_list'); ?>">
                    <option value="0"<?php selected( $setup['taxonomy_list'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['taxonomy_list'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('readmore'); ?>"><?php _e('Readmore', 'wpct-drag-drop-recent-posts'); ?></label>
                <select class="wpct-recent-posts-source-select" name="<?php echo $this->get_field_name('readmore'); ?>" id="<?php echo $this->get_field_id('readmore'); ?>">
                    <option value="0"<?php selected( $setup['readmore'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['readmore'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('order_direction'); ?>"><?php _e('Order Direction', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('order_direction'); ?>" id="<?php echo $this->get_field_id('order_direction'); ?>">
                    <option value="ASC"<?php selected( $setup['order_direction'], 'ASC' ); ?>><?php _e('ASC', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="DESC"<?php selected( $setup['order_direction'], 'DESC' ); ?>><?php _e('DESC', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('order_posts'); ?>"><?php _e('Ordering', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('order_posts'); ?>" id="<?php echo $this->get_field_id('order_posts'); ?>">
                    <option value="date"<?php selected( $setup['order_posts'], 'date' ); ?>><?php _e('Date', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="title"<?php selected( $setup['order_posts'], 'title' ); ?>><?php _e('Title', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="comment_count"<?php selected( $setup['order_posts'], 'comment_count' ); ?>><?php _e('Most commented', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="meta_value_num"<?php selected( $setup['order_posts'], 'meta_value_num' ); ?>><?php _e('Most read', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('navigation_way'); ?>"><?php _e('Navigation', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('navigation_way'); ?>" id="<?php echo $this->get_field_id('navigation_way'); ?>">
                	<option value="0"<?php selected( $setup['navigation_way'], '0' ); ?>><?php _e('None', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['navigation_way'], '1' ); ?>><?php _e('Bullets', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="2"<?php selected( $setup['navigation_way'], '2' ); ?>><?php _e('Arrows (prev/next)', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="3"<?php selected( $setup['navigation_way'], '3' ); ?>><?php _e('Arrow (up/down)', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('desc_limit'); ?>"><?php _e('Description Limit (chars)', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('desc_limit'); ?>" name="<?php echo $this->get_field_name('desc_limit'); ?>" type="text" value="<?php echo $desc_limit; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('image_floating'); ?>"><?php _e('Image Floating', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('image_floating'); ?>" id="<?php echo $this->get_field_id('image_floating'); ?>">
                    <option value="left"<?php selected( $setup['image_floating'], 'left' ); ?>><?php _e('Left', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="right"<?php selected( $setup['image_floating'], 'right' ); ?>><?php _e('Right', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="none"<?php selected( $setup['image_floating'], 'none' ); ?>><?php _e('None', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php _e('Thumbnail', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('show_thumbnail'); ?>" id="<?php echo $this->get_field_id('show_thumbnail'); ?>">
                    <option value="0"<?php selected( $setup['show_thumbnail'], '0' ); ?>><?php _e('Hide', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['show_thumbnail'], '1' ); ?>><?php _e('Show', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('thumbnail_linkable'); ?>"><?php _e('Thumbnail Clickable', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('thumbnail_linkable'); ?>" id="<?php echo $this->get_field_id('thumbnail_linkable'); ?>">
                    <option value="0"<?php selected( $setup['thumbnail_linkable'], '0' ); ?>><?php _e('No', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['thumbnail_linkable'], '1' ); ?>><?php _e('Yes', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('interval'); ?>"><?php _e('Interval in ms ( 0 - autoplay is disabled )', 'wpct-drag-drop-recent-posts'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('interval'); ?>" name="<?php echo $this->get_field_name('interval'); ?>" type="text" value="<?php echo $interval; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('slider_pause'); ?>"><?php _e('Pause on hover', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('slider_pause'); ?>" id="<?php echo $this->get_field_id('slider_pause'); ?>">
                	<option value="hover"<?php selected( $setup['slider_pause'], 'hover' ); ?>><?php _e('Yes', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="null"<?php selected( $setup['slider_pause'], 'null' ); ?>><?php _e('No', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('image_size'); ?>"><?php _e('Image Size', 'wpct-drag-drop-recent-posts'); ?></label>
                <select name="<?php echo $this->get_field_name('image_size'); ?>" id="<?php echo $this->get_field_id('image_size'); ?>">
                    <option value="thumbnail"<?php selected( $setup['image_size'], 'thumbnail' ); ?>><?php _e('Thumbnail', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="medium"<?php selected( $setup['image_size'], 'medium' ); ?>><?php _e('Medium', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="large"<?php selected( $setup['image_size'], 'large' ); ?>><?php _e('Large', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('grid_spacing'); ?>"><?php _e('Grid Spacing (px)', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('grid_spacing'); ?>" name="<?php echo $this->get_field_name('grid_spacing'); ?>" type="text" value="<?php echo $grid_spacing; ?>" />
            </p>
            <p class="wpct-elements-order-container">
                <label for="<?php echo $this->get_field_id('elements_order'); ?>"><?php _e('Elements order', 'wpct-drag-drop-recent-posts'); ?></label>
                <input class="widefat elements-order-input" id="<?php echo $this->get_field_id('elements_order'); ?>" name="<?php echo $this->get_field_name('elements_order'); ?>" value="<?php echo $elements_order; ?>" />
            </p>
            <p>
            	<label>
            		<?php _e('Order for item elements', 'wpct-drag-drop-recent-posts'); ?>
            	</label>
            </p>
			<ul id="wpct-sortable">
				<?php foreach ( $elements_order_array as $element_order_item ){
					if($element_order_item == 1){
						$element_text = 'Thumbnail';
					} else if($element_order_item == 2){
						$element_text = 'Title';
					} else if($element_order_item == 3){
						$element_text = 'Date';
					} else if($element_order_item == 4){
						$element_text = 'Author';
					} else if($element_order_item == 5){
						$element_text = 'Post Content';
					} else if($element_order_item == 6){
						$element_text = 'Readmore';
					} else if($element_order_item == 7){
						$element_text = 'Taxonomy List';
					} ?>
					<li data-element-id="<?php echo $element_order_item; ?>"><?php _e($element_text, 'wpct-drag-drop-recent-posts'); ?></li>
			    <?php } ?>
			</ul>
            <p>
                <label for="<?php echo $this->get_field_id('theme'); ?>"><?php _e('Theme', 'wpct-drag-drop-recent-posts'); ?></label>
                <select <?php if(!$is_pro){ echo 'disabled'; }?> name="<?php echo $this->get_field_name('theme'); ?>" id="<?php echo $this->get_field_id('theme'); ?>">
                    <option value="0"<?php selected( $setup['theme'], '0' ); ?>><?php _e('Default styles', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['theme'], '1' ); ?>><?php _e('Theme 1', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="2"<?php selected( $setup['theme'], '2' ); ?>><?php _e('Theme 2', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="3"<?php selected( $setup['theme'], '3' ); ?>><?php _e('Theme 3', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('load_fa'); ?>"><?php _e('Load Font Awesome 5', 'wpct-drag-drop-recent-posts'); ?></label>
                <select <?php if(!$is_pro){ echo 'disabled'; }?> name="<?php echo $this->get_field_name('load_fa'); ?>" id="<?php echo $this->get_field_id('load_fa'); ?>">
                    <option value="0"<?php selected( $setup['load_fa'], '0' ); ?>><?php _e('No', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['load_fa'], '1' ); ?>><?php _e('Yes', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('load_animate'); ?>"><?php _e('Load Animate CSS', 'wpct-drag-drop-recent-posts'); ?></label>
                <select <?php if(!$is_pro){ echo 'disabled'; }?> name="<?php echo $this->get_field_name('load_animate'); ?>" id="<?php echo $this->get_field_id('load_animate'); ?>">
                    <option value="0"<?php selected( $setup['load_animate'], '0' ); ?>><?php _e('No', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="1"<?php selected( $setup['load_animate'], '1' ); ?>><?php _e('Yes', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('animate_css_effect'); ?>"><?php _e('Animate CSS Effect', 'wpct-drag-drop-recent-posts'); ?></label>
                <select <?php if(!$is_pro){ echo 'disabled'; }?> name="<?php echo $this->get_field_name('animate_css_effect'); ?>" id="<?php echo $this->get_field_id('animate_css_effect'); ?>">
                    <option value="bounce"<?php selected( $setup['animate_css_effect'], 'bounce' ); ?>><?php _e('bounce', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="flash"<?php selected( $setup['animate_css_effect'], 'flash' ); ?>><?php _e('flash', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="pulse"<?php selected( $setup['animate_css_effect'], 'pulse' ); ?>><?php _e('pulse', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="rubberBand"<?php selected( $setup['animate_css_effect'], 'rubberBand' ); ?>><?php _e('rubberBand', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="shake"<?php selected( $setup['animate_css_effect'], 'shake' ); ?>><?php _e('shake', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="swing"<?php selected( $setup['animate_css_effect'], 'swing' ); ?>><?php _e('swing', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="tada"<?php selected( $setup['animate_css_effect'], 'tada' ); ?>><?php _e('tada', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="wobble"<?php selected( $setup['animate_css_effect'], 'wobble' ); ?>><?php _e('wobble', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="jello"<?php selected( $setup['animate_css_effect'], 'jello' ); ?>><?php _e('jello', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="heartBeat"<?php selected( $setup['animate_css_effect'], 'heartBeat' ); ?>><?php _e('heartBeat', 'wpct-drag-drop-recent-posts'); ?></option>
                    
                    <option value="bounceIn"<?php selected( $setup['animate_css_effect'], 'bounceIn' ); ?>><?php _e('bounceIn', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="bounceInDown"<?php selected( $setup['animate_css_effect'], 'bounceInDown' ); ?>><?php _e('bounceInDown', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="bounceInLeft"<?php selected( $setup['animate_css_effect'], 'bounceInLeft' ); ?>><?php _e('bounceInLeft', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="bounceInRight"<?php selected( $setup['animate_css_effect'], 'bounceInRight' ); ?>><?php _e('bounceInRight', 'wpct-drag-drop-recent-posts'); ?></option>
                    <option value="bounceInUp"<?php selected( $setup['animate_css_effect'], 'bounceInUp' ); ?>><?php _e('bounceInUp', 'wpct-drag-drop-recent-posts'); ?></option>

					<option value="bounceOut"<?php selected( $setup['animate_css_effect'], 'bounceOut' ); ?>><?php _e('bounceOut', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="bounceOutDown"<?php selected( $setup['animate_css_effect'], 'bounceOutDown' ); ?>><?php _e('bounceOutDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="bounceOutLeft"<?php selected( $setup['animate_css_effect'], 'bounceOutLeft' ); ?>><?php _e('bounceOutLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="bounceOutRight"<?php selected( $setup['animate_css_effect'], 'bounceOutRight' ); ?>><?php _e('bounceOutRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="bounceOutUp"<?php selected( $setup['animate_css_effect'], 'bounceOutUp' ); ?>><?php _e('bounceOutUp', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="fadeIn"<?php selected( $setup['animate_css_effect'], 'fadeIn' ); ?>><?php _e('fadeIn', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInDown"<?php selected( $setup['animate_css_effect'], 'fadeInDown' ); ?>><?php _e('fadeInDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInDownBig"<?php selected( $setup['animate_css_effect'], 'fadeInDownBig' ); ?>><?php _e('fadeInDownBig', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInLeft"<?php selected( $setup['animate_css_effect'], 'fadeInLeft' ); ?>><?php _e('fadeInLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInLeftBig"<?php selected( $setup['animate_css_effect'], 'fadeInLeftBig' ); ?>><?php _e('fadeInLeftBig', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInRight"<?php selected( $setup['animate_css_effect'], 'fadeInRight' ); ?>><?php _e('fadeInRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInRightBig"<?php selected( $setup['animate_css_effect'], 'fadeInRightBig' ); ?>><?php _e('fadeInRightBig', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInUp"<?php selected( $setup['animate_css_effect'], 'fadeInUp' ); ?>><?php _e('fadeInUp', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeInUpBig"<?php selected( $setup['animate_css_effect'], 'fadeInUpBig' ); ?>><?php _e('fadeInUpBig', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="fadeOut"<?php selected( $setup['animate_css_effect'], 'fadeOut' ); ?>><?php _e('fadeOut', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutDown"<?php selected( $setup['animate_css_effect'], 'fadeOutDown' ); ?>><?php _e('fadeOutDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutDownBig"<?php selected( $setup['animate_css_effect'], 'fadeOutDownBig' ); ?>><?php _e('fadeOutDownBig', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutLeft"<?php selected( $setup['animate_css_effect'], 'fadeOutLeft' ); ?>><?php _e('fadeOutLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutLeftBig"<?php selected( $setup['animate_css_effect'], 'fadeOutLeftBig' ); ?>><?php _e('fadeOutLeftBig', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutRight"<?php selected( $setup['animate_css_effect'], 'fadeOutRight' ); ?>><?php _e('fadeOutRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutRightBig"<?php selected( $setup['animate_css_effect'], 'fadeOutRightBig' ); ?>><?php _e('fadeOutRightBig', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutUp"<?php selected( $setup['animate_css_effect'], 'fadeOutUp' ); ?>><?php _e('fadeOutUp', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="fadeOutUpBig"<?php selected( $setup['animate_css_effect'], 'fadeOutUpBig' ); ?>><?php _e('fadeOutUpBig', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="flip"<?php selected( $setup['animate_css_effect'], 'flip' ); ?>><?php _e('flip', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="flipInX"<?php selected( $setup['animate_css_effect'], 'flipInX' ); ?>><?php _e('flipInX', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="flipInY"<?php selected( $setup['animate_css_effect'], 'flipInY' ); ?>><?php _e('flipInY', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="flipOutX"<?php selected( $setup['animate_css_effect'], 'flipOutX' ); ?>><?php _e('flipOutX', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="flipOutY"<?php selected( $setup['animate_css_effect'], 'flipOutY' ); ?>><?php _e('flipOutY', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="lightSpeedIn"<?php selected( $setup['animate_css_effect'], 'lightSpeedIn' ); ?>><?php _e('lightSpeedIn', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="lightSpeedOut"<?php selected( $setup['animate_css_effect'], 'lightSpeedOut' ); ?>><?php _e('lightSpeedOut', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="rotateIn"<?php selected( $setup['animate_css_effect'], 'rotateIn' ); ?>><?php _e('rotateIn', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateInDownLeft"<?php selected( $setup['animate_css_effect'], 'rotateInDownLeft' ); ?>><?php _e('rotateInDownLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateInDownRight"<?php selected( $setup['animate_css_effect'], 'rotateInDownRight' ); ?>><?php _e('rotateInDownRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateInUpLeft"<?php selected( $setup['animate_css_effect'], 'rotateInUpLeft' ); ?>><?php _e('rotateInUpLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateInUpRight"<?php selected( $setup['animate_css_effect'], 'rotateInUpRight' ); ?>><?php _e('rotateInUpRight', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="rotateOut"<?php selected( $setup['animate_css_effect'], 'rotateOut' ); ?>><?php _e('rotateOut', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateOutDownLeft"<?php selected( $setup['animate_css_effect'], 'rotateOutDownLeft' ); ?>><?php _e('rotateOutDownLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateOutDownRight"<?php selected( $setup['animate_css_effect'], 'rotateOutDownRight' ); ?>><?php _e('rotateOutDownRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateOutUpLeft"<?php selected( $setup['animate_css_effect'], 'rotateOutUpLeft' ); ?>><?php _e('rotateOutUpLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rotateOutUpRight"<?php selected( $setup['animate_css_effect'], 'rotateOutUpRight' ); ?>><?php _e('rotateOutUpRight', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="slideInUp"<?php selected( $setup['animate_css_effect'], 'slideInUp' ); ?>><?php _e('slideInUp', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="slideInDown"<?php selected( $setup['animate_css_effect'], 'slideInDown' ); ?>><?php _e('slideInDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="slideInLeft"<?php selected( $setup['animate_css_effect'], 'slideInLeft' ); ?>><?php _e('slideInLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="slideInRight"<?php selected( $setup['animate_css_effect'], 'slideInRight' ); ?>><?php _e('slideInRight', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="slideOutUp"<?php selected( $setup['animate_css_effect'], 'slideOutUp' ); ?>><?php _e('slideOutUp', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="slideOutDown"<?php selected( $setup['animate_css_effect'], 'slideOutDown' ); ?>><?php _e('slideOutDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="slideOutLeft"<?php selected( $setup['animate_css_effect'], 'slideOutLeft' ); ?>><?php _e('slideOutLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="slideOutRight"<?php selected( $setup['animate_css_effect'], 'slideOutRight' ); ?>><?php _e('slideOutRight', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="zoomIn"<?php selected( $setup['animate_css_effect'], 'zoomIn' ); ?>><?php _e('zoomIn', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomInDown"<?php selected( $setup['animate_css_effect'], 'zoomInDown' ); ?>><?php _e('zoomInDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomInLeft"<?php selected( $setup['animate_css_effect'], 'zoomInLeft' ); ?>><?php _e('zoomInLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomInRight"<?php selected( $setup['animate_css_effect'], 'zoomInRight' ); ?>><?php _e('zoomInRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomInUp"<?php selected( $setup['animate_css_effect'], 'zoomInUp' ); ?>><?php _e('zoomInUp', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="zoomOut"<?php selected( $setup['animate_css_effect'], 'zoomOut' ); ?>><?php _e('zoomOut', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomOutDown"<?php selected( $setup['animate_css_effect'], 'zoomOutDown' ); ?>><?php _e('zoomOutDown', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomOutLeft"<?php selected( $setup['animate_css_effect'], 'zoomOutLeft' ); ?>><?php _e('zoomOutLeft', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomOutRight"<?php selected( $setup['animate_css_effect'], 'zoomOutRight' ); ?>><?php _e('zoomOutRight', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="zoomOutUp"<?php selected( $setup['animate_css_effect'], 'zoomOutUp' ); ?>><?php _e('zoomOutUp', 'wpct-drag-drop-recent-posts'); ?></option>
					
					<option value="hinge"<?php selected( $setup['animate_css_effect'], 'hinge' ); ?>><?php _e('hinge', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="jackInTheBox"<?php selected( $setup['animate_css_effect'], 'jackInTheBox' ); ?>><?php _e('jackInTheBox', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rollIn"<?php selected( $setup['animate_css_effect'], 'rollIn' ); ?>><?php _e('rollIn', 'wpct-drag-drop-recent-posts'); ?></option>
					<option value="rollOut"<?php selected( $setup['animate_css_effect'], 'rollOut' ); ?>><?php _e('rollOut', 'wpct-drag-drop-recent-posts'); ?></option>
                </select>
            </p>
        <?php }

        //Update widget
        public function update($new_setup, $old_setup){
            $setup=$old_setup;
            $setup['title'] = strip_tags($new_setup['title']);
			$setup['post_type'] = $new_setup['post_type'];
			$setup['post_type_category'] = $new_setup['post_type_category'];
			$setup['taxonomy_link'] = $new_setup['taxonomy_link'];
			$setup['sticky_posts'] = $new_setup['sticky_posts'];
			$setup['title_show'] = $new_setup['title_show'];
			$setup['title_linkable'] = $new_setup['title_linkable'];
			$setup['readmore'] = $new_setup['readmore'];
			$setup['header_tag'] = $new_setup['header_tag'];
			$setup['create_date'] = $new_setup['create_date'];
			$setup['author'] = $new_setup['author'];
			$setup['taxonomy_list'] = $new_setup['taxonomy_list'];
			$setup['number_of_all_items']  = $new_setup['number_of_all_items'];
			$setup['number_of_columns']  = $new_setup['number_of_columns'];
			$setup['number_of_rows']  = $new_setup['number_of_rows'];
			$setup['order_posts']  = $new_setup['order_posts'];
			$setup['order_direction']  = $new_setup['order_direction'];
			$setup['navigation_way']  = $new_setup['navigation_way'];
			$setup['desc_limit']  = strip_tags($new_setup['desc_limit']);
			$setup['image_floating']  = $new_setup['image_floating'];
			$setup['show_thumbnail']  = $new_setup['show_thumbnail'];
			$setup['thumbnail_linkable']  = $new_setup['thumbnail_linkable'];
			$setup['image_size']  = $new_setup['image_size'];
			$setup['category_id'] = $new_setup['category_id'];
			$setup['grid_spacing']  = strip_tags($new_setup['grid_spacing']);
			$setup['interval']  = strip_tags($new_setup['interval']);
			$setup['slider_pause']  = strip_tags($new_setup['slider_pause']);
			$setup['elements_order']  = strip_tags($new_setup['elements_order']);
			$setup['theme']  = strip_tags($new_setup['theme']);
			$setup['load_fa']  = strip_tags($new_setup['load_fa']);
			$setup['load_animate']  = strip_tags($new_setup['load_animate']);
			$setup['animate_css_effect']  = strip_tags($new_setup['animate_css_effect']);
            return $setup;
        }
    }
}

// add CSS - frontend
if ( ! function_exists( 'wpctDragAndDropRecentPostsLoadCSS' ) ) {
	function wpctDragAndDropRecentPostsLoadCSS() {
		wp_enqueue_style( 'wpct-drag-drop-recent-posts', plugin_dir_url( __FILE__ ) . '/assets/css/wpct-drag-drop-recent-posts-frontend.css' );
	}
	add_action( 'wp_enqueue_scripts', 'wpctDragAndDropRecentPostsLoadCSS', 20 );
}

// add JS - frontend
if ( ! function_exists( 'wpctDragAndDropRecentPostsLoadJS' ) ) {
	function wpctDragAndDropRecentPostsLoadJS(){
		wp_enqueue_script('jquery', true);
		if (!(wp_script_is( 'bootstrap.js', 'enqueued' ) || wp_script_is( 'bootstrap.min.js', 'enqueued' ))) {
			wp_enqueue_script( 'bootstrap', plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap.min.js', array('jquery'), '4.4.0', true );
		}
	}
	add_action( 'wp_enqueue_scripts', 'wpctDragAndDropRecentPostsLoadJS' );
}

//add JS and CSS - backend
if ( ! function_exists( 'wpctDragAndDropRecentPostsBackendCssJs' ) ) {
	function wpctDragAndDropRecentPostsBackendCssJs(){
		wp_enqueue_script('jquery-ui-sortable');
		wp_register_script( 'wpct-drag-drop-recent-posts-backend', plugin_dir_url( __FILE__ ) . 'assets/js/wpct-drag-drop-recent-posts-backend.js', array('jquery'), '1.0', false );
		wp_enqueue_script('wpct-drag-drop-recent-posts-backend');
		wp_enqueue_style('wpct-drag-drop-recent-posts-backend', plugin_dir_url( __FILE__ ) . 'assets/css/wpct-drag-drop-recent-posts-backend.css');
	}
	add_action('admin_enqueue_scripts','wpctDragAndDropRecentPostsBackendCssJs', 99);
}

// register widget
if ( ! function_exists( 'wpctDragAndDropRecentPostsRegisterWidget' ) ) {
	function wpctDragAndDropRecentPostsRegisterWidget (){
	    return register_widget('WPCTrecentPosts');
	}
	add_action ('widgets_init', 'wpctDragAndDropRecentPostsRegisterWidget');
}

// enable translations
if ( ! function_exists( 'wpctDragAndDropRecentPostsTextDomain' ) ) {
	function wpctDragAndDropRecentPostsTextDomain() {
		load_plugin_textdomain( 'wpct-drag-drop-recent-posts', false, plugin_dir_url( __FILE__ ) . '/languages/' );
	}
	add_action('plugins_loaded', 'wpctDragAndDropRecentPostsTextDomain');
}
