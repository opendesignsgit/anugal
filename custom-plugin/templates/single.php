<?php
/**
 * The template for displaying single posts.
 *
 * @package Salient WordPress Theme
 * @version 11.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$nectar_options    = get_nectar_theme_options();
$fullscreen_header = ( ! empty( $nectar_options['blog_header_type'] ) && 'fullscreen' === $nectar_options['blog_header_type'] && is_singular( 'post' ) ) ? true : false;
$blog_header_type  = ( ! empty( $nectar_options['blog_header_type'] ) ) ? $nectar_options['blog_header_type'] : 'default';
$theme_skin        = ( ! empty( $nectar_options['theme-skin'] ) ) ? $nectar_options['theme-skin'] : 'original';
$header_format     = ( ! empty( $nectar_options['header_format'] ) ) ? $nectar_options['header_format'] : 'default';
if ( 'centered-menu-bottom-bar' === $header_format ) {
	$theme_skin = 'material';
}

$hide_sidebar                      = ( ! empty( $nectar_options['blog_hide_sidebar'] ) ) ? $nectar_options['blog_hide_sidebar'] : '0';
$blog_type                         = $nectar_options['blog_type'];
$blog_social_style                 = ( get_option( 'salient_social_button_style' ) ) ? get_option( 'salient_social_button_style' ) : 'fixed';
$enable_ss                         = ( ! empty( $nectar_options['blog_enable_ss'] ) ) ? $nectar_options['blog_enable_ss'] : 'false';
$remove_single_post_date           = ( ! empty( $nectar_options['blog_remove_single_date'] ) ) ? $nectar_options['blog_remove_single_date'] : '0';
$remove_single_post_author         = ( ! empty( $nectar_options['blog_remove_single_author'] ) ) ? $nectar_options['blog_remove_single_author'] : '0';
$remove_single_post_comment_number = ( ! empty( $nectar_options['blog_remove_single_comment_number'] ) ) ? $nectar_options['blog_remove_single_comment_number'] : '0';
$remove_single_post_nectar_love    = ( ! empty( $nectar_options['blog_remove_single_nectar_love'] ) ) ? $nectar_options['blog_remove_single_nectar_love'] : '0';
$container_wrap_class              = ( true === $fullscreen_header ) ? 'container-wrap fullscreen-blog-header' : 'container-wrap';

// Post header.
if ( have_posts() ) :
	while ( have_posts() ) :
		
		the_post();
		nectar_page_header( $post->ID );

endwhile;
endif;


// Post header fullscreen style when no image is supplied.
if ( true === $fullscreen_header ) {
	//get_template_part( 'includes/partials/single-post/post-header-no-img-fullscreen' );
}

if('case-study' === get_post_type()){
?>

<div id="page-header-wrap" data-animate-in-effect="none" data-responsive="true" data-midnight="light" class="" style="height: 75vh;"><div id="page-header-bg" class="not-loaded  hentry" data-post-hs="default_minimal" data-padding-amt="normal" data-animate-in-effect="none" data-midnight="light" data-text-effect="" data-bg-pos="center" data-alignment="left" data-alignment-v="middle" data-parallax="0" data-height="75vh" style="height:75vh;">					<div class="page-header-bg-image-wrap" id="nectar-page-header-p-wrap" data-parallax-speed="fast">
					
				<?php if ( has_post_thumbnail() ) : 
				$id = get_post_thumbnail_id();
				$src = wp_get_attachment_image_src( $id, 'full' );
				$srcset = wp_get_attachment_image_srcset( $id, 'full' );
				$sizes = wp_get_attachment_image_sizes( $id, 'full' );
				$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
				 
				 //print_r($src);
				 ?>

				
				<div class="page-header-bg-image" style="background-image: url(<?php echo $src[0]; ?>);"></div>
				<?php endif; ?>
					</div> 
				
</div>

</div>
<?php } ?>
<div class="<?php echo esc_attr( $container_wrap_class ); if ( $blog_type === 'std-blog-fullwidth' || $hide_sidebar === '1' ) { echo ' no-sidebar'; } ?>" data-midnight="dark" data-remove-post-date="<?php echo esc_attr( $remove_single_post_date ); ?>" data-remove-post-author="<?php echo esc_attr( $remove_single_post_author ); ?>" data-remove-post-comment-number="<?php echo esc_attr( $remove_single_post_comment_number ); ?>">
	<div class="container main-content">
		
		<?php
		// Post header regular style when no image is supplied.
		//get_template_part( 'includes/partials/single-post/post-header-no-img-regular' );
		?>
			
		<div class="row">
			
			<?php

			nectar_hook_before_content(); 

			$blog_standard_type = ( ! empty( $nectar_options['blog_standard_type'] ) ) ? $nectar_options['blog_standard_type'] : 'classic';
			$blog_type          = $nectar_options['blog_type'];
			
			if ( null === $blog_type ) {
				$blog_type = 'std-blog-sidebar';
			}

			if ( 'minimal' === $blog_standard_type && 'std-blog-sidebar' === $blog_type || 'std-blog-fullwidth' === $blog_type ) {
				$std_minimal_class = 'standard-minimal';
			} else {
				$std_minimal_class = '';
			}
			
			$single_post_area_col_class = 'span_9';
			
			// No sidebar.
			if ( 'std-blog-fullwidth' === $blog_type || '1' === $hide_sidebar ) {
				$single_post_area_col_class = 'span_12 col_last';
			} 
			
			?>
			
			<div class="post-area col <?php echo esc_attr($std_minimal_class) . ' ' . esc_attr($single_post_area_col_class); ?>">
			
			<?php 
			// Main content loop.
			if ( have_posts() ) :
				while ( have_posts() ) :
					
					the_post();
					
					include('./wp-content/plugins/custom-plugin/templates/post-content.php');

				 endwhile;
			 endif;

			wp_link_pages();
			
			nectar_hook_after_content(); 

			// Bottom social location for default minimal post header style.
			if ( 'default_minimal' === $blog_header_type && 
			'fixed' !== $blog_social_style && 
			'post' === get_post_type() ) {

				get_template_part( 'includes/partials/single-post/default-minimal-bottom-social' );

			}
			
			if ( true === $fullscreen_header && get_post_type() === 'post' ) {
				// Bottom meta bar when using fullscreen post header.
				get_template_part( 'includes/partials/single-post/post-meta-bar-ascend-skin' );
			}

			if ( 'ascend' !== $theme_skin ) {
				
				// Original/Material Theme Skin Author Bio.
				if ( ! empty( $nectar_options['author_bio'] ) && 
					$nectar_options['author_bio'] === '1' && 
					'post' == get_post_type() ) {
					 get_template_part( 'includes/partials/single-post/author-bio' );

				}

			}
			
			?>

		</div><!--/post-area-->
			
			<?php if ( 'std-blog-fullwidth' !== $blog_type && '1' !== $hide_sidebar ) { ?>
				
				<div id="sidebar" data-nectar-ss="<?php echo esc_attr( $enable_ss ); ?>" class="col span_3 col_last">
					<?php get_sidebar(); ?>
				</div><!--/sidebar-->
				
			<?php } ?>
				
		</div><!--/row-->

		<div class="row">

			<?php 
				
				// Pagination/Related Posts.
				nectar_next_post_display();
				nectar_related_post_display();
				
				// Ascend Theme Skin Author Bio.
				if ( ! empty( $nectar_options['author_bio'] ) && 
					'1' === $nectar_options['author_bio'] && 
					'ascend' === $theme_skin && 
					'post' == get_post_type() ) {
					get_template_part( 'includes/partials/single-post/author-bio-ascend-skin' );
				}
			
			?>

			<div class="comments-section" data-author-bio="<?php if ( ! empty( $nectar_options['author_bio'] ) && $nectar_options['author_bio'] === '1' ) { echo 'true'; } else { echo 'false'; } ?>">
				<?php comments_template(); ?>
			</div>   

		</div><!--/row-->
<section class="call-to-action">
 <div id="fws_609917ac88cd1" data-column-margin="default" data-midnight="dark" class="wpb_row vc_row-fluid vc_row  home-calltoaction" style="padding-top: 0px; padding-bottom: 0px; "><div class="row-bg-wrap" data-bg-animation="none" data-bg-overlay="false"><div class="inner-wrap"><div class="row-bg" style=""></div></div></div><div class="row_col_wrap_12 col span_12 dark left">	<div class="vc_col-sm-12 wpb_column column_container vc_column_container col no-extra-padding inherit_tablet inherit_phone " data-padding-pos="all" data-has-bg-color="false" data-bg-color="" data-bg-opacity="1" data-animation="" data-delay="0">		<div class="vc_column-inner">			<div class="wpb_wrapper">				<div id="fws_609917ac891ee" data-midnight="" data-column-margin="default" class="wpb_row vc_row-fluid vc_row inner_row  home-inner-calltoaction" style=""><div class="row-bg-wrap"> <div class="row-bg"></div> </div><div class="row_col_wrap_12_inner col span_12  left">	<div class="vc_col-sm-8 os-first-section wpb_column column_container vc_column_container col child_column no-extra-padding inherit_tablet inherit_phone " data-padding-pos="all" data-has-bg-color="false" data-bg-color="" data-bg-opacity="1" data-animation="" data-delay="0">		<div class="vc_column-inner">		<div class="wpb_wrapper">			<div class="wpb_text_column wpb_content_element  head-content">	<div class="wpb_wrapper">		<h2>We’d love to connect with you.<br>Drop in a line and you’ll hear<br>from us soon.</h2>	</div></div>		</div> 	</div>	</div> 	<div class="vc_col-sm-4 os-sec-section wpb_column column_container vc_column_container col child_column no-extra-padding inherit_tablet inherit_phone " data-padding-pos="all" data-has-bg-color="false" data-bg-color="" data-bg-opacity="1" data-animation="" data-delay="0">		<div class="vc_column-inner">		<div class="wpb_wrapper">			<div class="wpb_text_column wpb_content_element  calltoaction-right-tb">	<div class="wpb_wrapper">		<p><a class="exploremore cta" href="#">Enquire Now </a></p>	</div></div>		</div> 	</div>	</div> </div></div>			</div> 		</div>	</div> </div></div>
   
</section>

	</div><!--/container main-content-->

</div><!--/container-wrap-->

<?php if ( 'fixed' === $blog_social_style ) {
	  // Social sharing buttons.
		if( function_exists('nectar_social_sharing_output') ) {
			nectar_social_sharing_output('fixed');
		}
}?>

 

<section class="footer-post-slider">
	<div class="wpb_wrapper">
		<h4>Insights</h4>
<h2>Intelligences – From Yesterday to<br>
Today to <strong>Tomorrow</strong></h2>
	</div>
 <?php echo do_shortcode('[posts-slider]'); ?>
   
</section>


<?php get_footer(); ?>