<?php
if (!empty($accordiongroup)) {
    $taxquery = array(
        'taxonomy' => 'accordion-group',
        'field'    => 'slug',
        'terms' => $accordiongroup,
        'operator' => 'IN'
    );
} else {
    $taxquery = "";
}
$args = array(
    'post_type' => 'popup',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'hierarchical' => true,
    'meta_query' => array('relation' => 'OR',        array('key' => 'add_in_footer',            'value' => '1',            'compare' => '=',),        array('key' => 'popup_trigger',            'value' => 'class',            'compare' => '=',),        array('key' => 'popup_trigger',            'value' => 'onload',            'compare' => '=',),),
);
$the_query = new WP_Query($args);
if ($the_query->have_posts()) {
    while ($the_query->have_posts()) {
        $unique_id = uniqid();
        $the_query->the_post();
        if (get_field('show_in_page')) {
            if (is_single(get_field('show_in_page')) || is_page(get_field('show_in_page'))) { ?>
                <div class="custom-model-main_custom_popup <?php echo get_field('popup_class'); ?> <?php echo $unique_id; ?>">
                    <div class="custom-model-inner_custom_popup">
                        <div class="close-btn_custom_popup">×</div>
                        <div class="custom-model-wrap_custom_popup">
                            <div id="pop_content" class="pop-up-content-wrap_custom_popup">
                                <?php echo the_content(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="bg-overlay_custom_popup"></div>
                </div>
                <script>
                    jQuery(function($) {
                        var popup_trigger = "<?php echo get_field('popup_trigger') ?>";
                        if (popup_trigger && popup_trigger == "onload") {
                            var popup_delay = "<?php echo get_field('delay') ?>";
                            setTimeout(function() {
                                jQuery(".custom-model-main_custom_popup.<?php echo $unique_id; ?>").addClass(
                                    'model-open_custom_popup');
                            }, popup_delay * 1000);
                            console.log(popup_delay * 1000);
                        }
                        var trigger_class = "<?php echo get_field('trigger_class') ?>";
                        var add_trigger_class = "";
                        if (trigger_class) {
                            var arr = trigger_class.split(' ');
                            $.each(arr, function(i, obj) {
                                add_trigger_class += ',.' + arr[i];
                            });
                        } else {
                            add_trigger_class = ",.nill";
                        }
                        $('body').on('click', '.open_popup_<?php echo get_the_ID() ?>' + add_trigger_class, function(e) {
                            e.preventDefault();
                            jQuery("body").addClass('popupOpened');
                            jQuery(".custom-model-main_custom_popup.<?php echo $unique_id; ?>").addClass(
                                'model-open_custom_popup');
                        });
                    });
                </script>
            <?php }
        } elseif (get_field('popup_trigger') == 'disclaimer') { ?>
            <div class="custom-model-main_custom_popup <?php echo get_field('popup_class') ?> <?php echo $unique_id; ?> disclaimer">
                <div class="custom-model-inner_custom_popup">
                    <div class="custom-model-wrap_custom_popup">
                        <div id="pop_content" class="pop-up-content-wrap_custom_popup">
                            <?php echo the_content(); ?>
                        </div>
                    </div>
                </div>
                <div class="bg-overlay_custom_popupnoClose"></div>
            </div>
        <?php } else { ?>
            <div class="custom-model-main_custom_popup <?php echo get_field('popup_class') ?> <?php echo $unique_id; ?>">
                <div class="custom-model-inner_custom_popup">
                    <div class="close-btn_custom_popup">×</div>
                    <div class="custom-model-wrap_custom_popup">
                        <div id="pop_content" class="pop-up-content-wrap_custom_popup">
                            <?php echo the_content(); ?>
                        </div>
                    </div>
                </div>
                <div class="bg-overlay_custom_popup"></div>
            </div>
            <script>
                jQuery(function($) {
                    var trigger_class = "<?php echo get_field('trigger_class') ?>";
                    var add_trigger_class = "";
                    if (trigger_class) {
                        var arr = trigger_class.split(' ');
                        $.each(arr, function(i, obj) {
                            add_trigger_class += ',.' + arr[i];
                        });
                    } else {
                        add_trigger_class = ",.nill";
                    }
                    $('body').on('click', '.open_popup_<?php echo get_the_ID() ?>' + add_trigger_class, function(e) {
                        e.preventDefault();
                        jQuery("body").addClass('popupOpened');
                        jQuery(".custom-model-main_custom_popup.<?php echo $unique_id; ?>").addClass(
                            'model-open_custom_popup');
                    });
                });
            </script>
<?php }
    }
}
wp_reset_postdata(); ?>