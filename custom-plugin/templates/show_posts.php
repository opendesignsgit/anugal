<div id="response">
<?php		
	$per_page = 6;
	$post_type = 'post';
	$template = 'show_post';
	$args = array(
	'post_type' => $post_type, 
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
	$the_query->the_post();
		include(PLUGIN_DIR .'/templates/show_post.php');
	}
	$count = $the_query->found_posts; 
	echo pagination_load_posts(1,$count,$per_page,$post_type,$template);
	}
	else{
		echo '404 Nothing Found.';
	}		
	wp_reset_postdata();
?>
</div>