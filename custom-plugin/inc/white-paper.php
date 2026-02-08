<?php
/**
 * White Paper module
 * - Search + Sort + Load More
 * - REST by default with admin-ajax fallback
 * - Card grid layout with images, titles, descriptions and "Read More" links
 */

if (!defined('ABSPATH')) exit;
if (defined('WHITE_PAPER_INC_LOADED')) return;
define('WHITE_PAPER_INC_LOADED', true);

// Register Custom Post Type
add_action('init', 'cpt_register_white_paper');
function cpt_register_white_paper() {
    $labels = array(
        'name'               => __('White Papers', 'custom-post-type-ui'),
        'singular_name'      => __('White Paper', 'custom-post-type-ui'),
        'menu_name'          => __('White Papers', 'custom-post-type-ui'),
        'add_new'            => __('Add New', 'custom-post-type-ui'),
        'add_new_item'       => __('Add New White Paper', 'custom-post-type-ui'),
        'edit_item'          => __('Edit White Paper', 'custom-post-type-ui'),
        'new_item'           => __('New White Paper', 'custom-post-type-ui'),
        'view_item'          => __('View White Paper', 'custom-post-type-ui'),
        'search_items'       => __('Search White Papers', 'custom-post-type-ui'),
        'not_found'          => __('No White Papers found', 'custom-post-type-ui'),
        'not_found_in_trash' => __('No White Papers found in Trash', 'custom-post-type-ui'),
    );

    $args = array(
        'label'               => __('White Papers', 'custom-post-type-ui'),
        'labels'              => $labels,
        'menu_icon'           => 'dashicons-media-document',
        'description'         => 'White papers and resources',
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_rest'        => true,
        'rest_base'           => 'white-papers',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'has_archive'         => false,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'delete_with_user'    => false,
        'exclude_from_search' => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'rewrite'             => array('slug' => 'white-paper', 'with_front' => true),
        'query_var'           => true,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_graphql'     => false,
    );

    register_post_type('white_paper', $args);

    // Register taxonomy for categories
    register_taxonomy('whitepaper_category', 'white_paper', array(
        'hierarchical' => true,
        'labels' => array(
            'name'              => _x('Categories', 'taxonomy general name'),
            'singular_name'     => _x('Category', 'taxonomy singular name'),
            'search_items'      => __('Search Categories'),
            'all_items'         => __('All Categories'),
            'parent_item'       => __('Parent Category'),
            'parent_item_colon' => __('Parent Category:'),
            'edit_item'         => __('Edit Category'),
            'update_item'       => __('Update Category'),
            'add_new_item'      => __('Add New Category'),
            'new_item_name'     => __('New Category Name'),
            'menu_name'         => __('Categories'),
        ),
        'show_in_rest' => true,
        'rewrite' => array(
            'slug' => 'whitepaper-category',
            'with_front' => false,
            'hierarchical' => true
        ),
    ));
}

if (!class_exists('White_Paper_Module')) {
    class White_Paper_Module {

        public function __construct() {
            add_shortcode('white_papers', array($this, 'shortcode'));
            add_action('wp_ajax_white_paper_query', array($this, 'ajax_query'));
            add_action('wp_ajax_nopriv_white_paper_query', array($this, 'ajax_query'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'title'          => 'Recent',
                'title_accent'   => 'Resources',
                'subtitle'       => 'Stay informed with our newest whitepapers, offering in-depth analysis and industry best practices.',
                'per_page'       => 6,
                'excerpt_length' => 120,
                'category'       => '',
            ), $atts, 'white_papers');

            // Styles
            wp_register_style('white-paper-inline-style', false, array(), '1.0.0');
            wp_enqueue_style('white-paper-inline-style');
            wp_add_inline_style('white-paper-inline-style', $this->inline_css());

            // Scripts
            wp_register_script('white-paper-inline-script', false, array(), '1.0.0', true);
            wp_enqueue_script('white-paper-inline-script');

            $data = array(
                'restUrl'       => esc_url_raw(get_rest_url()),
                'siteUrl'       => home_url(),
                'ajaxUrl'       => admin_url('admin-ajax.php'),
                'perPage'       => max(1, (int) $atts['per_page']),
                'excerptLength' => max(1, (int) $atts['excerpt_length']),
                'category'      => sanitize_text_field($atts['category']),
            );
            wp_add_inline_script('white-paper-inline-script', 'window.WhitePaperData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('white-paper-inline-script', $this->inline_js(), 'after');

            ob_start(); ?>
            <section id="white-paper-module" class="wp-wrap" data-wp-init="0">
                <div class="wp-header">
                    <div class="wp-header__text">
                        <h2 class="wp-title">
                            <span class="wp-title__first"><?php echo esc_html($atts['title']); ?></span>
                            <span class="wp-title__accent"><?php echo esc_html($atts['title_accent']); ?></span>
                        </h2>
                        <p class="wp-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
                    </div>
                    <div class="wp-header__tools">
                        <div class="wp-searchbar">
                            <div class="wp-input-wrap">
                                <svg class="wp-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input type="text" id="wp-search-input" class="wp-searchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
                                <button id="wp-clear-btn" class="wp-clear-btn" type="button" title="Clear search" aria-label="Clear search">×</button>
                            </div>
                            <button id="wp-search-btn" class="wp-searchbar__btn" type="button">SEARCH</button>
                            <button id="wp-sort-toggle" class="wp-searchbar__filter" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="wp-sort-menu" title="Sort">
                                <span class="wp-filter__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="4" y1="6" x2="20" y2="6"></line>
                                        <line x1="4" y1="12" x2="16" y2="12"></line>
                                        <line x1="4" y1="18" x2="12" y2="18"></line>
                                    </svg>
                                </span>
                            </button>
                            <div id="wp-sort-menu" class="wp-sort-menu" role="menu" aria-hidden="true">
                                <button class="wp-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="wp-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="wp-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="wp-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="wp-grid" class="wp-grid" aria-live="polite"></section>

                <div class="wp-loadmore-wrap">
                    <button id="wp-loadmore" class="wp-loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="wp-loadmore__arrow">›</span>
                    </button>
                </div>
            </section>
            <?php
            return ob_get_clean();
        }

        public function ajax_query() {
            $per_page       = isset($_REQUEST['per_page']) ? max(1, (int) $_REQUEST['per_page']) : 6;
            $page           = isset($_REQUEST['page']) ? max(1, (int) $_REQUEST['page']) : 1;
            $search         = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
            $orderby        = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'date';
            $order          = isset($_REQUEST['order']) ? strtolower(sanitize_key($_REQUEST['order'])) : 'desc';
            $excerpt_length = isset($_REQUEST['excerpt_length']) ? max(1, (int) $_REQUEST['excerpt_length']) : 120;
            $category       = isset($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : '';

            if (!in_array($orderby, array('date','title','ID','modified'))) $orderby = 'date';
            if (!in_array($order, array('asc','desc'))) $order = 'desc';

            $args = array(
                'post_type'      => 'white_paper',
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'orderby'        => $orderby,
                'order'          => $order,
                's'              => $search,
                'no_found_rows'  => false,
            );

            // Filter by category if provided
            if (!empty($category)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'whitepaper_category',
                        'field'    => is_numeric($category) ? 'term_id' : 'slug',
                        'terms'    => $category,
                    ),
                );
            }

            $q = new WP_Query($args);

            $items = array();
            foreach ($q->posts as $p) {
                $items[] = $this->serialize_post($p, $excerpt_length);
            }

            $resp = array(
                'posts'      => $items,
                'totalPages' => (int) $q->max_num_pages,
                'page'       => (int) $page,
            );

            wp_send_json($resp);
        }

        private function serialize_post($p, $excerpt_length = 120) {
            $title = get_the_title($p);
            $link  = get_permalink($p);
            $excerpt = get_the_excerpt($p);

            // Truncate excerpt to character limit
            if (!empty($excerpt) && mb_strlen($excerpt) > $excerpt_length) {
                $excerpt = mb_substr($excerpt, 0, $excerpt_length);
                // Avoid cutting in the middle of a word - break on last space if it's not too early
                $last_space = mb_strrpos($excerpt, ' ');
                if ($last_space !== false && $last_space > mb_strlen($excerpt) * 0.8) {
                    $excerpt = mb_substr($excerpt, 0, $last_space);
                }
                $excerpt = rtrim($excerpt, '.,!? ') . '...';
            }

            $thumb = '';
            $thumb_id = get_post_thumbnail_id($p);
            if ($thumb_id) {
                $img = wp_get_attachment_image_src($thumb_id, 'large');
                if ($img && is_array($img)) {
                    $thumb = $img[0];
                }
            }

            return array(
                'id'       => (int) $p->ID,
                'title'    => $title,
                'link'     => $link,
                'excerpt'  => $excerpt ?: '',
                'featured' => $thumb ?: 'https://via.placeholder.com/768x432?text=Resource',
            );
        }

        private function inline_css() {
            return <<<CSS
.wp-wrap{max-width:1200px;margin:0 auto;padding:24px 16px 48px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.wp-header{display:grid;grid-template-columns:1fr 520px;gap:24px;align-items:start;margin-bottom:24px}
@media (max-width:1024px){.wp-header{grid-template-columns:1fr}}
.wp-title{font-size:36px;line-height:1.2;margin:0;font-weight:800}
.wp-title__first{color:#111}
.wp-title__accent{color:#3E54E8;margin-left:8px}
.wp-subtitle{margin:12px 0 0;color:#555;max-width:480px;line-height:1.5}
.wp-header__tools{display:flex;justify-content:flex-end}
.wp-searchbar{display:flex;gap:12px;align-items:center}
.wp-input-wrap{position:relative;display:flex;align-items:center}
.wp-search-icon{position:absolute;left:16px;color:#999}
.wp-searchbar__input{border:1px solid #E0E0E0;border-radius:999px;padding:12px 40px 12px 44px;font-size:14px;outline:none;min-width:280px;background:#fff}
.wp-searchbar__input:focus{border-color:#3E54E8}
@media (max-width:640px){.wp-searchbar__input{min-width:200px}}
.wp-clear-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:24px;height:24px;border-radius:999px;border:none;background:#E0E0E0;color:#666;display:none;cursor:pointer;font-size:16px;line-height:22px;text-align:center}
.wp-clear-btn:hover{background:#ccc}
.wp-searchbar__btn{background:#3E54E8;color:#fff;border:none;border-radius:10px;font-weight:600;padding:12px 20px;cursor:pointer;font-size:14px}
.wp-searchbar__btn:hover{background:#2d43d6}
.wp-searchbar__filter{background:#fff;border:1px solid #E0E0E0;border-radius:10px;padding:10px 12px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.wp-searchbar__filter:hover{background:#f5f5f5}
.wp-filter__icon{display:flex;color:#666}
.wp-sort-menu{position:absolute;margin-top:8px;right:16px;background:#fff;border:1px solid #E0E0E0;box-shadow:0 6px 20px rgba(0,0,0,.08);border-radius:12px;display:none;min-width:180px;z-index:100;overflow:hidden}
.wp-sort-menu[aria-hidden="false"]{display:block}
.wp-sort-menu__item{display:block;width:100%;text-align:left;padding:12px 16px;border:none;background:#fff;cursor:pointer;font-size:14px}
.wp-sort-menu__item:hover{background:#F7F7F7}
.wp-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
@media (max-width:1024px){.wp-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:640px){.wp-grid{grid-template-columns:1fr}}
.wp-card{background:#fff;border-radius:16px;border:1px solid #EEE;box-shadow:0 4px 12px rgba(0,0,0,.04);overflow:hidden;transition:box-shadow .2s,transform .2s}
.wp-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.wp-card__image{display:block;height:200px;background:#f0f0f0;overflow:hidden}
.wp-card__image img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.wp-card:hover .wp-card__image img{transform:scale(1.05)}
.wp-card__body{padding:20px}
.wp-card__title{font-size:16px;font-weight:700;margin:0 0 8px;color:#111;line-height:1.4}
.wp-card__desc{font-size:14px;line-height:1.6;color:#555;margin:0 0 16px;min-height:44px}
.wp-card__cta{font-weight:600;font-size:13px;color:#3E54E8;text-decoration:none;text-transform:uppercase;letter-spacing:.5px}
.wp-card__cta:hover{text-decoration:underline}
.wp-card--skeleton{height:340px;background:linear-gradient(90deg,#f2f2f2 25%,#e9e9e9 37%,#f2f2f2 63%);background-size:400% 100%;animation:wp-shine 1.2s ease infinite;border-radius:16px}
@keyframes wp-shine{0%{background-position:0% 0}100%{background-position:100% 0}}
.wp-loadmore-wrap{display:flex;justify-content:center;padding:40px 0}
.wp-loadmore{display:inline-flex;align-items:center;gap:12px;background:#111827;color:#fff;border:none;border-radius:10px;padding:14px 24px;font-weight:600;cursor:pointer;font-size:14px}
.wp-loadmore:disabled{opacity:.5;cursor:not-allowed}
.wp-loadmore__arrow{display:inline-flex;align-items:center;justify-content:center;background:#000;color:#fff;border-radius:8px;padding:6px 12px;font-size:18px}
.wp-empty{text-align:center;color:#777;grid-column:1 / -1;padding:48px 24px}
CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    var cfg = window.WhitePaperData || {};
    var container = null, page = 1, totalPages = 1, loading = false, initialized = false;
    var currentSearch = '', currentOrderby = 'date', currentOrder = 'desc';

    function el(id){ return document.getElementById(id); }
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }

    function tryInit(){
        container = document.getElementById('white-paper-module');
        if (!container || initialized) return;
        if (container.getAttribute('data-wp-init') === '1') return;
        container.setAttribute('data-wp-init', '1');
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
                if (n.nodeType === 1 && (n.id === 'white-paper-module' || n.querySelector && n.querySelector('#white-paper-module'))) {
                    tryInit();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    function bindEvents(){
        var searchInput = el('wp-search-input');
        var clearBtn = el('wp-clear-btn');
        var searchBtn = el('wp-search-btn');
        var sortToggle = el('wp-sort-toggle');
        var sortMenu = el('wp-sort-menu');
        var loadMoreBtn = el('wp-loadmore');

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

            qsa('.wp-sort-menu__item', sortMenu).forEach(function(item){
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
        var searchInput = el('wp-search-input');
        currentSearch = searchInput ? searchInput.value.trim() : '';
        fetchPosts(1, true);
    }

    function fetchPosts(pageNum, replace){
        if (loading) return;
        loading = true;

        var grid = el('wp-grid');
        var loadMoreBtn = el('wp-loadmore');

        if (replace) {
            grid.innerHTML = '<div class="wp-card--skeleton"></div><div class="wp-card--skeleton"></div><div class="wp-card--skeleton"></div>';
        }

        var params = new URLSearchParams({
            action: 'white_paper_query',
            page: pageNum,
            per_page: cfg.perPage || 6,
            excerpt_length: cfg.excerptLength || 120,
            category: cfg.category || '',
            search: currentSearch,
            orderby: currentOrderby,
            order: currentOrder
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
                    grid.innerHTML = '<p class="wp-empty">No white papers found.</p>';
                }

                loadMoreBtn.disabled = page >= totalPages;
                loading = false;
            })
            .catch(function(){
                loading = false;
                if (replace) grid.innerHTML = '<p class="wp-empty">Error loading white papers.</p>';
            });
    }

    function renderCard(post){
        return '<article class="wp-card">' +
            '<a href="'+escHtml(post.link)+'" class="wp-card__image"><img src="'+escHtml(post.featured)+'" alt="'+escHtml(post.title)+'"></a>' +
            '<div class="wp-card__body">' +
                '<h3 class="wp-card__title">'+escHtml(post.title)+'</h3>' +
                '<p class="wp-card__desc">'+escHtml(post.excerpt)+'</p>' +
                '<a href="'+escHtml(post.link)+'" class="wp-card__cta">Read More</a>' +
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

    new White_Paper_Module();
}
