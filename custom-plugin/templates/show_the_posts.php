<?php 

/* if(isset($_GET['cat'])){
$category = $_GET['cat'];
} */
$_format = ! empty( $format ) ? $format : get_option( 'date_format' );
$the_date = get_post_time( $_format, false, $post, true );

$columns = 12/$columns;
		 if(!empty($category)){
            $taxquery = array(
                  'taxonomy' => 'category',
                  'field'    => 'slug',
                  'terms' => $category,
                  'operator' => 'IN'
                  );
			}
			else{
			$taxquery ="";
			} 
			$args = array(
			'post_type' => $post_type, 
			'post_status' => 'publish' ,
			'hierarchical' => true,
			'posts_per_page' => $limit,
			'offset' => $offset,
			'orderby' => $orderby,
			'relation'		=> 'IN',			
  			'tax_query' => array(
			$taxquery
			)  
			);
			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) {
 
echo '<div class="show-all-posts col span_12" >';
	$count = 1;
while ( $the_query->have_posts() ) {
$the_query->the_post();
	  
 
if($count == 1){
	  ?>
<div class="post-list-row list-first-row">	  
<div class="show-the-posts col span_4 post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
 
<?php }
if($count == 2){
	  ?>
<div class="col span_4">	  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
 
<?php }
if($count == 3){
	  ?>
  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
</div>

 
<?php }
if($count == 4){
	  ?>
<div class="col span_4">	  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
 
<?php }
if($count == 5){
	  ?>
  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
</div>
 </div>
<?php }
if($count == 6){
	  ?>
<div class="post-list-row list-second-row">	  
<div class="col span_4">	  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
 
<?php }
if($count == 7){
	  ?>
  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
</div>
 
<?php }
if($count == 8){
	  ?>
	  
<div class="show-the-posts col span_4 post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
 
<?php }
if($count == 9){
	  ?>
<div class="col span_4">	  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
 
<?php }
if($count == 10){
	  ?>
  
<div class="show-the-posts post-<?php echo get_the_ID(); ?> post type-post status-publish format-standard has-post-thumbnail">
<div class="post_inner">
	<div class="post_image">
	<a href="<?php echo get_permalink(); ?>">
	 <?php if ( has_post_thumbnail() ) : 
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src( $id, 'full' );
    $srcset = wp_get_attachment_image_srcset( $id, 'full' );
    $sizes = wp_get_attachment_image_sizes( $id, 'full' );
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
	 
	 //print_r($src);
	 ?>

    <img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>" alt="<?php echo esc_attr( $alt );?>">
 

<?php endif; 

$categories = get_the_category();
?>
	</a>
	</div>
	
	<div class="post_list_content">
	<div class="meta_data">
	<div class="publish_date"><?php echo $the_date; ?></div>
	<div class="category <?php echo $categories[0]->slug; ?>"><?php echo $categories[0]->name; ?></div>
	</div>
	<div class="post-header">
		<h3 class="title">
		<a href="<?php echo get_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 5);?></a>
		</h3>	
    </div>
	
    <div class="excerpt">
		<?php echo mb_strimwidth( get_the_excerpt(), 0, 62, '..' );?>
	</div>
	</div>	
	
</div>
</div>
</div>
</div>
 
<?php }
 


$count++; } }
else{
	echo "No Posts Found.";
} ?>	
<div class="show-the-posts-button col span_12" >
<div class="explore_more"><a href="#">EXPLORE MORE</a></div>
</div>
</div>