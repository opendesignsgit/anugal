<?php

//Pagination ajax
function pagination_load_posts($page,$count,$per_page) {

		$msg = '';
	    
        $cur_page = $page;
        $page -= 1;
        // Set the number of results to display
        $previous_btn = true;
        $next_btn = true;
        $first_btn = true;
        $last_btn = true;
        $start = $page * $per_page;
		$pag_container ='';
		
		// Optional, wrap the output into a container
        $msg = "<div class='cvf-universal-content'>" . $msg . "</div><br class = 'clear' />";

        // This is where the magic happens
        $no_of_paginations = ceil($count / $per_page);

        if ($cur_page >= 7) {
            $start_loop = $cur_page - 3;
            if ($no_of_paginations > $cur_page + 3)
                $end_loop = $cur_page + 3;
            else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
                $start_loop = $no_of_paginations - 6;
                $end_loop = $no_of_paginations;
            } else {
                $end_loop = $no_of_paginations;
            }
        } else {
            $start_loop = 1;
            if ($no_of_paginations > 7)
                $end_loop = 7;
            else
                $end_loop = $no_of_paginations;
        }

        // Pagination Buttons logic     
        $pag_container .= "
        <div class='cvf-universal-pagination' id='cvf-universal-pagination'>
            <ul>";

        if ($first_btn && $cur_page > 1) {
            $pag_container .= "<li p='1' pp='$per_page' class='active'>First</li>";
        } else if ($first_btn) {
            $pag_container .= "<li p='1' pp='$per_page' class='inactive'>First</li>";
        }

        if ($previous_btn && $cur_page > 1) {
            $pre = $cur_page - 1;
            $pag_container .= "<li p='$pre' pp='$per_page' class='active previous'>Previous</li>";
        } else if ($previous_btn) {
            $pag_container .= "<li class='inactive previous'>Previous</li>";
        }
        for ($i = $start_loop; $i <= $end_loop; $i++) {

            if ($cur_page == $i)
                $pag_container .= "<li p='$i' pp='$per_page' class = 'selected' >{$i}</li>";
            else
                $pag_container .= "<li p='$i' pp='$per_page' class='active'>{$i}</li>";
        }

        if ($next_btn && $cur_page < $no_of_paginations) {
            $nex = $cur_page + 1;
            $pag_container .= "<li p='$nex' pp='$per_page' class='active next'>Next</li>";
        } else if ($next_btn) {
            $pag_container .= "<li class='inactive next'>Next</li>";
        }

        if ($last_btn && $cur_page < $no_of_paginations) {
            $pag_container .= "<li p='$no_of_paginations' pp='$per_page' class='active'>Last</li>";
        } else if ($last_btn) {
            $pag_container .= "<li p='$no_of_paginations' pp='$per_page' class='inactive'>Last</li>";
        }

        $pag_container = $pag_container . "
            </ul>
        </div>";

        // We echo the final output
        echo 
        '<div class = "cvf-pagination-content">' . $msg . '</div>' . 
        '<div class = "cvf-pagination-nav">' . $pag_container . '</div>';
		
}

add_action( 'wp_footer' , 'custom__pagination_scripts' );
function custom__pagination_scripts(){	?>
<script type='text/javascript'> 
	jQuery(function($){
		function load_all_posts_pagination(page,per_page){
			$(".postsoutputs .cvf-pagination-nav").fadeIn().css('background','#ccc');
			var data = {
				page: page,
				per_page: per_page,
				action: "pagination_posts"
			};

			// Send the data
			$.post(General.ajaxurl, data, function(response) {
				// If successful Append the data into our html container
				$(".postsoutputs #response").html(response);
				// End the transition
				$(".postsoutputs .cvf-pagination-nav").css({'background':'none', 'transition':'all 1s ease-out'});
			});
		}
		$(document).on('click','.postsoutputs .cvf-universal-pagination .active',function(e) {
			var page = $(this).attr('p');
			var per_page = $(this).attr('pp');
			load_all_posts_pagination(page,per_page);
		});
		function load_all_casestudy_pagination(page,per_page){
			$(".casestudyoutputs .cvf-pagination-nav").fadeIn().css('background','#ccc');
			var data = {
				page: page,
				per_page: per_page,
				action: "pagination_casestudy"
			};

			// Send the data
			$.post(General.ajaxurl, data, function(response) {
				// If successful Append the data into our html container
				$(".casestudyoutputs #response").html(response);
				// End the transition
				$(".casestudyoutputs .cvf-pagination-nav").css({'background':'none', 'transition':'all 1s ease-out'});
			});
		}
		$(document).on('click','.casestudyoutputs .cvf-universal-pagination .active',function(e) {
			var page = $(this).attr('p');
			var per_page = $(this).attr('pp');
			load_all_casestudy_pagination(page,per_page);
		});
	});
</script>
<?php }
//custom post types and careers taxnomy and filter start
add_action('wp_ajax_pagination_posts', 'pagination_posts'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_pagination_posts', 'pagination_posts');
 
function pagination_posts(){
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
	$start = ( $page - 1 ) * $per_page;
	
	$args = array(
		'post_type' => 'post', 
		'post_status' => 'publish' ,
		'hierarchical' => true,
		'posts_per_page' => $per_page,
		'offset' => $start,
	);
	
	$query = new WP_Query( $args );
	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			include(PLUGIN_DIR .'/templates/show_post.php');
		endwhile;
		$count = $query->found_posts; 
		echo pagination_load_posts($page,$count,$per_page);
		wp_reset_postdata();
	else :
		echo 'No Post Found.';
	endif;
 
	die();
}
//custom post types and careers taxnomy and filter start
add_action('wp_ajax_pagination_casestudy', 'pagination_casestudy'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_pagination_casestudy', 'pagination_casestudy');
 
function pagination_casestudy(){
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
	$start = ( $page - 1 ) * $per_page;
	
	$args = array(
		'post_type' => 'case_studies', 
		'post_status' => 'publish' ,
		'hierarchical' => true,
		'posts_per_page' => $per_page,
		'offset' => $start,
	);
	
	$query = new WP_Query( $args );
	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			include(PLUGIN_DIR .'/templates/show_casestudies.php');
		endwhile;
		$count = $query->found_posts; 
		echo pagination_load_posts($page,$count,$per_page);
		wp_reset_postdata();
	else :
		echo 'No Casestudy Found.';
	endif;
 
	die();
}
