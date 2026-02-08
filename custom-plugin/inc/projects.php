<?php
add_shortcode('show_projects', 'show_projects');
function show_projects($atts) {
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
    if (isset($atts['order'])) {
        $order = $atts['order'];
    } else {
        $order = 'ASC';
    }
    if (isset($atts['type'])) {
        $type = $atts['type'];
    } else {
        $type = '';
    }
    if (isset($atts['category'])) {
        $category = $atts['category'];
    }
    if (isset($atts['status'])) {
        $status = $atts['status'];
    }
    if (isset($atts['orderby'])) {
        $orderby = $atts['orderby'];
    } else {
        $orderby = 'ID';
    }
    include('./wp-content/plugins/custom-plugin/templates/show_projects.php');
    $stringa = ob_get_contents();
    ob_end_clean();
    return $stringa;
}

// add_shortcode('list_projects', 'list_projects');
// function list_projects($atts) {
//     ob_start();
//     if (isset($atts['list'])) {
//         $list = $atts['list'];
//     } else {
//         $list = '';
//     }
//     if (isset($atts['tabgroup'])) {
//         $tabgroup = $atts['tabgroup'];
//     } else {
//         $tabgroup = '';
//     }
//     if (isset($atts['class'])) {
//         $class = $atts['class'];
//     } else {
//         $class = '';
//     }
//     if (isset($atts['order'])) {
//         $order = $atts['order'];
//     } else {
//         $order = 'ASC';
//     }
//     if (isset($atts['type'])) {
//         $type = $atts['type'];
//     } else {
//         $type = '';
//     }
//     if (isset($atts['category'])) {
//         $category = $atts['category'];
//     }
//     if (isset($atts['status'])) {
//         $status = $atts['status'];
//     }
//     if (isset($atts['orderby'])) {
//         $orderby = $atts['orderby'];
//     } else {
//         $orderby = 'ID';
//     }
//     include('./wp-content/plugins/custom-plugin/templates/list_projects.php');
//     $stringa = ob_get_contents();
//     ob_end_clean();
//     return $stringa;
// }



add_shortcode('list_projects', 'list_projects');
function list_projects($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(
        array(
            'status' => '',
            'type' => '',
            'location' => '',
            'layout' => '',
            'class' => '',
            'limit' => -1,
            'view_more_link' => '',
        ),
        $atts
    );

    // Include the external file
    $file_path = ABSPATH . 'wp-content/plugins/custom-plugin/templates/list_projects.php';
    if (file_exists($file_path)) {
        ob_start();
        include($file_path);
        return ob_get_clean();
    } else {
        return 'Template file not found.';
    }
}

// Register Custom Post Type
function custom_post_type_project() {

    /**	 * Post Type: Projects.	 */	
	$labels = array(
        'name'                  => _x( 'Projects', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Project', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Projects', 'text_domain' ),
        'name_admin_bar'        => __( 'Projects', 'text_domain' ),
        'archives'              => __( 'Project Archives', 'text_domain' ),
        'attributes'            => __( 'Project Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Project:', 'text_domain' ),
        'all_items'             => __( 'All Projects', 'text_domain' ),
        'add_new_item'          => __( 'Add New Project', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Project', 'text_domain' ),
        'edit_item'             => __( 'Edit Project', 'text_domain' ),
        'update_item'           => __( 'Update Project', 'text_domain' ),
        'view_item'             => __( 'View Project', 'text_domain' ),
        'view_items'            => __( 'View Projects', 'text_domain' ),
        'search_items'          => __( 'Search Project', 'text_domain' ),
        'not_found'             => __( 'Project Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Project Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into project', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this project', 'text_domain' ),
        'items_list'            => __( 'Projects list', 'text_domain' ),
        'items_list_navigation' => __( 'Projects list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter projects list', 'text_domain' ),
    );
	$args = [		
		"label" => __( "Project", "custom-post-type-ui" ),
		'menu_icon' => 'dashicons-building',
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"supports" => array( "title", "editor", "author", "thumbnail", "excerpt", "page-attributes", "revisions"), 
		"query_var" => true,
		"show_in_graphql" => false,
        "rewrite" => array( "slug" => "project" )
		//"cptp_permalink_structure" => "/%proj-status%/%post_id%-%postname%/",
	];
	register_post_type( "projects", $args );

}
add_action( 'init', 'custom_post_type_project', 0 );

// Register Custom Taxonomies
function register_project_taxonomies() {
    
    // Taxonomy 1: Project Status
    register_taxonomy('project-status', 'projects', array(
        'hierarchical' => true,
        'label' => 'Project Status',
        'rewrite' => array('slug' => 'project-status'), 
        'show_admin_column' => true,
    ));

    // Taxonomy 2: Project Type
    register_taxonomy('project-type', 'projects', array(
        'hierarchical' => true,
        'label' => 'Project Type',
        'rewrite' => array('slug' => 'project-type'),
        'show_admin_column' => true,
    ));

    // Taxonomy 3: Project Location
    register_taxonomy('project-location', 'projects', array(
        'hierarchical' => true,
        'label' => 'Project Location',
        'rewrite' => array('slug' => 'project-location'),
        'show_admin_column' => true,
    ));

    add_rewrite_rule(
        '^projects/([^/]+)-([^/]+)-([^/]+)/?$',
        'index.php?post_type=projects&project-type=$matches[1]&project-location=$matches[2]&project-status=$matches[3]',
        'top'
    );
    add_rewrite_rule(
        '^projects/([^/]+)-([^/]+)/?$',
        'index.php?post_type=projects&project-type=$matches[1]&project-location=$matches[2]',
        'top'
    );
 
    add_action('init', 'flush_rewrite_rules');
}
add_action('init', 'register_project_taxonomies');



// add_filter( 'rewrite_rules_array', 'custom_rewrite_rules' );
// function custom_rewrite_rules( $rules ) {
//     $new_rules = array(
//         'project/([^/]+)-([^/]+)/?$' => 'index.php?post_type=project&parameter1=$matches[1]&parameter2=$matches[2]'
//     );
//     return $new_rules + $rules;
// }


add_filter('wp_insert_term_data', 'custom_modify_term_data', 10, 3);
function custom_modify_term_data($data, $taxonomy, $args)
{
    // Check if the taxonomy is 'your_taxonomy'
    if ($taxonomy === 'proj-status-location') {
        // Check if a parent term is selected
        if (isset($args['parent']) && $args['parent'] !== 0) {
            // Get the parent terms hierarchy
            $parent_terms = get_ancestors($args['parent'], $taxonomy, 'taxonomy');
            $parent_terms = array_reverse($parent_terms); // Reverse the array to start with the top-level parent
            // Build the prefix
            $prefix = '';
            foreach ($parent_terms as $parent_id) {
                $parent_term = get_term($parent_id, $taxonomy);
                if (!is_wp_error($parent_term)) {
                    $prefix .= $parent_term->slug . '-';
                }
            }
            // Get the immediate parent term
            $immediate_parent = get_term($args['parent'], $taxonomy);
            if (!is_wp_error($immediate_parent)) {
                //return print_r($immediate_parent);
                $prefix .= strtolower($immediate_parent->name) . '-';
            }
            // Manipulate the term data here
            if (isset($data['slug'])) {
                $data['slug'] = rtrim($prefix, '-') . '-' . $data['slug'];
            }
        }
    }
    return $data;
}


add_shortcode('projects_breadcrumb', 'projects_breadcrumb');
function projects_breadcrumb($atts) {

    ob_start();

    if (isset($atts['post_id'])) {

        $post_id = $atts['post_id'];

    } else {

        $post_id = '';

    }

	custom_post_type_breadcrumb();

    $stringa = ob_get_contents();

    ob_end_clean();

    return $stringa;

}
function custom_post_type_breadcrumb() {
    // Check if it's a single projects post
    if (is_singular('projects')) {
        global $post;
        $post_id = $post->ID;
        $terms = array();

        // Get the project-type term
        $project_type = get_the_terms($post_id, 'project-type');
        if (!empty($project_type)) {
            $terms[] = $project_type[0]->slug; 
            $termsnames[] = $project_type[0]->name; 
        }

        // Get the project-location term
        $project_location = get_the_terms($post_id, 'project-location');
        if (!empty($project_location)) {
            $terms[] = $project_location[0]->slug;
            $termsnames[] = $project_location[0]->name;
        }

        // Get the project-status term
        $project_status = get_the_terms($post_id, 'project-status');
        if (!empty($project_status)) {
            $terms[] = $project_status[0]->slug;
            $termsnames[] = $project_status[0]->name;
        }

        // Combine slugs to form the URL
        $combined_url = home_url('projects/' . implode('-', $terms));

        // Output breadcrumb
        echo '<div class="breadcrumb">';
        echo '<a href="' . home_url() . '">Home </a>'; // Home link
        echo '<a href="' . $combined_url . '">' . implode(' » ', $termsnames) . '</a>';
        echo get_the_title();
        echo '</div>';
    }
}



add_action( 'admin_menu', 'projects_metabox' );
function projects_metabox() {
    add_meta_box(
        'projects_metabox_id',
        'Project Options',
        'projects_metabox_callback',
        'projects', // Changed 'sliders' to 'projects'
        'normal',
        'default'
    );
}

function projects_metabox_callback( $post ) {
    $_projects_order = get_post_meta( $post->ID, '_projects_order', true );
    $count_projects = wp_count_posts( 'projects' )->publish; // Changed 'sliders' to 'projects'
    wp_nonce_field( 'project_q_edit_nonce', 'project_nonce' );
    
    ?>
    <table class="form-table">
        <tbody>
            <tr>
                <th><label for="_projects_order">Project Order</label></th>
                <td>
                    <select id="_projects_order" name="_projects_order">
                        <option value="">Select Order</option>
                        <?php 
                        for ($x = 1; $x <= $count_projects; $x++) { ?>
                            <option value="<?php echo $x ?>" <?php if($_projects_order == $x){echo 'selected';} ?>><?php echo $x ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
<?php }

add_filter('manage_projects_posts_columns', 'add_projects_order_column', 10, 1 );
function add_projects_order_column( $columns ) {    
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( $key === 'title' ) {
            // Add your custom column after the 'title' column
            $new_columns['_projects_order'] = 'NRI Order'; // Change '_projects_order' to your actual metadata key
        }
    }
    return $new_columns;
}


add_action('manage_projects_posts_custom_column', 'get_projects_order_column_value', 10, 2 );
function get_projects_order_column_value( $column_name, $post_id ) {
    switch ( $column_name ) {
        case '_projects_order':
            echo get_post_meta( $post_id, '_projects_order', true );
            break;
    }
}

add_action( 'quick_edit_custom_box', 'add_quick_edit_projects_order_field', 10, 3 );
function add_quick_edit_projects_order_field( $column_name, $post_type, $taxonomy ) {
    global $post;
    if ( $post_type == 'projects' ) { // Changed 'sliders' to 'projects'
        switch ( $column_name ) {
            case '_projects_order':
                wp_nonce_field( 'project_q_edit_nonce', 'project_nonce' ); ?>                                
                <fieldset class="inline-edit-col-right" id="#edit-">
                    <div class="inline-edit-col">
                        <label>
                            <span class="title">Project Order</span>
                            <span class="input-text-wrap">
                                <select name="_projects_order">
                                    <option value="">Select Order</option>
                                    <?php 
                                    $count_projects = wp_count_posts( 'projects' )->publish; // Changed 'sliders' to 'projects'
                                    for ($x = 1; $x <= $count_projects; $x++) { ?>
                                        <option value="<?php echo $x ?>" <?php //if (in_array($x, $existence)){echo 'disabled';} ?>><?php echo $x ?></option>
                                    <?php } ?>
                                </select>
                            </span>
                        </label>
                    </div>
                </fieldset>
                <?php
                break;
        }
    }
}

add_action( 'save_post_projects', 'save_projects_order_field' );
function save_projects_order_field() {
    if ( !wp_verify_nonce( $_POST['project_nonce'], 'project_q_edit_nonce' ) ) {
        return;
    }
    if( isset( $_POST ) && isset( $_POST['_projects_order'] ) ) {
        update_post_meta($_POST['post_ID'], '_projects_order', $_POST['_projects_order']);
    }
        
    return;
}

add_action('admin_print_footer_scripts-edit.php', 'quick_edit_projects_admin_js');
function quick_edit_projects_admin_js(){ 
    global $post_type;
    if ( $post_type == 'projects' ){ // Changed 'sliders' to 'projects'
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            var wp_inline_edit_function = inlineEditPost.edit;
            inlineEditPost.edit = function(post_id) {
                wp_inline_edit_function.apply(this, arguments);
                var id = 0;
                if (typeof(post_id) == 'object') {
                    id = parseInt(this.getId(post_id));
                }
                if (id > 0) {
                    var specific_post_edit_row = $('#edit-' + id),
                        specific_post_row = $('#post-' + id),
                        project_order = $('.column-_projects_order', specific_post_row).text();
                    $(':input[name="_projects_order"]', specific_post_edit_row).val(project_order);
                }
            }
        });
        </script>
        <?php
    }
}

add_action( 'wp_ajax_save_editable_cell', 'save_editable_cell' );

function save_editable_cell() {
    $column = $_POST['column'];
    $newValue = $_POST['newValue'];
    $postId = $_POST['postId'];

    // Update the database with the new value
    update_post_meta( $postId, $column, $newValue );

    wp_die();
}
// Add custom data-id attribute to post rows
function custom_post_row_actions($actions, $post) {
    // Add custom data-id attribute to the row
    $actions['data-id'] = $post->ID;
    return $actions;
}
add_filter('post_row_actions', 'custom_post_row_actions', 10, 2);
