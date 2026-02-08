<?php

function cptui_register_my_cpts_whitepaper() {

	/**
	 * Post Type: Whitepaper.
	 */

	$labels = [
		"name" => __( "Whitepaper", "astra" ),
		"singular_name" => __( "Whitepapers", "astra" ),
		"menu_name" => __( "Whitepapers", "astra" ),
	];

	$args = [
		"label" => __( "Whitepaper", "astra" ),
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
		"rewrite" => [ "slug" => "whitepaper", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-media-document",
		"supports" => [ "title", "editor", "thumbnail" ],
		"show_in_graphql" => false,
	];

	register_post_type( "whitepaper", $args );
}

add_action( 'init', 'cptui_register_my_cpts_whitepaper' );


add_shortcode('show_whitepapers','show_whitepapers');
function show_whitepapers(){
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
    }
    include('./wp-content/plugins/custom-plugin/templates/show_whitepapers.php');
    $stringa = ob_get_contents();
    ob_end_clean();
    return $stringa;
}