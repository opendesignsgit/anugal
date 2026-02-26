<?php
/**
 * All Posts Shortcode – dark-themed card grid
 *
 * Layout (matches allposts.jpg):
 *   Row 1 : 1 big card (left, spans 2 rows)  +  2 small cards (right column)
 *   Row 2 : 3 equal-width regular cards
 *
 * Usage:
 *   [allposts]
 *   [allposts blogs="3" product_tours="1" white_papers="1" help_center="1"]
 */

if (!defined('ABSPATH')) exit;
if (defined('ALLPOSTS_INC_LOADED')) return;
define('ALLPOSTS_INC_LOADED', true);

add_shortcode('allposts', 'render_allposts_shortcode');

function render_allposts_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'blogs'         => 2,
        'product_tours' => 1,
        'white_papers'  => 1,
        'help_center'   => 1,
    ), $atts, 'allposts');

    $cards = array();

    // --- Blogs (post) ---------------------------------------------------
    if ((int) $atts['blogs'] > 0) {
        $q = new WP_Query(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['blogs'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        while ($q->have_posts()) {
            $q->the_post();
            $cards[] = allposts_card_data('blog');
        }
        wp_reset_postdata();
    }

    // --- Product Tours ---------------------------------------------------
    if ((int) $atts['product_tours'] > 0) {
        $q = new WP_Query(array(
            'post_type'      => 'product_tour',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['product_tours'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        while ($q->have_posts()) {
            $q->the_post();
            $cards[] = allposts_card_data('product_tour');
        }
        wp_reset_postdata();
    }

    // --- White Papers -----------------------------------------------------
    if ((int) $atts['white_papers'] > 0) {
        $q = new WP_Query(array(
            'post_type'      => 'white_paper',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['white_papers'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        while ($q->have_posts()) {
            $q->the_post();
            $cards[] = allposts_card_data('white_paper');
        }
        wp_reset_postdata();
    }

    // --- Help Center ------------------------------------------------------
    if ((int) $atts['help_center'] > 0) {
        $q = new WP_Query(array(
            'post_type'      => 'help_center',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['help_center'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        while ($q->have_posts()) {
            $q->the_post();
            $cards[] = allposts_card_data('help_center');
        }
        wp_reset_postdata();
    }

    // Pad to 6 so the grid never breaks
    while (count($cards) < 6) {
        $cards[] = null;
    }

    ob_start();
    ?>
  

        <!-- Row 1: big + 2 small -->
        <div class="ap-row">
            <div class="ap-col-big">
                <?php allposts_render_card($cards[0], 'big'); ?>
            </div>
            <div class="ap-col-right">
                <div class="ap-row-top">
                    <?php allposts_render_card($cards[1], 'regular'); ?>
                    <?php allposts_render_card($cards[2], 'regular'); ?>
                </div>
                <!-- Row 2: 3 regular -->
                <div class="ap-row-bottom">
                    <?php allposts_render_card($cards[3], 'regular'); ?>
                    <?php allposts_render_card($cards[4], 'regular'); ?>
                    <?php allposts_render_card($cards[5], 'regular'); ?>
                </div>
            </div>
        </div>

    <?php
    return ob_get_clean();
}

/**
 * Build card data array from current post in the loop.
 */
function allposts_card_data($type = 'blog') {
    $thumb = '';
    if (has_post_thumbnail()) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
        if ($img) {
            $thumb = $img[0];
        }
    }

    return array(
        'title'   => get_the_title(),
        'excerpt' => wp_trim_words(get_the_excerpt(), 18, '...'),
        'link'    => get_permalink(),
        'image'   => $thumb,
        'type'    => $type,
    );
}

/**
 * Render a single card.
 */
function allposts_render_card($card, $size = 'regular') {
    if (empty($card)) {
        return;
    }
    $placeholder = 'https://via.placeholder.com/768x432?text=Post';
    $img         = !empty($card['image']) ? $card['image'] : $placeholder;
    ?>
    <article class="ap-card ap-card--<?php echo esc_attr($size); ?>">
        <a href="<?php echo esc_url($card['link']); ?>" class="ap-card__img">
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($card['title']); ?>">
        </a>
        <div class="ap-card__body">
            <h3 class="ap-card__title"><?php echo esc_html($card['title']); ?></h3>
            <p class="ap-card__excerpt"><?php echo esc_html($card['excerpt']); ?></p>
            <a href="<?php echo esc_url($card['link']); ?>" class="ap-card__cta">Read More</a>
        </div>
    </article>
    <?php
}
