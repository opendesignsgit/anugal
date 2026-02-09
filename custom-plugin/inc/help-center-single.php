<?php
/**
 * Help Center Single Page Template Shortcodes
 * 
 * Shortcodes for Help Center CPT single page template:
 * - [help_center_sidebar] - Left sidebar with category tabs and post lists
 * - [help_center_updated_date] - Display "Updated X days ago" date
 * - [help_center_prev_next] - Previous/Next navigation buttons
 * - [help_center_toc] - Right sidebar Table of Contents
 */

if (!defined('ABSPATH')) exit;
if (defined('HELP_CENTER_SINGLE_LOADED')) return;
define('HELP_CENTER_SINGLE_LOADED', true);

class Help_Center_Single_Shortcodes {

    public function __construct() {
        add_shortcode('help_center_sidebar', array($this, 'render_sidebar'));
        add_shortcode('help_center_updated_date', array($this, 'render_updated_date'));
        add_shortcode('help_center_prev_next', array($this, 'render_prev_next'));
        add_shortcode('help_center_toc', array($this, 'render_toc'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue styles and scripts
     */
    public function enqueue_assets() {
        if (!is_singular('help_center')) return;

        wp_register_style('help-center-single-style', false, array(), '1.0.0');
        wp_enqueue_style('help-center-single-style');
        wp_add_inline_style('help-center-single-style', $this->get_inline_css());

        wp_register_script('help-center-single-script', false, array(), '1.0.0', true);
        wp_enqueue_script('help-center-single-script');
        wp_add_inline_script('help-center-single-script', $this->get_inline_js());
    }

    /**
     * Render sidebar with category tabs and post lists
     */
    public function render_sidebar($atts = array()) {
        $atts = shortcode_atts(array(
            'title' => 'GUIDES',
        ), $atts, 'help_center_sidebar');

        $current_post_id = get_the_ID();
        
        // Get all help_category terms ordered by menu order or name
        $categories = get_terms(array(
            'taxonomy'   => 'help_category',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        if (empty($categories) || is_wp_error($categories)) {
            return '<div class="hcs-sidebar"><p>No categories found.</p></div>';
        }

        // Get current post's categories
        $current_categories = wp_get_post_terms($current_post_id, 'help_category', array('fields' => 'ids'));

        ob_start();
        ?>
        <div class="hcs-sidebar">
            <?php if (!empty($atts['title'])): ?>
                <h3 class="hcs-sidebar__title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="hcs-accordion">
                <?php foreach ($categories as $category): 
                    // Check if this category is active (current post belongs to it)
                    $is_active = in_array($category->term_id, $current_categories);
                    
                    // Get posts in this category, excluding current post
                    $posts = get_posts(array(
                        'post_type'      => 'help_center',
                        'posts_per_page' => -1,
                        'post__not_in'   => array($current_post_id),
                        'orderby'        => 'menu_order title',
                        'order'          => 'ASC',
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'help_category',
                                'field'    => 'term_id',
                                'terms'    => $category->term_id,
                            ),
                        ),
                    ));
                    ?>
                    <div class="hcs-accordion__item<?php echo $is_active ? ' hcs-accordion__item--active' : ''; ?>">
                        <button class="hcs-accordion__header" type="button" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">
                            <span class="hcs-accordion__header-text"><?php echo esc_html($category->name); ?></span>
                            <span class="hcs-accordion__arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <div class="hcs-accordion__content"<?php echo $is_active ? '' : ' style="display:none;"'; ?>>
                            <?php if (!empty($posts)): ?>
                                <ul class="hcs-post-list">
                                    <?php foreach ($posts as $post): ?>
                                        <li class="hcs-post-list__item">
                                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="hcs-post-list__link">
                                                <?php echo esc_html(get_the_title($post->ID)); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="hcs-post-list__empty">No other articles in this category.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render "Updated X days ago" date
     */
    public function render_updated_date($atts = array()) {
        $atts = shortcode_atts(array(
            'icon' => 'true',
        ), $atts, 'help_center_updated_date');

        $post_id = get_the_ID();
        $modified_date = get_the_modified_date('U', $post_id);
        $current_time = current_time('timestamp');
        
        $diff = $current_time - $modified_date;
        $days = floor($diff / (60 * 60 * 24));
        
        if ($days === 0) {
            $time_text = 'Updated today';
        } elseif ($days === 1) {
            $time_text = 'Updated 1 day ago';
        } elseif ($days < 30) {
            $time_text = sprintf('Updated %d days ago', $days);
        } elseif ($days < 60) {
            $time_text = 'Updated 1 month ago';
        } elseif ($days < 365) {
            $months = floor($days / 30);
            $time_text = sprintf('Updated %d months ago', $months);
        } else {
            $years = floor($days / 365);
            $time_text = $years === 1 ? 'Updated 1 year ago' : sprintf('Updated %d years ago', $years);
        }

        $show_icon = $atts['icon'] === 'true';
        
        ob_start();
        ?>
        <div class="hcs-updated-date">
            <?php if ($show_icon): ?>
                <span class="hcs-updated-date__icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </span>
            <?php endif; ?>
            <span class="hcs-updated-date__text"><?php echo esc_html($time_text); ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Previous/Next navigation buttons
     */
    public function render_prev_next($atts = array()) {
        $atts = shortcode_atts(array(
            'prev_text' => 'Previous',
            'next_text' => 'Next',
        ), $atts, 'help_center_prev_next');

        $current_post_id = get_the_ID();
        
        // Get current post's primary category
        $categories = wp_get_post_terms($current_post_id, 'help_category', array('fields' => 'ids'));
        
        $prev_post = null;
        $next_post = null;

        if (!empty($categories)) {
            // Get all posts in the same category
            $posts = get_posts(array(
                'post_type'      => 'help_center',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'help_category',
                        'field'    => 'term_id',
                        'terms'    => $categories[0],
                    ),
                ),
            ));

            $current_index = -1;
            foreach ($posts as $index => $post) {
                if ($post->ID === $current_post_id) {
                    $current_index = $index;
                    break;
                }
            }

            if ($current_index > 0) {
                $prev_post = $posts[$current_index - 1];
            }
            if ($current_index !== -1 && $current_index < count($posts) - 1) {
                $next_post = $posts[$current_index + 1];
            }
        }

        // Fallback to generic adjacent posts if no category-based posts found
        if (!$prev_post) {
            $prev_post = get_adjacent_post(false, '', true, 'help_category');
        }
        if (!$next_post) {
            $next_post = get_adjacent_post(false, '', false, 'help_category');
        }

        ob_start();
        ?>
        <nav class="hcs-prev-next">
            <div class="hcs-prev-next__item hcs-prev-next__item--prev">
                <?php if ($prev_post): ?>
                    <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="hcs-prev-next__link">
                        <span class="hcs-prev-next__arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </span>
                        <span class="hcs-prev-next__text"><?php echo esc_html($atts['prev_text']); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="hcs-prev-next__item hcs-prev-next__item--next">
                <?php if ($next_post): ?>
                    <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="hcs-prev-next__link">
                        <span class="hcs-prev-next__text"><?php echo esc_html($atts['next_text']); ?></span>
                        <span class="hcs-prev-next__arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Table of Contents
     */
    public function render_toc($atts = array()) {
        $atts = shortcode_atts(array(
            'title'    => 'TABLE OF CONTENTS',
            'headings' => 'h2,h3',
            'sticky'   => 'true',
        ), $atts, 'help_center_toc');

        $post_id = get_the_ID();
        $content = get_post_field('post_content', $post_id);
        
        // Parse headings from content
        $heading_tags = array_map('trim', explode(',', $atts['headings']));
        $pattern = '/<(' . implode('|', $heading_tags) . ')[^>]*>(.*?)<\/\1>/is';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return '';
        }

        $is_sticky = $atts['sticky'] === 'true';
        
        ob_start();
        ?>
        <div class="hcs-toc<?php echo $is_sticky ? ' hcs-toc--sticky' : ''; ?>" data-hcs-toc>
            <?php if (!empty($atts['title'])): ?>
                <h4 class="hcs-toc__title"><?php echo esc_html($atts['title']); ?></h4>
            <?php endif; ?>
            
            <nav class="hcs-toc__nav">
                <ul class="hcs-toc__list">
                    <?php 
                    $index = 0;
                    foreach ($matches as $match): 
                        $tag = strtolower($match[1]);
                        $text = strip_tags($match[2]);
                        $slug = sanitize_title($text);
                        $indent_class = $tag === 'h3' ? ' hcs-toc__item--indent' : '';
                        ?>
                        <li class="hcs-toc__item<?php echo $indent_class; ?>">
                            <a href="#<?php echo esc_attr($slug); ?>" class="hcs-toc__link" data-toc-target="<?php echo esc_attr($slug); ?>">
                                <?php echo esc_html($text); ?>
                            </a>
                        </li>
                    <?php 
                        $index++;
                    endforeach; 
                    ?>
                </ul>
            </nav>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Inline CSS styles
     */
    private function get_inline_css() {
        return <<<CSS
/* Help Center Single - Sidebar */
.hcs-sidebar{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.hcs-sidebar__title{font-size:14px;font-weight:700;letter-spacing:1px;color:#111;margin:0 0 20px;text-transform:uppercase}

/* Accordion */
.hcs-accordion__item{border-bottom:1px solid #E8E8E8}
.hcs-accordion__item:first-child{border-top:1px solid #E8E8E8}
.hcs-accordion__header{display:flex;align-items:center;justify-content:space-between;width:100%;padding:16px 0;background:none;border:none;cursor:pointer;text-align:left;font-size:15px;font-weight:500;color:#333;transition:color .2s}
.hcs-accordion__header:hover{color:#3E54E8}
.hcs-accordion__item--active .hcs-accordion__header{color:#3E54E8}
.hcs-accordion__header-text{flex:1}
.hcs-accordion__arrow{display:flex;align-items:center;justify-content:center;width:24px;height:24px;color:#999;transition:transform .2s}
.hcs-accordion__item--active .hcs-accordion__arrow{transform:rotate(180deg)}
.hcs-accordion__content{padding:0 0 16px 16px}

/* Post List */
.hcs-post-list{list-style:none;margin:0;padding:0}
.hcs-post-list__item{margin:0 0 10px}
.hcs-post-list__link{font-size:14px;color:#666;text-decoration:none;transition:color .2s}
.hcs-post-list__link:hover{color:#3E54E8}
.hcs-post-list__empty{font-size:13px;color:#999;margin:0}

/* Updated Date */
.hcs-updated-date{display:flex;align-items:center;gap:8px;padding:20px 0;border-top:1px solid #E8E8E8;margin-top:40px}
.hcs-updated-date__icon{display:flex;align-items:center;color:#3E54E8}
.hcs-updated-date__text{font-size:14px;color:#3E54E8}

/* Previous/Next Navigation */
.hcs-prev-next{display:flex;justify-content:space-between;align-items:center;padding:20px 0;margin-top:16px}
.hcs-prev-next__item{display:flex}
.hcs-prev-next__item--prev{justify-content:flex-start}
.hcs-prev-next__item--next{justify-content:flex-end;margin-left:auto}
.hcs-prev-next__link{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:#333;font-size:14px;font-weight:500;transition:color .2s}
.hcs-prev-next__link:hover{color:#3E54E8}
.hcs-prev-next__arrow{display:flex;align-items:center;justify-content:center}

/* Table of Contents */
.hcs-toc{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.hcs-toc--sticky{position:sticky;top:100px}
.hcs-toc__title{font-size:13px;font-weight:700;letter-spacing:1px;color:#111;margin:0 0 16px;text-transform:uppercase}
.hcs-toc__nav{border-left:2px solid #E8E8E8}
.hcs-toc__list{list-style:none;margin:0;padding:0}
.hcs-toc__item{margin:0;padding:0}
.hcs-toc__item--indent{padding-left:16px}
.hcs-toc__link{display:block;padding:8px 0 8px 16px;margin-left:-2px;border-left:2px solid transparent;font-size:14px;color:#666;text-decoration:none;transition:color .2s,border-color .2s}
.hcs-toc__link:hover,.hcs-toc__link.hcs-toc__link--active{color:#3E54E8;border-left-color:#3E54E8}

/* Responsive */
@media (max-width:1024px){
    .hcs-toc--sticky{position:relative;top:0}
}
CSS;
    }

    /**
     * Inline JavaScript
     */
    private function get_inline_js() {
        return <<<'JS'
(function() {
    'use strict';

    // Accordion functionality
    function initAccordion() {
        var headers = document.querySelectorAll('.hcs-accordion__header');
        headers.forEach(function(header) {
            header.addEventListener('click', function() {
                var item = this.closest('.hcs-accordion__item');
                var content = item.querySelector('.hcs-accordion__content');
                var isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Toggle current item
                this.setAttribute('aria-expanded', !isExpanded);
                item.classList.toggle('hcs-accordion__item--active');
                
                if (isExpanded) {
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';
                }
            });
        });
    }

    // Table of Contents scroll functionality
    function initTOC() {
        var tocLinks = document.querySelectorAll('.hcs-toc__link');
        var contentArea = document.querySelector('.elementor-widget-theme-post-content, .entry-content, article');
        
        if (!tocLinks.length) return;

        // Add IDs to headings in content for scroll targets
        var headings = document.querySelectorAll('.elementor-widget-theme-post-content h2, .elementor-widget-theme-post-content h3, .entry-content h2, .entry-content h3, article h2, article h3');
        headings.forEach(function(heading) {
            if (!heading.id) {
                heading.id = sanitizeTitle(heading.textContent);
            }
        });

        // Smooth scroll on TOC link click
        tocLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-toc-target');
                var target = document.getElementById(targetId);
                
                if (target) {
                    var offset = 100; // Account for sticky header
                    var targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    // Update active state
                    tocLinks.forEach(function(l) { l.classList.remove('hcs-toc__link--active'); });
                    this.classList.add('hcs-toc__link--active');
                }
            });
        });

        // Highlight active TOC item on scroll
        function updateActiveLink() {
            var scrollPos = window.scrollY + 150;
            var activeLink = null;

            headings.forEach(function(heading) {
                if (heading.offsetTop <= scrollPos) {
                    var matchingLink = document.querySelector('.hcs-toc__link[data-toc-target="' + heading.id + '"]');
                    if (matchingLink) {
                        activeLink = matchingLink;
                    }
                }
            });

            tocLinks.forEach(function(l) { l.classList.remove('hcs-toc__link--active'); });
            if (activeLink) {
                activeLink.classList.add('hcs-toc__link--active');
            }
        }

        window.addEventListener('scroll', debounce(updateActiveLink, 50));
        updateActiveLink(); // Initial check
    }

    function sanitizeTitle(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initAccordion();
            initTOC();
        });
    } else {
        initAccordion();
        initTOC();
    }

    // Re-initialize for Elementor editor preview
    if (typeof elementorFrontend !== 'undefined') {
        jQuery(window).on('elementor/frontend/init', function() {
            elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
                initAccordion();
                initTOC();
            });
        });
    }
})();
JS;
    }
}

new Help_Center_Single_Shortcodes();
