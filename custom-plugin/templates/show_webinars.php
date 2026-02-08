<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="webinar_filter">
	<div class="webinarsinputs span_12" id="webinarsinputs">
	<div class="span_25 webinars_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
	<?php 
	$webinarcategory = array(
					'show_option_none'   => 'Choose Category',
					'option_none_value'  => '',
                    'orderby'            => 'ID', 
                    'order'              => 'ASC',
                    'show_count'         => 1,
                    'hide_empty'         => 1, 
                    'child_of'           => 0,
                    'exclude'            => '',
                    'echo'               => 1,
					//'value_field'		 => 'slug',
					'value_field'		 => 'ID',
                    'selected'           => 1,
                    'hierarchical'       => 1, 
                    'name'               => 'webinarcategory',
                    'id'                 => 'webinarcategory',
                    'class'              => 'form-no-clear',
                    'depth'              => 1,
                    'tab_index'          => 1,
                    'taxonomy'           => 'webinarcategory',
                    //'taxonomy'           => 'category',
                    'hide_if_empty'      => false,
					
            ); 
	wp_dropdown_categories($webinarcategory); 
	?>
	</div>
	<div class="span_25 webinars_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
	<input type="text" name="webinarsearch" class="webinarsearch" id="webinarsearch" placeholder="Search">
	</div>
	
	<div class="span_25 webinars_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
	<button class="btn btn-go">Go</button>
	<span class="loader" style="display:none;width: 13px;margin-left: auto;margin-right: auto;"><img src="<?php echo site_url() ?>/wp-content/plugins/custom-plugin//images/loader.gif"></span>
	</div>
	</div>
	<input type="hidden" name="action" value="webinar_filter">
</form>

<div class="webinarsoutputs span_12 cvf_pag_loading" id="webinarsoutputs">
	<div id="response">
		<?php		
			$per_page = 6;
			$args = array(
			'post_type' => 'webinars', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			/* 'tax_query' => array(
			 $tax_query_val
			) */
			);
			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
			$the_query->the_post();
				include(PLUGIN_DIR .'/templates/webinar-post.php');
			}
			$count = $the_query->found_posts; 
			echo pagination_load_posts(1,$count,$per_page);
			}
			else{
				echo 'No Webinar Found.';
			}		
			wp_reset_postdata();
		?>
	</div>
</div>
 
<script>
jQuery(function($){
	// Ajax for pagination
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

            function load_all_posts(page){
                // Start the transition
                $(".cvf_pag_loading").fadeIn().css('background','#ccc');

                // Data to receive from our server
                // the value in 'action' is the key that will be identified by the 'wp_ajax_' hook 
				var webinarsearch = $("#cvf-universal-pagination").data('webinarsearch'),
					webinarcategory = $("#cvf-universal-pagination").data('webinarcategory');
                var data = {
                    page: page,
                    action: "webinar_filter",
					webinarsearch: webinarsearch,
					webinarcategory: webinarcategory
                };

                // Send the data
                $.post(ajaxurl, data, function(response) {
                    // If successful Append the data into our html container
                    $("#response").html(response);
                    // End the transition
                    $(".cvf_pag_loading").css({'background':'none', 'transition':'all 1s ease-out'});
					$('#cvf-universal-pagination').attr('data-webinarsearch', $('#webinarsearch').val());
					$('#cvf-universal-pagination').attr('data-webinarcategory', $('#webinarcategory').val());
                });
            }
			$(document).on('click','#cvf-universal-pagination .active',function(e) {
				var page = $(this).attr('p');
				load_all_posts(page);
				 
			});	
		
	// Ajax for pagination



	$("#response").on("click",".show-more", function(){
        if($(this).parent().find(".text").hasClass("show-more-height")) {
            $(this).text("JOB DESCRIPTION -");
        } else {
            $(this).text("JOB DESCRIPTION +"); 
        }

        $(this).parent().find(".text").toggleClass("show-more-height");
    });
	$('select.form-no-clear').on('change', function() {
	$('#webinar_filter').find('button').text('Go');
	});
	$('.webinarsearch').on('input',function(e){
    $('#webinar_filter').find('button').text('Go');
	});
	$('#webinar_filter').submit(function(e){
		e.preventDefault();
		/* var formData = $(this).serializeArray();
		dataObj = {};
		$(formData).each(function(i, field){
			dataObj[field.name] = field.value;
		});
		var search_string = "";
		if(dataObj['webinarcategory']){
			search_string += "/category/" + dataObj['webinarcategory'];
		}
		if(dataObj['webinarsearch']){
			search_string += "/?s=" + dataObj['webinarsearch'];
		}
		if(dataObj['webinarcategory'] || dataObj['webinarsearch']){
			window.open(General.site_url + search_string);
		}
		return; */
		var webinar_filter = $('#webinar_filter');
		$.ajax({
			url:webinar_filter.attr('action'),
			data:webinar_filter.serialize(), // form data
			type:webinar_filter.attr('method'), // POST
			beforeSend:function(xhr){ 
				$('#webinar_filter .loader').css('display','block');
				$('#webinar_filter').find('button').text('Go');
			},
			success:function(data){
				webinar_filter.find('button').text('Go'); // changing the button label back
				$('#response').html(data); // insert data
				$('#webinar_filter .loader').css('display','none');	
				$('#cvf-universal-pagination').attr('data-webinarsearch', $('#webinarsearch').val());
				$('#cvf-universal-pagination').attr('data-webinarcategory', $('#webinarcategory').val());
			}
		});
		return false;
	});
   
});
</script>