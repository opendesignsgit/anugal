<?php if(wp_is_mobile() && is_home()){ ?>
<amp-accordion id="my-accordion2" disable-session-states expand-single-section>
	<?php 
	if(!empty($accordiongroup)){
		$taxquery = array(
			'taxonomy' => 'accordion-group',
			'field'    => 'slug',
			'terms' => $accordiongroup,
			'operator' => 'IN'
		);
	}
	else{
		$taxquery ="";
	}
	$args = array(
		'post_type' => 'simple-accordion', 
		'post_status' => 'publish' ,
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
		$i = 1;
	while ( $the_query->have_posts() ) {
	$the_query->the_post();
	$value_id = preg_replace('#[ -]+#', '-', get_the_title());?>
		<section class="acc" <?php //if($i == 1){echo 'expanded';}?>>
			<div class="acc-head">
				<h4><?php echo get_the_title()?></h4>
			</div>
			<div class="acc-content">
				<?php echo the_content() ?>
			</div>
		</section>
	<?php $i++;}} ?>
</amp-accordion> 
<?php }else{
$el_id = uniqid();?>
<div class="acc-container <?php echo $class?> <?php echo $el_id ?>" id="accordion_<?php echo $el_id ?>"> 
	<?php 
		if(!empty($accordiongroup)){
			$taxquery = array(
				'taxonomy' => 'accordion-group',
				'field'    => 'slug',
				'terms' => $accordiongroup,
				'operator' => 'IN'
			);
		}
		else{
			$taxquery ="";
		} 
		$args = array(
			'post_type' => 'simple-accordion', 
			'post_status' => 'publish' ,
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
		$value_id = preg_replace('#[ -]+#', '-', get_the_title());
		?>

	<div class="acc">
		<div class="acc-head">
			<h4><?php echo get_the_title()?></h4>
		</div>
		<div class="acc-content">
			<?php echo the_content() ?>
		</div>
	</div>
	<?php }} ?>
</div>
<?php wp_reset_postdata(); }?>
	
	
	
<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		$('#accordion_<?php echo $el_id ?>>.acc .acc-head').removeClass('active');
		$('#accordion_<?php echo $el_id ?>>.acc .acc-content').slideUp();
		if($('#accordion_<?php echo $el_id ?>').hasClass('footer-accordion')) {
			$('this>.acc:nth-child(1)>.acc-head').removeClass('active');
			$('this>.acc:nth-child(1)>.acc-content').slideUp();
		}
		else {
			$('#accordion_<?php echo $el_id ?>>.acc:nth-child(1)>.acc-head').addClass('active');
			$('#accordion_<?php echo $el_id ?>>.acc:nth-child(1)>.acc-content').slideDown();
		}
		$('#accordion_<?php echo $el_id ?> .acc-head').on('click', function() {
			if($(this).hasClass('active')) {
			  $(this).siblings('.acc-content').slideUp();
			  $(this).removeClass('active');
			}
			
			else {
			  $('#accordion_<?php echo $el_id ?> .acc-content').slideUp();
			  $('#accordion_<?php echo $el_id ?> .acc-head').removeClass('active');
			  $(this).siblings('.acc-content').slideToggle();
			  $(this).toggleClass('active');
			}
		});     
	});
})(jQuery);
</script>