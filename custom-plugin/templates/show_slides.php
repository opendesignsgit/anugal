<?php if(wp_is_mobile() && is_home()){ ?>
<amp-carousel class="carousel1" layout="responsive" height="450" width="500" type="slides" adaptiveHeight id="carouselID" on="slideChange: SelectorID.toggle(index=event.index, value=true), carouselDots.goToSlide(index=event.index)">
		<?php 
		if(!empty($slidegroup)){
			$taxquery = array(
				'taxonomy' => 'slide-group',
				'field'    => 'slug',
				'terms' => $slidegroup,
				'operator' => 'IN'
			);
		}
		else{
			$taxquery ="";
		}
		$args = array(
			'post_type' => 'slick-slides', 
			'post_status' => 'publish' ,
			'posts_per_page' => $limit ,
			'hierarchical' => true,
			'orderby'   => 'ID',
			'order' => $order,
			'relation'		=> 'IN',			
			'tax_query' => array(
				$taxquery
			)
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
		$the_query->the_post();
		?>
		<div class="slide custom_amp_slider">
			<div>
				<div class="custom_slick_slider_inner">
					<?php 
						if ( has_post_thumbnail() ) : 
						$id = get_post_thumbnail_id();
						$src = wp_get_attachment_image_src( $id, 'full' );
						$srcset = wp_get_attachment_image_srcset( $id, 'full' );
						$sizes = wp_get_attachment_image_sizes( $id, 'full' );
						$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
					?>

					<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
				 

					<?php endif; ?>
					<?php echo the_content()?>
				</div>
			</div>
		</div>
		<?php } ?>

</amp-carousel>

<amp-selector id="selectorID" on="select:carouselID.goToSlide(index=event.targetOption)" layout="container"> 
	<ul id="carouselDots" class="dots"> 
		<?php 
		$i = 0;
		while ( $the_query->have_posts() ) {
		$the_query->the_post();
		?>
		<li option="<?php echo $i; ?>" selected><?php echo $i; ?></li> 
		<?php $i++; } ?>
	</ul> 
</amp-selector>
<?php }}else{
	$el_id = uniqid();
	if($class){
		$class = $class;
	}else{
		$class = $el_id;
	}
	?>
	<div class="<?php echo $class ?> custom_slick_slider" id="<?php echo $el_id ?>">
		<?php 
		if(!empty($slidegroup)){
			$taxquery = array(
				'taxonomy' => 'slide-group',
				'field'    => 'slug',
				'terms' => $slidegroup,
				'operator' => 'IN'
			);
		}
		else{
			$taxquery ="";
		}
		$args = array(
			'post_type' => 'slick-slides', 
			'post_status' => 'publish' ,
			'posts_per_page' => $limit ,
			'hierarchical' => true,
			'orderby'   => 'ID',
			'order' => $order,
			'relation'		=> 'IN',			
			'tax_query' => array(
				$taxquery
			)
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
		$the_query->the_post();
		?>
		<div>
			<div class="custom_slick_slider_inner">
				<?php if ( has_post_thumbnail() ) : 
					$id = get_post_thumbnail_id();
					$src = wp_get_attachment_image_src( $id, 'full' );
					$srcset = wp_get_attachment_image_srcset( $id, 'full' );
					$sizes = wp_get_attachment_image_sizes( $id, 'full' );
					$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
					 
					 //print_r($src);
					 ?>

					<div class="featureimg">
						<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
					</div>	
				<?php endif; ?>
				<?php echo the_content()?>
			</div>
		</div>
		<?php }}
		wp_reset_postdata();
		?>
	</div>
	
<?php } ?>
