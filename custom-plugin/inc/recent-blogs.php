<?php
/**
 * Recent Blogs module (include from your plugin index.php)
 * - Search + Clear + Sort + Load More
 * - REST by default with admin-ajax fallback on 500
 * - Event delegation + MutationObserver so controls work when content is injected later (e.g., Elementor)
 */

if (!defined('ABSPATH')) exit;
if (defined('RECENT_BLOGS_INC_LOADED')) return;
define('RECENT_BLOGS_INC_LOADED', true);

if (!class_exists('Recent_Blogs_Module')) {
    class Recent_Blogs_Module {

        public function __construct() {
            add_shortcode('recent_blogs', array($this, 'shortcode'));
            add_action('wp_ajax_recent_blogs_query', array($this, 'ajax_recent_blogs'));
            add_action('wp_ajax_nopriv_recent_blogs_query', array($this, 'ajax_recent_blogs'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'title'            => 'Recent',
                'subtitle'         => 'Browse through our recent thoughts and expert perspectives on identity and access management.',
                'per_page'         => 6,
                'featured_post_id' => 0, // 0 = auto (most recent), or specific post ID
            ), $atts, 'recent_blogs');

            // Styles
            wp_register_style('recent-blogs-inline-style', false, array(), '1.0.5');
            wp_enqueue_style('recent-blogs-inline-style');
            wp_add_inline_style('recent-blogs-inline-style', $this->inline_css());

            // Get featured post
            $featured_post = $this->get_featured_post((int) $atts['featured_post_id']);
            
            // Scripts
            wp_register_script('recent-blogs-inline-script', false, array(), '1.0.5', true);
            wp_enqueue_script('recent-blogs-inline-script');

            $data = array(
                'restUrl'       => esc_url_raw(get_rest_url()),               // e.g. https://domain/subdir/wp-json/
                'siteUrl'       => home_url(),                                 // e.g. https://domain/subdir
                'ajaxUrl'       => admin_url('admin-ajax.php'),                // fallback endpoint
                'perPage'       => max(1, (int) $atts['per_page']),
                'excludePostId' => $featured_post ? $featured_post['id'] : 0,  // Exclude featured post from grid
            );
            wp_add_inline_script('recent-blogs-inline-script', 'window.RecentBlogsData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('recent-blogs-inline-script', $this->inline_js(), 'after');

            // Markup (note the container id recent-blogs-module used by MutationObserver)
            ob_start(); ?>
            <?php if ($featured_post): 
                $bg_url = esc_url($featured_post['featured']);
                // Additional validation: ensure the URL doesn't contain dangerous characters
                $bg_url = str_replace(array('"', "'", '(', ')'), '', $bg_url);
            ?>
            <section class="rb-featured-blog">
                <div class="rb-featured-blog__hero" style="background-image: url(&quot;<?php echo $bg_url; ?>&quot;);">
                    <div class="rb-featured-blog__overlay"></div>
                    <div class="rb-featured-blog__content">
                        <span class="rb-featured-blog__badge">Featured Blog</span>
                        <h1 class="rb-featured-blog__title"><?php echo esc_html($featured_post['title']); ?></h1>
                        <p class="rb-featured-blog__excerpt"><?php echo esc_html($featured_post['excerpt']); ?></p>
                        <div class="rb-featured-blog__actions">
                            <a href="<?php echo esc_url($featured_post['link']); ?>" class="rb-featured-blog__btn rb-featured-blog__btn--primary">GET STARTED</a>
                            <a href="<?php echo esc_url($featured_post['link']); ?>" class="rb-featured-blog__btn rb-featured-blog__btn--icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>
            <section id="recent-blogs-module" class="postwrap rb-wrap" data-rb-init="0">
                <div class="rb-header postlistHead">
                    <div class="rb-header__text comtitlestb plhText">
                        <h2 class="rb-title plhTitle">
                            <span class="rb-title__first plhTitleFirst"><?php echo esc_html($atts['title']); ?></span>
                            <span class="rb-title__accent plhTitleAccent "></span>
                        </h2>
                        <p class="rb-subtitle plhsubTitle"><?php echo esc_html($atts['subtitle']); ?></p>
                    </div>
                    <div class="rb-header__tools plhTools">
                        <div class="rb-searchbar plhSearchbar">
                            <div class="plhinSearwrap">
                                <svg class="wp-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
								<div class="rb-input-wrap plhinputwrap">
									<input type="text" id="rb-search-input" class="plhsearchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
									<button id="rb-clear-btn" class="rb-clear-btn plhclearbtn" type="button" title="Clear search" aria-label="Clear search">×</button>
								</div>
								<button id="rb-search-btn" class="rb-searchbar__btn plhSearchbarbtn" type="button">SEARCH</button>
							</div>
                            <button id="rb-sort-toggle" class="rb-searchbar__filter plhfilterbtn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="rb-sort-menu" title="Sort">
                                <img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/02/FunnelSimple.png" alt=""/>
                            </button>
                            <div id="rb-sort-menu" class="rb-sort-menu plhsortmenu" role="menu" aria-hidden="true">
                                <button class="rb-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="rb-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="rb-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="rb-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="rb-grid" class="rb-grid" aria-live="polite"></section>

                <div class="rb-loadmore-wrap loadmore-wrap">
                    <button id="rb-loadmore" class="rb-loadmore loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="rb-loadmore__arrow loadmore__arrow"><img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/01/emore-arrow-img1.png" alt=""/></span>
                    </button>
                </div>
            </section>
            <?php
            return ob_get_clean();
        }

        // AJAX fallback
        public function ajax_recent_blogs() {
            $per_page = isset($_REQUEST['per_page']) ? max(1, (int) $_REQUEST['per_page']) : 6;
            $page     = isset($_REQUEST['page']) ? max(1, (int) $_REQUEST['page']) : 1;
            $search   = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
            $orderby  = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'date';
            $order    = isset($_REQUEST['order']) ? strtolower(sanitize_key($_REQUEST['order'])) : 'desc';
            $exclude  = isset($_REQUEST['exclude_post_id']) ? (int) $_REQUEST['exclude_post_id'] : 0;

            if (!in_array($orderby, array('date','title','ID','modified'))) $orderby = 'date';
            if (!in_array($order, array('asc','desc'))) $order = 'desc';

            $args = array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'orderby'        => $orderby,
                'order'          => $order,
                's'              => $search,
                'no_found_rows'  => false,
            );
            
            // Exclude featured post from results
            if ($exclude > 0) {
                $args['post__not_in'] = array($exclude);
            }

            $q = new WP_Query($args);

            $items = array();
            foreach ($q->posts as $p) {
                $items[] = $this->serialize_post($p);
            }

            $resp = array(
                'posts'      => $items,
                'totalPages' => (int) $q->max_num_pages,
                'page'       => (int) $page,
            );

            wp_send_json($resp);
        }

        private function get_featured_post($post_id = 0) {
            // If post ID is provided, try to get that specific post
            if ($post_id > 0) {
                $post = get_post($post_id);
                if ($post && $post->post_status === 'publish' && $post->post_type === 'post') {
                    return $this->serialize_post($post);
                }
            }
            
            // Otherwise, get the most recent post
            $args = array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );
            
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                return $this->serialize_post($query->posts[0]);
            }
            
            return null;
        }

        private function serialize_post($p) {
            $title = get_the_title($p);
            $link  = get_permalink($p);
            $date  = get_post_time('c', true, $p); // ISO8601
            $author_id = $p->post_author;
            $author_name = $author_id ? get_the_author_meta('display_name', $author_id) : '';

            $thumb = '';
            $thumb_id = get_post_thumbnail_id($p);
            if ($thumb_id) {
                $img = wp_get_attachment_image_src($thumb_id, 'large');
                if ($img && is_array($img)) {
                    $thumb = $img[0];
                }
            }
            
            // Get excerpt for featured blog
            $excerpt = '';
            if (!empty($p->post_excerpt)) {
                $excerpt = $p->post_excerpt;
            } else {
                $excerpt = wp_trim_words(strip_shortcodes($p->post_content), 30, '...');
            }

            return array(
                'id'         => (int) $p->ID,
                'title'      => $title,
                'link'       => $link,
                'date'       => $date,
                'author'     => $author_name,
                'featured'   => $thumb ?: 'https://via.placeholder.com/768x432?text=Blog',
                'excerpt'    => $excerpt,
            );
        }

        private function inline_css() {
            return <<<CSS
/* Featured Blog Section */
.rb-featured-blog {
    margin-bottom: 60px;
}

.rb-featured-blog__hero {
    position: relative;
    width: 100%;
    min-height: 580px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 80px 60px;
}

.rb-featured-blog__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
    z-index: 1;
}

.rb-featured-blog__content {
    position: relative;
    z-index: 2;
    max-width: 800px;
}

.rb-featured-blog__badge {
    display: inline-block;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 24px;
}

@supports (backdrop-filter: blur(10px)) {
    .rb-featured-blog__badge {
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(10px);
    }
}

.rb-featured-blog__title {
    color: #fff;
    font-size: 48px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 16px 0;
}

.rb-featured-blog__excerpt {
    color: #fff;
    font-size: 16px;
    line-height: 1.6;
    margin: 0 0 32px 0;
    opacity: 0.95;
}

.rb-featured-blog__actions {
    display: flex;
    gap: 16px;
    align-items: center;
}

.rb-featured-blog__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.rb-featured-blog__btn--primary {
    background: #fff;
    color: #000;
    padding: 14px 32px;
    border-radius: 4px;
    border: 2px solid #fff;
}

.rb-featured-blog__btn--primary:hover {
    background: transparent;
    color: #fff;
}

.rb-featured-blog__btn--icon {
    background: #fff;
    color: #000;
    width: 48px;
    height: 48px;
    border-radius: 4px;
    border: 2px solid #fff;
}

.rb-featured-blog__btn--icon:hover {
    background: transparent;
    color: #fff;
}

.rb-featured-blog__btn--icon svg {
    width: 24px;
    height: 24px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .rb-featured-blog__hero {
        min-height: 480px;
        padding: 60px 40px;
    }
    
    .rb-featured-blog__title {
        font-size: 40px;
    }
}

@media (max-width: 768px) {
    .rb-featured-blog {
        margin-bottom: 40px;
    }
    
    .rb-featured-blog__hero {
        min-height: 400px;
        padding: 40px 24px;
    }
    
    .rb-featured-blog__title {
        font-size: 32px;
    }
    
    .rb-featured-blog__excerpt {
        font-size: 14px;
    }
    
    .rb-featured-blog__actions {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .rb-featured-blog__btn--primary {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .rb-featured-blog__hero {
        min-height: 350px;
        padding: 32px 20px;
    }
    
    .rb-featured-blog__title {
        font-size: 24px;
    }
    
    .rb-featured-blog__badge {
        font-size: 12px;
        padding: 6px 16px;
    }
}

CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    var cfg = window.RecentBlogsData || {};
    var container = null, page = 1, totalPages = 1, loading = false, initialized = false;
    var currentSearch = '', currentOrderby = 'date', currentOrder = 'desc';

    function el(id){ return document.getElementById(id); }
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }

    function tryInit(){
        container = document.getElementById('recent-blogs-module');
        if (!container || initialized) return;
        if (container.getAttribute('data-rb-init') === '1') return;
        container.setAttribute('data-rb-init', '1');
        bindEvents();
        fetchPosts(1, true);
        initialized = true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInit);
    } else {
        tryInit();
    }

    var observer = new MutationObserver(function(mutations){
        mutations.forEach(function(m){
            m.addedNodes.forEach(function(n){
                if (n.nodeType === 1 && (n.id === 'recent-blogs-module' || n.querySelector && n.querySelector('#recent-blogs-module'))) {
                    tryInit();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    function bindEvents(){
        var searchInput = el('rb-search-input');
        var clearBtn = el('rb-clear-btn');
        var searchBtn = el('rb-search-btn');
        var sortToggle = el('rb-sort-toggle');
        var sortMenu = el('rb-sort-menu');
        var loadMoreBtn = el('rb-loadmore');

        if (searchInput) {
            searchInput.addEventListener('input', function(){
                clearBtn.style.display = this.value ? 'block' : 'none';
            });
            searchInput.addEventListener('keypress', function(e){
                if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function(){
                searchInput.value = '';
                clearBtn.style.display = 'none';
                currentSearch = '';
                fetchPosts(1, true);
            });
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', doSearch);
        }

        if (sortToggle && sortMenu) {
            sortToggle.addEventListener('click', function(e){
                e.stopPropagation();
                var expanded = sortMenu.getAttribute('aria-hidden') === 'false';
                sortMenu.setAttribute('aria-hidden', expanded ? 'true' : 'false');
                sortToggle.setAttribute('aria-expanded', !expanded);
            });

            qsa('.rb-sort-menu__item', sortMenu).forEach(function(item){
                item.addEventListener('click', function(){
                    currentOrderby = this.dataset.orderby;
                    currentOrder = this.dataset.order;
                    sortMenu.setAttribute('aria-hidden', 'true');
                    fetchPosts(1, true);
                });
            });

            document.addEventListener('click', function(){
                sortMenu.setAttribute('aria-hidden', 'true');
            });
        }

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function(){
                if (page < totalPages && !loading) {
                    fetchPosts(page + 1, false);
                }
            });
        }
    }

    function doSearch(){
        var searchInput = el('rb-search-input');
        currentSearch = searchInput ? searchInput.value.trim() : '';
        fetchPosts(1, true);
    }

    function fetchPosts(pageNum, replace){
        if (loading) return;
        loading = true;

        var grid = el('rb-grid');
        var loadMoreBtn = el('rb-loadmore');

        if (replace) {
            grid.innerHTML = '<div class="rb-card--skeleton"></div><div class="rb-card--skeleton"></div><div class="rb-card--skeleton"></div>';
        }

        var params = new URLSearchParams({
            action: 'recent_blogs_query',
            page: pageNum,
            per_page: cfg.perPage || 6,
            search: currentSearch,
            orderby: currentOrderby,
            order: currentOrder,
            exclude_post_id: cfg.excludePostId || 0
        });

        fetch(cfg.ajaxUrl + '?' + params.toString())
            .then(function(r){ return r.json(); })
            .then(function(data){
                page = data.page || 1;
                totalPages = data.totalPages || 1;

                if (replace) grid.innerHTML = '';

                if (data.posts && data.posts.length) {
                    data.posts.forEach(function(post){
                        grid.insertAdjacentHTML('beforeend', renderCard(post));
                    });
                } else if (replace) {
                    grid.innerHTML = '<p class="rb-empty">No blogs found.</p>';
                }

                loadMoreBtn.disabled = page >= totalPages;
                loading = false;
            })
            .catch(function(){
                loading = false;
                if (replace) grid.innerHTML = '<p class="rb-empty">Error loading blogs.</p>';
            });
    }

    function renderCard(post){
        var dateStr = '';
        if (post.date) {
            try {
                var d = new Date(post.date);
                dateStr = d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            } catch(e) { dateStr = post.date; }
        }
        return '<article class="rb-card">' +
            '<a href="'+escHtml(post.link)+'" class="rb-card__image"><img src="'+escHtml(post.featured)+'" alt="'+escHtml(post.title)+'"></a>' +
            '<div class="rb-card__body">' +
                '<h3 class="rb-card__title"><a href="'+escHtml(post.link)+'">'+escHtml(post.title)+'</a></h3>' +
                '<div class="rb-card__meta"><span>'+escHtml(post.author)+'</span><span>'+escHtml(dateStr)+'</span></div>' +
                '<a href="'+escHtml(post.link)+'" class="rb-card__cta">Read More</a>' +
            '</div>' +
        '</article>';
    }

    function escHtml(str){
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
JS;
        }
    }

    new Recent_Blogs_Module();
}