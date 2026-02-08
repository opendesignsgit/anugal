<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
	<div class="careersinputs span_12" id="careersinputs">
	<!--<div class="span_25 careers_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
	<input type="text" name="careersearch" class="careersearch" id="careersearch" placeholder="Search">
	</div>-->
	<div class="span_25 careers_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
		<?php 
		$careerdepartment = array(
						'show_option_none'   => 'Select Industry',
						'option_none_value'  => '',
						'orderby'            => 'ID', 
						'order'              => 'ASC',
						'show_count'         => 0,
						'hide_empty'         => 0, 
						'child_of'           => 0,
						'exclude'            => '',
						'echo'               => 1,
						'selected'           => 1,
						'hierarchical'       => 1, 
						'name'               => 'careerdepartment',
						'id'                 => 'careerdepartment',
						'class'              => 'form-no-clear',
						'depth'              => 1,
						'tab_index'          => 1,
						'taxonomy'           => 'department',
						'hide_if_empty'      => true,
						'required'           => false
				); 
		wp_dropdown_categories($careerdepartment); 
		?>
	</div>
	<div class="span_25 careers_col wpb_column col no-extra-padding inherit_tablet inherit_phone caree_position_values">
		<select name="careerposition" id="careerposition" class="form-no-clear" tabindex="1" required>
			<option value="">Select Position</option>
		</select>
	</div>
	<div class="span_25 careers_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
		<?php 
		$careerlocation = array(
						'show_option_none'   => 'Choose Location',
						'option_none_value'  => '',
						'orderby'            => 'ID', 
						'order'              => 'ASC',
						'show_count'         => 1,
						'hide_empty'         => 1, 
						'child_of'           => 0,
						'exclude'            => '',
						'echo'               => 1,
						'selected'           => 1,
						'hierarchical'       => 1, 
						'name'               => 'careerlocation',
						'id'                 => 'careerlocation',
						'class'              => 'form-no-clear',
						'depth'              => 1,
						'tab_index'          => 1,
						'taxonomy'           => 'location',
						'hide_if_empty'      => true,
						'required'           => false
						
				); 
		wp_dropdown_categories($careerlocation); 
		?>
	</div>
	
	
	<div class="span_25 careers_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
	<button class="btn btn-go">Go</button>
	<span class="loader" style="display:none;width: 13px;margin-left: auto;margin-right: auto;"><img src="<?php echo site_url() ?>/wp-content/plugins/custom-plugin//images/loader.gif"></span>
	</div>
	</div>
	<input type="hidden" name="action" value="myfilter">
</form>

<div class="careersoutputs span_12 cvf_pag_loading" id="careersoutputs">
	<div id="response">
		<?php		
			$per_page = 2;
			$args = array(
			'post_type' => 'careers', 
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
				include(PLUGIN_DIR .'/templates/career-post.php');
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
	
	$('#careerdepartment').on('change', function() {
		var data = {action: "careerpositions", careerdepartment: this.value};
		$.ajax({
			url:General.ajaxurl,
			data:data, // form data
			type:'POST', // POST
			beforeSend:function(xhr){ 
				$(".btn.btn-go").attr("disabled", true);
				$(".caree_position_values select").fadeIn().css('background','#ccc');
			},
			success:function(data){
				$(".caree_position_values").html(data);
				$(".caree_position_values select").css({'background':'none', 'transition':'all 1s ease-out'});
				$(".btn.btn-go").attr("disabled", false);	
			}
		});
	});
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

	function load_all_posts(page,per_page){
		$(".btn.btn-go").attr("disabled", true);
		// Start the transition
		$(".cvf_pag_loading").fadeIn().css('background','#ccc');

		// Data to receive from our server
		// the value in 'action' is the key that will be identified by the 'wp_ajax_' hook 
		var careersearch = $(".careersoutputs .cvf-universal-pagination").data('careersearch'),
			careerlocation = $(".careersoutputs .cvf-universal-pagination").data('careerlocation'),
			careerposition = $(".careersoutputs .cvf-universal-pagination").data('careerposition');
		var data = {
			page: page,
			per_page: per_page,
			action: "myfilter",
			careersearch: careersearch,
			careerlocation: careerlocation,
			careerposition: careerposition
		};

		// Send the data
		$.post(ajaxurl, data, function(response) {
			// If successful Append the data into our html container
			$("#response").html(response);
			// End the transition
			$(".cvf_pag_loading").css({'background':'none', 'transition':'all 1s ease-out'});
			$(".btn.btn-go").attr("disabled", false);
			$('.careersoutputs .cvf-universal-pagination').attr('data-careersearch', $('#careersearch').val());
			$('.careersoutputs .cvf-universal-pagination').attr('data-careerlocation', $('#careerlocation').val());
			$('.careersoutputs .cvf-universal-pagination').attr('data-careerposition', $('#careerposition').val());
		});
	}
	$(document).on('click','.careersoutputs .cvf-universal-pagination .active',function(e) {
		var page = $(this).attr('p');
		var per_page = $(this).attr('pp');
		load_all_posts(page,per_page);
		 
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
	$('#filter').find('button').text('Go');
	});
	$('.careersearch').on('input',function(e){
    $('#filter').find('button').text('Go');
	});
	$('#filter').submit(function(e){
		e.preventDefault();
		var filter = $('#filter');
		$.ajax({
			url:filter.attr('action'),
			data:filter.serialize(), // form data
			type:filter.attr('method'), // POST
			beforeSend:function(xhr){ 
				$('#filter .loader').css('display','block');
				$('#filter').find('button').text('Go');
			},
			success:function(data){
				filter.find('button').text('Go'); // changing the button label back
				$('#response').html(data); // insert data
				$('#filter .loader').css('display','none');	
				$('.careersoutputs .cvf-universal-pagination').attr('data-careersearch', $('#careersearch').val());
				$('.careersoutputs .cvf-universal-pagination').attr('data-careerlocation', $('#careerlocation').val());
				$('.careersoutputs .cvf-universal-pagination').attr('data-careerdepartment', $('#careerdepartment').val());		
			}
		});
		return false;
	});
   
});
</script>