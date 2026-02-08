<?php
$el_id = uniqid();
if($class){
	$class = $class;
}else{
	$class = $el_id;
}
	$categories = get_the_terms( get_the_ID(), 'industries');
	if(!empty($categories)){
		$industry = $categories[0]->slug;
	}
?> 
<div class="<?php echo $class ?> custom_slick_slider" id="<?php echo $el_id ?>">
	<?php 
	
	if(!empty($industry)){
		$taxquery = array(
			'taxonomy' => 'industries',
			'field'    => 'slug',
			'terms' => $industry,
			'operator' => 'IN'
		);
	}
	else{
		$taxquery ="";
	}
	$args = array(
		'post_type' => 'case_studies', 
		'post__not_in' => array(get_the_ID()),
		'ignore_sticky_posts' => true,
		'post_status' => 'publish' ,
		'posts_per_page' => $limit ,
		'hierarchical' => true,
		'orderby'   => $orderby,
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
	<div class="casestudies_outer">
		<div class="custom_slick_slider_inner">
			<div class="fusion-layout-column fusion_builder_column_inner fusion-builder-nested-column-8 fusion_builder_column_inner_1_2 1_2 fusion-flex-column enrchemiCSCol ecCSLcol">
				<div class="fusion-column-wrapper fusion-flex-justify-content-flex-start fusion-content-layout-column" style="background-position:left top;background-repeat:no-repeat;-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;padding: 0px 0px 0px 0px;">
					<div>
						<span class=" fusion-imageframe imageframe-none imageframe-4 hover-type-none">
							<?php if ( has_post_thumbnail() ) : 
								$id = get_post_thumbnail_id();
								$src = wp_get_attachment_image_src( $id, 'full' );
								$srcset = wp_get_attachment_image_srcset( $id, 'full' );
								$sizes = wp_get_attachment_image_sizes( $id, 'full' );
								$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
								 
								 //print_r($src);
								 ?>
								<div class="featureimg">
									<a href="<?php echo get_permalink()?>"><img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>"></a>
								</div>	
							<?php endif; ?>
						</span>
					</div>
					<div class="fusion-text fusion-text-22">
						<h4><a href="<?php echo get_permalink()?>"><?php echo get_the_title();?></a></h4>
						<p><a href="<?php echo get_permalink()?>">Read More</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php }}
	wp_reset_postdata();
	?>
</div>
<script>
jQuery(document).ready(function($) {
	$('#<?php echo $el_id?>').slick({
		infinite: true,
		autoplay: true,  
		autoplaySpeed: 5000,
		speed: 800,
		slidesToScroll: 1,  
		slidesToShow: 2,  
		//centerMode: true,
		//centerPadding: '60px',
		arrows: true,  
		dots: true,  
		cssEase: 'linear',     
			responsive: [{            
				breakpoint: 1100,            
					settings: {                
						slidesToShow: 2,                
						slidesToScroll: 1            
					}        
				}, {
				breakpoint: 768,            
					settings: {
						slidesToShow: 1,                
						slidesToScroll: 1            
					}        
				}, {            
				breakpoint: 480,            
					settings: {                
						slidesToShow: 1,                
						slidesToScroll: 1            
					}        
				}]
	});
});
</script>


			
			
		