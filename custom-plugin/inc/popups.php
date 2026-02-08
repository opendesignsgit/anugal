<?php
add_action('wp_head', 'custom_popoup_css');
function custom_popoup_css()
{ ?>
    <div class="loading-overlay">
        <span class="fas fa-spinner fa-3x fa-spin"></span>
    </div>
    <script type='text/javascript'>
        jQuery(function($) {
            $('.vIcon').on("click", function(e) {
                console.log($(this).find('a').attr('href'));
                $('.testiMonialVideo iframe').attr('src', $(this).find('a').attr('href'));
                e.preventDefault;
            })
        });
    </script>
<?php
}
add_action('init', 'popup_post_type');
function popup_post_type()
{
    /**
     * Post Type: Popups.
     */
    $labels = [
        "name" => __("Popups", "custom-post-type-ui"),
        "singular_name" => __("Popup", "custom-post-type-ui"),
    ];
    $args = [
        "label" => __("Popups", "custom-post-type-ui"),
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
        "exclude_from_search" => true,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => ["slug" => "popup", "with_front" => true],
        "query_var" => true,
        "supports" => ["title", "editor", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "author", "page-attributes", "post-formats"],
        "taxonomies" => ["popup-categories"],
        "show_in_graphql" => false,
    ];
    register_post_type("popup", $args);
    flush_rewrite_rules();
}
add_action('wp_footer', 'popup_custom__script', 1000);
function popup_custom__script()
{
    global $post;
    echo do_shortcode('[list_popups_in_footer]');
?>
    <div class="custom-model-main_custom_popup all">
        <div class="custom-model-inner_custom_popup">
            <div class="close-btn_custom_popup">×</div>
            <div class="custom-model-wrap_custom_popup">
                <div id="pop_content" class="pop-up-content-wrap_custom_popup">
                </div>
            </div>
        </div>
        <div class="bg-overlay_custom_popup"></div>
    </div>
    <script type='text/javascript'>
        jQuery(function($) {
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }
            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
            function eraseCookie(name) {
                document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            }
            $("body").on("click", ".open_popup", function(e) {
                e.preventDefault();
                var id = $(this).data('postid');
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: 'open_popup',
                        id: id
                    }, // form data
                    type: 'POST', // POST
                    beforeSend: function(xhr) {
                        $(".loading-overlay").toggleClass("is-active");
                    },
                    success: function(data) {
                        $("body").addClass('popupOpened');
                        $(".loading-overlay").toggleClass("is-active");
                        console.log(data);
                        $('.custom-model-main_custom_popup.all #pop_content').html(data);
                        $(".custom-model-main_custom_popup.all").addClass('model-open_custom_popup');
                        document.querySelectorAll(".wpcf7 > form").forEach((function(e) {
                            return wpcf7.init(e)
                        }));
                    }
                });
            });
            $(document).on('keydown', function(event) {
                if (event.key == "Escape") {
                    var $activePopup = $(".custom-model-main_custom_popup.model-open_custom_popup");
                    if (!$activePopup.hasClass('disclaimer')) {
                        $("body").removeClass('popupOpened');
                        $activePopup.removeClass('model-open_custom_popup');
                        $('#pop_content iframe').attr('src', '');
                        $('.fusion-alert.alert').css('display', 'none');
                    }
                }
            });
            $(".close-btn_custom_popup, .bg-overlay_custom_popup").click(function() {
                var $activePopup = $(this).closest(".custom-model-main_custom_popup");
                if (!$activePopup.hasClass('disclaimer')) {
                    $("body").removeClass('popupOpened');
                    $activePopup.removeClass('model-open_custom_popup');
                    $('#pop_content iframe').attr('src', '');
                }
            });
            // Function to set data in localStorage with expiration
            function setItemWithExpiry(key, value, expiryInHours) {
                const now = new Date();
                const item = {
                    value: value,
                    expiry: now.getTime() + expiryInHours * 60 * 60 * 1000, // convert hours to milliseconds
                };
                localStorage.setItem(key, JSON.stringify(item));
            }
            // Function to get data from localStorage and check if it's expired
            function getItemWithExpiry(key) {
                const itemStr = localStorage.getItem(key);
                // If the item doesn't exist, return null
                if (!itemStr) {
                    return null;
                }
                const item = JSON.parse(itemStr);
                const now = new Date();
                // Compare the expiry time with the current time
                if (now.getTime() > item.expiry) {
                    // If the item is expired, remove it from storage and return null
                    localStorage.removeItem(key);
                    return null;
                }
                return item.value;
            }
            // Check if the disclaimer has been agreed to
//             var agreed = getItemWithExpiry('disclaimerAgreed');
//             if (agreed) {
//                 $("body").removeClass('popupOpened');
//                 $(".custom-model-main_custom_popup.DisclaimerOuter").removeClass('model-open_custom_popup');
//             } else {
//                 setTimeout(function() {
//                     $(".custom-model-main_custom_popup.DisclaimerOuter").addClass('model-open_custom_popup');
//                     $("body").addClass('popupOpened');
//                 }, 1000);
//             }
            // Handle the agree button click
            $(".discAgree").click(function() {
                var $activePopup = $(this).closest(".custom-model-main_custom_popup");
                if ($activePopup.hasClass('disclaimer')) {
                    setItemWithExpiry('disclaimerAgreed', 'true', 5); // Store agreement for 5 hours
                    $("body").removeClass('popupOpened');
                    $activePopup.removeClass('model-open_custom_popup');
                }
            });
        });
    </script>
<?php }
add_action('wp_ajax_open_popup', 'open_popup');
add_action('wp_ajax_nopriv_open_popup', 'open_popup');
function open_popup()
{
    $id = $_POST['id'];
    $content_post = get_post($id);
    $content = $content_post->post_content;
    $content = apply_filters('the_content', $content);
    $html = str_replace(']]>', ']]&gt;', $content);
    echo $html;
    die();
}
add_filter('manage_popup_posts_columns', 'add_popup_order_column', 10, 2);
function add_popup_order_column($columns)
{
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'title' => '<span class="popup_admin_span">Title</span>',
        '_popup_class' => '<span class="popup_admin_span">Popup Class</span>',
        '_popup_attribute' => '<span class="popup_admin_span">Popup Attribute</span>',
        'date' => 'Date'
    );
    return $columns;
}
add_action('manage_popup_posts_custom_column', 'get_popup_order_column_value', 10, 2);
function get_popup_order_column_value($column_name, $post_id)
{
    $popup_class = (get_field("add_in_footer", $post_id)) ? "open_popup_" . $post_id . "" : "open_popup";
    switch ($column_name) {
        case '_popup_class':
            echo '<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value="' . $popup_class . '" class="large-text code"></span>';
            break;
        case '_popup_attribute':
            echo '<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value="data-postid=' . $post_id . '" class="large-text code"></span>';
            break;
    }
}
add_shortcode('list_popups_in_footer', 'list_popups_in_footer');
function list_popups_in_footer($atts)
{
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
    include('./wp-content/plugins/custom-plugin/templates/list_popups_in_footer.php');
    $stringa = ob_get_contents();
    ob_end_clean();
    return $stringa;
}
