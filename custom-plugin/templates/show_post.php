<div class="vc_col-sm-4 wpb_column column_container vc_column_container col child_column no-extra-padding instance-31" data-t-w-inherits="default" data-shadow="none" data-border-radius="none" data-border-animation="" data-border-animation-delay="" data-border-width="none" data-border-style="solid" data-border-color="" data-bg-cover="" data-padding-pos="all" data-has-bg-color="false" data-bg-color="" data-bg-opacity="1" data-hover-bg="" data-hover-bg-opacity="1" data-animation="" data-delay="0">
	<div class="vc_column-inner">
		<div class="column-bg-overlay-wrap" data-bg-animation="none">
			<div class="column-bg-overlay"></div>
		</div>
		<div class="wpb_wrapper">
			<div class="iwithtext">
				<div class="iwt-icon">
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
						</a>
					</div>
				</div>
				<div class="iwt-text">
					<h3 class="title">
						<a href="
						<?php echo get_permalink(); ?>"> <?php echo get_the_title();?> </a>
					</h3>
					<div class="excerpt"> <?php echo wp_trim_words(get_the_excerpt(), 15) ;?> </div>
					<div class="Postarrow">
						<a class="epmoreBtn" href="<?php echo get_permalink(); ?>">explore more</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>