<?php

function enqueue_webinar_scripts($hook) {
	global $post;
	if(is_singular('webinars')){
		$webinar_video_link = get_field( "webinar_video", get_the_ID() );
		wp_localize_script( 'jquery', 'webinar_Data',
			array(
				'site_url' => site_url(),
				'wp_content_url' => content_url() . '/',
				'webinar_video_link' => $webinar_video_link
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'enqueue_webinar_scripts',1 );

add_action( 'wp_head' , 'webinar_header_codes' );
function webinar_header_codes(){ 
if(is_singular('webinars')){
?>
<script type='text/javascript'> 
	jQuery( function( $ ) {
		//alert(webinar_Data.webinar_video_link);
		$("#webinar_video").val(webinar_Data.webinar_video_link);
	});
</script>
<?php }}

add_action( 'init', 'cpt_register_webinars' );
function cpt_register_webinars() {

	/**
	 * Post Type: Webinars.
	 */

	$labels = [
		"name" => __( "Webinars", "custom-post-type-ui" ),
		"singular_name" => __( "Webinar", "custom-post-type-ui" ),
	];

	$args = [
		"label" => __( "Webinars", "custom-post-type-ui" ),
		"menu_icon" => "dashicons-format-video",
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "webinars", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "excerpt"],
		//"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "webinars", $args );
	
	register_taxonomy('webinarcategory', 'webinars', array(
		'hierarchical' => true,
		'labels' => array(
		  'name' => _x( 'Categories', 'taxonomy general name' ),
		  'singular_name' => _x( 'category', 'taxonomy singular name' ),
		  'search_items' =>  __( 'Search Categories' ),
		  'all_items' => __( 'All Categories' ),
		  'parent_item' => __( 'Parent category' ),
		  'parent_item_colon' => __( 'Parent category:' ),
		  'edit_item' => __( 'Edit category' ),
		  'update_item' => __( 'Update category' ),
		  'add_new_item' => __( 'Add New category' ),
		  'new_item_name' => __( 'New category Name' ),
		  'menu_name' => __( 'Categories' ),
		),
		'rewrite' => array(
		  'slug' => 'webinarcategory',
		  'with_front' => false,
		  'hierarchical' => true
		),
	));
}


add_shortcode("show_webinars", "show_webinars");  
function show_webinars( $atts ) {  
ob_start();

if(isset($atts['columns'])){
	$columns =  $atts['columns'];
}
else{
	$columns = 3;
}

if(isset($atts['display'])){
	$display =  $atts['display'];
}
else{
	$display = 500;
}

include(PLUGIN_DIR .'/templates/show_webinars.php');
$stringa = ob_get_contents();
ob_end_clean();
return $stringa;
}




//custom post types and webinars taxnomy and filter start
add_action('wp_ajax_webinar_filter', 'webinar_filter_function'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_webinar_filter', 'webinar_filter_function');
 
function webinar_filter_function(){
    if(isset($_REQUEST['page'])){
        // Sanitize the received page   
        $page = sanitize_text_field($_REQUEST['page']);
    }else{
		$page = 1;
	}	
	$per_page = 6;
	$webinarsearch = $webinarcategory =  ''; 
    $webinarsearch =  isset( $_REQUEST[ 'webinarsearch' ] ) ? sanitize_text_field( $_REQUEST[ 'webinarsearch' ] ) : '';
    $webinarcategory =  isset( $_REQUEST[ 'webinarcategory' ] ) ? sanitize_text_field( $_REQUEST[ 'webinarcategory' ] ) : '';
	$start = ( $page - 1 ) * $per_page;
	
	$args = array(
		'post_type' => 'webinars', 
		'post_status' => 'publish' ,
		'hierarchical' => true,
		'posts_per_page' => $per_page,
		'offset' => $start,
	);


	// single filter
	if( $webinarsearch )
		$args  = array(
			'post_type' => 'webinars', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			'search_prod_title' => $webinarsearch,
			'offset' => $start,
			
		);
 	if( $webinarcategory )
		$args  = array(
			'post_type' => 'webinars', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			'offset' => $start,
			'tax_query' => array(
              array(
                  'taxonomy' => 'webinarcategory',
                  'field'    => 'id',
                  'terms' => $webinarcategory,
                  'operator' => 'IN'
                  )
			)
		);
 	// 2 combination filter
  	if( $webinarsearch && $webinarcategory)
		$args  = array(
			'post_type' => 'webinars', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			'offset' => $start,
			'search_prod_title' => $webinarsearch,
			'tax_query' => array(
			 'relation' => 'AND',              
 			   array(
                  'taxonomy' => 'webinarcategory',
                  'field'    => 'id',
                  'terms'    => $webinarcategory ,
                 
             )
		)
	 
	);
	add_filter( 'posts_where', 'webinar_title_filter', 10, 2 );
	$query = new WP_Query( $args );
	remove_filter( 'posts_where', 'webinar_title_filter', 10, 2 );
	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			include(PLUGIN_DIR .'/templates/webinar-post.php');
		endwhile;
		$count = $query->found_posts; 
		echo pagination_load_posts($page,$count,$per_page);
		wp_reset_postdata();
	else :
		echo 'No Webinar Found.';
	endif;
 
	die();
}

function webinar_title_filter( $where, &$wp_query ){
    global $wpdb;
    if ( $search_term = $wp_query->get( 'search_prod_title' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $search_term ) ) . '%\'';
    }
    return $where;
}



add_shortcode("show_webinar_speaker", "show_webinar_speaker");  
function show_webinar_speaker( $atts ) {  
	ob_start();

	if(isset($atts['columns'])){
		$columns =  $atts['columns'];
	}
	else{
		$columns = 3;
	}

	if(isset($atts['display'])){
		$display =  $atts['display'];
	}
	else{
		$display = 500;
	}

	include(PLUGIN_DIR .'/templates/show_webinar_speaker.php');
	$stringa = ob_get_contents();
	ob_end_clean();
	return $stringa;
}
