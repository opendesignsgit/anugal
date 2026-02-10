<?php
/**
 * Related Posts Shortcode
 * 
 * Universal shortcode for displaying related posts with consistent styling.
 * Works with multiple post types: post, white_paper, product_tour, news_room
 * 
 * Usage: [related_posts type="post" count="3" title="Related Posts"]
 */

if (!defined('ABSPATH')) exit;
if (defined('RELATED_POSTS_LOADED')) return;
define('RELATED_POSTS_LOADED', true);

class Related_Posts_Shortcode {

    /**
     * Supported post types configuration
     */
    private $post_types = array(
        'post' => array(
            'label'         => 'Blogs',
            'taxonomy'      => 'category',
            'placeholder'   => 'https://via.placeholder.com/768x432?text=Blog',
            'cta_text'      => 'Read More',
            'show_date'     => true,
            'show_author'   => true,
            'show_excerpt'  => false,
        ),
        'white_paper' => array(
            'label'         => 'White Papers',
            'taxonomy'      => 'whitepaper_category',
            'placeholder'   => 'https://via.placeholder.com/768x432?text=Resource',
            'cta_text'      => 'Read More',
            'show_date'     => false,
            'show_author'   => false,
            'show_excerpt'  => true,
        ),
        'product_tour' => array(
            'label'         => 'Product Tours',
            'taxonomy'      => 'product_tour_category',
            'placeholder'   => 'https://via.placeholder.com/768x432?text=Product+Tour',
            'cta_text'      => 'Read More',
            'show_date'     => false,
            'show_author'   => false,
            'show_excerpt'  => true,
        ),
        'news_room' => array(
            'label'         => 'News',
            'taxonomy'      => 'news_category',
            'placeholder'   => 'https://via.placeholder.com/768x432?text=News',
            'cta_text'      => 'Read More',
            'show_date'     => false,
            'show_author'   => false,
            'show_excerpt'  => true,
        ),
    );

    public function __construct() {
        add_shortcode('related_posts', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue styles
     */
    public function enqueue_assets() {
        wp_register_style('related-posts-style', false, array(), '1.0.0');
        wp_enqueue_style('related-posts-style');
        wp_add_inline_style('related-posts-style', $this->get_inline_css());
    }

    /**
     * Render related posts shortcode
     */
    public function render_shortcode($atts = array()) {
        $atts = shortcode_atts(array(
            'type'           => 'post',           // post, white_paper, product_tour, news_room
            'count'          => 3,                // Number of posts to show
            'title'          => '',               // Title above the posts (empty = no title)
            'columns'        => 3,                // Number of columns (1, 2, 3, 4)
            'excerpt_length' => 100,              // Excerpt character limit
            'orderby'        => 'date',           // date, title, rand, menu_order
            'order'          => 'DESC',           // ASC, DESC
            'category'       => '',               // Filter by specific category slug/id
            'same_category'  => 'true',           // Show posts from same category as current
        ), $atts, 'related_posts');

        $post_type = sanitize_key($atts['type']);
        
        // Validate post type
        if (!isset($this->post_types[$post_type])) {
            $post_type = 'post';
        }

        $config = $this->post_types[$post_type];
        $current_post_id = get_the_ID();
        $count = max(1, min(12, (int) $atts['count']));
        $columns = max(1, min(4, (int) $atts['columns']));
        $excerpt_length = max(1, (int) $atts['excerpt_length']);

        // Build query args
        $args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $count,
            'post__not_in'   => array($current_post_id),
            'orderby'        => sanitize_key($atts['orderby']),
            'order'          => strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC',
        );

        // Filter by same category if enabled
        if ($atts['same_category'] === 'true' && !empty($config['taxonomy'])) {
            $current_terms = wp_get_post_terms($current_post_id, $config['taxonomy'], array('fields' => 'ids'));
            if (!empty($current_terms) && !is_wp_error($current_terms)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => $config['taxonomy'],
                        'field'    => 'term_id',
                        'terms'    => $current_terms,
                    ),
                );
            }
        }

        // Filter by specific category if provided
        if (!empty($atts['category']) && !empty($config['taxonomy'])) {
            $category = sanitize_text_field($atts['category']);
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $config['taxonomy'],
                    'field'    => is_numeric($category) ? 'term_id' : 'slug',
                    'terms'    => $category,
                ),
            );
        }

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return '';
        }

        // Build title if not provided
        $title = $atts['title'];
        if (empty($title)) {
            $title = 'Related ' . $config['label'];
        }

        ob_start();
        ?>
        <div class="rp-wrap" data-rp-columns="<?php echo esc_attr($columns); ?>">
            <?php if (!empty($title)): ?>
                <h3 class="rp-title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>
            
            <div class="rp-grid rp-grid--cols-<?php echo esc_attr($columns); ?>">
                <?php while ($query->have_posts()): $query->the_post(); ?>
                    <?php echo $this->render_card(get_post(), $config, $excerpt_length); ?>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render individual card
     */
    private function render_card($post, $config, $excerpt_length = 100) {
        $title = get_the_title($post);
        $link = get_permalink($post);
        $thumb_id = get_post_thumbnail_id($post);
        $featured = '';

        if ($thumb_id) {
            $img = wp_get_attachment_image_src($thumb_id, 'large');
            if ($img && is_array($img)) {
                $featured = $img[0];
            }
        }

        if (empty($featured)) {
            $featured = $config['placeholder'];
        }

        ob_start();
        ?>
        <article class="rp-card">
            <a href="<?php echo esc_url($link); ?>" class="rp-card__image">
                <img src="<?php echo esc_url($featured); ?>" alt="<?php echo esc_attr($title); ?>">
            </a>
            <div class="rp-card__body">
                <h4 class="rp-card__title">
                    <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
                </h4>
                
                <?php if ($config['show_date'] || $config['show_author']): ?>
                    <div class="rp-card__meta">
                        <?php if ($config['show_date']): ?>
                            <span class="rp-card__date"><?php echo esc_html(get_the_date('M j, Y', $post)); ?></span>
                        <?php endif; ?>
                        <?php if ($config['show_author']): ?>
                            <span class="rp-card__author"><?php echo esc_html(get_the_author_meta('display_name', $post->post_author)); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($config['show_excerpt']): 
                    $excerpt = get_the_excerpt($post);
                    if (!empty($excerpt) && mb_strlen($excerpt) > $excerpt_length) {
                        $excerpt = mb_substr($excerpt, 0, $excerpt_length);
                        $last_space = mb_strrpos($excerpt, ' ');
                        if ($last_space !== false && $last_space > mb_strlen($excerpt) * 0.8) {
                            $excerpt = mb_substr($excerpt, 0, $last_space);
                        }
                        $excerpt = rtrim($excerpt, '.,!? ') . '...';
                    }
                    ?>
                    <p class="rp-card__excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                
                <a href="<?php echo esc_url($link); ?>" class="rp-card__cta"><?php echo esc_html($config['cta_text']); ?></a>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }

    /**
     * Inline CSS styles
     */
    private function get_inline_css() {
        return <<<CSS
/* Related Posts - Container */
.rp-wrap{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;margin:40px 0}
.rp-title{font-size:24px;font-weight:700;color:#111;margin:0 0 24px;text-transform:none}

/* Related Posts - Grid */
.rp-grid{display:grid;gap:24px}
.rp-grid--cols-1{grid-template-columns:1fr}
.rp-grid--cols-2{grid-template-columns:repeat(2,1fr)}
.rp-grid--cols-3{grid-template-columns:repeat(3,1fr)}
.rp-grid--cols-4{grid-template-columns:repeat(4,1fr)}
@media (max-width:1024px){
    .rp-grid--cols-3,.rp-grid--cols-4{grid-template-columns:repeat(2,1fr)}
}
@media (max-width:640px){
    .rp-grid--cols-2,.rp-grid--cols-3,.rp-grid--cols-4{grid-template-columns:1fr}
}

/* Related Posts - Card */
.rp-card{background:#fff;border-radius:16px;border:1px solid #EEE;box-shadow:0 4px 12px rgba(0,0,0,.04);overflow:hidden;transition:box-shadow .2s,transform .2s}
.rp-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.rp-card__image{display:block;height:180px;background:#f0f0f0;overflow:hidden}
.rp-card__image img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.rp-card:hover .rp-card__image img{transform:scale(1.05)}
.rp-card__body{padding:20px}
.rp-card__title{font-size:16px;font-weight:700;margin:0 0 8px;color:#111;line-height:1.4}
.rp-card__title a{color:inherit;text-decoration:none}
.rp-card__title a:hover{color:#3E54E8}
.rp-card__meta{display:flex;gap:12px;font-size:12px;color:#666;margin:0 0 10px}
.rp-card__excerpt{font-size:14px;line-height:1.6;color:#555;margin:0 0 16px;min-height:44px}
.rp-card__cta{font-weight:600;font-size:13px;color:#3E54E8;text-decoration:none;text-transform:uppercase;letter-spacing:.5px}
.rp-card__cta:hover{text-decoration:underline}
CSS;
    }
}

new Related_Posts_Shortcode();
