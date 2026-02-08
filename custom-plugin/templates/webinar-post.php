<div class="show-the-posts col span_4 post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
	<div class="post_inner">
		<div class="post_image">
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
				<button>+</button>
			</a>
		</div>
		<div class="post_list_content">
			<div class="career-meta">
				<div class="post_meta">
					<div class="tag">WEBINAR</div>
				</div>
			</div>
			<div class="post-header">
				<h3 class="title">
					<a href="<?php echo get_permalink(); ?>"><?php echo get_the_title();?></a>
				</h3>	
			</div>
		</div>
	</div>
</div>