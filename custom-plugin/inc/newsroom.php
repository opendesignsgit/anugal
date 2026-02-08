<?php

add_action('init', 'cpt_register_newsroom');
function cpt_register_newsroom(){

	/**
	 * Post Type: Newsroom.
	 */

	$labels = [
		"name" => __("Newsrooms", "custom-post-type-ui"),
		"singular_name" => __("Newsroom", "custom-post-type-ui"),
	];

	$args = [
		"label" => __("Newsroom", "custom-post-type-ui"),
		"menu_icon" => "dashicons-megaphone",
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
		"rewrite" => ["slug" => "newsroom", "with_front" => true],
		"query_var" => true,
		"supports" => ["title", "editor", "thumbnail", "excerpt"],
		//"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type("newsroom", $args);

	register_taxonomy('newsroomregion', 'newsroom', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => _x('Regions', 'taxonomy general name'),
			'singular_name' => _x('Region', 'taxonomy singular name'),
			'search_items' => __('Search Region'),
			'all_items' => __('All Region'),
			'parent_item' => __('Parent Region'),
			'parent_item_colon' => __('Parent Region:'),
			'edit_item' => __('Edit Region'),
			'update_item' => __('Update Region'),
			'add_new_item' => __('Add New Region'),
			'new_item_name' => __('New Region Name'),
			'menu_name' => __('Region'),
		),
		'rewrite' => array(
			'slug' => 'newsroomregion',
			'with_front' => false,
			'hierarchical' => true
		),
	)
	);
	register_taxonomy('newsroomcategory', 'newsroom', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => _x('Categories', 'taxonomy general name'),
			'singular_name' => _x('Category', 'taxonomy singular name'),
			'search_items' => __('Search Category'),
			'all_items' => __('All Category'),
			'parent_item' => __('Parent Category'),
			'parent_item_colon' => __('Parent Category:'),
			'edit_item' => __('Edit Category'),
			'update_item' => __('Update Category'),
			'add_new_item' => __('Add New Category'),
			'new_item_name' => __('New Category Name'),
			'menu_name' => __('Category'),
		),
		'rewrite' => array(
			'slug' => 'newsroomcategory',
			'with_front' => false,
			'hierarchical' => true
		),
	)
	);
}

add_shortcode('show_newsroom', 'show_newsroom');
function show_newsroom($atts){
	ob_start();
	$page = 1;
	$per_page = isset($atts['per_page']) ? $atts['per_page'] : 7;
	$class = isset($atts['class']) ? $atts['class'] : '';
	$order = isset($atts['order']) ? $atts['order'] : 'ASC';
	$orderby = isset($atts['order']) ? $atts['order'] : 'ID';
	$categories_in = isset($atts['categories_in']) ? $atts['categories_in'] : '';
	$categories_not_in = isset($atts['categories_not_in']) ? $atts['categories_not_in'] : '';
	$type = isset($atts['type']) ? $atts['type'] : '';
	$show_filter = isset($atts['show_filter']) ? $atts['show_filter'] : '';
	$selected_region = isset($atts['selected_region']) ? $atts['selected_region'] : '';
	?>
	<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="newsroom_filter">
		<div class="postsinputs span_12" id="postsinputs">
			<div class="span_25 posts_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
				<label>Filter by : </label>
				<?php
				$newsroomregion = array(
					'show_option_none' => 'Select Region',
					'option_none_value' => '',
					'orderby' => 'ID',
					'order' => 'ASC',
					'show_count' => 0,
					'hide_empty' => 0,
					'child_of' => 0,
					'exclude' => '1',
					'echo' => 1,
					'selected' => $selected_region,
					'hierarchical' => 1,
					'name' => 'newsroomregion',
					'id' => 'newsroomregion',
					'class' => 'form-no-clear',
					'depth' => 1,
					'tab_index' => 0,
					'taxonomy' => 'newsroomregion',
					'hide_if_empty' => true,
					'required' => false
				);
				wp_dropdown_categories($newsroomregion);
				?>
			</div>
			<div class="span_25 posts_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
				<span class="loader" style="display:none;width: 13px;margin-left: auto;margin-right: auto;"><img
						src="<?php echo site_url() ?>/wp-content/plugins/custom-plugin//images/loader.gif"></span>
			</div>
		</div>
		<input type="hidden" name="action" value="newsroomfilter">
		<input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
		<input type="hidden" name="categories_in" value="<?php echo $categories_in; ?>">
		<input type="hidden" name="categories_not_in" value="<?php echo $categories_not_in; ?>">
		<input type="hidden" name="type" value="<?php echo $type; ?>">
	</form>
	<?php $ns_id = uniqid(); ?>
	<div class="newsroomoutputs span_12 cvf_pag_loading" id="newsroomoutputs">
		<div id="response" class="<?php echo $ns_id ?>">
			<?php
			$post_type = 'newsroom';
			$template = 'show_newsroom';
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'hierarchical' => true,
				'posts_per_page' => $per_page,
				'orderby' => 'date',
				'order' => 'DESC', 
			);
			
			if(!empty($categories_in) && !empty($categories_not_in))
				$args['tax_query']['relation'] = 'AND';

			if (!empty($selected_region))
				$args['tax_query'][] = array('taxonomy' => 'newsroomregion', 'field' => 'ID', 'terms' => $selected_region);
				
			if (!empty($categories_in))
				$args['tax_query'][] = array('taxonomy' => 'newsroomcategory','field' => 'ID','terms' => $categories_in);

			if (!empty($categories_not_in))
				$args['tax_query'][] = array('taxonomy' => 'newsroomcategory', 'field' => 'ID', 'terms' => $categories_not_in, 'operator' => 'NOT IN');

			//echo '<div>'.print_r($args).'</div>';
			//print_r($args);
			$the_query = new WP_Query($args);
			if ($the_query->have_posts()) {
				$i = 1;
				while ($the_query->have_posts()) {
					$the_query->the_post();
					include(PLUGIN_DIR . '/templates/show_newsroom.php');
					$i++;
				}
				$count = $the_query->found_posts;
				if($type != 'slider')
					echo pagination_load_posts($page, $count, $per_page);
			} else {
				echo 'No Post Found.';
			}
			wp_reset_postdata();
			?>
		</div>
		</div>
	<script>
		jQuery(function ($) {
			<?php if($type == 'slider'){ ?>
				function destroyCarousel(){
					if($('#response').hasClass('<?php echo $ns_id?>')){
						$('#response').slick('unslick');
					}
				}
				function applySlider() {
					$('.<?php echo $ns_id ?>').slick({
						infinite: false,
						autoplay: false,
						autoplaySpeed: 5000,
						speed: 800,
						slidesToScroll: 1,
						slidesToShow: 3,
						arrows: true,
						dots: false,
						cssEase: 'linear',
						responsive: [{
							breakpoint: 1100,
							settings: {
								slidesToShow: 3,
								slidesToScroll: 1
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: 1,
								slidesToScroll: 1
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: 1,
								slidesToScroll: 1
							}
						}]
					});
				}
				applySlider();
			<?php } ?>
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

			function load_all_post(page, per_page) {
				$(".btn.btn-go").attr("disabled", true);
				$(".cvf-pagination-nav").fadeIn().css('background', '#ccc');
				var newsroomregion = $(".newsroomoutputs .cvf-universal-pagination").data('newsroomregion');
				var data = {
					page: page,
					per_page: per_page,
					categories_in: $('#newsroom_filter input[name="categories_in"]').val(),
					categories_not_in: $('#newsroom_filter input[name="categories_not_in"]').val(),
					type: $('#newsroom_filter input[name="type"]').val(),
					action: "newsroomfilter",
					newsroomregion: newsroomregion
				};
				$.post(ajaxurl, data, function (response) {
					$(".newsroomoutputs #response").html(response);
					$('html, body').scrollTop($('#newsroom_filter').offset().top); 
					$(".cvf-pagination-nav").css({ 'background': 'none', 'transition': 'all 1s ease-out' });
					$(".btn.btn-go").attr("disabled", false);
					$('#newsroom_filter .loader').css('display', 'none');
					$('.newsroomoutputs .cvf-universal-pagination').attr('data-newsroomregion', $('#newsroomregion').val());
				});
			}
			$(document).on('click', '.newsroomoutputs .cvf-universal-pagination .active', function (e) {
				var page = $(this).attr('p');
				var per_page = $(this).attr('pp');
				load_all_post(page, per_page);
			});

			$('#newsroomregion').change(function() {
				//alert('change');
				var type = '<?php echo $type ?>';
				var filter = $('#newsroom_filter');
				$.ajax({
					url: filter.attr('action'),
					data: filter.serialize(), // form data
					type: filter.attr('method'), // POST
					beforeSend: function (xhr) {
						$('#newsroom_filter .loader').css('display', 'block');
						$('#newsroom_filter').find('button').text('Go');
					},
					success: function (data) {
						if(type == 'slider'){
							destroyCarousel();
						}
						filter.find('button').text('Go');
						$('.newsroomoutputs #response').html(data);
						$(".btn.btn-go").attr("disabled", false);
						$('#newsroom_filter .loader').css('display', 'none');
						$('.newsroomoutputs .cvf-universal-pagination').attr('data-newsroomregion', $('#newsroomregion').val());
						if(type == 'slider'){
							applySlider();
						}
					}
				});
				return false;
			});

		});
	</script>
	<?php
	$stringa = ob_get_contents();
	ob_end_clean();
	return $stringa;
}

add_action('wp_ajax_newsroomfilter', 'newsroomfilter');
add_action('wp_ajax_nopriv_newsroomfilter', 'newsroomfilter');
function newsroomfilter(){
	$page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : 1;
	$per_page = isset($_REQUEST['per_page']) ? sanitize_text_field($_REQUEST['per_page']) : 3;
	$categories_in = isset($_REQUEST['categories_in']) ? sanitize_text_field($_REQUEST['categories_in']) : '';
	$categories_not_in = isset($_REQUEST['categories_not_in']) ? sanitize_text_field($_REQUEST['categories_not_in']) : '';
	$newsroomregion = isset($_REQUEST['newsroomregion']) ? sanitize_text_field($_REQUEST['newsroomregion']) : '';
	$type = isset($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';

	$start = ($page - 1) * $per_page;

	$args = array(
		'post_type' => 'newsroom',
		'post_status' => 'publish',
		'hierarchical' => true,
		'posts_per_page' => $per_page,
		'offset' => $start,
		'orderby' => 'date', // Order by date
		'order' => 'DESC', // Sort in descending order (newest first)
	);

	if (!empty($categories_in) && !empty($categories_not_in))
		$args['tax_query']['relation'] = 'AND';

	if (!empty($categories_not_in))
		$args['tax_query'][] = array('taxonomy' => 'newsroomcategory', 'field' => 'ID', 'terms' => $categories_not_in, 'operator' => 'NOT IN');

	if (!empty($categories_in))
		$args['tax_query'][] = array('taxonomy' => 'newsroomcategory', 'field' => 'ID', 'terms' => $categories_in);

	if (!empty($newsroomregion))
		$args['tax_query'][] = array('taxonomy' => 'newsroomregion', 'field' => 'ID', 'terms' => $newsroomregion );
	
			
	$query = new WP_Query($args);
	if ($query->have_posts()) {
		$i = 1;
		while ($query->have_posts()) {
			$query->the_post();
			include(PLUGIN_DIR . '/templates/show_newsroom.php');
			$i++;
		}
		$count = $query->found_posts;
		if ($type != 'slider')
			echo pagination_load_posts($page, $count, $per_page);
	} else {
		echo 'No Post Found.';
	}

	die();
}