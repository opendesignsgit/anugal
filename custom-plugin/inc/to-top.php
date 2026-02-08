<?php

function enqueue_totop_assets($hook) {
	wp_enqueue_style('to_top',  CUSTOM_PLUGIN_DIR . 	'/assets/css/to-top.css');
}
add_action('wp_enqueue_scripts', 'enqueue_totop_assets');

add_action( 'wp_footer' , 'totop_custom__script', 1000 );
function totop_custom__script(){ 
?>
<div class="progress-wrap">
	<svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
		<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>
	</svg>
</div>
<script type='text/javascript'>
	(function($) { "use strict";
		$(document).ready(function(){
			"use strict";
			var progressPath = document.querySelector('.progress-wrap path');
			var pathLength = progressPath.getTotalLength();
			progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
			progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
			progressPath.style.strokeDashoffset = pathLength;
			progressPath.getBoundingClientRect();
			progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';		
			var updateProgress = function () {
				var scroll = $(window).scrollTop();
				var height = $(document).height() - $(window).height();
				var progress = pathLength - (scroll * pathLength / height);
				progressPath.style.strokeDashoffset = progress;
			}
			updateProgress();
			$(window).scroll(updateProgress);	
			var offset = 50;
			var duration = 550;
			jQuery(window).on('scroll', function() {
				if (jQuery(this).scrollTop() > offset) {
					jQuery('.progress-wrap').addClass('active-progress');
				} else {
					jQuery('.progress-wrap').removeClass('active-progress');
				}
			});				
			jQuery('.progress-wrap').on('click', function(event) {
				event.preventDefault();
				jQuery('html, body').animate({scrollTop: 0}, duration);
				return false;
			})
		});
	})(jQuery);
</script>
<?php }

add_shortcode('add_to_top_arrow', 'add_to_top_arrow');
function add_to_top_arrow($atts) { ?>
	
<?php }