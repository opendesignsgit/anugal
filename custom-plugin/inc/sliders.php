<?php

function enque_sliders_scripts() {

	wp_enqueue_style( 'slickcss',  CUSTOM_PLUGIN_DIR . '/assets/css/slick.css',  [] );	

	wp_enqueue_style( 'slickthemecss', CUSTOM_PLUGIN_DIR . '/assets/css/slick-theme.css',  [] );	

	wp_enqueue_script( 'slickjs', CUSTOM_PLUGIN_DIR . '/assets/js/slick.js',  array('jquery'), '1.0', true );

} 

add_action( 'wp_enqueue_scripts', 'enque_sliders_scripts',1 );





add_action( 'init', 'cptui_register_slider' );

function cptui_register_slider() {

	

	

	/**	 * Post Type: Slick Slides.	 */	

	$labels = [		

		"name" => __( "All Slick Slides", "custom-post-type-ui" ),		

		"singular_name" => __( "Slick Slide", "custom-post-type-ui" ),	

	];	

	$args = [		

		"label" => __( "Slick Slides", "custom-post-type-ui" ),

		'menu_icon' => 'dashicons-slides',

		"labels" => $labels,

		"description" => "",

		"public" => true,

		"publicly_queryable" => true,

		"show_ui" => true,

		"show_in_rest" => true,

		"rest_base" => "",

		"rest_controller_class" => "WP_REST_Posts_Controller",

		"has_archive" => true,

		"show_in_menu" => "Slick Sliders_group",

		"show_in_nav_menus" => true,

		"delete_with_user" => false,

		"exclude_from_search" => false,

		"capability_type" => "post",

		"map_meta_cap" => true,

		"hierarchical" => false,

		"rewrite" => [ "slug" => "slick-slides", "with_front" => true ],

		"supports" => array( "title", "editor", "author", "thumbnail", "excerpt"), 

		"query_var" => true,

		"show_in_graphql" => false,

	];	

	register_post_type( "slick-slides", $args );



	register_taxonomy('slide-group',

	'slick-slides',

	array(

		'hierarchical' => true,

		'label' => 'Slide Group',

		'show_admin_column' => true,

	)

	);

}



add_shortcode('show_slides', 'show_slides');

function show_slides($atts) {

    ob_start();

    if (isset($atts['list'])) {

        $list = $atts['list'];

    } else {

        $list = '';

    }

    if (isset($atts['limit'])) {

        $limit = $atts['limit'];

    } else {

        $limit = -1;

    }

    if (isset($atts['slidegroup'])) {

        $slidegroup = $atts['slidegroup'];

    } else {

        $slidegroup = '';

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

    include('./wp-content/plugins/custom-plugin/templates/show_slides.php');

    $stringa = ob_get_contents();

    ob_end_clean();

    return $stringa;

}

if(is_admin()){

    new Paulund_Wp_List_Table('Slick Sliders','slick-slides','slide-group','dashicons-slides');

}