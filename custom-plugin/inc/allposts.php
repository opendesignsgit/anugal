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
        'blogs'         => 3,
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
    <style>
    /* ---- All Posts – dark card grid ---- */
    .ap-section{background:#0f1638;padding:48px 24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
    .ap-row-top{display:flex;gap:20px;margin-bottom:20px}
    .ap-col-big{flex:0 0 48%;min-width:0}
    .ap-col-right{flex:1;display:flex;flex-direction:column;gap:20px;min-width:0}
    .ap-row-bottom{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
    @media(max-width:1024px){
        .ap-row-top{flex-direction:column}
        .ap-col-big{flex:none}
        .ap-row-bottom{grid-template-columns:1fr 1fr}
    }
    @media(max-width:640px){
        .ap-row-bottom{grid-template-columns:1fr}
    }

    /* Card base */
    .ap-card{background:rgba(25,32,72,.85);border:1px solid rgba(100,120,255,.25);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .3s,transform .3s}
    .ap-card:hover{box-shadow:0 0 24px rgba(80,100,255,.35);transform:translateY(-3px)}

    /* Image */
    .ap-card__img{display:block;overflow:hidden;border-radius:12px;margin:10px 10px 0}
    .ap-card__img img{width:100%;height:100%;object-fit:cover;display:block;border-radius:12px}
    .ap-card--big .ap-card__img{height:400px}
    .ap-card--small .ap-card__img{height:160px}
    .ap-card--regular .ap-card__img{height:200px}

    /* Body */
    .ap-card__body{padding:16px 18px 20px;flex:1;display:flex;flex-direction:column}
    .ap-card__title{font-size:17px;font-weight:700;color:#fff;margin:0 0 8px;line-height:1.35}
    .ap-card--big .ap-card__title{font-size:20px}
    .ap-card__excerpt{font-size:14px;color:rgba(255,255,255,.7);margin:0 0 14px;line-height:1.55;flex:1}
    .ap-card__cta{font-size:13px;font-weight:600;color:#7b8cff;text-decoration:none;text-transform:uppercase;letter-spacing:.5px}
    .ap-card__cta:hover{text-decoration:underline;color:#a3b1ff}
    </style>

    <section class="ap-section">
        <!-- Row 1: big + 2 small -->
        <div class="ap-row-top">
            <div class="ap-col-big">
                <?php allposts_render_card($cards[0], 'big'); ?>
            </div>
            <div class="ap-col-right">
                <?php allposts_render_card($cards[1], 'small'); ?>
                <?php allposts_render_card($cards[2], 'small'); ?>
            </div>
        </div>

        <!-- Row 2: 3 regular -->
        <div class="ap-row-bottom">
            <?php allposts_render_card($cards[3], 'regular'); ?>
            <?php allposts_render_card($cards[4], 'regular'); ?>
            <?php allposts_render_card($cards[5], 'regular'); ?>
        </div>
    </section>
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
