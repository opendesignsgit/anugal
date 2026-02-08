<?php

add_shortcode('show_posts','show_posts');
function show_posts(){
	ob_start();
	
	$per_page = 6;
    if (isset($atts['class'])) {
        $class = $atts['class'];
    } else {
        $class = '';
    }
    if (isset($atts['order'])) {
        $order = $atts['order'];
    } else {
        $order = 'ASC';
    } ?>
		<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="posts_filter">
			<div class="postsinputs span_12" id="postsinputs">
				<div class="span_25 posts_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
					<?php 
					$postcategories = array(
									'show_option_none'   => 'Select Category',
									'option_none_value'  => '',
									'orderby'            => 'ID', 
									'order'              => 'ASC',
									'show_count'         => 0,
									'hide_empty'         => 0, 
									'child_of'           => 0,
									'exclude'            => '1',
									'echo'               => 1,
									'selected'           => 0,
									'hierarchical'       => 1, 
									'name'               => 'postcategories',
									'id'                 => 'postcategories',
									'class'              => 'form-no-clear',
									'depth'              => 1,
									'tab_index'          => 0,
									'taxonomy'           => 'category',
									'hide_if_empty'      => true,
									'required'           => false
							); 
					wp_dropdown_categories($postcategories); 
					?>
				</div>
				<div class="span_25 posts_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
					<input type="text" name="postsearch" class="postsearch" id="postsearch" placeholder="Search">
				</div>
				
				
				<div class="span_25 posts_col wpb_column col no-extra-padding inherit_tablet inherit_phone">
					<button class="btn btn-go">Go</button>
					<span class="loader" style="display:none;width: 13px;margin-left: auto;margin-right: auto;"><img src="<?php echo site_url() ?>/wp-content/plugins/custom-plugin//images/loader.gif"></span>
				</div>
			</div>
			<input type="hidden" name="action" value="myblogfilter">
			<input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
		</form>
		<div class="postsoutputs span_12 cvf_pag_loading" id="postsoutputs">
			<div id="response">
			<?php		
				$post_type = 'post';
				$template = 'show_post';
				$args = array(
				'post_type' => $post_type, 
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
					include(PLUGIN_DIR .'/templates/show_post.php');
				}
				$count = $the_query->found_posts; 
				echo pagination_load_posts(1,$count,$per_page);
				}
				else{
					echo '404 Nothing Found.';
				}		
				wp_reset_postdata();
			?>
			</div>
		</div>
		<script>
			jQuery(function($){
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

				function load_all_post(page,per_page){
					$(".btn.btn-go").attr("disabled", true);
					// Start the transition
					$(".cvf-pagination-nav").fadeIn().css('background','#ccc');

					// Data to receive from our server
					// the value in 'action' is the key that will be identified by the 'wp_ajax_' hook 
					var postsearch = $(".postsoutputs .cvf-universal-pagination").data('postsearch'),
						postcategories = $(".postsoutputs .cvf-universal-pagination").data('postcategories');
					var data = {
						page: page,
						per_page: per_page,
						action: "myblogfilter",
						postsearch: postsearch,
						postcategories: postcategories
					};

					// Send the data
					$.post(ajaxurl, data, function(response) {
						// If successful Append the data into our html container
						$(".postsoutputs #response").html(response);
						// End the transition
						$(".cvf-pagination-nav").css({'background':'none', 'transition':'all 1s ease-out'});
						$(".btn.btn-go").attr("disabled", false);
						$('#posts_filter .loader').css('display','none');	
						$('.postsoutputs .cvf-universal-pagination').attr('data-postsearch', $('#postsearch').val());
						$('.postsoutputs .cvf-universal-pagination').attr('data-postcategories', $('#postcategories').val());	
					});
				}
				$(document).on('click','.postsoutputs .cvf-universal-pagination .active',function(e) {
					var page = $(this).attr('p');
					var per_page = $(this).attr('pp');
					load_all_post(page,per_page);
					 
				});	

				$('select.form-no-clear').on('change', function() {
					$('#posts_filter').find('button').text('Go');
				});
				$('.postsearch').on('input',function(e){
					$('#posts_filter').find('button').text('Go');
				});
				$('#posts_filter').submit(function(e){
					e.preventDefault();
					var filter = $('#posts_filter');
					$.ajax({
						url:filter.attr('action'),
						data:filter.serialize(), // form data
						type:filter.attr('method'), // POST
						beforeSend:function(xhr){ 
							$('#posts_filter .loader').css('display','block');
							$('#posts_filter').find('button').text('Go');
						},
						success:function(data){
							filter.find('button').text('Go'); // changing the button label back
							$('.postsoutputs #response').html(data); // insert data
							$(".btn.btn-go").attr("disabled", false);
							$('#posts_filter .loader').css('display','none');	
							$('.postsoutputs .cvf-universal-pagination').attr('data-postsearch', $('#postsearch').val());
							$('.postsoutputs .cvf-universal-pagination').attr('data-postcategories', $('#postcategories').val());		
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

//custom post types and careers taxnomy and filter start
add_action('wp_ajax_myblogfilter', 'myblogfilter'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_myblogfilter', 'myblogfilter');
 
function myblogfilter(){
    if(isset($_REQUEST['page'])){
        // Sanitize the received page   
        $page = sanitize_text_field($_REQUEST['page']);
    }else{
		$page = 1;
	}
    if(isset($_REQUEST['per_page'])){
        // Sanitize the received page   
        $per_page = sanitize_text_field($_REQUEST['per_page']);
    }else{
		$per_page = 3;
	}
	$postsearch = $postcategories = ''; 
    $postcategories =  isset( $_REQUEST[ 'postcategories' ] ) ? sanitize_text_field( $_REQUEST[ 'postcategories' ] ) : '';
	$start = ( $page - 1 ) * $per_page;
	
	$args = array(
		'post_type' => 'post', 
		'post_status' => 'publish' ,
		'hierarchical' => true,
		'posts_per_page' => $per_page,
		'offset' => $start,
	);


	// single filter
	if( $postsearch )
		$args  = array(
			'post_type' => 'post', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			'search_prod_title' => $postsearch,
			'offset' => $start,
			
		);
 	if( $postcategories )
		$args  = array(
			'post_type' => 'post', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			'offset' => $start,
			'tax_query' => array(
              array(
                  'taxonomy' => 'category',
                  'field'    => 'id',
                  'terms' => $postcategories,
                  'operator' => 'IN'
                  )
			)
		);
 	// 2 combination filter
  	if( $postsearch && $postcategories)
		$args  = array(
			'post_type' => 'post', 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $per_page,
			'offset' => $start,
			'search_prod_title' => $postsearch,
			'tax_query' => array(
			 'relation' => 'AND',              
 			   array(
                  'taxonomy' => 'category',
                  'field'    => 'id',
                  'terms'    => $postcategories ,
                 
             )
		)
	 
	);
	add_filter( 'posts_where', 'title_filter', 10, 2 );
	$query = new WP_Query( $args );
	remove_filter( 'posts_where', 'title_filter', 10, 2 );
	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			include(PLUGIN_DIR .'/templates/show_post.php');
		endwhile;
		$count = $query->found_posts; 
		echo pagination_load_posts($page,$count,$per_page);
		wp_reset_postdata();
	else :
		echo 'No Posts Found.';
	endif;
 
	die();
}