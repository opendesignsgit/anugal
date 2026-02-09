<?php
/**
 * Post Single Page Template Shortcodes
 * 
 * Shortcodes for Blog Post single page template:
 * - [post_single_toc] - Left sidebar Table of Contents with scroll
 * - [post_single_share] - Social share icons
 * - [post_single_author] - About Author section
 */

if (!defined('ABSPATH')) exit;
if (defined('POST_SINGLE_LOADED')) return;
define('POST_SINGLE_LOADED', true);

class Post_Single_Shortcodes {

    public function __construct() {
        add_shortcode('post_single_toc', array($this, 'render_toc'));
        add_shortcode('post_single_share', array($this, 'render_share'));
        add_shortcode('post_single_author', array($this, 'render_author'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue styles and scripts
     */
    public function enqueue_assets() {
        if (!is_singular('post')) return;

        wp_register_style('post-single-style', false, array(), '1.0.0');
        wp_enqueue_style('post-single-style');
        wp_add_inline_style('post-single-style', $this->get_inline_css());

        wp_register_script('post-single-script', false, array(), '1.0.0', true);
        wp_enqueue_script('post-single-script');
        wp_add_inline_script('post-single-script', $this->get_inline_js());
    }

    /**
     * Render Table of Contents
     */
    public function render_toc($atts = array()) {
        $atts = shortcode_atts(array(
            'title'    => 'TABLE OF CONTENTS',
            'headings' => 'h2,h3',
            'sticky'   => 'true',
        ), $atts, 'post_single_toc');

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
        <div class="pss-toc<?php echo $is_sticky ? ' pss-toc--sticky' : ''; ?>" data-pss-toc>
            <?php if (!empty($atts['title'])): ?>
                <h4 class="pss-toc__title"><?php echo esc_html($atts['title']); ?></h4>
            <?php endif; ?>
            
            <nav class="pss-toc__nav">
                <ul class="pss-toc__list">
                    <?php 
                    foreach ($matches as $match): 
                        $tag = strtolower($match[1]);
                        $text = strip_tags($match[2]);
                        $slug = sanitize_title($text);
                        $indent_class = $tag === 'h3' ? ' pss-toc__item--indent' : '';
                        ?>
                        <li class="pss-toc__item<?php echo $indent_class; ?>">
                            <a href="#<?php echo esc_attr($slug); ?>" class="pss-toc__link" data-toc-target="<?php echo esc_attr($slug); ?>">
                                <?php echo esc_html($text); ?>
                            </a>
                        </li>
                    <?php 
                    endforeach; 
                    ?>
                </ul>
            </nav>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Social Share Icons
     */
    public function render_share($atts = array()) {
        $atts = shortcode_atts(array(
            'title'     => 'SHARE',
            'platforms' => 'instagram,facebook,twitter,linkedin',
        ), $atts, 'post_single_share');

        $post_id = get_the_ID();
        $post_url = urlencode(get_permalink($post_id));
        $post_title = urlencode(get_the_title($post_id));
        
        $platforms = array_map('trim', explode(',', $atts['platforms']));
        
        // Define share URLs and icons
        $share_data = array(
            'instagram' => array(
                'url'   => 'https://www.instagram.com/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
                'label' => 'Instagram',
            ),
            'facebook' => array(
                'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $post_url,
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
                'label' => 'Facebook',
            ),
            'twitter' => array(
                'url'   => 'https://twitter.com/intent/tweet?url=' . $post_url . '&text=' . $post_title,
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                'label' => 'X (Twitter)',
            ),
            'linkedin' => array(
                'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $post_url . '&title=' . $post_title,
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
                'label' => 'LinkedIn',
            ),
        );

        ob_start();
        ?>
        <div class="pss-share">
            <?php if (!empty($atts['title'])): ?>
                <h4 class="pss-share__title"><?php echo esc_html($atts['title']); ?></h4>
            <?php endif; ?>
            
            <div class="pss-share__icons">
                <?php foreach ($platforms as $platform): 
                    if (!isset($share_data[$platform])) continue;
                    $data = $share_data[$platform];
                    ?>
                    <a href="<?php echo esc_url($data['url']); ?>" 
                       class="pss-share__link pss-share__link--<?php echo esc_attr($platform); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       title="Share on <?php echo esc_attr($data['label']); ?>"
                       aria-label="Share on <?php echo esc_attr($data['label']); ?>">
                        <?php echo $data['icon']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render About Author section
     */
    public function render_author($atts = array()) {
        $atts = shortcode_atts(array(
            'title' => 'ABOUT AUTHOR',
        ), $atts, 'post_single_author');

        $post_id = get_the_ID();
        $author_id = get_post_field('post_author', $post_id);
        
        $author_name = get_the_author_meta('display_name', $author_id);
        $author_bio = get_the_author_meta('description', $author_id);
        $author_avatar = get_avatar_url($author_id, array('size' => 80));
        
        // If no bio is set, provide a default
        if (empty($author_bio)) {
            $author_bio = 'This author has not provided a bio yet.';
        }

        ob_start();
        ?>
        <div class="pss-author">
            <?php if (!empty($atts['title'])): ?>
                <h4 class="pss-author__title"><?php echo esc_html($atts['title']); ?></h4>
            <?php endif; ?>
            
            <div class="pss-author__content">
                <p class="pss-author__bio"><?php echo esc_html($author_bio); ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Inline CSS styles
     */
    private function get_inline_css() {
        return <<<CSS
/* Post Single - Table of Contents */
.pss-toc{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.pss-toc--sticky{position:sticky;top:100px}
.pss-toc__title{font-size:14px;font-weight:700;letter-spacing:1px;color:#111;margin:0 0 16px;padding-bottom:16px;border-bottom:1px solid #E8E8E8;text-transform:uppercase}
.pss-toc__nav{}
.pss-toc__list{list-style:none;margin:0;padding:0}
.pss-toc__item{margin:0;padding:0}
.pss-toc__item--indent{padding-left:16px}
.pss-toc__link{display:block;padding:10px 0;font-size:14px;line-height:1.5;color:#666;text-decoration:none;transition:color .2s}
.pss-toc__link:hover,.pss-toc__link.pss-toc__link--active{color:#3E54E8}

/* Post Single - Share */
.pss-share{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;margin-top:40px}
.pss-share__title{font-size:14px;font-weight:700;letter-spacing:1px;color:#111;margin:0 0 16px;padding-bottom:16px;border-bottom:1px solid #E8E8E8;text-transform:uppercase}
.pss-share__icons{display:flex;gap:12px;flex-wrap:wrap}
.pss-share__link{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#f5f5f5;color:#333;text-decoration:none;transition:background .2s,color .2s,transform .2s}
.pss-share__link:hover{transform:translateY(-2px)}
.pss-share__link--instagram:hover{background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff}
.pss-share__link--facebook:hover{background:#1877F2;color:#fff}
.pss-share__link--twitter:hover{background:#000;color:#fff}
.pss-share__link--linkedin:hover{background:#0A66C2;color:#fff}

/* Post Single - Author */
.pss-author{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;margin-top:40px}
.pss-author__title{font-size:14px;font-weight:700;letter-spacing:1px;color:#111;margin:0 0 16px;padding-bottom:16px;border-bottom:1px solid #E8E8E8;text-transform:uppercase}
.pss-author__content{}
.pss-author__bio{font-size:14px;line-height:1.7;color:#666;margin:0}

/* Responsive */
@media (max-width:1024px){
    .pss-toc--sticky{position:relative;top:0}
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

    // Table of Contents scroll functionality
    function initTOC() {
        var tocLinks = document.querySelectorAll('.pss-toc__link');
        
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
                    tocLinks.forEach(function(l) { l.classList.remove('pss-toc__link--active'); });
                    this.classList.add('pss-toc__link--active');
                }
            });
        });

        // Highlight active TOC item on scroll
        function updateActiveLink() {
            var scrollPos = window.scrollY + 150;
            var activeLink = null;

            headings.forEach(function(heading) {
                if (heading.offsetTop <= scrollPos) {
                    var matchingLink = document.querySelector('.pss-toc__link[data-toc-target="' + heading.id + '"]');
                    if (matchingLink) {
                        activeLink = matchingLink;
                    }
                }
            });

            tocLinks.forEach(function(l) { l.classList.remove('pss-toc__link--active'); });
            if (activeLink) {
                activeLink.classList.add('pss-toc__link--active');
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
            initTOC();
        });
    } else {
        initTOC();
    }

    // Re-initialize for Elementor editor preview
    if (typeof elementorFrontend !== 'undefined' && typeof jQuery !== 'undefined') {
        jQuery(window).on('elementor/frontend/init', function() {
            elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
                initTOC();
            });
        });
    }
})();
JS;
    }
}

new Post_Single_Shortcodes();
