<?php if(wp_is_mobile() && is_home()){ ?>
<div id="parentHorizontalTab">
	<amp-accordion id="my-accordion" disable-session-states expand-single-section>
		<?php 
		if(!empty($tabgroup)){
			$taxquery = array(
				'taxonomy' => 'tab-group',
				'field'    => 'slug',
				'terms' => $tabgroup,
				'operator' => 'IN'
			);
		}
		else{
			$taxquery ="";
		} 
		$args = array(
			'post_type' => 'responsive-tabs', 
			'post_status' => 'publish' ,
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
			$i = 1;
		while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$value_id = preg_replace('#[ -]+#', '-', get_the_title());
		?>
		<section <?php if($i == 1){echo 'expanded';}?>>
			<h3>
			 <?php if ( has_post_thumbnail() ) : 
				$id = get_post_thumbnail_id();
				$src = wp_get_attachment_image_src( $id, 'full' );
				$srcset = wp_get_attachment_image_srcset( $id, 'full' );
				$sizes = wp_get_attachment_image_sizes( $id, 'full' );
				$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
				 
				 //print_r($src);
				 ?>

				<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
			 <span><?php echo get_the_title()?></span></h3>
				<div>
				<?php echo the_content() ?>
			</div>
			<?php endif; ?>
		</section>
		<?php $i++;}} ?> 
	</amp-accordion>
</div>	
<?php }else{ ?>
<?php $el_id = uniqid();?>
	<div id="parentHorizontalTab_<?php echo $el_id ?>" class="simple-resp-tabs <?php echo $class?> <?php echo $el_id ?>">
		<ul class="resp-tabs-list hor_1">
		<?php 
		if(!empty($tabgroup)){
			$taxquery = array(
				'taxonomy' => 'tab-group',
				'field'    => 'slug',
				'terms' => $tabgroup,
				'operator' => 'IN'
			);
		}
		else{
			$taxquery ="";
		} 
		$args = array(
			'post_type' => 'responsive-tabs', 
			'post_status' => 'publish' ,
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
		$value_id = preg_replace('#[ -]+#', '-', get_the_title());
		?>
		<li>
			<h3><?php echo get_the_title()?></h3>
			 <?php if ( has_post_thumbnail() ) : 
				$id = get_post_thumbnail_id();
				$src = wp_get_attachment_image_src( $id, 'full' );
				$srcset = wp_get_attachment_image_srcset( $id, 'full' );
				$sizes = wp_get_attachment_image_sizes( $id, 'full' );
				$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
				 
				 //print_r($src);
				 ?>

				<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
			 

			<?php endif; ?>
		</li>
		<?php }} ?>
		</ul>
		<div class="resp-tabs-container hor_1">
		<?php if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$value_id = preg_replace('#[ -]+#', '-', get_the_title());
		?>
			<div>
				<?php echo the_content() ?>
			</div>
		<?php }} ?>
		</div>
	</div>	
		<?php
		wp_reset_postdata();
	?>
	
	

<script type='text/javascript'>
jQuery( function( $ ) {
	var elId = '<?php echo $el_id; ?>';
	//Horizontal Tab
	$('#parentHorizontalTab_'+elId).easyResponsiveTabs({
		type: 'default', //Types: default, vertical, accordion
		width: 'auto', //auto or any width like 600px
		fit: true, // 100% fit in a container
		tabidentify: 'hor_1', // The tab groups identifier
		activate: function(event) {
			var $tab = $(this);
			var $info = $('#nested-tabInfo');
			var $name = $('span', $info);
			$name.text($tab.text());
			$info.show();
		}
	});
});
</script>  

<?php } ?>