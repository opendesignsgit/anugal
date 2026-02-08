<?php

function enque_tabs_scripts() {	
    wp_enqueue_style( 'easyResponsiveTabscss', CUSTOM_PLUGIN_DIR . 'assets/css/easy-responsive-tabs.css', [] );
	wp_enqueue_script( 'easyResponsiveTabsjs', CUSTOM_PLUGIN_DIR . '/assets/js/easyResponsiveTabs.js',  array('jquery'), '1.0', true );
} 
add_action( 'wp_enqueue_scripts', 'enque_tabs_scripts',1 );


add_action( 'init', 'cptui_register_tab' );
function cptui_register_tab() {
	
	/**	 * Post Type: Responsive Tabs.	 */	
	$labels = [		
		"name" => __( "Responsive Tabs", "custom-post-type-ui" ),		
		"singular_name" => __( "Responsive Tab", "custom-post-type-ui" ),	
	];	
	$args = [		
		"label" => __( "Responsive Tabs", "custom-post-type-ui" ),
		'menu_icon' => 'dashicons-table-row-after',
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => "Responsive Tabs_group",
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "responsive-tabs", "with_front" => true ],
		"supports" => array( "title", "editor", "author", "thumbnail", "excerpt"), 
		"query_var" => true,
		"show_in_graphql" => false,
	];	
	register_post_type( "responsive-tabs", $args );

	register_taxonomy('tab-group',
	'responsive-tabs',
	array(
		'hierarchical' => true,
		'label' => 'Tab Group',
		'show_admin_column' => true,
	)
	);
	
}

add_shortcode('show_tabs', 'show_tabs');
function show_tabs($atts) {
    ob_start();
    if (isset($atts['list'])) {
        $list = $atts['list'];
    } else {
        $list = '';
    }
    if (isset($atts['tabgroup'])) {
        $tabgroup = $atts['tabgroup'];
    } else {
        $tabgroup = '';
    }
    if (isset($atts['class'])) {
        $class = $atts['class'];
    } else {
        $class = '';
    }
    if (isset($atts['orderby'])) {
        $orderby = $atts['orderby'];
    } else {
        $orderby = 'ID';
    }
    if (isset($atts['order'])) {
        $order = $atts['order'];
    } else {
        $order = 'ASC';
    }
    include('./wp-content/plugins/custom-plugin/templates/show_tabs.php');
    $stringa = ob_get_contents();
    ob_end_clean();
    return $stringa;
}
if(is_admin()){
    new Paulund_Wp_List_Table('Responsive Tabs','responsive-tabs','tab-group','dashicons-table-row-after');
}