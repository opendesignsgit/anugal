<?php
	if($post_id){
		$content_post = get_post($post_id);
		$content = $content_post->post_content;
		$content = apply_filters('the_content', $content);
		$html = str_replace(']]>', ']]&gt;', $content);
		echo $html;
	}else{
		echo 'Add Post Id';
	}
?> 