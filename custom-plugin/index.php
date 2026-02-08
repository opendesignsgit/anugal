<?php

/*

 * Plugin Name: Custom Plugin

 * Description: Cutsom Plugin for VGK Developers

 * Version: 1.0

 * Author: Open Designs

 * Author URI: https://www.opendesignsin.com/

 * Text Domain: custom

 */



defined('ABSPATH') or die('Forbidden');



if (!function_exists('add_action')) {

	echo "Hi there! I'm just a plugin, not much I can do when called directly.";

	exit;
}



// Setup

define('CUSTOM_PLUGIN_URL', __FILE__);

define('CUSTOM_PLUGIN_DIR', plugin_dir_url(__FILE__));

define('PLUGIN_DIR', WP_PLUGIN_DIR . '/custom-plugin');



/** Disable Gutenberg Editor For Post's and Widgets **/

add_filter('gutenberg_use_widgets_block_editor', '__return_false', 100);

add_filter('use_widgets_block_editor', '__return_false');

add_filter('use_block_editor_for_post', '__return_false', 10);

/** Disable Gutenberg Editor For Post's and Widgets **/



/** AMP **/

//include( plugin_dir_path(__FILE__) . '/inc/amp.php');

/** AMP **/



/** Custom Post Types Table **/

include(plugin_dir_path(__FILE__) . '/inc/tables.php');

/** Custom Post Types Table **/



/** TAB'S **/

include(plugin_dir_path(__FILE__) . '/inc/tabs.php');

/** TAB'S **/



/** SLIDER'S **/

include(plugin_dir_path(__FILE__) . '/inc/sliders.php');

/** SLIDER'S **/



/** POPUP'S **/

// include(plugin_dir_path(__FILE__) . '/inc/popups.php');

/** POPUP'S **/



/** ACCORDION'S **/

include(plugin_dir_path(__FILE__) . '/inc/accordion.php');

/** ACCORDION'S **/



/** CF7 EXTENSION'S **/

include(plugin_dir_path(__FILE__) . '/inc/cf7-extensions.php');

/** CF7 EXTENSION'S **/



/** Product Tour **/

include(plugin_dir_path(__FILE__) . '/inc/product-tour.php');

/** Product Tour **/


/** Recent Blogs **/

include(plugin_dir_path(__FILE__) . '/inc/recent-blogs.php');

/** Recent Blogs **/

/** ROI Calculator **/

include(plugin_dir_path(__FILE__) . '/inc/roi-calculator.php');

/** ROI Calculator **/


/** PROJECTS **/

// include( plugin_dir_path(__FILE__) . '/inc/projects.php');

/** PROJECTS **/

/** CF7 BLOG'S **/

// include( plugin_dir_path(__FILE__) . '/inc/newsroom.php');

/** CF7 BLOG'S **/

/** International Tel **/

// include( plugin_dir_path(__FILE__) . '/inc/international-tel.php');

/** International Tel **/


/** Currency Converter **/

// include( plugin_dir_path(__FILE__) . '/inc/currency-converter.php');

/** Currency Converter **/

/** careers **/

// include( plugin_dir_path(__FILE__) . '/inc/careers.php');

/** Currency Converter **/


/** Selldo API **/
// include( plugin_dir_path(__FILE__) . '/inc/selldo-api.php');
/** Selldo API **/


/** careers **/

//include( plugin_dir_path(__FILE__) . '/inc/pagination.php');

/** PAGINATION **/



/** TO TOP **/

//include( plugin_dir_path(__FILE__) . '/inc/to-top.php');

/** TO TOP **/



function my_enqueue($hook)
{

	wp_enqueue_script('admin_script', CUSTOM_PLUGIN_DIR . 	'/assets/js/admin_script.js', array('jquery'), '1.5', true);

	wp_localize_script('admin_script', 'admin_script', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('admin_enqueue_scripts', 'my_enqueue');



add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts()
{ ?>

	<style>
		td.actions.column-actions a {

			margin-right: 7px;

			border: 1px solid;

			padding: 1%;

		}

		tr#accelerated-mobile-pages-update {

			display: none;

		}

		tr#amp-cf7-update {

			display: none;

		}
	</style>

<?php }



function enquemyscripts()
{

	//wp_enqueue_script( 'owlslider-js', plugin_dir_url( __FILE__ ) . 'assets/owl.carousel.min.js',array('jquery'),'1.5',false ); 

	//wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');	

	global $post;

	$post_title = get_the_title($post->ID);

	$post_slug = $post->post_name;

	$post_type = get_post_type($post->ID);

	wp_localize_script(
		'jquery',
		'General',

		array(

			'post_title' => $post_title,

			'post_type' => $post_type,

			'site_url' => site_url(),

			'wp_content_url' => content_url() . '/',

			'slug' => $post_slug,

			'ajaxurl' => admin_url('admin-ajax.php')

		)

	);
}

add_action('wp_enqueue_scripts', 'enquemyscripts', 1);



add_action('wp_head', 'custom__css');

function custom__css()
{ ?>

	<style>
		[data-tip] {

			position: relative;



		}

		[data-tip]:before {

			content: '';

			/* hides the tooltip when not hovered */

			display: none;

			content: '';

			border-left: 5px solid transparent;

			border-right: 5px solid transparent;

			border-bottom: 5px solid #1a1a1a;

			position: absolute;

			top: 30px;

			left: 35px;

			z-index: 8;

			font-size: 0;

			line-height: 0;

			width: 0;

			height: 0;

		}

		[data-tip]:after {

			display: none;

			content: attr(data-tip);

			position: absolute;

			top: 35px;

			left: 0px;

			padding: 5px 8px;

			background: #1a1a1a;

			color: #fff;

			z-index: 9;

			font-size: 0.75em;

			height: 18px;

			line-height: 18px;

			-webkit-border-radius: 3px;

			-moz-border-radius: 3px;

			border-radius: 3px;

			white-space: nowrap;

			word-wrap: normal;

		}

		[data-tip]:hover:before,

		[data-tip]:hover:after {

			display: block;

		}
	</style>

<?php }



add_action('wp_footer', 'custom__script');

function custom__script()
{	?>

	<script type='text/javascript'>
		function scrollAboveElement() {
			var targetElement = document.getElementById(window.location.hash.replace(/#/g, ''));
			var targetPosition = targetElement.offsetTop + 800;
			window.scrollTo({
				top: targetPosition,
				behavior: 'smooth'
			});
		}
		window.onload = function() {
			var hashFromUrl = getHashFromUrl();
			if (window.location.hash === hashFromUrl) {
				setTimeout(scrollAboveElement, 100);
			}
		};

		function getHashFromUrl() {
			return window.location.hash;
		}
		var hashFromUrl = getHashFromUrl();
		console.log('Hash from URL:', hashFromUrl);
		// Add event listener to handle click on the accordion
		// document.querySelectorAll('.sp-tab__card-header').forEach(function(accordionHeader) {
		//     accordionHeader.addEventListener('click', function() {
		//         // Get the parent element of the clicked accordion
		//         var accordionParent = this.closest('.sp-tab__lay-default');

		//         // Get the active accordion
		//         var activeAccordion = accordionParent.querySelector('.sp-tab__show');

		//         if (activeAccordion) {
		//             // Get the offset top position of the active accordion
		//             var scrollTo = activeAccordion.offsetTop;

		//             // Scroll the page to the top of the active accordion
		//             window.scrollTo({
		//                 top: scrollTo,
		//                 behavior: 'smooth' // You can change this to 'auto' for instant scrolling
		//             });
		//         }
		//     });
		// });

		jQuery(function($) {
			$('.homeTour').on("click", function(e) {
				$('.HometourPopdesin iframe').attr('src', $(this).attr('href'));
				e.preventDefault;
			})
			console.log('Hash from referrer:', document.referrer);
			console.log('Hash from site_url:', General.site_url + '/nri');
			console.log('Hash from ==:', document.referrer == General.site_url + '/nri/');
			if (document.referrer == General.site_url + '/nri/') {
				console.log(document.referrer);
				$('.backToNRI').css('display', 'block');
			}
			$(document).ready(function() {
				$(".backToNRI").click(function() {
					window.history.back();
				});
			});
			/* $('.languages input').change(function() {

				if ($(this).val('None')) {

					$('.languages input').not(this).removeAttr('checked');

				}

				else {

					$(".languages input[type=checkbox][value=None]").prop("checked",false);

				}

			}); */


			$("input[type='tel']").on("input paste", function(e) {
				var value = $(this).val();
				value = value.replace(/[^0-9]/g, '');
				if (!/^[6-9]/.test(value)) {
					value = value.substring(1); // Remove first character if it's not 6-9
				}
				if (value.length > 10) {
					value = value.substring(0, 10); // Limit to 10 digits
				}
				$(this).val(value);
			});
			$("input.preventspecialcharc").on("input paste", function(e) {
				var value = $(this).val();
				var regex = /^[a-zA-Z0-9\s]*$/;
				if (!regex.test(value)) {
					$(this).val(value.replace(/[^a-zA-Z0-9\s]/g, ''));
					e.preventDefault();
				}
			});
			$('input:text,textarea').keyup(function(event) {
				var urlPattern = /^(ftp|http|https):\/\/[^ "]+$|^([a-zA-Z0-9-]+\.){1,}[a-zA-Z]{2,}$/;
				var inputValue = $(this).val();
				console.log(urlPattern.test(inputValue));
				if (urlPattern.test(inputValue)) {
					var nonUrlPart = inputValue.replace(urlPattern, '');
					$(this).val(nonUrlPart);
				} else {}
			});
			$('input:text,textarea').keypress(function(event) {
				var urlPattern = /^(ftp|http|https):\/\/[^ "]+$|^([a-zA-Z0-9-]+\.){1,}[a-zA-Z]{2,}$/;
				var inputValue = $(this).val();
				console.log(urlPattern.test(inputValue));
				if (urlPattern.test(inputValue)) {
					var nonUrlPart = inputValue.replace(urlPattern, '');
					$(this).val(nonUrlPart);
				} else {}
			});


			document.addEventListener('wpcf7mailsent', function(event) {

				var inputs = event.detail.inputs;

				if (event.detail.contactFormId == '18485') {

					if (General.pdf_link) {

						//SaveToDisk(General.pdf_link, General.post_title);

					}

				}

			}, false);

		});
	</script>

<!-- <script>
  document.addEventListener("DOMContentLoaded", function () {
    const paragraphs = document.querySelectorAll(".resourceDetail .postContent p");

    paragraphs.forEach(p => {
      if (p.querySelector("img")) {
        p.classList.add("has-image"); // Change "has-image" to your desired class
      }
    });
  });
</script> -->


<script>
  document.addEventListener("DOMContentLoaded", function () {
    const paragraphs = document.querySelectorAll(".resourceDetail .postContent p");

    paragraphs.forEach(p => {
      if (p.querySelector("img") || p.querySelector("iframe")) {
        p.classList.add("has-image"); // You can change "has-media" to any class name you prefer
      }
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const targetH2 = document.querySelector(".resourceDetail .postContent h2#toc-heading-0");

    if (targetH2) {
      let prev = targetH2.previousElementSibling;

      // If there is a tag before it, add class
      if (prev) {
        targetH2.classList.add("beforeTextSection");
      }
    }
  });
</script>



<script>
  document.addEventListener("DOMContentLoaded", function () {
    const headers = document.querySelectorAll(".resourceDetail .postContent h2 b");

    headers.forEach(b => {
      const parent = b.parentNode;
      while (b.firstChild) {
        parent.insertBefore(b.firstChild, b);
      }
      parent.removeChild(b);
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const paragraphs = document.querySelectorAll(".resourceDetail .postContent p");

    paragraphs.forEach((p) => {
      const next = p.nextElementSibling;
      if (next && next.tagName.toLowerCase() === "ul") {
        p.classList.add("has-list-below");
      }
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const h2s = document.querySelectorAll(".resourceDetail .postContent h2");

    h2s.forEach((h2) => {
      const prev = h2.previousElementSibling;
      if (prev) {
        prev.classList.add("before-heading");
      }
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const paragraphs = document.querySelectorAll("p");

    paragraphs.forEach((p) => {
      const next = p.nextElementSibling;

      const isStrongOnly =
        p.children.length === 1 &&
        p.children[0].tagName === "STRONG" &&
        p.textContent.trim() !== "";

      const followedByBeforeHeading =
        next && next.classList.contains("before-heading");

      if (isStrongOnly && followedByBeforeHeading) {
        p.classList.add("pstrongsection");
      }
    });
  });
</script>



<?php }



add_action('init', 'cptui_register_my_cpts');

function cptui_register_my_cpts()
{
	/** Post Type: Sections **/
	$labels = [
		"name" => __("Sections", "custom-post-type-ui"),
		"singular_name" => __("Section", "custom-post-type-ui"),
	];
	$args = [
		"label" => __("Sections", "custom-post-type-ui"),
		'menu_icon' => 'dashicons-media-spreadsheet',
		"labels" => $labels,
		"public" => true,
		"supports" => ["title", "editor", "thumbnail", "excerpt", "page-attributes"],
		"has_archive" => true,
	];
	register_post_type("sections", $args);

	/** Post Type: Floor Plans **/
	$labels1 = [
		"name" => __("Floor Plans", "custom-post-type-ui"),
		"singular_name" => __("Floor Plan", "custom-post-type-ui"),
	];
	$args1 = [
		"label" => __("Floor Plans", "custom-post-type-ui"),
		"public" => true,
		"supports" => ["title", "editor", "thumbnail", "excerpt", "page-attributes"],
		"has_archive" => true,
	];
	register_post_type("floor-plans", $args1);

	/** Post Type: Location Advantages **/
	$labels2 = [
		"name" => __("Location Advantages", "custom-post-type-ui"),
		"singular_name" => __("Location Advantage", "custom-post-type-ui"),
	];
	$args2 = [
		"label" => __("Location Advantages", "custom-post-type-ui"),
		"public" => true,
		"supports" => ["title", "editor", "thumbnail", "excerpt", "page-attributes"],
		"has_archive" => true,
	];
	register_post_type("location-advantages", $args2);

	/** Post Type: Client Logos **/
	$labels3 = [
		"name" => __("Client Logos", "custom-post-type-ui"),
		"singular_name" => __("Client Logo", "custom-post-type-ui"),
	];
	$args3 = [
		"label" => __("Client Logos", "custom-post-type-ui"),
		"public" => true,
		"supports" => ["title", "editor", "thumbnail", "excerpt", "page-attributes"],
		"has_archive" => true,
	];
	register_post_type("client-logos", $args3);

	flush_rewrite_rules();
}

add_shortcode('show_client_logos', 'show_client_logos');


function show_client_logos($atts)
{
	ob_start();

	$args = [
		'post_type'      => 'client-logos',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	];

	if (!empty($atts['post_id'])) {
		$ids = array_map('trim', explode(',', $atts['post_id']));
		$args['post__in'] = $ids;
		$args['orderby']  = 'post__in'; // Maintain order
	}

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$post_id = get_the_ID();
			include('./wp-content/plugins/custom-plugin/templates/show_client_logos.php');
		}
		wp_reset_postdata();
	}

	$output = ob_get_clean();
	return $output;
}





add_shortcode('show_section', 'show_section');

function show_section($atts)
{

	ob_start();

	if (isset($atts['post_id'])) {

		$post_id = $atts['post_id'];
	} else {

		$post_id = '';
	}

	include('./wp-content/plugins/custom-plugin/templates/show_section.php');

	$stringa = ob_get_contents();

	ob_end_clean();

	return $stringa;
}


add_shortcode('show_floorplan', 'show_floorplan');

function show_floorplan($atts)
{

	ob_start();

	if (isset($atts['post_id'])) {

		$post_id = $atts['post_id'];
	} else {

		$post_id = '';
	}

	include('./wp-content/plugins/custom-plugin/templates/show_section.php');

	$stringa = ob_get_contents();

	ob_end_clean();

	return $stringa;
}


add_shortcode('show_locationadvantage', 'show_locationadvantage');

function show_locationadvantage($atts)
{

	ob_start();

	if (isset($atts['post_id'])) {

		$post_id = $atts['post_id'];
	} else {

		$post_id = '';
	}

	include('./wp-content/plugins/custom-plugin/templates/show_section.php');

	$stringa = ob_get_contents();

	ob_end_clean();

	return $stringa;
}




if (!function_exists('redirect_404_to_homepage')) {

	add_action('template_redirect', 'redirect_404_to_homepage');

	function redirect_404_to_homepage()
	{

		if (is_404()) {

			wp_safe_redirect(home_url('/'));

			exit;
		}
	}
}

add_action('wp', function () {
	// Replace 'your-page-slug' with your target page slug or ID
	if (is_page('ongoing') || is_front_page()) {
		// Clean up the image from wp_get_attachment_image()
		add_filter('wp_get_attachment_image_attributes', function ($attr) {
			if (isset($attr['sizes'])) {
				unset($attr['sizes']);
			}

			if (isset($attr['srcset'])) {
				unset($attr['srcset']);
			}

			return $attr;
		}, PHP_INT_MAX);

		// Override the calculated image sizes
		add_filter('wp_calculate_image_sizes', '__return_empty_array', PHP_INT_MAX);

		// Override the calculated image sources
		add_filter('wp_calculate_image_srcset', '__return_empty_array', PHP_INT_MAX);

		// Remove the responsive stuff from the content
		remove_filter('the_content', 'wp_make_content_images_responsive');
	}
});




// Remove 'category' base
add_filter('category_rewrite_rules', function ($category_rewrite) {
	$categories = get_categories(['hide_empty' => false]);
	$new_rules = [];

	foreach ($categories as $category) {
		$category_nicename = $category->slug;
		if ($category->parent === 0) {
			$new_rules[$category_nicename . '/?$'] = 'index.php?category_name=' . $category_nicename;
			$new_rules[$category_nicename . '/page/?([0-9]{1,})/?$'] = 'index.php?category_name=' . $category_nicename . '&paged=$matches[1]';
		}
	}

	return $new_rules + $category_rewrite;
});

// Remove the category base
add_filter('term_link', function ($url, $term, $taxonomy) {
	if ($taxonomy === 'category') {
		return str_replace('/category/', '/', $url);
	}
	return $url;
}, 10, 3);

// Flush rewrite rules on activation
function remove_category_base_activate()
{
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'remove_category_base_activate');

// Flush on deactivation
function remove_category_base_deactivate()
{
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'remove_category_base_deactivate');



function custom_categories_shortcode()
{
	ob_start();
	$current_slug = '';
	if (is_category()) {
		$current_slug = get_queried_object()->slug;
	}
?>
	<!-- Category Filters -->
	<div class="filters desktop">
		<a href="<?php echo site_url('resources'); ?>" class="category-btn <?php echo empty($current_slug) ? 'active' : ''; ?>" data-filter="all">All</a>
	</div>

	<!-- Mobile Category Dropdown -->
	<div class="filters mobile">
		<select id="category-filter">
			<option value="<?php echo site_url('resources'); ?>" <?php echo empty($current_slug) ? 'selected' : ''; ?>>All</option>
		</select>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const categoryButtonsContainer = document.querySelector(".filters.desktop");
			const categoryDropdown = document.getElementById("category-filter");
			const categoriesApiUrl = General.site_url + "/wp-json/wp/v2/categories";
			const currentSlug = "<?php echo esc_js($current_slug); ?>";

			async function fetchCategories() {
				try {
					const response = await fetch(categoriesApiUrl);
					const categories = await response.json();

					categories.forEach(category => {
						const catSlug = category.slug;
						const catName = category.name;
						const catLink = `${General.site_url}/${catSlug}`;

						// Add anchor link for desktop
						const a = document.createElement("a");
						a.textContent = catName;
						a.href = catLink;
						a.classList.add("category-btn");
						a.setAttribute("data-filter", catSlug);
						if (catSlug === currentSlug) a.classList.add("active");
						categoryButtonsContainer.appendChild(a);

						// Add option for mobile
						const option = document.createElement("option");
						option.value = catLink;
						option.textContent = catName;
						if (catSlug === currentSlug) option.selected = true;
						categoryDropdown.appendChild(option);
					});
				} catch (error) {
					console.error("Error fetching categories:", error);
				}
			}

			// Redirect on dropdown change
			categoryDropdown.addEventListener("change", function() {
				const selected = this.value;
				if (selected === "resource-centre") {
					window.location.href = "<?php echo get_permalink(get_option('page_for_posts')); ?>";
				} else {
					window.location.href = selected;
				}
			});

			fetchCategories();
		});
	</script>
<?php
	return ob_get_clean();
}
add_shortcode('custom_categories', 'custom_categories_shortcode');




function custom_posts_with_slug_and_search()
{
	ob_start();
?>
	<div class="search-bar-container">
		<div class="searchbarLeft">
			<span id="current-category-label">All</span>
			<p class="subText">Explore by categories</p>
		</div>
		<div class="searchbarRight">
			<input type="text" id="search-input" placeholder="Search">
		</div>
	</div>

	<div class="posts-grid"></div>

	<div class="pagination">
		<button id="prev-page" disabled>&#8592;</button>
		<div id="pagination-numbers"></div>
		<button id="next-page" disabled>&#8594;</button>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const postsContainer = document.querySelector(".posts-grid");
			const paginationContainer = document.getElementById("pagination-numbers");
			const prevPageBtn = document.getElementById("prev-page");
			const nextPageBtn = document.getElementById("next-page");
			const searchInput = document.getElementById("search-input");
			const currentCategoryLabel = document.getElementById("current-category-label");

			let currentPage = 1;
			let totalPages = 1;
			let currentCategoryId = null;
			let searchTerm = "";

			const siteUrl = General.site_url;
			const apiBaseUrl = siteUrl + "/wp-json/wp/v2/posts";

			function getSlugFromURL() {
				const pathSegments = window.location.pathname.split('/').filter(Boolean);
				return pathSegments[pathSegments.length - 1];
			}

			async function getCategoryIdFromSlug(slug) {
				try {
					const res = await fetch(`${siteUrl}/wp-json/wp/v2/categories?slug=${slug}`);
					const data = await res.json();
					return data.length > 0 ? data[0].id : null;
				} catch (err) {
					console.error("Category fetch error:", err);
					return null;
				}
			}

			async function fetchPosts(page = 1) {
				currentPage = page;	
				postsContainer.innerHTML = "<div></div><div class='postLoadingOut'><p class='postLoading'></p></div><div></div>";
				paginationContainer.innerHTML = "";

				let url = `${apiBaseUrl}?per_page=6&page=${page}&_embed`;

				if (currentCategoryId) {
					url += `&categories=${currentCategoryId}`;
				}
				if (searchTerm) {
					url += `&search=${encodeURIComponent(searchTerm)}`;
				}

				try {
					const res = await fetch(url);
					if (!res.ok) throw new Error("No posts found.");
					const posts = await res.json();
					totalPages = parseInt(res.headers.get("X-WP-TotalPages")) || 1;
					displayPosts(posts);
					setupPagination(totalPages, page);

					// Scroll only on non-mobile devices
// 					if (window.innerWidth >= 768) {
// 						const scrollTarget = document.getElementById("resourceListSec");
// 						if (scrollTarget) {
// 							scrollTarget.scrollIntoView({
// 								behavior: 'smooth',
// 								block: 'start'
// 							});
// 						}
// 					}
					
					window.addEventListener("load", () => {
  if (window.innerWidth >= 768) {
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth" // or "auto" if you don’t want smooth scroll
    });
  }
});

				} catch (err) {
					postsContainer.innerHTML = "<p>No posts found.</p>";
				}
			}


			function displayPosts(posts) {
				postsContainer.innerHTML = posts.map(post => {

					const title = post.title.rendered.substring(0, 80) + (post.title.rendered.length > 80 ? "..." : "");
					const excerpt = post.excerpt.rendered.replace(/<[^>]+>/g, "").substring(0, 100) + "...";


					const category = post._embedded["wp:term"]?.[0]?.[0]?.name || "Uncategorized";
					const categorySlug = post._embedded["wp:term"][0][0]?.slug || "Uncategorized";
					const isEvent = category.toLowerCase() === "events";

					// Get date from ACF field if category is "events", else use post.date
					let dateStr = isEvent ? post.acf?.event_date : post.date;
					let date = "";

					if (dateStr) {
						const dateObj = new Date(dateStr);
						date = dateObj.toLocaleDateString('en-US', {
							month: 'short',
							day: 'numeric',
							year: 'numeric'
						});
					}

					const postUrl = isEvent ? "#" : post.link;
					const imageUrl = post._embedded["wp:featuredmedia"]?.[0]?.source_url || "https://via.placeholder.com/300";

					return `
			<a href="${postUrl}" class="post-card ${categorySlug}" ${isEvent ? 'onclick="return false;"' : ""}>
				<div class="imgTitle">
					<img src="${imageUrl}" alt="${title}" class="post-image">
					<h3>${title}</h3>
				</div>
				<div class="description">
					<span class="category">${category}</span>
					<p>${excerpt}</p>
					<span class="date">${date}</span>
				</div>
			</a>
		`;
				}).join("");
			}


			function setupPagination(totalPages, currentPage) {
				paginationContainer.innerHTML = "";

				prevPageBtn.disabled = currentPage === 1;
				nextPageBtn.disabled = currentPage === totalPages;

				console.log(prevPageBtn.disabled);


				let startPage = Math.max(1, currentPage - 1);
				let endPage = Math.min(totalPages, startPage + 2);

				for (let i = startPage; i <= endPage; i++) {
					const btn = document.createElement("button");
					btn.textContent = i;
					btn.classList.add("page-button");
					if (i === currentPage) btn.classList.add("active");
					btn.addEventListener("click", () => {
						currentPage = i;
						fetchPosts(i);
					});
					paginationContainer.appendChild(btn);
				}
			}

			prevPageBtn.addEventListener("click", () => {

				if (currentPage > 1) {
					currentPage--;
					fetchPosts(currentPage);
				}
			});

			nextPageBtn.addEventListener("click", () => {
				if (currentPage < totalPages) {
					currentPage++;
					fetchPosts(currentPage);
				}
			});

			searchInput.addEventListener("input", (e) => {
				searchTerm = e.target.value;
				currentPage = 1;
				fetchPosts();
			});

			// INIT
			(async () => {
				const slug = getSlugFromURL();
				currentCategoryId = await getCategoryIdFromSlug(slug);

				function toCamelCase(slug) {
					return slug
						.split('-')
						.map(word => word.charAt(0).toUpperCase() + word.slice(1))
						.join(' ');
				}
				const formattedLabel = slug === 'resources' ? 'All' : toCamelCase(slug);
				currentCategoryLabel.textContent = formattedLabel;

				fetchPosts();
			})();
		});
	</script>

	<style>
		.posts-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 20px;
		}

		.post-card {
			display: block;
			padding: 20px;
			background: #001F3F;
			color: white;
			border-radius: 10px;
			text-align: center;
			text-decoration: none;
		}

		.pagination {
			display: flex;
			gap: 5px;
			justify-content: center;
			margin-top: 20px;
		}

		.pagination button {
			padding: 8px 12px;
			background: #001F3F;
			color: #fff;
			border-radius: 5px;
			border: none;
			cursor: pointer;
		}

		.pagination button:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}
	</style>
<?php
	return ob_get_clean();
}
add_shortcode('custom_posts', 'custom_posts_with_slug_and_search');


function expose_acf_fields_to_rest_api()
{
	register_rest_field('post', 'acf', [
		'get_callback' => function ($post_arr) {
			return [
				'event_date' => get_field('event_date', $post_arr['id']),
				'author_name' => get_field('author_name', $post_arr['id']),
				'author_image' => get_field('author_image', $post_arr['id']),
			];
		},
		'schema' => null,
	]);
}
// add_action('rest_api_init', 'expose_acf_fields_to_rest_api');



function custom_posts_slider_shortcode($atts)
{
	$atts = shortcode_atts([
		'category' => 'all', // slug or 'all'
		'count'    => 10,
	], $atts);

	ob_start();
?>
	<div class="custom-posts-slider" data-category="<?php echo esc_attr($atts['category']); ?>" data-count="<?php echo esc_attr($atts['count']); ?>">
		<div class="postLoadingOut"><p class="postLoading"></p></div>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			function initSlickSlider() {
				jQuery('.custom-posts-slider .slider-wrapper').slick({
					slidesToShow: 3,
					slidesToScroll: 1,
					arrows: true,
					dots: true,
					infinite: true,
					autoplay: false,
					responsive: [{
							breakpoint: 1024,
							settings: {
								slidesToShow: 2
							}
						},
						{
							breakpoint: 768,
							settings: {
								slidesToShow: 1
							}
						}
					]
				});
			}

			jQuery('.custom-posts-slider').each(function() {
				const container = jQuery(this);
				const category = container.data('category');
				const count = container.data('count');

				function fetchPosts(url) {
					fetch(url)
						.then(response => response.json())
						.then(posts => {
							if (!posts.length) {
								container.html('<p>No posts found.</p>');
								return;
							}

							const slides = posts.map(post => {
								const title = post.title.rendered;
								const limitedTitle = title.length > 25 ? title.substring(0, 25) + "..." : title;
								const excerpt = post.excerpt.rendered.replace(/<[^>]+>/g, "").substring(0, 100) + "...";
								const permalink = post.link;
								const imageUrl = post._embedded["wp:featuredmedia"] ?
									post._embedded["wp:featuredmedia"][0].source_url :
									"https://via.placeholder.com/300";

								return `
									<a href="${permalink}" class="post-slide" target="_self">
										<div class="custom_slick_slider_inner">
											<div class="fusion-image-element in-legacy-container csimg">
												<span class="fusion-imageframe imageframe-none imageframe-16 hover-type-none">
													<img src="${imageUrl}" alt="${title}">
												</span>
                                       <div class="fusion-text fusion-text-27 Htitle">
												<h3>${limitedTitle}</h3>
											</div>
											</div>
											
											<div class="fusion-text fusion-text-28">
												<h4>Case Study</h4>
												<p>${excerpt}</p>
                                                
											</div>
											<div>
												<span class="fusion-button button-flat fusion-button-default-size button-default fusion-button-default button-1 fusion-button-default-span fusion-button-default-type readMore">
													<span class="fusion-button-text">Read More</span>
												</span>
											</div>
										</div>
									</a>
								`;
							}).join("");
							container.html(`<div class="slider-wrapper">${slides}</div>`);
							initSlickSlider();
						})
						.catch(() => {
							container.html('<p>Error loading posts.</p>');
						});
				}

				if (category === 'all') {
					const url = `${General.site_url}/wp-json/wp/v2/posts?_embed&per_page=${count}`;
					fetchPosts(url);
				} else {
					fetch(`${General.site_url}/wp-json/wp/v2/categories?slug=${category}`)
						.then(res => res.json())
						.then(catData => {
							if (!catData.length) {
								container.html('<p>No posts found for this category.</p>');
								return;
							}
							const catId = catData[0].id;
							const url = `${General.site_url}/wp-json/wp/v2/posts?_embed&per_page=${count}&categories=${catId}`;
							fetchPosts(url);
						})
						.catch(() => {
							container.html('<p>Error fetching category.</p>');
						});
				}
			});
		});
	</script>

<?php
	return ob_get_clean();
}
add_shortcode('custom_posts_slider', 'custom_posts_slider_shortcode');




function industry_events_slider_shortcode($atts)
{
	$atts = shortcode_atts([
		'category' => 'all', // slug or 'all'
		'count'    => 10,
		'order'    => 'desc', // 'asc' or 'desc'
		'orderby'  => 'date', // 'date', 'title', 'modified', etc.
	], $atts);

	ob_start();
?>
	<div class="industry-events-slider"
		data-category="<?php echo esc_attr($atts['category']); ?>"
		data-count="<?php echo esc_attr($atts['count']); ?>"
		data-order="<?php echo esc_attr($atts['order']); ?>"
		data-orderby="<?php echo esc_attr($atts['orderby']); ?>">
		<p class="postLoading">Loading events...</p>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			function initEventsSlider() {
				jQuery('.industry-events-slider .slider-wrapper').slick({
					rows: 2,
					dots: true,
					arrows: false,
					infinite: true,
					speed: 300,
					slidesToShow: 1,
					slidesToScroll: 1,
					responsive: [{
							breakpoint: 768,
							settings: {
								rows: 2,
								adaptiveHeight: true
							}
						},
						{
							breakpoint: 480,
							settings: {
								rows: 2,
								adaptiveHeight: true
							}
						}
					]
				});
			}

			jQuery('.industry-events-slider').each(function() {
				const container = jQuery(this);
				const category = container.data('category');
				const count = container.data('count');
				const order = container.data('order');
				const orderby = container.data('orderby');

				function fetchPosts(url) {
					fetch(url)
						.then(response => response.json())
						.then(posts => {
							if (!posts.length) {
								container.html('<p>No events found.</p>');
								return;
							}

							const slides = posts.map(post => {
								const title = post.title.rendered;
								const limitedTitle = title.length > 125 ? title.substring(0, 25) : title;
								const excerpt = post.excerpt.rendered.replace(/<[^>]+>/g, "").substring(0, 100);
								const permalink = post.link;
								const imageUrl = post._embedded?.["wp:featuredmedia"] ?
									post._embedded["wp:featuredmedia"][0].source_url :
									"https://via.placeholder.com/80";
								const isEvent = category.toLowerCase() === "events";
								const authorImageUrl = post.acf?.author_image?.url;
								const authorImageAlt = post.acf?.author_name || limitedTitle;

								let authorImageHtml = '';
								if (authorImageUrl) {
									authorImageHtml = `
										<span class="fusion-imageframe imageframe-none imageframe-20 hover-type-none">
											<img src="${authorImageUrl}" width="80" height="80" class="img-responsive" alt="${authorImageAlt}">
										</span>`;
								} else {
									authorImageHtml = `
										<span class="no-avatar" style="display:inline-block;width:40px;height:40px;border-radius:50%;background:#ccc;"></span>`;
								}
								let dateStr = isEvent ? post.acf?.event_date : post.date;
								let date = "";

								if (dateStr) {
									const dateObj = new Date(dateStr);
									date = dateObj.toLocaleDateString('en-US', {
										month: 'short',
										day: 'numeric',
										year: 'numeric'
									});
								}

								return `
									<a class="event-slide" target="_self">
										<div style="width: 100%; display: inline-block;">
											<div class="custom_slick_slider_inner">
												<div class="eventsBox">
													<div class="fusion-column-wrapper fusion-column-has-shadow fusion-flex-justify-content-flex-start fusion-content-layout-column">
														<div class="fusion-text fusion-text-28">
															<h5>${dateStr}</h5>
															<h3>${limitedTitle}</h3>
															<p>${excerpt}</p>
														</div>
													</div>
												</div>
												<div class="eventsBoxImg">
													<span class="fusion-imageframe imageframe-none imageframe-20 hover-type-none">
														 <div class="eventsBoxImg">
															${authorImageHtml}
														</div>
													</span>
												</div>
											</div>
										</div>
									</a>
								`;
							}).join("");

							container.html(`<div class="slider-wrapper">${slides}</div>`);
							initEventsSlider();
						})
						.catch(() => {
							container.html('<p>Error loading events.</p>');
						});
				}

				if (category === 'all') {
					const url = `${General.site_url}/wp-json/wp/v2/posts?_embed&per_page=${count}`;
					fetchPosts(url);
				} else {
					fetch(`${General.site_url}/wp-json/wp/v2/categories?slug=${category}`)
						.then(res => res.json())
						.then(catData => {
							if (!catData.length) {
								container.html('<p>No events found for this category.</p>');
								return;
							}
							const catId = catData[0].id;
							const url = `${General.site_url}/wp-json/wp/v2/posts?_embed&per_page=${count}&categories=${catId}&order=desc&orderby=date`;

							fetchPosts(url);
						})
						.catch(() => {
							container.html('<p>Error fetching category.</p>');
						});
				}
			});
		});
	</script>

	<style>
		.industry-events-slider .custom_slick_slider_inner {
			background: #fff;
			border-radius: 12px;
			padding: 20px;
			margin: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.eventsBox {
			width: 75%;
		}

		.eventsBoxImg img {
			border-radius: 10px;
			max-width: 80px;
			height: auto;
		}

		.fusion-text h3 {
			font-size: 1.2rem;
			margin: 0 0 10px;
		}

		.fusion-text h5 {
			font-size: 0.9rem;
			color: #555;
			margin-bottom: 5px;
		}

		.fusion-text p {
			font-size: 0.95rem;
			color: #333;
		}
	</style>
<?php
	return ob_get_clean();
}
add_shortcode('industry_events_slider', 'industry_events_slider_shortcode');



function generate_toc_shortcode($atts)
{
	global $post;

	if (!$post) return '';

	// Get ACF fields from the current post
	// $author_name  = get_field('author_name', $post->ID);
	// $author_image = get_field('author_image', $post->ID); // Expecting array
	// $author_bio   = get_field('author_bio', $post->ID);
	
	$author_name  = '';
	$author_image = ''; // Expecting array
	$author_bio   = '';

	// Check if any field exists
	if ($author_name || $author_image || $author_bio) {

		// Fallbacks if fields are empty
		$author_name_display = $author_name ?: '';
		$author_bio_display  = $author_bio ?: 'No biography available.';

		// Build avatar HTML
		if (is_array($author_image) && !empty($author_image['url'])) {
			$img_url = $author_image['sizes']['thumbnail'] ?? $author_image['url'];
			$author_avatar = sprintf(
				'<img src="%s" alt="%s" class="custom-avatar" width="100" height="100" loading="lazy">',
				esc_url($img_url),
				esc_attr($author_name_display)
			);
		} else {
			// Optional: leave empty instead of placeholder
			$author_avatar = '';
		}

		// Build author profile HTML
		$author_profile  = '<div class="custom-author-profile" style="display:flex;flex-direction:column;gap:15px;padding:15px;background:#f9f9f9;border-radius:10px;margin-top:20px;">';
		$author_profile .=   '<h2 id="author-details">Author Details</h2>';
		$author_profile .=   '<div class="author-detailsIn">';

		if ($author_avatar) {
			$author_profile .= '<div class="author-avatar">' . $author_avatar . '</div>';
		}

		$author_profile .=     '<div class="author-details">';

		if ($author_name) {
			$author_profile .= '<h3 class="author-name">' . esc_html($author_name_display) . '</h3>';
		}

		if ($author_bio) {
			$author_profile .= '<p class="author-bio">' . esc_html($author_bio_display) . '</p>';
		}

		$author_profile .=     '</div>';
		$author_profile .=   '</div>';
		$author_profile .= '</div>';
	} else {
		$author_profile = ''; // Nothing to show
	}



	// Get post content and process shortcodes
	$content = do_shortcode($post->post_content);

	// Match all H2 elements
	preg_match_all('/<h2>(.*?)<\/h2>/', $content, $matches);

	if (empty($matches[1])) {
		// If no headings are found, return just the content and author details
		return '<div class="postContent">' . $content . '</div>' . $author_profile;
	}

	$toc_html = '<div class="resourceDetail"><div class="custom-toc"><h3>Table of Contents</h3><ul>';
	$updated_content = $content;
	foreach ($matches[1] as $index => $heading) {
		$id = 'toc-heading-' . $index; // Unique ID for each heading
		$toc_html .= '<li><a href="#' . $id . '">' . $heading . '</a></li>';
		// Add ID to the original H2 in post content
		$updated_content = preg_replace('/<h2>' . preg_quote($heading, '/') . '<\/h2>/', '<h2 id="' . $id . '">' . $heading . '</h2>', $updated_content, 1);
	}
	if ($author_profile) {
		$toc_html .= '<li><a href="#author-details">Author Details</a></li>'; // Add Author Details to TOC
	}
	$toc_html .= '</ul></div>';

	// CSS Styles
	$css = '<style>
        .custom-toc ul { list-style: none; padding: 0; }
        .custom-toc li { margin: 5px 0; }
        .custom-toc a { text-decoration: none; color: #0073aa; }
        .custom-toc a.active { font-weight: bold; color: #ff6600; }
    </style>';

	// JavaScript for TOC highlighting
	$js = '<script>
document.addEventListener("DOMContentLoaded", function () {
    const tocLinks = document.querySelectorAll(".custom-toc a");
    const headings = document.querySelectorAll(".postContent h2, #author-details");
    const yOffset = -90; // Set your offset here (negative for space above heading)

    function onScroll() {
        let currentSection = null;
        headings.forEach((heading) => {
            const rect = heading.getBoundingClientRect();
            // Adjust the threshold to match the offset
            // When the heading is at yOffset from the top (or closest to it, but not below it)
            if (rect.top <= (0 - yOffset + 50)) {
                currentSection = heading;
            }
        });
        if (currentSection) {
            tocLinks.forEach(link => link.classList.remove("active"));
            const activeLink = document.querySelector(\'.custom-toc a[href="#\' + currentSection.id + \'"]\');
            if (activeLink) {
                activeLink.classList.add("active");
            }
        }
    }

    document.addEventListener("scroll", onScroll);

    tocLinks.forEach(anchor => {
        anchor.addEventListener("click", function (event) {
            event.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                const y = target.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({
                    top: y,
                    behavior: "smooth"
                });
            }
        });
    });

    onScroll();
});
</script>';

	// Return TOC + modified content + author profile at the end + styles & script
	return $css . $js . $toc_html . '<div class="postContent">' . $updated_content . '</div></div>' . $author_profile;
}
add_shortcode('generate_toc', 'generate_toc_shortcode');





function custom_breadcrumb_shortcode()
{
	global $post;

	// Define the first breadcrumb (custom page)
	$home_page_id = 624;
	$home_page_link = get_permalink($home_page_id);
	$home_page_title = get_the_title($home_page_id);
	$breadcrumb_html = '<nav class="custom-breadcrumb"><ul>';
	$breadcrumb_html .= '<li><a href="' . esc_url($home_page_link) . '">' . esc_html($home_page_title) . '</a></li>';

	// Get the category (for single posts)
	if (is_single()) {
		$categories = get_the_category();
		if (!empty($categories)) {
			$first_category = $categories[0];
			$category_link = get_category_link($first_category->term_id);
			$breadcrumb_html .= '<li><a href="' . esc_url($category_link) . '">' . esc_html($first_category->name) . '</a></li>';
		}
	}

	// Get category for category archive page
	elseif (is_category()) {
		$current_category = get_queried_object();
		$breadcrumb_html .= '<li>' . esc_html($current_category->name) . '</li>';
	}

	// Get the current post/page title
	if (is_single() || is_page()) {
		// $breadcrumb_html .= '<li>' . esc_html(get_the_title()) . '</li>';
	}

	$breadcrumb_html .= '</ul></nav>';

	return $breadcrumb_html;
}

add_shortcode('custom_breadcrumb', 'custom_breadcrumb_shortcode');


function custom_author_info_shortcode()
{
	global $post;
	if (! $post) {
		return '';                           // Nothing to show
	}

	/* ---------------------------------------------------------------------
	 * 1. Author name (ACF text – fallback to placeholder)
	 * -------------------------------------------------------------------*/
	$author_name = get_field('author_name', $post->ID);
	if (empty($author_name)) {
		$author_name = '';
	}

	/* ---------------------------------------------------------------------
	 * 2. Author image  (ACF image array – fallback to grey circle)
	 * -------------------------------------------------------------------*/
	$author_image = get_field('author_image', $post->ID);
	if (is_array($author_image) && ! empty($author_image['url'])) {

		// Try to grab the 40 px-wide "thumbnail" version first
		$desired_size  = 'thumbnail';               // change to any ACF / WP size slug you prefer
		$img_url       = $author_image['sizes'][$desired_size] ?? $author_image['url'];
		$img_width     = $author_image['sizes'][$desired_size . '-width']  ?? 40;
		$img_height    = $author_image['sizes'][$desired_size . '-height'] ?? 40;

		$author_avatar = sprintf(
			'<img src="%s" alt="%s" class="custom-avatar" width="%d" height="%d" loading="lazy">',
			esc_url($img_url),
			esc_attr($author_name),
			(int) $img_width,
			(int) $img_height
		);
	} else {
		// Simple grey circle if no image; swap for get_avatar() if you like
		$author_avatar = '<span class="no-avatar" style="display:inline-block;width:40px;height:40px;border-radius:50%;background:#ccc;"></span>';
	}

	/* ---------------------------------------------------------------------
	 * 3. Publish date
	 * -------------------------------------------------------------------*/
	$publish_date = get_the_date('F j, Y', $post);

	/* ---------------------------------------------------------------------
	 * 4. Build markup
	 * -------------------------------------------------------------------*/
	$output  = '<div class="custom-author-info">';
	$output .=   '<span class="author-avatar">' . $author_avatar . '</span>';
	$output .=   '<span class="author-name">'   . esc_html($author_name) . '</span>';
	$output .=   '<span class="publish-date">'  . esc_html($publish_date) . '</span>';
	$output .= '</div>';

	return $output;
}
add_shortcode('author_info', 'custom_author_info_shortcode');




function custom_author_profile_shortcode()
{
	global $post;

	if (!$post) {
		return ''; // Return empty if no post found
	}

	// Get author ID
	$author_id = $post->post_author;

	// Get author name
	$author_name = get_the_author_meta('display_name', $author_id);

	// Get author avatar
	$author_avatar = get_avatar($author_id, 100); // 100px avatar size for better clarity

	// Get author bio
	$author_bio = get_the_author_meta('description', $author_id);

	// Ensure author name is retrieved properly
	if (empty($author_name)) {
		$author_name = ''; // Fallback
	}

	// Ensure author bio has content
	if (empty($author_bio)) {
		$author_bio = 'No biography available.'; // Fallback
	}

	// HTML output with styling
	$output = '<div class="custom-author-profile" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #f9f9f9; border-radius: 10px;">';
	$output .= '<div class="author-avatar">' . $author_avatar . '</div>';
	$output .= '<div class="author-details">';
	$output .= '<h3 class="author-name" style="margin: 0; font-size: 18px; font-weight: bold;">' . esc_html($author_name) . '</h3>';
	$output .= '<p class="author-bio" style="margin: 5px 0; font-size: 14px; color: #555;">' . esc_html($author_bio) . '</p>';
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}
add_shortcode('author_profile', 'custom_author_profile_shortcode');




function recent_posts_shortcode()
{
	ob_start();
?>
	<div id="recent-posts-container">
		<p>Loading recent posts...</p>
	</div>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const recentPostsContainer = document.getElementById("recent-posts-container");

			async function fetchRecentPosts() {
				let url = General.site_url + "/wp-json/wp/v2/posts?per_page=3&page=1&_embed";

				try {
					const response = await fetch(url);
					if (!response.ok) throw new Error("No recent posts found.");
					const posts = await response.json();
					displayRecentPosts(posts);
				} catch (error) {
					recentPostsContainer.innerHTML = "<p>No recent posts found.</p>";
				}
			}

			function displayRecentPosts(posts) {
				recentPostsContainer.innerHTML = posts.map(post => {
					const title = post.title.rendered;
					const excerpt = post.excerpt.rendered.replace(/<[^>]+>/g, "").substring(0, 100) + "...";
					const date = new Date(post.date).toLocaleDateString();
					const category = post._embedded["wp:term"] ? post._embedded["wp:term"][0][0].name : "Uncategorized";
					const postUrl = post.link;
					const imageUrl = post._embedded["wp:featuredmedia"] ?
						post._embedded["wp:featuredmedia"][0].source_url :
						"https://via.placeholder.com/300";

					return `
                        <a href="${postUrl}" class="post-card">
                            <div class="imgTitle">
                                <img src="${imageUrl}" alt="${title}" class="post-image">
                                <h3>${title}</h3>
                            </div>
                            <div class="description">
                                <span class="category">${category}</span>
                                <p>${excerpt}</p>
                                <span class="date">${date}</span>
                            </div>
                        </a>
                    `;
				}).join("");
			}

			fetchRecentPosts();
		});
	</script>
	<style>
		#recent-posts-container {
			display: flex;
			flex-wrap: wrap;
			gap: 20px;
			margin-top: 20px;
		}

		.post-card {
			width: 100%;
			max-width: 300px;
			text-decoration: none;
			color: black;
			border: 1px solid #ddd;
			padding: 15px;
			border-radius: 10px;
			background: #fff;
			transition: 0.3s;
		}

		.post-card:hover {
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
		}

		.imgTitle {
			text-align: center;
		}

		.post-image {
			width: 100%;
			max-width: 250px;
			height: auto;
			border-radius: 10px;
		}

		.description {
			margin-top: 10px;
		}

		.category {
			font-size: 12px;
			color: #666;
			font-weight: bold;
		}

		.date {
			font-size: 12px;
			color: #999;
		}
	</style>
<?php
	return ob_get_clean();
}
add_shortcode('recent_posts', 'recent_posts_shortcode');
