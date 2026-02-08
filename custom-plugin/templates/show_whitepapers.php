<?php		
	$per_page = -1;
	$args = array(
	'post_type' => 'whitepaper', 
	'post_status' => 'publish' ,
	'hierarchical' => true,
	'posts_per_page' => $per_page,
	/* 'tax_query' => array(
	 $tax_query_val
	) */
	);
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
	$the_query->the_post(); ?>
		<div class="whitepapers-post col span_3 post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
			<a href="<?php echo get_permalink(); ?>">
			 <?php if ( has_post_thumbnail() ) : 
			$id = get_post_thumbnail_id();
			$src = wp_get_attachment_image_src( $id, 'full' );
			$srcset = wp_get_attachment_image_srcset( $id, 'full' );
			$sizes = wp_get_attachment_image_sizes( $id, 'full' );
			$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
			 
			 ?>


			<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
		<?php endif; ?>
			</a>
			<div class="post-content-outer">
				<div class="post-header">
					<h3 class="title">
						<a href="<?php echo get_permalink(); ?>"><?php echo get_the_title();?></a>
					</h3>	
				</div>
				<div class="col span_12">
					<div class="excerpt">
						<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
					</div>
				</div>	
				<div class="col span_12">
					<div class="Postarrow">
						<a class="epmoreBtn" href="<?php echo get_permalink(); ?>">explore more</a>
					</div>
				</div>	
			</div>	
		</div>
	<?php	//include(PLUGIN_DIR .'/templates/webinar-post.php');
	}
	$count = $the_query->found_posts; 
	echo pagination_load_posts(1,$count,$per_page);
	}
	else{
		echo 'No Webinar Found.';
	}		
	wp_reset_postdata();
?>