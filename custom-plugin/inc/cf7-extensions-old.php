<?php
add_action( 'wp_head' , 'custom_cf7ext_css' );
function custom_cf7ext_css(){?>
<script type='text/javascript'> 
document.addEventListener( 'wpcf7mailsent', function( event ) {
	var inputs = event.detail.inputs;
	if(event.detail.contactFormId == '19218') {
		//SaveToDisk(General.site_url + '/wp-content/uploads/2023/02/Kaar-Tech-Brochure.pdf', "Kaar Tech Brochure");
	}
});
function SaveToDisk(fileURL, fileName) {
	// for non-IE
	if (!window.ActiveXObject) {
		var save = document.createElement('a');
		save.href = fileURL;
		save.target = '_blank';
		save.download = fileName || 'unknown';

		var evt = new MouseEvent('click', {
			'view': window,
			'bubbles': true,
			'cancelable': false
		});
		save.dispatchEvent(evt);
		(window.URL || window.webkitURL).revokeObjectURL(save.href);
	}

	// for IE < 11
	else if (!!window.ActiveXObject && document.execCommand) {
		var _window = window.open(fileURL, '_blank');
		_window.document.close();
		_window.document.execCommand('SaveAs', true, fileName || fileURL)
		_window.close();
	}
}
</script> 
<style>
</style>
<?php
}

add_action( 'init', 'cpt_for_cf7_submission' );
function cpt_for_cf7_submission() {
	/**
	 * Post Type: CF7 Submissions.
	 */
	$labels = [
		"name" => __( "CF7 Submissions", "custom-post-type-ui" ),
		"singular_name" => __( "CF7 Submission", "custom-post-type-ui" ),
		"edit_item" => __( "View Submitted Data", "custom-post-type-ui" ),
	];
	$args = [
		"label" => __( "CF7 Submissions", "custom-post-type-ui" ),
		"menu_icon" => "dashicons-database",
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		'capabilities' => array(
			'create_posts' => false,
		),
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "cf7_submissions", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "nill"],
		//"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];
	register_post_type( "cf7_submissions", $args );
}

add_action('wpcf7_mail_sent','save_my_form_data_to_my_cpt');
add_action('wpcf7_mail_failed','save_my_form_data_to_my_cpt');
function save_my_form_data_to_my_cpt($contact_form){
    $submission = WPCF7_Submission::get_instance();
    if (!$submission){
        return;
    }
    $posted_data = $submission->get_posted_data();
	$string = serialize($posted_data); 
    $new_post = array();
	$new_post['post_title'] = '';
	foreach($posted_data as $name => $value) {
		$new_post['meta_input'][$name] = $value;
	}
    $new_post['post_type'] = 'cf7_submissions'; //insert here your CPT
    $new_post['post_content'] = $string;
    $new_post['post_status'] = 'publish';
    $new_post['meta_input']['submitted-from'] = $contact_form->title;
    //When everything is prepared, insert the post into your Wordpress Database
    if($post_id = wp_insert_post($new_post)){
		$uploaded_files = $submission->uploaded_files();
        foreach ($uploaded_files as $key => $value) {
            $upload = wp_upload_bits($value[0], null, file_get_contents($value[0]));
            $wp_filetype = wp_check_filetype($value[0], null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => basename($value[0]),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            $attachment_url = wp_get_attachment_url($attachment_id);
			$link_html = "<a href=".$attachment_url." target='_blank'>Open / Download</a>";
			update_post_meta( $post_id, $key , $link_html );
        }
    } else {
       //The post was not inserted correctly, do something (or don't ;) )
    }
    return;
}

add_filter('manage_cf7_submissions_posts_columns', 'add_cf7_submissions_order_column', 10, 2 );
function add_cf7_submissions_order_column( $columns ) {
		$lists = get_all_meta_keys('cf7_submissions',true,true);
		$columns = array();
		foreach ($lists as $list) {
			$columns[$list] = '<span class="popup_admin_span">'.slugToTitle($list).'</span>';
		}
        //$columns['cb'] = '<input type="checkbox" />';
        //$columns['title'] = '<span class="popup_admin_span">Title</span>';
    return $columns;
}
add_action('manage_cf7_submissions_posts_custom_column', 'get_cf7_submissions_order_column_value', 10, 2 );
function get_cf7_submissions_order_column_value( $column_name, $post_id ) {
	$lists = get_all_meta_keys('cf7_submissions',true,true);
	foreach ($lists as $list) {
		if($column_name === $list){
			echo '<div class="shortcode">'.get_post_meta( $post_id, $list, true ).'</div>';
		}
	}
}

function custom_meta_box() {
    add_meta_box(
        'custom_meta_box', // ID
        'Submission Data', // Title
        'display_custom_meta_box', // Callback function
        'cf7_submissions', // Screen (post, page, dashboard, link, attachment, custom post type)
        'normal', // Context (normal, advanced, side)
        'default' // Priority (default, high, low, core)
    );
}
add_action( 'add_meta_boxes', 'custom_meta_box' );
function display_custom_meta_box( $post ) {
    $my_postid = $post->ID;//This is page id or post id
	$content_post = get_post($my_postid);
	$content = $content_post->post_content;
	$content = unserialize($content);
	echo "<table>";
	$custom_fields = get_post_custom($post->ID);
	foreach ( $custom_fields as $key => $row ) {
		if ( ! starts_with( $key, '_' ) ) {
			if(is_serialized(implode( ', ', $row ))){
				$row = implode(', ',maybe_unserialize(implode( ', ', $row )));
			}else{
				$row = implode( ', ', $row );
			}
			echo "<tr>";
			echo "<td><b>" . slugToTitle($key) . " : </b></td>";
			echo "<td>" .  $row  . "</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
}

function starts_with( $haystack, $needle ) {
    return substr( $haystack, 0, strlen( $needle ) ) === $needle;
}

add_filter( 'post_row_actions', 'remove_row_actions_post', 10, 2 );
function remove_row_actions_post( $actions, $post ) {
    if( $post->post_type === 'cf7_submissions' ) {
        unset( $actions['inline'] );
        unset( $actions['clone'] );
        unset( $actions['trash'] );
    }
    return $actions;
}

add_action('wp_trash_post', 'restrict_post_deletion');
function restrict_post_deletion($post_id) {
    if( get_post_type($post_id) === 'cf7_submissions' ) {
      wp_die('The post you were trying to delete is protected.');
    }
}

add_action( 'admin_head', function () { 

?>
<style>table.wp-list-table th {width: 110px;}</style>
<?php
    $current_screen = get_current_screen();
    if ( 'post' === $current_screen->base &&
    'cf7_submissions' === $current_screen->post_type ) :
    ?>
        <style>#delete-action { display: none; }</style>
    <?php
    endif;
    if ( 'term' === $current_screen->base &&
    'org' === $current_screen->taxonomy ) :
    ?>
        <style>#delete-link { display: none; }</style>
    <?php
    endif;
} );


/* form_tag handler */

add_action( 'wpcf7_init', 'wpcf7_add_form_tag_custom_checkbox', 10, 0 );

function wpcf7_add_form_tag_custom_checkbox() {
	wpcf7_add_form_tag( array( 'custom_checkbox', 'custom_checkbox*' ),
		'wpcf7_custom_checkbox_form_tag_handler',
		array(
			'name-attr' => true,
			'selectable-values' => true,
			'multiple-controls-container' => true,
		)
	);
}

function wpcf7_custom_checkbox_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$label_first = $tag->has_option( 'label_first' );
	$use_label_element = $tag->has_option( 'use_label_element' );
	$exclusive = $tag->has_option( 'exclusive' );
	$free_text = $tag->has_option( 'free_text' );
	$multiple = false;

	if ( 'custom_checkbox' == $tag->basetype ) {
		$multiple = ! $exclusive;
	} else { // radio
		$exclusive = false;
	}

	if ( $exclusive ) {
		$class .= ' wpcf7-exclusive-custom_checkbox';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();

	if ( $validation_error ) {
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
			$tag->name
		);
	}

	$tabindex = $tag->get_option( 'tabindex', 'signed_int', true );

	if ( false !== $tabindex ) {
		$tabindex = (int) $tabindex;
	}

	$html = '';
	$count = 0;

	if ( $data = (array) $tag->get_data_option() ) {
		if ( $free_text ) {
			$tag->values = array_merge(
				array_slice( $tag->values, 0, -1 ),
				array_values( $data ),
				array_slice( $tag->values, -1 ) );
			$tag->labels = array_merge(
				array_slice( $tag->labels, 0, -1 ),
				array_values( $data ),
				array_slice( $tag->labels, -1 ) );
		} else {
			$tag->values = array_merge( $tag->values, array_values( $data ) );
			$tag->labels = array_merge( $tag->labels, array_values( $data ) );
		}
	}

	$values = $tag->values;
	$labels = $tag->labels;

	$default_choice = $tag->get_default_option( null, array(
		'multiple' => $multiple,
	) );

	$hangover = wpcf7_get_hangover( $tag->name, $multiple ? array() : '' );

	foreach ( $values as $key => $value ) {
		if ( $hangover ) {
			$checked = in_array( $value, (array) $hangover, true );
		} else {
			$checked = in_array( $value, (array) $default_choice, true );
		}

		if ( isset( $labels[$key] ) ) {
			$label = $labels[$key];
		} else {
			$label = $value;
		}

		$item_atts = array(
			'type' => 'checkbox',
			'name' => $tag->name . ( $multiple ? '[]' : '' ),
			'value' => $value,
			'checked' => $checked,
			'tabindex' => false !== $tabindex ? $tabindex : '',
			'aria-required' => $tag->is_required() ? 'true' : '',
		);

		$item_atts = wpcf7_format_atts( $item_atts );

		if ( $label_first ) { // put label first, input last
			$item = sprintf(
				'<span class="wpcf7-list-item-label">%1$s</span><input %2$s />',
				esc_html( $label ),
				$item_atts
			);
		} else {
			$item = sprintf(
				'<input %2$s /><span class="wpcf7-list-item-label">%1$s</span>',
				esc_html( $label ),
				$item_atts
			);
		}

		if ( $use_label_element ) {
			$item = '<label>' . $item . '</label>';
		}

		if ( false !== $tabindex
		and 0 < $tabindex ) {
			$tabindex += 1;
		}

		$class = 'wpcf7-list-item';
		$count += 1;

		if ( 1 == $count ) {
			$class .= ' first';
		}

		if ( count( $values ) == $count ) { // last round
			$class .= ' last';

			if ( $free_text ) {
				$free_text_name = $tag->name . '_free_text';

				$free_text_atts = array(
					'name' => $free_text_name,
					'class' => 'wpcf7-free-text',
					'tabindex' => false !== $tabindex ? $tabindex : '',
				);

				if ( wpcf7_is_posted()
				and isset( $_POST[$free_text_name] ) ) {
					$free_text_atts['value'] = wp_unslash( $_POST[$free_text_name] );
				}

				$free_text_atts = wpcf7_format_atts( $free_text_atts );

				$item .= sprintf( ' <input type="text" %s />', $free_text_atts );

				$class .= ' has-free-text';
			}
		}

		$item = '<span class="' . esc_attr( $class ) . '">' . $item . '</span>';
		$html .= $item;
	}

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap" data-name="%1$s"><span %2$s>%3$s</span>%4$s</span>',
		esc_attr( $tag->name ),
		wpcf7_format_atts( $atts ),
		$html,
		$validation_error
	);

	return $html;
}


add_action(
	'wpcf7_swv_create_schema',
	'wpcf7_swv_add_custom_checkbox_rules',
	10, 2
);

function wpcf7_swv_add_custom_checkbox_rules( $schema, $contact_form ) {
	$tags = $contact_form->scan_form_tags( array(
		'type' => array( 'custom_checkbox*' ),
	) );

	foreach ( $tags as $tag ) {
		$schema->add_rule(
			wpcf7_swv_create_rule( 'required', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_required' ),
			) )
		);
	}
}

function slugToTitle($slug) {

	$words = explode("-", $slug);

	$title = "";

	foreach ($words as $word) {

		$title .= ucfirst($word) . " ";

	}

	return trim($title);

}

function get_meta_values( $meta_key = '', $post_type = 'post', $post_status = 'publish' ) {

    global $wpdb;

    if( empty( $meta_key ) )

        return;

    $meta_values = $wpdb->get_col( $wpdb->prepare( "

        SELECT pm.meta_value FROM {$wpdb->postmeta} pm

        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id

        WHERE pm.meta_key = %s 

        AND p.post_type = %s 

        AND p.post_status = %s 

    ", $meta_key, $post_type, $post_status ) );

    return $meta_values;

}

function get_all_meta_keys($post_type = 'post', $exclude_empty = false,$exclude_hidden = false){

    global $wpdb;

    $query = "

        SELECT DISTINCT($wpdb->postmeta.meta_key) 

        FROM $wpdb->posts 

        LEFT JOIN $wpdb->postmeta 

        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 

        WHERE $wpdb->posts.post_type = '%s'

    ";

    if($exclude_empty) 

        $query .= " AND $wpdb->postmeta.meta_key != ''";

    if($exclude_hidden) 

        $query .= " AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' ";

    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));

    return $meta_keys;

}



/* add_filter( 'wpcf7_form_elements', 'dd_wpcf7_form_elements_replace' );
function dd_wpcf7_form_elements_replace( $content ) {
    // $name == Form Tag Name [textarea* your-message] 
    $name = 'aria-required="true"';
    $str_pos = strpos( $content, $name );
    if (false !== $str_pos) {
        $content = substr_replace( $content, ' required ', $str_pos, 0 );
    }
    return $content;
} 
add_filter( 'wpcf7_form_tag', function ( $tag ) {
    $datas = [];
    foreach ( (array)$tag['options'] as $option ) {
        if ( strpos( $option, 'required' ) === 0 ) {
            $option = explode( ':', $option, 2 );
            $datas[$option[0]] = apply_filters('wpcf7_option_value', $option[1], $option[0]);
        }
    }
    if ( ! empty( $datas ) ) {
        $id = uniqid('tmp-wpcf');
        $tag['options'][] = "class:$id";
        add_filter( 'wpcf7_form_elements', function ($content) use ($id, $datas) {
            return str_replace($id, $name, str_replace($id.'"', '"'. wpcf7_format_atts($datas), $content));
        });
    }
    return $tag;
} );

add_action( 'wpcf7_init', 'custom_add_form_tag_time_selector' );
function custom_add_form_tag_time_selector() {
	wpcf7_add_form_tag( array( 'time_selector', 'time_selector*' ), 'time_selector_form_tag_handler', true );
}

function time_selector_form_tag_handler( $tag ) {

    $tag = new WPCF7_FormTag( $tag );

    if ( empty( $tag->name ) ) {
        return '';
    }

    $validation_error = wpcf7_get_validation_error( $tag->name );

    $class = wpcf7_form_controls_class( $tag->type );

    if ( $validation_error ) {
        $class .= ' wpcf7-not-valid';
    }

    $atts = array();

    $atts['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();

    if ( $tag->is_required() ) {
    $atts['aria-required'] = 'true';
    }

    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $atts['name'] = $tag->name;

    $atts = wpcf7_format_atts( $atts );

     

    $html = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s"><input type="time" %2$s></input>%3$s</span>',
        sanitize_html_class( $tag->name ),
        $atts,
        $validation_error
    );

    return $html;
}

add_filter( 'wpcf7_validate_time_selector', 'wpcf7_time_selector_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_time_selector*', 'wpcf7_time_selector_validation_filter', 10, 2 );

function wpcf7_time_selector_validation_filter( $result, $tag ) {
    $tag = new WPCF7_FormTag( $tag );

    $name = $tag->name;

    if ( isset( $_POST[$name] ) && is_array( $_POST[$name] ) ) {
        foreach ( $_POST[$name] as $key => $value ) {
            if ( '' === $value ) {
                unset( $_POST[$name][$key] );
            }
        }
    }

    $empty = ! isset( $_POST[$name] ) || empty( $_POST[$name] ) && '0' !== $_POST[$name];

    if ( $tag->is_required() && $empty ) {
        $result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
    }

    return $result;
}

add_action( 'wpcf7_init', 'custom_add_form_tag_myCustomField' );

function custom_add_form_tag_myCustomField() {
    wpcf7_add_form_tag( array( 'myCustomField', 'myCustomField*' ), 
'custom_myCustomField_form_tag_handler', true );
}

function custom_myCustomField_form_tag_handler( $tag ) {

    $tag = new WPCF7_FormTag( $tag );

    if ( empty( $tag->name ) ) {
        return '';
    }

    $validation_error = wpcf7_get_validation_error( $tag->name );

    $class = wpcf7_form_controls_class( $tag->type );

    if ( $validation_error ) {
        $class .= ' wpcf7-not-valid';
    }

    $atts = array();

    $atts['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();

    if ( $tag->is_required() ) {
    $atts['aria-required'] = 'true';
    }

    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $atts['name'] = $tag->name;

    $atts = wpcf7_format_atts( $atts );

    $myCustomField = '';

    $query = new WP_Query(array(
        'post_type' => 'CUSTOM POST TYPE HERE',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby'       => 'title',
        'order'         => 'ASC',
    ));

    while ($query->have_posts()) {
        $query->the_post();
        $post_title = get_the_title();
        $myCustomField .= sprintf( '<option value="%1$s">%1$s</option>', 
esc_html( $post_title ) );
    }

    wp_reset_query();

    $myCustomField = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
        sanitize_html_class( $tag->name ),
        $atts,
        $myCustomField,
        $validation_error
    );

    return $myCustomField;
}*/


