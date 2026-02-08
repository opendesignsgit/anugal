<div class="careers-post col span_12 post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="fusion-builder-row fusion-builder-row-inner fusion-row fusion-flex-align-items-flex-start fusion-flex-content-wrap"
	style="width:104% !important;max-width:104% !important;margin-left: calc(-4% / 2 );margin-right: calc(-4% / 2 );">
	<div class="fusion-layout-column fusion_builder_column_inner fusion-builder-nested-column-7 fusion_builder_column_inner_1_4 1_4 fusion-flex-column copLeftBox"
		style="--awb-bg-size:cover;--awb-width-large:25%;--awb-margin-top-large:0px;--awb-spacing-right-large:7.68%;--awb-margin-bottom-large:20px;--awb-spacing-left-large:7.68%;--awb-width-medium:25%;--awb-order-medium:0;--awb-spacing-right-medium:7.68%;--awb-spacing-left-medium:7.68%;--awb-width-small:100%;--awb-order-small:0;--awb-spacing-right-small:1.92%;--awb-spacing-left-small:1.92%;">
		<div
			class="fusion-column-wrapper fusion-column-has-shadow fusion-flex-justify-content-flex-start fusion-content-layout-column">
			<div class="fusion-image-element "
				style="--awb-caption-title-font-family:var(--h2_typography-font-family);--awb-caption-title-font-weight:var(--h2_typography-font-weight);--awb-caption-title-font-style:var(--h2_typography-font-style);--awb-caption-title-size:var(--h2_typography-font-size);--awb-caption-title-transform:var(--h2_typography-text-transform);--awb-caption-title-line-height:var(--h2_typography-line-height);--awb-caption-title-letter-spacing:var(--h2_typography-letter-spacing);">
				<span class=" fusion-imageframe imageframe-none imageframe-12 hover-type-none">
				<?php if ( has_post_thumbnail() ) : 
						$id = get_post_thumbnail_id();
						$src = wp_get_attachment_image_src( $id, 'full' );
						$srcset = wp_get_attachment_image_srcset( $id, 'full' );
						$sizes = wp_get_attachment_image_sizes( $id, 'full' );
						$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
						?>
						<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
				<?php endif; ?>
					
					</span></div>
		</div>
	</div>
	<div class="fusion-layout-column fusion_builder_column_inner fusion-builder-nested-column-8 fusion_builder_column_inner_1_2 1_2 fusion-flex-column copMiddleBox"
		style="--awb-bg-size:cover;--awb-width-large:50%;--awb-margin-top-large:0px;--awb-spacing-right-large:3.84%;--awb-margin-bottom-large:20px;--awb-spacing-left-large:3.84%;--awb-width-medium:50%;--awb-order-medium:0;--awb-spacing-right-medium:3.84%;--awb-spacing-left-medium:3.84%;--awb-width-small:100%;--awb-order-small:0;--awb-spacing-right-small:1.92%;--awb-spacing-left-small:1.92%;">
		<div
			class="fusion-column-wrapper fusion-column-has-shadow fusion-flex-justify-content-flex-start fusion-content-layout-column">
			<div class="fusion-text fusion-text-20">
				<h3 data-fontsize="28" style="--fontSize: 28; line-height: 1.2;" data-lineheight="33.6px"
					class="fusion-responsive-typography-calculated"><?php echo get_the_title();?></h3>
				<ul>
					<li class="licon"><?php echo implode(',', array_column(get_the_terms( get_the_ID(), 'location' ), 'name')); ?></li>
					<li class="exicon"><?php echo implode(',', array_column(get_the_terms( get_the_ID(), 'experience' ), 'name')); ?></li>
				</ul>
				<p><?php echo get_the_excerpt();?></p>
				<p><a class="arrow" href="javaScript:void(0)"><img decoding="async"
							src="<?php echo site_url();?>/wp-content/uploads/2024/03/HtestArrowRight.png"
							alt=""></a></p>
			</div>
		</div>
	</div>
	<div class="fusion-layout-column fusion_builder_column_inner fusion-builder-nested-column-9 fusion_builder_column_inner_1_4 1_4 fusion-flex-column copRightBox"
		style="--awb-bg-size:cover;--awb-width-large:25%;--awb-margin-top-large:0px;--awb-spacing-right-large:7.68%;--awb-margin-bottom-large:20px;--awb-spacing-left-large:7.68%;--awb-width-medium:25%;--awb-order-medium:0;--awb-spacing-right-medium:7.68%;--awb-spacing-left-medium:7.68%;--awb-width-small:100%;--awb-order-small:0;--awb-spacing-right-small:1.92%;--awb-spacing-left-small:1.92%;">
		<div
			class="fusion-column-wrapper fusion-column-has-shadow fusion-flex-justify-content-flex-start fusion-content-layout-column">
			<div class="fusion-text fusion-text-21">
			<?php if(!empty(get_the_content())){ ?><p><a class="jobDes job_description_<?php echo get_the_ID(); ?>" href="javaScript:void(0)">JOB DESCRIPTION +</a><br><?php } ?>
					<a class="applyBtn" href="javaScript:void(0)" data-job="<?php echo get_the_title();?>">Apply Now</a>
				</p>
			</div>
		</div>
	</div>
</div>
</div>





<?php if(!empty(get_the_content())){ ?>
<div class="job_description custom-model-main_custom_popup <?php echo get_the_ID(); ?>">
	<div class="custom-model-inner_custom_popup">        
	<div class="close-btn_custom_popup">×</div>
		<div class="custom-model-wrap_custom_popup">
			<div id="pop_content" class="pop-up-content-wrap_custom_popup">
				<?php echo the_content();?>
			</div>
		</div>  
	</div>  
	<div class="bg-overlay_custom_popup"></div>
</div>
<script>
	jQuery( function( $ ) {
		$(".job_description_<?php echo get_the_ID() ?>").on('click', function(e) {
			e.preventDefault();
			jQuery(".job_description.custom-model-main_custom_popup.<?php echo get_the_ID(); ?>").addClass('model-open_custom_popup');
			jQuery("body").addClass('JobDesc_opened');
		});
		$(document).on('keydown', function(event) {
			if (event.key == "Escape"){
				jQuery("body").removeClass('JobDesc_opened');
			}
		});
		$(".close-btn_custom_popup, #bg-overlay_custom_popup").click(function(){
				jQuery("body").removeClass('JobDesc_opened');
		});
		$('.applyBtn').on('click', function(e) {
			alert('s');
		})
	});
</script>
<?php } ?>