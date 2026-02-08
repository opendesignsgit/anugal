<?php
function enquemyscripts_accordion() {
    //wp_enqueue_script( 'easyResponsiveTabsjs', CUSTOM_PLUGIN_DIR . '/assets/js/easyResponsiveTabs.js' , array('jquery'),'1.5',true );
    //wp_enqueue_style( 'easyResponsiveTabsjs', CUSTOM_PLUGIN_DIR . '/assets/js/easyResponsiveTabs.js' , array('jquery'),'1.5',true );
	wp_enqueue_style( 'simpleAccordion', CUSTOM_PLUGIN_DIR . '/assets/css/simple-accordion.css',  [] );
} 
add_action( 'wp_enqueue_scripts', 'enquemyscripts_accordion',1 );

add_action( 'init', 'cptui_register_accordion' );
function cptui_register_accordion() {
	/**	 * Post Type: Simple Accordion.	 */	
	$labels = [		
		"name" => __( "Simple Accordion", "custom-post-type-ui" ),		
		"singular_name" => __( "Simple Accordion", "custom-post-type-ui" ),	
	];	
	$args = [		
		"label" => __( "Simple Accordion", "custom-post-type-ui" ),
		"menu_icon" => "dashicons-slides",
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => "Simple Accordion_group",
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "simple-accordion", "with_front" => true ],
		"supports" => array( "title", "editor", "author", "thumbnail", "excerpt"), 
		"query_var" => true,
		"show_in_graphql" => false,
	];	
	register_post_type( "simple-accordion", $args );

	register_taxonomy('accordion-group',
	'simple-accordion',
	array(
		'hierarchical' => true,
		'label' => 'Accordion Group',
		'show_admin_column' => true,
	)
	);	
}

add_shortcode('show_accordion', 'show_accordion');
function show_accordion($atts) {
    ob_start();
    if (isset($atts['list'])) {
        $list = $atts['list'];
    } else {
        $list = '';
    }
    if (isset($atts['accordiongroup'])) {
        $accordiongroup = $atts['accordiongroup'];
    } else {
        $accordiongroup = '';
    }
    if (isset($atts['class'])) {
        $class = $atts['class'];
    } else {
        $class = '';
    }
    if (isset($atts['order'])) {
        $order = $atts['order'];
    } else {
        $order = 'ASC';
    }
    include('./wp-content/plugins/custom-plugin/templates/show_accordion.php');
    $stringa = ob_get_contents();
    ob_end_clean();
    return $stringa;
}

if(is_admin()){
    new Paulund_Wp_List_Table('Simple Accordion','simple-accordion','accordion-group','dashicons-insert');
}