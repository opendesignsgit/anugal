<?php
// //print_r($current_term);
// $term = get_term_by('slug', $current_term->slug, $current_term->taxonomy);
// //print_r($term);
// if ($current_term && !empty($current_term->parent)) {
//     $parent_term = get_term($current_term->parent, $current_term->taxonomy);
//     //print_r($parent_term);
//     if (!is_wp_error($parent_term)) {
//         if ($parent_term && !empty($parent_term->parent)) {
//             $super_parent_term = get_term($parent_term->parent, $parent_term->taxonomy);
//             $parent_category_name = $super_parent_term->name;
//             $parent_category_id = $super_parent_term->term_id;
//         }else{
//             $parent_category_name = $parent_term->name;
//             $parent_category_id = $parent_term->term_id;
//         }
//     }
// }else{
//     $parent_category_name = $current_term->name;
//     $parent_category_id = $current_term->term_id;
// }
// $current_term_parents = count( get_ancestors( $current_term->term_id, 'proj-status-location' ) );

// if($current_term_parents == 2){
//     $parent_selection = $current_term->parent;
// }else{
//     $parent_selection = $current_term->term_id;
// }
// $child_terms = get_term_children($parent_selection, 'proj-status-location');
// $super_parent_id = $parent_category_id;

// print_r($current_term);
// $parents = count( get_ancestors( $term->term_id, 'proj-status-location' ) );


// $child_terms_by_id = get_terms(array(
//     'taxonomy' => 'proj-status-location', 
//     'parent' => $super_parent_id,
// ));




$current_term = get_queried_object();

$currentURL = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($currentURL, '/'));
$lastPart = end($parts);
$proj_typelocsta = explode('-', $lastPart);

$projecttype = get_term_by('slug', $proj_typelocsta[0], 'project-type');
$projectlocation = get_term_by('slug', $proj_typelocsta[1], 'project-location');
$projectstatus = get_term_by('slug', $proj_typelocsta[2], 'project-status');

//print_r($projectstatus);

if($projectlocation->parent){
    $projectlocationParent_all = get_term_by('id', $projectlocation->parent, 'project-location');
    $projectlocationParent = $projectlocationParent_all->term_id;
    $projectlocationParent_name = $projectlocationParent_all->name;
    $projectlocationParent_slug = $projectlocationParent_all->slug;
}else{
    $projectlocationParent = $projectlocation->term_id;
    $projectlocationParent_name = $projectlocation->name;
    $projectlocationParent_slug = $projectlocation->slug;
}
//print_r($projectstatus->slug);

$argsForQueryTypeLocationCurrent = array(
    'post_type' => 'projects',
    'tax_query' => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'project-type',
            'field' => 'slug',
            'terms' => $projecttype->slug,
        ),
        array(
            'taxonomy' => 'project-location',
            'field' => 'id',
            'terms' => $projectlocation->term_id ,
        ),
    ),
);
$QueryTypeLocationCurrent = new WP_Query( $argsForQueryTypeLocationCurrent );
$status_ids = array();
if ( $QueryTypeLocationCurrent->have_posts() ) {
    while ( $QueryTypeLocationCurrent->have_posts() ) {
        $QueryTypeLocationCurrent->the_post();
       
        $projectstatuses = get_the_terms(get_the_ID(), 'project-status');
        
        if ( $projectstatuses && ! is_wp_error( $projectstatuses ) ) {
            foreach ( $projectstatuses as $projectsta ) {
                    $status_ids[] = $projectsta->term_id;
            }
        }
    }
    wp_reset_postdata(); 
}
$status_ids = array_values(array_unique($status_ids));
//print_r($status_ids);
// print_r($projectlocationParent);
// print_r($projecttype->slug);
// print_r($projectstatus->slug);
$argsForQueryAllCurrent = array(
    'post_type' => 'projects',
    'tax_query' => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'project-type',
            'field' => 'slug',
            'terms' => $projecttype->slug,
        ),
        array(
            'taxonomy' => 'project-location',
            'field' => 'id',
            'terms' => $projectlocationParent ,
        ),
        array(
            'taxonomy' => 'project-status',
            'field' => 'slug',
            'terms' => $projectstatus->slug ,
        ),
    ),
);
$QueryAllCurrent = new WP_Query( $argsForQueryAllCurrent );
$location_ids = array();
if ( $QueryAllCurrent->have_posts() ) {
    while ( $QueryAllCurrent->have_posts() ) {
        $QueryAllCurrent->the_post();
        $projectlocations = get_the_terms(get_the_ID(), 'project-location');
        if ( $projectlocations && ! is_wp_error( $projectlocations ) ) {
            foreach ( $projectlocations as $projectloc ) {
                if ( $projectloc->parent != 0 ) {
                    $location_ids[] = $projectloc->term_id;
                }
            }
        }
    }
    wp_reset_postdata(); 
}
$location_ids = array_values(array_unique($location_ids));
//print_r($location_ids);
$Locationdropargs = array(
    'taxonomy' => 'project-location',
    'parent' => $projectlocationParent,
    'include' => $location_ids,
);
$Locationdrops = get_terms($Locationdropargs);

$projectStatuses = get_terms(array(
    'taxonomy' => 'project-status',
    'orderby' => 'term_id',
    'order' => 'ASC',
    'include' => $status_ids
));


?>
<div class="project-listing-outer">
    <div class="fusion-column-wrapper fusion-column-has-shadow fusion-flex-justify-content-flex-start fusion-content-layout-column">
        <div class="fusion-text fusion-text-15 ComTitlesTB textcenter marbtm">
            <h2 data-fontsize="58" style="--fontSize: 58; line-height: 1;" data-lineheight="58px"
                class="fusion-responsive-typography-calculated"> <?php echo $projecttype->name; ?> in <?php echo $projectlocation->name ?> </h2>
            <p><?php echo $current_term->description; ?></p>
        </div>
        <div class="fusion-text fusion-text-16 ProtypelistTB">
            <!-- <ul class="Protypeul">
                <li class="active"><a href="#">Ongoing</a></li>
                <li class=""><a href="#">Upcoming</a></li>
                <li class=""><a href="#">Completed</a></li>
            </ul> -->
            <ul class="Protypeul">
            <?php 
                foreach ($projectStatuses as $status) {
                if($status->name == 'Upcoming' && $projectlocation->slug != 'coimbatore'){}elseif($status->name == 'Completed'){}else{ ?>
                <li class="<?php echo $status->name; ?> <?php echo  $projectlocation->slug; ?> <?php if(strstr($current_term->slug, $status->slug)){echo 'active';}?>"><a href="<?php echo site_url() . '/projects/'. $projecttype->slug . '-' . $projectlocation->slug . '-' . $status->slug ?>"><?php echo $status->name; ?></a></li>
            <?php } } ?>
            </ul>
        </div>
    </div>
    <div class="location_filter">
        <div class="filterselect">
            <select name="location" id="location_change">
                <option value="<?php echo site_url() . '/projects/' ?>">Search All Location</option>
                <?php  if (!empty($Locationdrops)) {
                foreach ($Locationdrops as $child_term) {?>
                    <option value="<?php echo site_url() . '/projects/' . $proj_typelocsta[0] . '-' . $child_term->slug . '-' . $proj_typelocsta[2] ?>" <?php if($child_term->slug==$proj_typelocsta[1]){echo 'selected';}?>><?php echo $child_term->name ?></option>
            <?php } } ?>
            </select>
        </div>
    </div>
    <div id="projlistboxes" class="projlistboxes">
        <div class="projlistbox">
            <?php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
            if(get_field('link_to_detail_page') == true){
                $permalink = get_permalink();
            }else{
                $permalink = 'javascript:void(0)';
            }

            $terms = get_the_terms( get_the_ID(), 'project-status' );
            $term_slugs = array();
            if ( $terms && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    $term_slugs[] = $term->slug;
                }
            }
            $post_classes = implode( ' ', $term_slugs );
            //print_r($term_slugs);
            $upcomingClass = in_array("upcoming", $term_slugs) ? 'upcomingProjPop' : '';

            //$upcomingHtml = $projectstatus->slug = 'upcoming' ? the_field('project_details') : '';
    ?>
            <div class="minpost_project <?php echo $post_classes;?>" data-html="<?php the_field('upcoming_project_details')?>">
                <div class="projtitle <?php echo $upcomingClass; ?>">
                    <h3><a href="<?php echo $permalink;?>"><?php echo the_title();?></a></h3>
                    <span class="projlocation">
                       <?php
                            $terms = get_the_terms(get_the_ID(), 'project-location');
                            if ($terms && !is_wp_error($terms)) {
                                $locations = array();
                                foreach ($terms as $term) {
                                    $locations[] = $term->name;
                                }
                                $locations = array_reverse($locations);
                                echo $post_location = implode(', ', $locations);
                            }
                        ?>
                    </span>
                </div>
                <?php
                                if ( has_post_thumbnail() ) : 
                                        $id = get_post_thumbnail_id();
                                        $src = wp_get_attachment_image_src( $id, 'full' );
                                        $srcset = wp_get_attachment_image_srcset( $id, 'full' );
                                        $sizes = wp_get_attachment_image_sizes( $id, 'full' );
                                        $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);	 
                                        ?>
                <div class="projimage <?php echo $upcomingClass; ?>">
                    <span class="fusion-imageframe">
                        <a href="<?php echo $permalink;?>" class="fusion-no-lightbox"><img src="<?php echo $src[0]; ?>"
                                width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>"
                                srcset="<?php echo esc_attr( $srcset ); ?>" sizes="<?php echo esc_attr( $sizes );?>"
                                alt="<?php echo esc_attr( $alt );?>"></a>
                    </span>
                </div>
                <?php endif; ?>
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
                        echo '<a href="'. $permalink .'">Experience the home</a>';
                    }?>
                    
                </div>
            </div>
            <?php
    endwhile;
else :
    echo 'No Project Found';
endif;
?>
        </div>
    </div>
    <?php 
    $projecttypes = get_terms(array(
        'taxonomy' => 'project-type',
        'orderby' => 'term_id',
        'order' => 'ASC'
    ));
    if($projectlocationParent_slug == 'coimbatore'){
        $SingleClass = ' PNSingle';
    }else{
        $SingleClass = '';
    }
    ?>
    <div class="prev-next<?php echo $SingleClass; ?>">
        <?php 
        $i = 1;
        foreach ($projecttypes as $index => $type) {
            $argsForQueryAllNext = array(
                'post_type' => 'projects',
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'project-type',
                        'field' => 'slug',
                        'terms' => $type->slug,
                    ),
                    array(
                        'taxonomy' => 'project-location',
                        'field' => 'id',
                        'terms' => $projectlocationParent ,
                    ),
                    array(
                        'taxonomy' => 'project-status',
                        'field' => 'slug',
                        'terms' => $projectstatus->slug ,
                    ),
                ),
            );
            $QueryAllNext = new WP_Query( $argsForQueryAllNext );
            if ( ! $QueryAllNext->have_posts() ) {
                continue;
            }
            $class = ($i % 2 == 0) ? 'prev' : 'next';
            $Oneclass = ($i % 2 == 0) ? 'secondOne' : 'firstOne';
            if ($type->slug == $proj_typelocsta[0]) {
                continue;
            }
            if($projectlocationParent_slug == 'coimbatore' && $type->slug == 'plots'){
                continue;
            }
            ?>
            <a class="<?php echo $Oneclass; ?>" href="<?php echo site_url() . '/projects/'. $type->slug . '-' . $projectlocationParent_slug . '-' . $proj_typelocsta[2] ?>"><div class="<?php echo $class; ?>"><?php echo $type->name .' in '. $projectlocationParent_name; ?></div></a>
        <?php $i++; } ?>
    </div>
    <?php 
    $projectlocationsnext = get_terms(array(
        'taxonomy' => 'project-location',
        'orderby' => 'term_id',
        'parent' => 0, 
        'order' => 'ASC',
    ));
    echo '<div class="next-location">';
    foreach ($projectlocationsnext as $projectlocationnext) {
        if($projectlocationnext->term_id != $projectlocationParent){
            if($projectlocationnext->slug == 'coimbatore'){
                if($proj_typelocsta[0] == 'plots'){
                    $coimbatoreType = 'apartments';
                }else{
                    $coimbatoreType = $proj_typelocsta[0];
                }
                //echo '<a href="'.site_url().'/projects/apartments-coimbatore-upcoming">Projects in '. $projectlocationnext->name .'</a>';
                echo '<a href="'.site_url().'/projects/'.$coimbatoreType.'-'.$projectlocationnext->slug.'-upcoming">Projects in '. $projectlocationnext->name .'</a>';
            }else{
                echo '<a href="'.site_url().'/projects/'.$proj_typelocsta[0].'-'.$projectlocationnext->slug.'-ongoing'.'">Projects in '. $projectlocationnext->name .'</a>';
            }
            //echo '<a href="'.site_url().'/projects/'.$proj_typelocsta[0].'-'.$projectlocationnext->slug.'-'.$proj_typelocsta[2].'">Projects in '. $projectlocationnext->name .'</a>';
        }
    }   
    echo '</div>';
    ?>
</div>
<script>
var selectElement = document.getElementById("location_change");
selectElement.onchange = function() {
    var selectedValue = selectElement.value;
    if (selectedValue) {
        window.location.href = selectedValue;
    }
};
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