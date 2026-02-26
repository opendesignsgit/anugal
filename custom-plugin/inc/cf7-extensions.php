<?php
add_action('wp_head', 'custom_cf7ext_css');
function custom_cf7ext_css()
{ ?>
    <script type='text/javascript'>
        document.addEventListener('wpcf7invalid', function(event) {
            // Extract event details
            var formData = event.detail;
            var invalidFields = formData.apiResponse.invalid_fields;

            // File input field name and validation status
            var fileInput = document.querySelector('input[name="your-resume"]');
            var fileFieldIsEmpty = !fileInput || !fileInput.files.length;

            // Check if the file input needs to be validated
            if (fileFieldIsEmpty) {
                fileInput.classList.add('wpcf7-not-valid');
                var errorMessage = document.createElement('span');
                errorMessage.classList.add('wpcf7-not-valid-tip');
                errorMessage.textContent = 'Please fill out this field.';

                // Check if the error message is already present
                if (!fileInput.nextElementSibling || !fileInput.nextElementSibling.classList.contains('wpcf7-not-valid-tip')) {
                    fileInput.parentNode.insertBefore(errorMessage, fileInput.nextSibling);
                }
            } else {
                fileInput.classList.remove('wpcf7-not-valid');
                var existingError = fileInput.parentNode.querySelector('.wpcf7-not-valid-tip');
                if (existingError) {
                    existingError.remove();
                }
            }

            // 		// Loop through the invalid fields and apply the corresponding errors
            // 		invalidFields.forEach(function(field) {
            // 			var fieldInput = document.querySelector('[name="' + field.field + '"]');
            // 			if (fieldInput) {
            // 				fieldInput.classList.add('wpcf7-not-valid');
            // 				var fieldErrorMessage = document.createElement('span');
            // 				fieldErrorMessage.classList.add('wpcf7-not-valid-tip');
            // 				fieldErrorMessage.textContent = field.message;

            // 				// Ensure error message is not duplicated
            // 				if (!fieldInput.nextElementSibling || !fieldInput.nextElementSibling.classList.contains('wpcf7-not-valid-tip')) {
            // 					fieldInput.parentNode.insertBefore(fieldErrorMessage, fieldInput.nextSibling);
            // 				}
            // 			}
            // 		});
        });

        jQuery(function($) {
            $('.numberF').on('keydown input', function(event) {
                // Prevent 'e', 'E', '-', and '.' from being typed
                if (event.key === 'e' || event.key === 'E' || event.key === '-' || event.key === '.') {
                    event.preventDefault();
                }

                // Remove 'e', 'E', '-', and '.' if typed via other methods (e.g., copy-paste)
                $(this).val($(this).val().replace(/[eE.-]/g, ''));
            });
            var $radios = $('input[name=areyoua]').change(function() {
                var value = $radios.filter(':checked').val();
                if (value == 'Individual') {
                    $('input[name=your-name]').attr('placeholder', 'Name');
                    $('.forindividuals').css('display', 'block');
                    $('.fororganisation').css('display', 'none');
                    $('.forindividuals input').attr('aria-required', true);
                    $('.fororganisation input').removeAttr("aria-required");
                    $('.fororganisation textarea,.fororganisation input').removeAttr("aria-required");
                    $('.fororganisation textarea,.fororganisation input').removeAttr("aria-invalid");
                    $('.fororganisation input').val("-");
                } else if (value == 'Organisation') {
                    $('input[name=your-name]').attr('placeholder', 'Name of the Owner');
                    $('.forindividuals').css('display', 'none');
                    $('.fororganisation').css('display', 'block');
                    $('.forindividuals input').removeAttr("aria-required");
                    $('.fororganisation textarea,.fororganisation input').removeAttr("aria-invalid");
                    $('.fororganisation input,.fororganisation textarea').attr('aria-required', true);
                    $('input[name=additional-certs]').removeAttr("aria-required");
                    $('.fororganisation input').val("");
                } else if (value == 'Existing Buyer') {
                    $('.forexisting').css('display', 'block');
                    $('.forexisting input').attr('aria-required', true);
                    $('.forexisting input').val("");
                } else if (value == 'New Referer') {
                    $('.forexisting').css('display', 'none');
                    $('.forexisting input').removeAttr("aria-required");
                    $('.forexisting input').val("-");
                }
            });


            /**
             * Disable WPCF7 button while it's submitting
             * Stops duplicate enquiries coming through
             */
            var formData = JSON.parse(sessionStorage.getItem("formData"));
            if (formData) {
                $('.brochureText').css('display', 'block');
            } else {
                $('.brochureText').css('display', 'none');
            }


            $('.submittedfromproject').each(function() {
                $(this).val(General.post_title);
            });


            document.addEventListener('wpcf7submit', function(event) {
                var button = $('.wpcf7-submit.currently-submitting');
                var old_value = button.data('old-value');
                button.prop('disabled', false);
                button.val(old_value);
                button.removeClass('currently-submitting');
            }, false);

            $('form.wpcf7-form').on('submit', function() {
                var form = $(this);
                var button = form.find('input[type=submit]');
                var current_val = button.val();
                button.data('old-value', current_val);
                button.prop("disabled", true);
                button.val("Submitting..");
                button.addClass('currently-submitting');
            });

            $('[name=tab]').each(function(i, d) {
                var p = $(this).prop('checked');
                if (p) {
                    $('article').eq(i)
                        .addClass('on');
                }
            });
            $('[name=tab]').on('change', function() {
                var p = $(this).prop('checked');
                var i = $('[name=tab]').index(this);
                $('article').removeClass('on');
                $('article').eq(i).addClass('on');
            });
            $(".enquiryNowPop,.siteVisitPop,.downloadPop").on('click', function(e) {
                e.preventDefault();
                $(".project_name").val(General.post_title);
            });
            $(".downloadPop,.dloadBrochurePop").on('click', function(e) {
                e.preventDefault();
                $(".download_brochure").val($(this).data("file"));
            });

            function makeTitle(slug) {
                if (slug) {
                    var words = slug.split('-');
                    for (var i = 0; i < words.length; i++) {
                        var word = words[i];
                        words[i] = word.charAt(0).toUpperCase() + word.slice(1);
                    }
                    return words.join(' ');
                }
            }
            $(".submittedfrompage").val(makeTitle(General.slug));
            $('.wpcf7-submit').on('click', function(e) {
                $(".submittedfromsection").val(makeTitle($(this).parents("form").attr("name")));
            });

            // Form 4413: Populate download_brochure with whitepaper PDF
            if (General.resource_pdf_url) {
                $(".download_brochure").val(General.resource_pdf_url);
            }
        });

        document.addEventListener('wpcf7mailsent', function(event) {
            var inputs = event.detail.inputs;
            let varibles = [];
            for (var i = 0; i < inputs.length; i++) {
                varibles[inputs[i].name] = inputs[i].value;
            }
            var form_id = event.detail.contactFormId;
            if (event.detail.contactFormId == '751') {
                var inputs = event.detail.inputs;
                for (var i = 0; i < inputs.length; i++) {
                    if ('download_brochure' == inputs[i].name) {
                        var file = inputs[i].value;
                        if (file) {
                            var fileNameIndex = file.lastIndexOf("/") + 1;
                            var pdf_name = file.substr(fileNameIndex);
                            var pdf_link = General.wp_content_url + file;
                        } else {
                            var pdf_name = '';
                            var pdf_link = '';
                        }
                    }
                }
                var dataObject = {
                    downloadForm: true,
                    pdf_link: pdf_link,
                    pdf_name: pdf_name,
                    FormID: event.detail.contactFormId
                };
            } else {
                // var dataObject = {downloadForm: false};
                // sessionStorage.setItem("formData", JSON.stringify(dataObject));
                var dataObject = {
                    downloadForm: false,
                    FormID: event.detail.contactFormId
                };
                if (General.slug == 'medallion') {
                    dataObject.tracking = true;
                    dataObject.trackingCode = 'zmD3CKOJta4YEJukl-kp';
                } else if (General.slug == 'manapark') {
                    dataObject.tracking = true;
                    dataObject.trackingCode = '-vbGCI6N3LIZEJukl-kp';
                }
                console.log(dataObject);
            }
            sessionStorage.setItem("formData", JSON.stringify(dataObject));
            if (sessionStorage.getItem("formData")) {
                if (form_id == '2710' || form_id == '651' || form_id == '652' || form_id == '1037' || form_id == '653') {
                    var projectname;
                    if (varibles['your-project']) {
                        projectname = varibles['your-project'];
                    } else {
                        projectname = '';
                    }
                    //             var data = {
                    //                 'webformid': '23',
                    //                 'moduletype': 'Basic',
                    //                 'company_name': 'MPDEV',
                    //                 'name': varibles['your-name'],
                    //                 'mobileno': varibles['your-phone'],
                    //                 'email': varibles['your-email'],
                    //                 'projectname': projectname,
                    //                 'description': '',
                    //                 'location': '',
                    //                 'enquiremedium': 'Google Ads'
                    //             };

                    //             jQuery.ajax({
                    //                 type: 'POST',
                    //                 url: 'https://www.thesalezrobot.com/public/api/WebformIntegration',
                    //                 crossDomain: true,
                    //                 contentType: 'application/x-www-form-urlencoded',
                    //                 data: jQuery.param(data),
                    //                 beforeSend: function() {},
                    //                 success: function(response) {
                    //                     // console.log(response);
                    //                     // alert('success');
                    //                     window.location.href = General.site_url + '/thank-you';
                    //                 },
                    //                 error: function(response) {
                    //                     console.error(response);
                    //                 }
                    //             });
                } else {
                    // window.location.href = General.site_url + '/thank-you';
                }
            }
        });
        if (General.slug == 'thank-you' && sessionStorage.getItem('formData')) {
            console.log(sessionStorage.getItem("formData"));
            var formData = JSON.parse(sessionStorage.getItem("formData"));
            jQuery(function($) {
                $('.InBanTitleCont h1').html(formData.thankyouText);
            })
            if (formData.downloadForm) {
                downloadPDF();
            }
        }

        function downloadPDF() {
            var formData = JSON.parse(sessionStorage.getItem("formData"));
            //console.log(formData.pdf_link);
            var pdfUrl = formData.pdf_link;
            var link = document.createElement('a');
            link.download = formData.pdf_name;
            link.href = pdfUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Form 4413: Handle whitepaper PDF download
        document.addEventListener('wpcf7mailsent', function (event) {
            if (event.detail.contactFormId !== 4413) {
                return;
            }

            var pdfLink = '';

            event.detail.inputs.forEach(function (item) {
                if (item.name === 'download_brochure') {
                    pdfLink = item.value;
                }
            });

            if (!pdfLink) return;

            // Trigger PDF download
            var downloadLink = document.createElement('a');
            downloadLink.href = pdfLink;
            downloadLink.download = '';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            setTimeout(function () {

                var responseOutput =
                    event.target.querySelector('.wpcf7-response-output');

                if (!responseOutput) return;

                responseOutput.innerHTML =
                    'Thank you for your message. It has been sent.<br>' +
                    '<a href="' + pdfLink + '" target="_blank" download>' +
                    'Click here to view brochure</a>';

            }, 200);

        });

        // function SaveToDisk(fileURL, fileName) {
        // 	if (!window.ActiveXObject) {
        // 		var save = document.createElement('a');
        // 		save.href = fileURL;
        // 		save.target = '_blank';
        // 		save.download = fileName || 'unknown';
        // 		var evt = new MouseEvent('click', {
        // 			'view': window,
        // 			'bubbles': true,
        // 			'cancelable': false
        // 		});
        // 		save.dispatchEvent(evt);
        // 		(window.URL || window.webkitURL).revokeObjectURL(save.href);
        // 	}
        // 	else if (!!window.ActiveXObject && document.execCommand) {
        // 		var _window = window.open(fileURL, '_blank');
        // 		_window.document.close();
        // 		_window.document.execCommand('SaveAs', true, fileName || fileURL)
        // 		_window.close();
        // 	}
        // }
        // function SaveToDiskSafari(fileURL, fileName) {
        // 	// For non-IE and modern browsers
        // 	if (!window.ActiveXObject) {
        // 		var save = document.createElement('a');
        // 		save.href = fileURL;
        // 		save.target = '_blank';
        // 		save.download = fileName || 'unknown';

        // 		// For Safari, use a different approach
        // 		var reader = new FileReader();
        // 		reader.onloadend = function () {
        // 			var blob = new Blob([reader.result], { type: 'application/octet-stream' });
        // 			save.href = window.URL.createObjectURL(blob);
        // 			document.body.appendChild(save);
        // 			save.click();
        // 			document.body.removeChild(save);
        // 		};
        // 		reader.readAsArrayBuffer(new Blob([fileURL]));

        // 		// Clean up
        // 		(window.URL || window.webkitURL).revokeObjectURL(save.href);
        // 	}
        // 	// for IE < 11
        // 	else if (!!window.ActiveXObject && document.execCommand) {
        // 		var _window = window.open(fileURL, '_blank');
        // 		_window.document.close();
        // 		_window.document.execCommand('SaveAs', true, fileName || fileURL);
        // 		_window.close();
        // 	}
        // }
    </script>
    <style>
        input[type="date"]:before,
        input[type="time"]:before {
            content: attr(placeholder) !important;
            margin-right: 0.5em;
            display: block;

            /* only for FF */
            @-moz-document url-prefix() {

                input[type="date"]:before,
                input[type="time"]:before {
                    content: attr(placeholder) !important;
                    margin-right: 0.5em;
                    display: block;
                    position: absolute;
                    left: 200px;
                    /* please adopt */
                }
            }

            input[type="date"]::-webkit-calendar-picker-indicator,
            input[type="time"]::-webkit-calendar-picker-indicator {
                background: transparent;
                bottom: 0;
                color: transparent;
                cursor: pointer;
                height: auto;
                left: 0;
                position: absolute;
                right: 0;
                top: 0;
                width: auto;
            }
    </style>
    <?php
}

//add_action('wpcf7_before_send_mail', 'wpcf7_add_attachment');
function wpcf7_add_attachment($contact_form)
{
    global $POST;
    $submission = WPCF7_Submission::get_instance();
    if ($submission) {
        $posted_data = $submission->get_posted_data();
    }
    $uploads = wp_upload_dir();
    $upload_path = $uploads['subdir'];
    $mail_2 = $contact_form->prop('mail_2');
    $mail_2['attachments'] .= $posted_data['download_brochure'];
    $contact_form->set_properties(array('mail_2' => $mail_2));
}
// function wpcf7_add_attachment($contact_form){
// 	global $_POST;
// 	$submission = WPCF7_Submission::get_instance();
// 	if ( $submission ) {
// 		$posted_data = $submission->get_posted_data();    
// 	}
// 	$uploads = wp_upload_dir();
//     $upload_path = $uploads['subdir'];
// 	$mail_2 = $contact_form->prop('mail_2');
// 	$mail_2['attachments'] .= 'uploads/2024/01/Cryo-Brochure-new.pdf';
// 	$mail_2['attachments'] .= "\n";
// 	$mail_2['attachments'] .= 'uploads/2024/01/Cryo2-IV-Brochure-new.pdf';
// 	$contact_form->set_properties(array('mail_2' => $mail_2));
// }
/* CF7 Submissions handler Start */
add_action('init', 'cpt_for_cf7_submission');
function cpt_for_cf7_submission()
{
    /**
     * Post Type: CF7 Submissions.
     */
    $labels = [
        "name" => __("CF7 Submissions", "custom-post-type-ui"),
        "singular_name" => __("CF7 Submission", "custom-post-type-ui"),
        "edit_item" => __("View Submitted Data", "custom-post-type-ui"),
    ];
    $args = [
        "label" => __("CF7 Submissions", "custom-post-type-ui"),
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
        //"show_in_menu" => 'wpcf7',
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => true,
        'capability_type' => array('cf7_submission', 'cf7_submissions'),
        // 'capabilities' => array(
        // 	'create_posts' => false,
        // ),
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => ["slug" => "cf7_submissions", "with_front" => true],
        "query_var" => true,
        "supports" => ["nill"],
        //"taxonomies" => [ "category", "post_tag" ],
        "show_in_graphql" => false,
    ];
    register_post_type("cf7_submissions", $args);
}

function add_cf7_submissions_role()
{
    // Add custom role
    add_role(
        'cf7_submissions_manager',
        'CF7 Submissions Manager',
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'publish_posts' => false,
            'upload_files' => true,
            'edit_cf7_submissions' => true,
            'read_cf7_submissions' => true,
            'delete_cf7_submissions' => true,
            'edit_others_cf7_submissions' => true,
            'publish_cf7_submissions' => true,
            'read_private_cf7_submissions' => true,
        )
    );
}
add_action('init', 'add_cf7_submissions_role');

add_action('wpcf7_mail_sent', 'save_my_form_data_to_my_cpt');
add_action('wpcf7_mail_failed', 'save_my_form_data_to_my_cpt');
function save_my_form_data_to_my_cpt($contact_form)
{
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) {
        return;
    }
    $posted_data = $submission->get_posted_data();
    $string = serialize($posted_data);
    $new_post = array();
    $new_post['post_title'] = '';
    foreach ($posted_data as $name => $value) {
        $new_post['meta_input'][$name] = $value;
    }
    $new_post['post_type'] = 'cf7_submissions'; //insert here your CPT
    $new_post['post_content'] = $string;
    $new_post['post_status'] = 'publish';
    $new_post['meta_input']['submitted-from'] = $contact_form->title;
    //When everything is prepared, insert the post into your Wordpress Database
    if ($post_id = wp_insert_post($new_post)) {
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
            $link_html = "<a href=" . $attachment_url . " target='_blank'>Open / Download</a>";
            update_post_meta($post_id, $key, $link_html);
        }
    } else {
        //The post was not inserted correctly, do something (or don't ;) )
    }
    return;
}


add_filter('manage_edit-cf7_submissions_sortable_columns', 'make_your_name_column_sortable');

function make_your_name_column_sortable($sortable_columns)
{
    $sortable_columns['your-name'] = 'your-name';
    $sortable_columns['submitted-from'] = 'submitted-fr';
    //return $sortable_columns;
}


add_filter('manage_cf7_submissions_posts_columns', 'add_cf7_submissions_order_column', 10, 2);
function add_cf7_submissions_order_column($columns)
{
    $lists = get_all_meta_keys('cf7_submissions', true, true);
    $columns = array();
    $columns['post_id'] = __('Submssion ID');
    foreach ($lists as $list) {
        $columns[$list] = '<span class="popup_admin_span">' . slugToTitle($list) . '</span>';
    }
    return $columns;
}

add_action('manage_cf7_submissions_posts_custom_column', 'get_cf7_submissions_order_column_value', 10, 2);
function get_cf7_submissions_order_column_value($column_name, $post_id)
{
    if ($column_name === 'post_id') {
        echo '<div class="shortcode"><a href="' . get_edit_post_link($post_id) . '">' . $post_id . '</a></div>';
    } else {
        $lists = get_all_meta_keys('cf7_submissions', true, true);
        foreach ($lists as $list) {
            if ($column_name === $list) {
                $meta_value = get_post_meta($post_id, $list, true);
                // Check if the meta value is an array
                if (is_array($meta_value)) {
                    // If it's an array, implode it with commas
                    $meta_value = implode(', ', $meta_value);
                }
                echo '<div class="shortcode">' . $meta_value . '</div>';
            }
        }
    }
}



function get_all_meta_keys($post_type = 'post', $exclude_empty = false, $exclude_hidden = false)
{
    global $wpdb;
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s'
    ";
    if ($exclude_empty)
        $query .= " AND $wpdb->postmeta.meta_key != ''";
    if ($exclude_hidden)
        $query .= " AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' ";
    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
    return $meta_keys;
}
function custom_meta_box()
{
    add_meta_box(
        'custom_meta_box', // ID
        'Submission Data', // Title
        'display_custom_meta_box', // Callback function
        'cf7_submissions', // Screen (post, page, dashboard, link, attachment, custom post type)
        'normal', // Context (normal, advanced, side)
        'default' // Priority (default, high, low, core)
    );
}
add_action('add_meta_boxes', 'custom_meta_box');
function display_custom_meta_box($post)
{
    $my_postid = $post->ID; //This is page id or post id
    $content_post = get_post($my_postid);
    $content = $content_post->post_content;
    $content = unserialize($content);
    echo "<table>";
    $custom_fields = get_post_custom($post->ID);
    foreach ($custom_fields as $key => $row) {
        if (! starts_with($key, '_')) {
            if (is_serialized(implode(', ', $row))) {
                $row = implode(', ', maybe_unserialize(implode(', ', $row)));
            } else {
                $row = implode(', ', $row);
            }
            echo "<tr>";
            echo "<td><b>" . slugToTitle($key) . " : </b></td>";
            echo "<td>" .  $row  . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}
function slugToTitle($slug)
{
    // Replace dashes with spaces
    $title = str_replace('-', ' ', $slug);
    // Capitalize the first letter of each word
    $title = ucwords($title);
    return $title;
}
function starts_with($haystack, $needle)
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}

add_action('restrict_manage_posts', 'cf7_submissions_filter_by_submitted_from');
function cf7_submissions_filter_by_submitted_from()
{
    global $typenow, $wpdb;
    if ($typenow == 'cf7_submissions') {
        $submitted_from = isset($_GET['submitted_from']) ? $_GET['submitted_from'] : '';

        // Query unique values for the 'submitted-from' meta key
        $meta_key = 'submitted-from';
        $submitted_from_values = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
                $meta_key
            )
        );

        echo '<select name="submitted_from[]" id="submitted_from" multiple="multiple">';

        foreach ($submitted_from_values as $value) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($value),
                in_array($value, (array)$submitted_from) ? ' selected="selected"' : '',
                esc_html($value)
            );
        }
        echo '</select>';
    }
}

add_action('admin_footer', 'cf7_submissions_select2_init', 1000000);
function cf7_submissions_select2_init()
{
    global $typenow;
    if ($typenow == 'cf7_submissions') {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#submitted_from').select2({
                    placeholder: 'Select Form',
                });
            });
        </script>
    <?php
    }
}

add_filter('parse_query', 'cf7_submissions_filter_query');
function cf7_submissions_filter_query($query)
{
    global $pagenow, $wpdb;
    $type = 'cf7_submissions';
    if ($pagenow == 'edit.php' && isset($_GET['submitted_from']) && !empty($_GET['submitted_from'])) {
        $query->query_vars['meta_query'] = array(
            array(
                'key' => 'submitted-from',
                'value' => $_GET['submitted_from'],
                'compare' => 'IN',
            ),
        );
    }
}

function enqueue_select2()
{
    global $typenow;
    if ($typenow == 'cf7_submissions') {
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_select2');



add_action('admin_footer', 'add_csv_download_button');
function add_csv_download_button()
{
    global $typenow;
    if ($typenow == 'cf7_submissions') {
    ?>
        <script>
            jQuery(document).ready(function($) {
                $('.wrap').find('.tablenav.top .alignleft.actions').append('<a href="#" class="button download-csv">Download CSV</a>');

                $('.download-csv').on('click', function(e) {
                    e.preventDefault();
                    var visibleRows = $('.wrap .wp-list-table tbody tr:visible');
                    var data = [];
                    var headers = [];
                    var title = $('.wrap .wrap h1').text().trim(); // Extract title from page

                    // Get table headers excluding the first column
                    $('.wrap .wp-list-table thead th').slice(1).each(function() {
                        headers.push($(this).text().trim());
                    });

                    // Include headers as first row
                    data.push(headers);

                    // Get data from visible rows, excluding the first column
                    visibleRows.each(function() {
                        var rowData = [];
                        $(this).find('td').slice(1).each(function() {
                            rowData.push($(this).text().trim());
                        });
                        data.push(rowData);
                    });

                    var csvContent = '';
                    data.forEach(function(rowArray) {
                        var row = rowArray.join(",");
                        csvContent += row + "\r\n";
                    });

                    // Construct download name based on submitted_from parameter or 'all' and current date and time
                    var submittedFrom = getUrlParameter('submitted_from') || 'all';
                    var currentDate = new Date().toISOString().slice(0, 19).replace(/:/g, "-").replace("T", " ");
                    var downloadName = 'website-form-submissions-' + submittedFrom + '-' + currentDate + '.csv';

                    var link = document.createElement('a');
                    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
                    link.download = downloadName;
                    link.click();
                });

                // Function to get URL parameter value by name
                function getUrlParameter(name) {
                    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                    var results = regex.exec(location.search);
                    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                }
            });
        </script>
    <?php
    }
}

function add_csv_download_button_with_column_selection()
{
    global $typenow;
    if ($typenow == 'cf7_submissions') {
    ?>
        <style>
            /* Modal CSS */
            .modal {
                display: none;
                /* Hidden by default */
                position: fixed;
                /* Stay in place */
                z-index: 9999;
                /* Sit on top */
                left: 0;
                top: 0;
                width: 100%;
                /* Full width */
                height: 100%;
                /* Full height */
                overflow: auto;
                /* Enable scroll if needed */
                background-color: rgba(0, 0, 0, 0.4);
                /* Black w/ opacity */
            }

            .modal-content {
                background-color: #fefefe;
                margin: 15% auto;
                /* 15% from the top and centered */
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                /* Could be more or less, depending on screen size */
            }

            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
            }

            .button {
                padding: 10px 20px;
                background-color: #0073e6;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .button:hover {
                background-color: #0056b3;
            }

            .row-list {
                list-style: none;
                padding: 0;
            }

            .row-list li {
                margin-bottom: 10px;
            }
        </style>

        <div id="download-popup" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Select columns to download</h2>
                <form id="download-form">
                    <ul class="column-list">
                        <!-- Dynamic list of columns will be inserted here -->
                    </ul>
                    <input type="submit" class="button" value="Download">
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Append download button
                var downloadButton = document.createElement('a');
                downloadButton.href = "#";
                downloadButton.className = "button download-csv";
                downloadButton.textContent = "Download CSV";
                var actionsContainer = document.querySelector('.wrap .tablenav.top .alignleft.actions');
                actionsContainer.appendChild(downloadButton);

                // Click event for download button
                downloadButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Show modal
                    document.getElementById('download-popup').style.display = 'block';
                });

                // Click event for closing the modal
                var closeButton = document.querySelector('.modal .close');
                closeButton.addEventListener('click', function() {
                    document.getElementById('download-popup').style.display = 'none';
                });

                // Dynamically add columns to modal content
                var columnList = document.querySelector('.modal .column-list');
                var tableHeaders = document.querySelectorAll('.wrap .wp-list-table thead th');
                tableHeaders.forEach(function(header) {
                    var columnHeader = header.textContent.trim();
                    var listItem = document.createElement('li');
                    var label = document.createElement('label');
                    var checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'column-' + columnHeader;
                    checkbox.value = columnHeader;
                    label.appendChild(checkbox);
                    label.appendChild(document.createTextNode(columnHeader));
                    listItem.appendChild(label);
                    columnList.appendChild(listItem);
                });

                // Form submission event handler
                var downloadForm = document.getElementById('download-form');
                downloadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var selectedColumns = [];
                    downloadForm.querySelectorAll('input[type="checkbox"]:checked').forEach(function(checkbox) {
                        selectedColumns.push(checkbox.value);
                    });

                    var csvContent = '';
                    // Add headers as the first row
                    var headers = [];
                    tableHeaders.forEach(function(header) {
                        if (selectedColumns.indexOf(header.textContent.trim()) !== -1) {
                            headers.push(header.textContent.trim());
                        }
                    });
                    csvContent += headers.join(",") + "\r\n";

                    // Get data rows from visible rows for selected columns
                    var visibleRows = document.querySelectorAll('.wrap .wp-list-table tbody tr');
                    visibleRows.forEach(function(row) {
                        var rowData = [];
                        row.querySelectorAll('td').forEach(function(cell, index) {
                            if (index > 0 && selectedColumns.indexOf(tableHeaders[index].textContent.trim()) !== -1) {
                                rowData.push(cell.textContent.trim());
                            }
                        });
                        csvContent += rowData.join(",") + "\r\n";
                    });

                    // Construct download name
                    var currentDate = new Date().toISOString().slice(0, 19).replace(/:/g, "-").replace("T", " ");
                    var downloadName = 'website-form-submissions-selected-' + currentDate + '.csv';

                    // Trigger CSV download
                    var link = document.createElement('a');
                    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
                    link.download = downloadName;
                    link.click();

                    // Close modal
                    document.getElementById('download-popup').style.display = 'none';
                });
            });
        </script>
    <?php
    }
}



add_filter('post_row_actions', 'remove_row_actions_post', 10, 2);
function remove_row_actions_post($actions, $post)
{
    if ($post->post_type === 'cf7_submissions') {
        unset($actions['inline hide-if-no-js']);
        unset($actions['clone']);
        unset($actions['trash']);
        unset($actions['edit']);
    }
    return $actions;
}
add_filter('bulk_actions-edit-cf7_submissions', 'remove_bulk_actions_cf7_submissions');
function remove_bulk_actions_cf7_submissions($actions)
{
    // Remove desired bulk actions
    unset($actions['edit']);
    unset($actions['trash']);
    // Add more unset statements as needed for other actions
    return $actions;
}

add_action('wp_trash_post', 'restrict_post_deletion');
function restrict_post_deletion($post_id)
{
    if (get_post_type($post_id) === 'cf7_submissions') {
        wp_die('The post you were trying to delete is protected.');
    }
}
add_action('admin_head', function () {
    ?>
    <style>
        table.wp-list-table th {
            width: 110px;
        }
    </style>
    <?php
    $current_screen = get_current_screen();
    if (
        'post' === $current_screen->base &&
        'cf7_submissions' === $current_screen->post_type
    ) :
    ?>
        <style>
            #delete-action {
                display: none;
            }
        </style>
    <?php
    endif;
    if (
        'term' === $current_screen->base &&
        'org' === $current_screen->taxonomy
    ) :
    ?>
        <style>
            #delete-link {
                display: none;
            }
        </style>
<?php
    endif;
});
/* CF7 Submissions handler End */

/* form_tag handler */
add_action('wpcf7_init', 'wpcf7_add_form_tag_custom_checkbox', 10, 0);
function wpcf7_add_form_tag_custom_checkbox()
{
    wpcf7_add_form_tag(
        array('custom_checkbox', 'custom_checkbox*'),
        'wpcf7_custom_checkbox_form_tag_handler',
        array(
            'name-attr' => true,
            'selectable-values' => true,
            'multiple-controls-container' => true,
        )
    );
}
function wpcf7_custom_checkbox_form_tag_handler($tag)
{
    if (empty($tag->name)) {
        return '';
    }
    $validation_error = wpcf7_get_validation_error($tag->name);
    $class = wpcf7_form_controls_class($tag->type);
    if ($validation_error) {
        $class .= ' wpcf7-not-valid';
    }
    $label_first = $tag->has_option('label_first');
    $use_label_element = $tag->has_option('use_label_element');
    $exclusive = $tag->has_option('exclusive');
    $free_text = $tag->has_option('free_text');
    $multiple = false;
    if ('custom_checkbox' == $tag->basetype) {
        $multiple = ! $exclusive;
    } else { // radio
        $exclusive = false;
    }
    if ($exclusive) {
        $class .= ' wpcf7-exclusive-custom_checkbox';
    }
    $atts = array();
    $atts['class'] = $tag->get_class_option($class);
    $atts['id'] = $tag->get_id_option();
    if ($validation_error) {
        $atts['aria-describedby'] = wpcf7_get_validation_error_reference(
            $tag->name
        );
    }
    $tabindex = $tag->get_option('tabindex', 'signed_int', true);
    if (false !== $tabindex) {
        $tabindex = (int) $tabindex;
    }
    $html = '';
    $count = 0;
    if ($data = (array) $tag->get_data_option()) {
        if ($free_text) {
            $tag->values = array_merge(
                array_slice($tag->values, 0, -1),
                array_values($data),
                array_slice($tag->values, -1)
            );
            $tag->labels = array_merge(
                array_slice($tag->labels, 0, -1),
                array_values($data),
                array_slice($tag->labels, -1)
            );
        } else {
            $tag->values = array_merge($tag->values, array_values($data));
            $tag->labels = array_merge($tag->labels, array_values($data));
        }
    }
    $values = $tag->values;
    $labels = $tag->labels;
    $default_choice = $tag->get_default_option(null, array(
        'multiple' => $multiple,
    ));
    $hangover = wpcf7_get_hangover($tag->name, $multiple ? array() : '');
    foreach ($values as $key => $value) {
        if ($hangover) {
            $checked = in_array($value, (array) $hangover, true);
        } else {
            $checked = in_array($value, (array) $default_choice, true);
        }
        if (isset($labels[$key])) {
            $label = $labels[$key];
        } else {
            $label = $value;
        }
        $item_atts = array(
            'type' => 'checkbox',
            'name' => $tag->name . ($multiple ? '[]' : ''),
            'value' => $value,
            'checked' => $checked,
            'tabindex' => false !== $tabindex ? $tabindex : '',
            'aria-required' => $tag->is_required() ? 'true' : '',
        );
        $item_atts = wpcf7_format_atts($item_atts);
        if ($label_first) { // put label first, input last
            $item = sprintf(
                '<span class="wpcf7-list-item-label">%1$s</span><input %2$s />',
                esc_html($label),
                $item_atts
            );
        } else {
            $item = sprintf(
                '<input %2$s /><span class="wpcf7-list-item-label">%1$s</span>',
                esc_html($label),
                $item_atts
            );
        }
        if ($use_label_element) {
            $item = '<label>' . $item . '</label>';
        }
        if (
            false !== $tabindex
            and 0 < $tabindex
        ) {
            $tabindex += 1;
        }
        $class = 'wpcf7-list-item';
        $count += 1;
        if (1 == $count) {
            $class .= ' first';
        }
        if (count($values) == $count) { // last round
            $class .= ' last';
            if ($free_text) {
                $free_text_name = $tag->name . '_free_text';
                $free_text_atts = array(
                    'name' => $free_text_name,
                    'class' => 'wpcf7-free-text',
                    'tabindex' => false !== $tabindex ? $tabindex : '',
                );
                if (
                    wpcf7_is_posted()
                    and isset($_POST[$free_text_name])
                ) {
                    $free_text_atts['value'] = wp_unslash($_POST[$free_text_name]);
                }
                $free_text_atts = wpcf7_format_atts($free_text_atts);
                $item .= sprintf(' <input type="text" %s />', $free_text_atts);
                $class .= ' has-free-text';
            }
        }
        $item = '<span class="' . esc_attr($class) . '">' . $item . '</span>';
        $html .= $item;
    }
    $html = sprintf(
        '<span class="wpcf7-form-control-wrap" data-name="%1$s"><span %2$s>%3$s</span>%4$s</span>',
        esc_attr($tag->name),
        wpcf7_format_atts($atts),
        $html,
        $validation_error
    );
    return $html;
}
add_action(
    'wpcf7_swv_create_schema',
    'wpcf7_swv_add_custom_checkbox_rules',
    10,
    2
);
function wpcf7_swv_add_custom_checkbox_rules($schema, $contact_form)
{
    $tags = $contact_form->scan_form_tags(array(
        'type' => array('custom_checkbox*'),
    ));
    foreach ($tags as $tag) {
        $schema->add_rule(
            wpcf7_swv_create_rule('required', array(
                'field' => $tag->name,
                'error' => wpcf7_get_message('invalid_required'),
            ))
        );
    }
}
add_action('wpcf7_init', 'custom_add_form_tag_time_selector');
function custom_add_form_tag_time_selector()
{
    wpcf7_add_form_tag(array('time_selector', 'time_selector*'), 'time_selector_form_tag_handler', true);
}
function time_selector_form_tag_handler($tag)
{
    $tag = new WPCF7_FormTag($tag);
    if (empty($tag->name)) {
        return '';
    }
    $validation_error = wpcf7_get_validation_error($tag->name);
    $class = wpcf7_form_controls_class($tag->type);
    if ($validation_error) {
        $class .= ' wpcf7-not-valid';
    }
    $atts = array();
    $atts['class'] = $tag->get_class_option($class);
    $atts['id'] = $tag->get_id_option();
    if ($tag->is_required()) {
        $atts['aria-required'] = 'true';
    }
    if ($tag->has_option('placeholder') || $tag->has_option('watermark')) {
        $value = (string) reset($tag->values);
        $atts['placeholder'] = $value;
        $value = '';
    }
    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';
    $atts['name'] = $tag->name;
    $atts = wpcf7_format_atts($atts);
    $html = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s"><input type="time" %2$s></input>%3$s</span>',
        sanitize_html_class($tag->name),
        $atts,
        $validation_error
    );
    return $html;
}
add_filter('wpcf7_validate_time_selector', 'wpcf7_time_selector_validation_filter', 10, 2);
add_filter('wpcf7_validate_time_selector*', 'wpcf7_time_selector_validation_filter', 10, 2);
function wpcf7_time_selector_validation_filter($result, $tag)
{
    $tag = new WPCF7_FormTag($tag);
    $name = $tag->name;
    if (isset($_POST[$name]) && is_array($_POST[$name])) {
        foreach ($_POST[$name] as $key => $value) {
            if ('' === $value) {
                unset($_POST[$name][$key]);
            }
        }
    }
    $empty = ! isset($_POST[$name]) || empty($_POST[$name]) && '0' !== $_POST[$name];
    if ($tag->is_required() && $empty) {
        $result->invalidate($tag, wpcf7_get_message('invalid_required'));
    }
    return $result;
}
/* 
add_filter( 'wpcf7_form_elements', 'dd_wpcf7_form_elements_replace' );
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
