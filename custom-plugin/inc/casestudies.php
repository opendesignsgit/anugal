<?php
add_action( 'init', 'cptui_register_my_cpts_case_studies' );
function cptui_register_my_cpts_case_studies() {

	/**
	 * Post Type: Case Studies.
	 */

	$labels = [
		"name" => __( "Case Studies", "astra" ),
		"singular_name" => __( "Case Studies", "astra" ),
		"menu_name" => __( "Case Studies", "astra" ),
		"all_items" => __( "All Case Studies", "astra" ),
		"add_new" => __( "Add new", "astra" ),
		"add_new_item" => __( "Add new Case Studies", "astra" ),
		"edit_item" => __( "Edit Case Studies", "astra" ),
		"new_item" => __( "New Case Studies", "astra" ),
		"view_item" => __( "View Case Studies", "astra" ),
		"view_items" => __( "View Case Studies", "astra" ),
		"search_items" => __( "Search Case Studies", "astra" ),
		"not_found" => __( "No Case Studies found", "astra" ),
		"not_found_in_trash" => __( "No Case Studies found in trash", "astra" ),
		"parent" => __( "Parent Case Studies:", "astra" ),
		"featured_image" => __( "Featured image for this Case Studies", "astra" ),
		"set_featured_image" => __( "Set featured image for this Case Studies", "astra" ),
		"remove_featured_image" => __( "Remove featured image for this Case Studies", "astra" ),
		"use_featured_image" => __( "Use as featured image for this Case Studies", "astra" ),
		"archives" => __( "Case Studies archives", "astra" ),
		"insert_into_item" => __( "Insert into Case Studies", "astra" ),
		"uploaded_to_this_item" => __( "Upload to this Case Studies", "astra" ),
		"filter_items_list" => __( "Filter Case Studies list", "astra" ),
		"items_list_navigation" => __( "Case Studies list navigation", "astra" ),
		"items_list" => __( "Case Studies list", "astra" ),
		"attributes" => __( "Case Studies attributes", "astra" ),
		"name_admin_bar" => __( "Case Studies", "astra" ),
		"item_published" => __( "Case Studies published", "astra" ),
		"item_published_privately" => __( "Case Studies published privately.", "astra" ),
		"item_reverted_to_draft" => __( "Case Studies reverted to draft.", "astra" ),
		"item_scheduled" => __( "Case Studies scheduled", "astra" ),
		"item_updated" => __( "Case Studies updated.", "astra" ),
		"parent_item_colon" => __( "Parent Case Studies:", "astra" ),
	];

	$args = [
		"label" => __( "Case Studies", "astra" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "case_studies", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-book",
		"supports" => [ "title", "editor", "thumbnail", "excerpt" ],
		"show_in_graphql" => false,
	];

	register_post_type( "case_studies", $args );	
	
	register_taxonomy('service',
	'case_studies',
	array(
		'hierarchical' => true,
		'label' => 'Services',
		'show_admin_column' => true,
	)
	);
	$labels = array(
		'name'                       => _x( 'Industries', 'Industries General Name', 'text_domain' ),
		'singular_name'              => _x( 'Industry', 'Industry Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Industries', 'text_domain' ),
		'all_items'                  => __( 'All Industries', 'text_domain' ),
		/* 'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Item Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Item', 'text_domain' ),
		'edit_item'                  => __( 'Edit Item', 'text_domain' ),
		'update_item'                => __( 'Update Item', 'text_domain' ),
		'view_item'                  => __( 'View Item', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Items', 'text_domain' ),
		'search_items'               => __( 'Search Items', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No items', 'text_domain' ),
		'items_list'                 => __( 'Items list', 'text_domain' ),
		'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ), */
	);
	$args = array(
		'labels'                     => $labels,
		'rewrite'					 => array('slug' => 'industry', 'with_front' => true),
		'has_archive'				 => false,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'industries', array( 'case_studies' ), $args );
}

add_action( 'init', 'cptui_register_my_cpts_case_studies' );


add_shortcode('show_casestudies','show_casestudies');
function show_casestudies(){
	ob_start();
    if (isset($atts['class'])) {
        $class = $atts['class'];
    } else {
        $class = '';
    }
    if (isset($atts['order'])) {
        $order = $atts['order'];
    } else {
        $order = 'ASC';
    } ?>
	<div class="casestudyoutputs span_12 cvf_pag_loading" id="casestudyoutputs">
		<div id="response">
			<?php		
				$per_page = 6;
				$args = array(
				'post_type' => 'case_studies', 
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
					include(PLUGIN_DIR .'/templates/show_casestudies.php');
				}
				$count = $the_query->found_posts; 
				echo pagination_load_posts(1,$count,$per_page);
				}
				else{
					echo 'No Casestudy Found.';
				}		
				wp_reset_postdata();
			?>
		</div>
	</div>
    <?php
	$stringa = ob_get_contents();
    ob_end_clean();
    return $stringa;
}

add_shortcode('case_studies_slider', 'case_studies_slider');
function case_studies_slider($atts) {
	ob_start();
	if(isset($atts['related'])){
		$related =  $atts['related'];
	}
	else{
		$related = false;
	}
	if(isset($atts['class'])){
		$class =  $atts['class'];
	}
	else{
		$class = '';
	}
	if(isset($atts['exclude'])){
		$exclude =  $atts['exclude'];
	}
	if(isset($atts['post_type'])){
		$post_type =  $atts['post_type'];
	}
	else{
		$post_type = 'post';
	}
	if(isset($atts['col'])){
		$columns =  $atts['col'];
	}
	else{
		$columns = 3;
	}
	if(isset($atts['limit'])){
		$limit =  $atts['limit'];
	}
	else{
		$limit = -1;
	}
	if(isset($atts['category'])){
		$category =  $atts['category'];
	}
	else{
		$category ='';
	}
	if(isset($atts['industry'])){
		$industry =  $atts['industry'];
	}
	else{
		$industry ='';
	}
	if(isset($atts['offset'])){
		$offset =  $atts['offset'];
	}
	else{
		$offset =0;
	}
	if(isset($atts['layout'])){
		$layout =  $atts['layout'];
	}
	else{
		$layout ='';
	}

	if(isset($atts['orderby'])){
		$orderby =  $atts['orderby'];
	}
	else{
		$orderby ='ID';
	}
	if(isset($atts['order'])){
		$order =  $atts['order'];
	}
	else{
		$order ='asc';
	}
	include('./wp-content/plugins/custom-plugin/templates/case_studies_slider.php');
	$stringa = ob_get_contents();
	ob_end_clean();
	return $stringa;
}