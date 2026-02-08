<?php
/**
 * Help Center module
 * - Search + Sort + Load More
 * - REST by default with admin-ajax fallback
 * - Card grid layout with icons, titles, descriptions and "Explore More" links
 */

if (!defined('ABSPATH')) exit;
if (defined('HELP_CENTER_INC_LOADED')) return;
define('HELP_CENTER_INC_LOADED', true);

// Register Custom Post Type
add_action('init', 'cpt_register_help_center');
function cpt_register_help_center() {
    $labels = array(
        'name'               => __('Help Center', 'custom-post-type-ui'),
        'singular_name'      => __('Help Article', 'custom-post-type-ui'),
        'menu_name'          => __('Help Center', 'custom-post-type-ui'),
        'add_new'            => __('Add New', 'custom-post-type-ui'),
        'add_new_item'       => __('Add New Help Article', 'custom-post-type-ui'),
        'edit_item'          => __('Edit Help Article', 'custom-post-type-ui'),
        'new_item'           => __('New Help Article', 'custom-post-type-ui'),
        'view_item'          => __('View Help Article', 'custom-post-type-ui'),
        'search_items'       => __('Search Help Articles', 'custom-post-type-ui'),
        'not_found'          => __('No Help Articles found', 'custom-post-type-ui'),
        'not_found_in_trash' => __('No Help Articles found in Trash', 'custom-post-type-ui'),
    );

    $args = array(
        'label'               => __('Help Center', 'custom-post-type-ui'),
        'labels'              => $labels,
        'menu_icon'           => 'dashicons-editor-help',
        'description'         => 'Help Center articles',
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_rest'        => true,
        'rest_base'           => 'help-center',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'has_archive'         => false,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'delete_with_user'    => false,
        'exclude_from_search' => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'rewrite'             => array('slug' => 'help-center', 'with_front' => true),
        'query_var'           => true,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_graphql'     => false,
    );

    register_post_type('help_center', $args);

    // Register taxonomy for categories
    register_taxonomy('help_category', 'help_center', array(
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
            'slug' => 'help-category',
            'with_front' => false,
            'hierarchical' => true
        ),
    ));
}

if (!class_exists('Help_Center_Module')) {
    class Help_Center_Module {

        public function __construct() {
            add_shortcode('help_center', array($this, 'shortcode'));
            add_action('wp_ajax_help_center_query', array($this, 'ajax_query'));
            add_action('wp_ajax_nopriv_help_center_query', array($this, 'ajax_query'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'title'          => 'Help',
                'title_accent'   => 'Center',
                'subtitle'       => 'Find answers to your questions and learn how to get the most out of Anugal',
                'per_page'       => 6,
                'excerpt_length' => 120,
                'category'       => '',
            ), $atts, 'help_center');

            // Styles
            wp_register_style('help-center-inline-style', false, array(), '1.0.0');
            wp_enqueue_style('help-center-inline-style');
            wp_add_inline_style('help-center-inline-style', $this->inline_css());

            // Scripts
            wp_register_script('help-center-inline-script', false, array(), '1.0.0', true);
            wp_enqueue_script('help-center-inline-script');

            $data = array(
                'restUrl'       => esc_url_raw(get_rest_url()),
                'siteUrl'       => home_url(),
                'ajaxUrl'       => admin_url('admin-ajax.php'),
                'perPage'       => max(1, (int) $atts['per_page']),
                'excerptLength' => max(1, (int) $atts['excerpt_length']),
                'category'      => sanitize_text_field($atts['category']),
            );
            wp_add_inline_script('help-center-inline-script', 'window.HelpCenterData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('help-center-inline-script', $this->inline_js(), 'after');

            ob_start(); ?>
            <section id="help-center-module" class="hc-wrap" data-hc-init="0">
                <div class="hc-header">
                    <div class="hc-header__tools">
                        <div class="hc-searchbar">
                            <div class="hc-input-wrap">
                                <svg class="hc-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input type="text" id="hc-search-input" class="hc-searchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
                                <button id="hc-clear-btn" class="hc-clear-btn" type="button" title="Clear search" aria-label="Clear search">×</button>
                            </div>
                            <button id="hc-search-btn" class="hc-searchbar__btn" type="button">SEARCH</button>
                            <button id="hc-sort-toggle" class="hc-searchbar__filter" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="hc-sort-menu" title="Sort">
                                <span class="hc-filter__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="4" y1="6" x2="20" y2="6"></line>
                                        <line x1="4" y1="12" x2="16" y2="12"></line>
                                        <line x1="4" y1="18" x2="12" y2="18"></line>
                                    </svg>
                                </span>
                            </button>
                            <div id="hc-sort-menu" class="hc-sort-menu" role="menu" aria-hidden="true">
                                <button class="hc-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="hc-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="hc-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="hc-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="hc-grid" class="hc-grid" aria-live="polite"></section>

                <div class="hc-loadmore-wrap">
                    <button id="hc-loadmore" class="hc-loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="hc-loadmore__arrow">›</span>
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
                'post_type'      => 'help_center',
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
                        'taxonomy' => 'help_category',
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

            return array(
                'id'       => (int) $p->ID,
                'title'    => $title,
                'link'     => $link,
                'excerpt'  => $excerpt ?: '',
            );
        }

        private function inline_css() {
            return <<<CSS
.hc-wrap{max-width:1200px;margin:0 auto;padding:24px 16px 48px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.hc-header{display:flex;justify-content:center;margin-bottom:32px}
.hc-searchbar{display:flex;gap:12px;align-items:center}
.hc-input-wrap{position:relative;display:flex;align-items:center}
.hc-search-icon{position:absolute;left:16px;color:#999}
.hc-searchbar__input{border:1px solid #E0E0E0;border-radius:999px;padding:14px 40px 14px 48px;font-size:14px;outline:none;min-width:400px;background:#fff}
.hc-searchbar__input:focus{border-color:#3E54E8}
@media (max-width:640px){.hc-searchbar__input{min-width:240px}}
.hc-clear-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:24px;height:24px;border-radius:999px;border:none;background:#E0E0E0;color:#666;display:none;cursor:pointer;font-size:16px;line-height:22px;text-align:center}
.hc-clear-btn:hover{background:#ccc}
.hc-searchbar__btn{background:#3E54E8;color:#fff;border:none;border-radius:10px;font-weight:600;padding:14px 24px;cursor:pointer;font-size:14px}
.hc-searchbar__btn:hover{background:#2d43d6}
.hc-searchbar__filter{background:#fff;border:1px solid #E0E0E0;border-radius:10px;padding:12px 14px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.hc-searchbar__filter:hover{background:#f5f5f5}
.hc-filter__icon{display:flex;color:#666}
.hc-sort-menu{position:absolute;margin-top:8px;right:16px;background:#fff;border:1px solid #E0E0E0;box-shadow:0 6px 20px rgba(0,0,0,.08);border-radius:12px;display:none;min-width:180px;z-index:100;overflow:hidden}
.hc-sort-menu[aria-hidden="false"]{display:block}
.hc-sort-menu__item{display:block;width:100%;text-align:left;padding:12px 16px;border:none;background:#fff;cursor:pointer;font-size:14px}
.hc-sort-menu__item:hover{background:#F7F7F7}
.hc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
@media (max-width:1024px){.hc-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:640px){.hc-grid{grid-template-columns:1fr}}
.hc-card{background:#fff;border-radius:16px;border:1px solid #EEE;box-shadow:0 4px 12px rgba(0,0,0,.04);overflow:hidden;padding:28px;transition:box-shadow .2s,transform .2s}
.hc-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.hc-card__icon{width:48px;height:48px;margin-bottom:20px;background:#EEF2FF;border-radius:12px;display:flex;align-items:center;justify-content:center}
.hc-card__icon svg{color:#3E54E8}
.hc-card__title{font-size:18px;font-weight:700;margin:0 0 16px;color:#111;position:relative;padding-bottom:16px}
.hc-card__title::after{content:'';position:absolute;bottom:0;left:0;width:60px;height:2px;background:#3E54E8}
.hc-card__desc{font-size:14px;line-height:1.6;color:#555;margin:0 0 20px;min-height:60px}
.hc-card__cta{font-weight:600;font-size:13px;color:#3E54E8;text-decoration:none;text-transform:uppercase;letter-spacing:.5px}
.hc-card__cta:hover{text-decoration:underline}
.hc-card--skeleton{height:220px;background:linear-gradient(90deg,#f2f2f2 25%,#e9e9e9 37%,#f2f2f2 63%);background-size:400% 100%;animation:hc-shine 1.2s ease infinite;border-radius:16px}
@keyframes hc-shine{0%{background-position:0% 0}100%{background-position:100% 0}}
.hc-loadmore-wrap{display:flex;justify-content:center;padding:40px 0}
.hc-loadmore{display:inline-flex;align-items:center;gap:12px;background:#111827;color:#fff;border:none;border-radius:10px;padding:14px 24px;font-weight:600;cursor:pointer;font-size:14px}
.hc-loadmore:disabled{opacity:.5;cursor:not-allowed}
.hc-loadmore__arrow{display:inline-flex;align-items:center;justify-content:center;background:#000;color:#fff;border-radius:8px;padding:6px 12px;font-size:18px}
.hc-empty{text-align:center;color:#777;grid-column:1 / -1;padding:48px 24px}
CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    var cfg = window.HelpCenterData || {};
    var container = null, page = 1, totalPages = 1, loading = false, initialized = false;
    var currentSearch = '', currentOrderby = 'date', currentOrder = 'desc';

    function el(id){ return document.getElementById(id); }
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }

    function tryInit(){
        container = document.getElementById('help-center-module');
        if (!container || initialized) return;
        if (container.getAttribute('data-hc-init') === '1') return;
        container.setAttribute('data-hc-init', '1');
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
                if (n.nodeType === 1 && (n.id === 'help-center-module' || n.querySelector && n.querySelector('#help-center-module'))) {
                    tryInit();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    function bindEvents(){
        var searchInput = el('hc-search-input');
        var clearBtn = el('hc-clear-btn');
        var searchBtn = el('hc-search-btn');
        var sortToggle = el('hc-sort-toggle');
        var sortMenu = el('hc-sort-menu');
        var loadMoreBtn = el('hc-loadmore');

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

            qsa('.hc-sort-menu__item', sortMenu).forEach(function(item){
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
        var searchInput = el('hc-search-input');
        currentSearch = searchInput ? searchInput.value.trim() : '';
        fetchPosts(1, true);
    }

    function fetchPosts(pageNum, replace){
        if (loading) return;
        loading = true;

        var grid = el('hc-grid');
        var loadMoreBtn = el('hc-loadmore');

        if (replace) {
            grid.innerHTML = '<div class="hc-card--skeleton"></div><div class="hc-card--skeleton"></div><div class="hc-card--skeleton"></div>';
        }

        var params = new URLSearchParams({
            action: 'help_center_query',
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
                    grid.innerHTML = '<p class="hc-empty">No help articles found.</p>';
                }

                loadMoreBtn.disabled = page >= totalPages;
                loading = false;
            })
            .catch(function(){
                loading = false;
                if (replace) grid.innerHTML = '<p class="hc-empty">Error loading help articles.</p>';
            });
    }

    function renderCard(post){
        var lockIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
        return '<article class="hc-card">' +
            '<div class="hc-card__icon">'+lockIcon+'</div>' +
            '<h3 class="hc-card__title">'+escHtml(post.title)+'</h3>' +
            '<p class="hc-card__desc">'+escHtml(post.excerpt)+'</p>' +
            '<a href="'+escHtml(post.link)+'" class="hc-card__cta">Explore More</a>' +
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

    new Help_Center_Module();
}
