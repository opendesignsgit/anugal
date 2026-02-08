<?php


add_action( 'init', 'cpt_register_careers' );
function cpt_register_careers() {

	/**
	 * Post Type: Careers.
	 */

	$labels = [
		"name" => __( "Careers", "custom-post-type-ui" ),
		"singular_name" => __( "Career", "custom-post-type-ui" ),
	];

	$args = [
		"label" => __( "Careers", "custom-post-type-ui" ),
		"menu_icon" => "dashicons-book",
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
		"rewrite" => [ "slug" => "career", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "excerpt"],
		//"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "careers", $args );
	
	// register_taxonomy('department', 'careers', array(
	// 	'hierarchical' => true,
	// 	'labels' => array(
	// 	  'name' => _x( 'Departments', 'taxonomy general name' ),
	// 	  'singular_name' => _x( 'Department', 'taxonomy singular name' ),
	// 	  'search_items' =>  __( 'Search Departments' ),
	// 	  'all_items' => __( 'All Departments' ),
	// 	  'parent_item' => __( 'Parent Department' ),
	// 	  'parent_item_colon' => __( 'Parent Department:' ),
	// 	  'edit_item' => __( 'Edit Department' ),
	// 	  'update_item' => __( 'Update Department' ),
	// 	  'add_new_item' => __( 'Add New Department' ),
	// 	  'new_item_name' => __( 'New Department Name' ),
	// 	  'menu_name' => __( 'Departments' ),
	// 	),
	// 	'rewrite' => array(
	// 	  'slug' => 'departments',
	// 	  'with_front' => false,
	// 	  'hierarchical' => true
	// 	),
	// ));
	register_taxonomy('experience', 'careers', array(
		'hierarchical' => true,
		'labels' => array(
		  'name' => _x( 'Experience', 'taxonomy general name' ),
		  'singular_name' => _x( 'Experience', 'taxonomy singular name' ),
		  'search_items' =>  __( 'Search Experience' ),
		  'all_items' => __( 'All Experience' ),
		  'parent_item' => __( 'Parent Experience' ),
		  'parent_item_colon' => __( 'Parent Experience:' ),
		  'edit_item' => __( 'Edit Experience' ),
		  'update_item' => __( 'Update Experience' ),
		  'add_new_item' => __( 'Add New Experience' ),
		  'new_item_name' => __( 'New Experience Name' ),
		  'menu_name' => __( 'Experience' ),
		),
		'rewrite' => array(
		  'slug' => 'experience',
		  'with_front' => false,
		  'hierarchical' => true
		),
	));
	register_taxonomy('location', 'careers', array(
		'hierarchical' => true,
		'labels' => array(
		  'name' => _x( 'Locations', 'taxonomy general name' ),
		  'singular_name' => _x( 'Location', 'taxonomy singular name' ),
		  'search_items' =>  __( 'Search Locations' ),
		  'all_items' => __( 'All Locations' ),
		  'parent_item' => __( 'Parent Location' ),
		  'parent_item_colon' => __( 'Parent Location:' ),
		  'edit_item' => __( 'Edit Location' ),
		  'update_item' => __( 'Update Location' ),
		  'add_new_item' => __( 'Add New Location' ),
		  'new_item_name' => __( 'New Location Name' ),
		  'menu_name' => __( 'Locations' ),
		),
		'rewrite' => array(
		  'slug' => 'locations',
		  'with_front' => false,
		  'hierarchical' => true
		),
	));
	// register_taxonomy('careerfor', 'careers', array(
	// 	'hierarchical' => true,
	// 	'labels' => array(
	// 	  'name' => _x( 'Careers For', 'taxonomy general name' ),
	// 	  'singular_name' => _x( 'Career For', 'taxonomy singular name' ),
	// 	  'menu_name' => __( 'Careers For' ),
	// 	),
	// 	'rewrite' => array(
	// 	  'slug' => 'careerfor',
	// 	  'with_front' => false,
	// 	  'hierarchical' => true
	// 	),
	// ));
}

// add_filter( 'wp_dropdown_cats', 'wp_dropdown_cats_multiple', 10, 2 );

// function wp_dropdown_cats_multiple( $output, $r ) {

//     if( isset( $r['multiple'] ) && $r['multiple'] ) {

//          $output = preg_replace( '/^<select/i', '<select multiple', $output );

//         $output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );

//         foreach ( array_map( 'trim', explode( ",", $r['selected'] ) ) as $value )
//             $output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );

//     }

//     return $output;
// }
add_shortcode("show_careers", "show_careers");  
function show_careers( $atts ) {  
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


	$per_page = -1;
	$args = array(
	'post_type' => 'careers', 
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
		include(PLUGIN_DIR .'/templates/career-post.php');
	}
	$count = $the_query->found_posts; 
	//echo pagination_load_posts(1,$count,$per_page);
	}
	else{
		echo 'No Webinar Found.';
	}		
	wp_reset_postdata();
$stringa = ob_get_contents();
ob_end_clean();
return $stringa;
}

//custom post types and careers taxnomy and filter start
add_action('wp_ajax_careerpositions', 'filter_careerpositions_function'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_careerpositions', 'filter_careerpositions_function');

function filter_careerpositions_function(){
	$careerdepartment  =  ''; 
    $careerdepartment =  isset( $_REQUEST[ 'careerdepartment' ] ) ? sanitize_text_field( $_REQUEST[ 'careerdepartment' ] ) : '';
	if($careerdepartment){
		$careerposition = array(
			'show_option_none'   => 'Select Position',
			'option_none_value'  => '',
			'orderby'            => 'ID', 
			'order'              => 'ASC',
			'show_count'         => 1,
			'hide_empty'         => 0, 
			'child_of'           => $careerdepartment,
			'exclude'            => '',
			'echo'               => 1,
			'selected'           => 1,
			'hierarchical'       => 1, 
			'name'               => 'careerposition',
			'id'                 => 'careerposition',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 1,
			'taxonomy'           => 'department',
			'hide_if_empty'      => false,
			'required'           => false
		); 
		wp_dropdown_categories($careerposition);
	}else{
		echo '<select name="careerposition" id="careerposition" class="form-no-clear" tabindex="1"><option value="">Select Position</option></select>';
	}
	die();
}


function wwp_custom_query_vars_filter($vars) {
    $vars[] .= 'careerfor';
    return $vars;
}
add_filter( 'query_vars', 'wwp_custom_query_vars_filter' );

//custom post types and careers taxnomy and filter start
add_action('wp_ajax_myfilter', 'misha_filter_function'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_myfilter', 'misha_filter_function');
 
function misha_filter_function(){
    if(isset($_REQUEST['page'])){
        $page = sanitize_text_field($_REQUEST['page']);
    }else{
		$page = 1;
	}
	$per_page = 2;
	$careerlocation =  $careerposition =  $careerexperience  = $careerfor  =  ''; 
    $careerlocation =  isset( $_REQUEST[ 'careerlocation' ] ) ? $_REQUEST[ 'careerlocation' ] : '';
    $careerposition =  isset( $_REQUEST[ 'careerposition' ] ) ? sanitize_text_field( $_REQUEST[ 'careerposition' ] ) : '';
    $careerexperience =  isset( $_REQUEST[ 'careerexperience' ] ) ? sanitize_text_field( $_REQUEST[ 'careerexperience' ] ) : '';
    $careerfor =  isset( $_REQUEST[ 'careerfor' ] ) ? sanitize_text_field( $_REQUEST[ 'careerfor' ] ) : '';
	if(!is_array($careerlocation) && !empty($careerlocation)){
		$careerlocation =  explode(",",$careerlocation);
	}
	$start = ( $page - 1 ) * $per_page;
	$args = array(
		'post_type' => 'careers', 
		'post_status' => 'publish' ,
		'hierarchical' => true,
		'posts_per_page' => $per_page,
		'offset' => $start,
		'tax_query' => array(
			'relation' => 'AND',
		)
	);
	if(!empty($careerlocation))
		$args['tax_query'][] = array(
			'taxonomy'	=> 'location',
			'field'		=> 'id',
			'terms'		=> $careerlocation
		);
	if($careerposition)
		$args['tax_query'][] = array(
			'taxonomy'	=> 'department',
			'field'		=> 'id',
			'terms'		=> $careerposition
		);
	if($careerexperience)
		$args['tax_query'][] = array(
			'taxonomy'	=> 'experience',
			'field'		=> 'id',
			'terms'		=> $careerexperience
		);
	if($careerfor)
		$args['tax_query'][] = array(
			'taxonomy'	=> 'careerfor',
			'field'		=> 'slug',
			'terms'		=> $careerfor
		);
	$query = new WP_Query( $args );
	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			include(PLUGIN_DIR .'/templates/career-post.php');
		endwhile;
		$count = $query->found_posts; 
		echo pagination_load_posts($page,$count,$per_page);
		wp_reset_postdata();
	else :
		echo 'No Careers Found.';
	endif;
 
	die();
}