<?php
$args = array(
    'post_type' => 'projects',
    'posts_per_page' => $atts['limit'], 
    'meta_key' => '_projects_order',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => '_projects_order',
            'compare' => 'EXISTS',
        ),
        array(
            'key' => '_projects_order',
            'compare' => '!=',
            'value' => '',
        ),
    ),
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
);
if (!empty($atts['status'])) {
    $args['tax_query'][] = array(
        'taxonomy' => 'project-status',
        'field' => 'slug',
        'terms' => $atts['status'],
    );
}
if (!empty($atts['type'])) {
    $args['tax_query'][] = array(
        'taxonomy' => 'project-type',
        'field' => 'slug',
        'terms' => $atts['type'],
    );
}
if (!empty($atts['location'])) {
    $args['tax_query'][] = array(
        'taxonomy' => 'project-location',
        'field' => 'slug',
        'terms' => $atts['location'],
    );
}


$projects_query = new WP_Query($args);
$el_id = uniqid();
?>

<div class="project-listing-outer">
    <div id="projlistboxes<?php echo $atts['layout']?>" class="projlistboxes projlistboxes<?php echo $atts['layout']?> <?php echo $atts['class'] ?> custom_slick_slider">

        <?php
// Check if there are any projects
if ($projects_query->have_posts()) {
    while ($projects_query->have_posts()) {
        $projects_query->the_post();
        $post_permalink = get_permalink(get_the_ID());
        // Get post data
        $post_id = get_the_ID();
        $post_title = get_the_title();
        $post_location = ''; // You should retrieve the location data from your taxonomy or custom fields
        $post_image = get_the_post_thumbnail_url($post_id, 'full');
        $post_content = get_the_content();

        // Example: Get terms from custom taxonomy 'project-location'
        $locationsterms = get_the_terms($post_id, 'project-location');
        if ($locationsterms && !is_wp_error($terms)) {
            $locations = array();
            foreach ($locationsterms as $term) {
                $locations[] = $term->name;
            }
            $locations = array_reverse($locations);
            $post_location = implode(', ', $locations);
        }
        $statusterms = get_the_terms( get_the_ID(), 'project-status' );
        $statusterm_slugs = array();
        if ( $statusterms && ! is_wp_error( $terms ) ) {
            foreach ( $statusterms as $term ) {
                $statusterm_slugs[] = $term->slug;
            }
        }
        $post_classes = implode( ' ', $statusterm_slugs );
        //$upcomingClass = in_array("upcoming", $statusterm_slugs) ? 'upcomingProjPop' : '';
        if(in_array("upcoming", $statusterm_slugs)){
            $upcomingClass = 'upcomingProjPop';
            $post_permalink = 'javaScript:void(0)';
        }else{
            $upcomingClass = '';
            $post_permalink = get_permalink(get_the_ID());
        }
        if($atts['layout'] == 'smallbox'){
            //$upcomingClass = ($atts['status'] == 'upcoming') ? 'upcomingProjPop' : '';
            //$post_permalink = ($atts['status'] == 'upcoming') ? get_permalink() : 'javaScript:void(0)';           
?>      
        <div class="">
            <div class="projlistbox">
                <div class="minpost_project" data-html="<?php the_field('upcoming_project_details')?>">
                    <div class="projimage left <?php echo $upcomingClass; ?>">
                        <span class="fusion-imageframe">
                            <a href="<?php echo $post_permalink; ?>" class="fusion-no-lightbox">
                                <img src="<?php echo $post_image; ?>" alt="<?php echo $post_title; ?>">
                            </a>
                        </span>
                    </div>
                    <div class="projcon right <?php echo $upcomingClass; ?>">
                        <h3><a href="<?php echo $post_permalink; ?>"><?php echo $post_title; ?></a></h3>
                        <span class="projlocation">
                            <?php echo $post_location; ?>
                        </span>
                        <?php the_field('project_details'); ?>
                            <a href="<?php echo $post_permalink; ?>"> -> </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } else{ ?>
        <div class="<?php //echo $upcomingClass; ?>">
            <div class="projlistbox">
                <div class="minpost_project" data-html="<?php the_field('upcoming_project_details')?>">
                    <div class="projtitle <?php echo $upcomingClass; ?>">
                        <h3><a href="<?php echo $post_permalink; ?>"><?php echo $post_title; ?></a></h3>
                        <span class="projlocation">
                            <?php echo $post_location; ?>
                        </span>
                    </div>
                    <div class="projimage <?php echo $upcomingClass; ?>">
                        <span class="fusion-imageframe">
                            <a href="<?php echo $post_permalink; ?>" class="fusion-no-lightbox">
                                <img src="<?php echo $post_image; ?>" alt="<?php echo $post_title; ?>">
                            </a>
                        </span>
                    </div>
                    <div class="projcon">
                        <?php the_field('project_details'); ?>
                        <?php if($upcomingClass){
                            echo '<ul class="projconBtns">';
                            echo '<li><a href="javaScript:void(0)" class="'.$upcomingClass.'">Know More</a></li>';
                            if(get_field('360_tour_link')){
                                echo '<li><a class="htourbtn homeTour HtourPrathyangiraPop" href="'.get_field('360_tour_link').'"><small>Home Tour</small><img decoding="async" src="'.site_url().'/wp-content/uploads/2024/02/play-icon.png" alt=""></a></li>';
                            }
                            echo '</ul>';
                        }else{
                            echo '<a href="'. $post_permalink .'">Experience the home</a>';
                        }?>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }}

    wp_reset_postdata(); // Reset post data
if($atts['view_more_link']){
?>
<a class="view_more_proj" href="<?php echo site_url() . $atts['view_more_link']?>">View More</a>
        <?php }} else {
    //echo 'No project found.';
} ?>

    </div>
</div>
<script>
var openPopupButtons = document.querySelectorAll(".upcomingProjPop");
openPopupButtons.forEach(function(button) {
    button.addEventListener("click", function(event) {
        var popupContent = document.querySelector(".upcomingProjContent");
        var parentElement = this.closest(".minpost_project");
        if (parentElement) {
            var htmlContent = parentElement.getAttribute("data-html");
            console.log(htmlContent);
            popupContent.innerHTML = htmlContent;
        }
    });
});
</script>