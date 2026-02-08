<?php
/**
 * Core Integrations module
 * - Search + Sort + Load More
 * - REST by default with admin-ajax fallback
 * - Card grid layout with icons, titles, descriptions and "Learn More" links
 */

if (!defined('ABSPATH')) exit;
if (defined('CORE_INTEGRATIONS_INC_LOADED')) return;
define('CORE_INTEGRATIONS_INC_LOADED', true);

// Register Custom Post Type
add_action('init', 'cpt_register_core_integration');
function cpt_register_core_integration() {
    $labels = array(
        'name'               => __('Core Integrations', 'custom-post-type-ui'),
        'singular_name'      => __('Core Integration', 'custom-post-type-ui'),
        'menu_name'          => __('Core Integrations', 'custom-post-type-ui'),
        'add_new'            => __('Add New', 'custom-post-type-ui'),
        'add_new_item'       => __('Add New Core Integration', 'custom-post-type-ui'),
        'edit_item'          => __('Edit Core Integration', 'custom-post-type-ui'),
        'new_item'           => __('New Core Integration', 'custom-post-type-ui'),
        'view_item'          => __('View Core Integration', 'custom-post-type-ui'),
        'search_items'       => __('Search Core Integrations', 'custom-post-type-ui'),
        'not_found'          => __('No Core Integrations found', 'custom-post-type-ui'),
        'not_found_in_trash' => __('No Core Integrations found in Trash', 'custom-post-type-ui'),
    );

    $args = array(
        'label'               => __('Core Integration', 'custom-post-type-ui'),
        'labels'              => $labels,
        'menu_icon'           => 'dashicons-networking',
        'description'         => 'Core Integrations for identity governance',
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_rest'        => true,
        'rest_base'           => 'core-integrations',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'has_archive'         => false,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'delete_with_user'    => false,
        'exclude_from_search' => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'rewrite'             => array('slug' => 'core-integration', 'with_front' => true),
        'query_var'           => true,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_graphql'     => false,
    );

    register_post_type('core_integration', $args);

    // Register taxonomy for categories
    register_taxonomy('integration_category', 'core_integration', array(
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
            'slug' => 'integration-category',
            'with_front' => false,
            'hierarchical' => true
        ),
    ));
}

if (!class_exists('Core_Integrations_Module')) {
    class Core_Integrations_Module {

        public function __construct() {
            add_shortcode('core_integrations', array($this, 'shortcode'));
            add_action('wp_ajax_core_integrations_query', array($this, 'ajax_query'));
            add_action('wp_ajax_nopriv_core_integrations_query', array($this, 'ajax_query'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'title'     => 'Core',
                'title_accent' => 'Integrations',
                'subtitle'  => 'Explore featured integrations designed to simplify identity governance, access control & enterprise security management',
                'per_page'  => 6,
            ), $atts, 'core_integrations');

            // Styles
            wp_register_style('core-integrations-inline-style', false, array(), '1.0.0');
            wp_enqueue_style('core-integrations-inline-style');
            wp_add_inline_style('core-integrations-inline-style', $this->inline_css());

            // Scripts
            wp_register_script('core-integrations-inline-script', false, array(), '1.0.0', true);
            wp_enqueue_script('core-integrations-inline-script');

            $data = array(
                'restUrl'   => esc_url_raw(get_rest_url()),
                'siteUrl'   => home_url(),
                'ajaxUrl'   => admin_url('admin-ajax.php'),
                'perPage'   => max(1, (int) $atts['per_page']),
            );
            wp_add_inline_script('core-integrations-inline-script', 'window.CoreIntegrationsData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('core-integrations-inline-script', $this->inline_js(), 'after');

            ob_start(); ?>
            <section id="core-integrations-module" class="ci-wrap" data-ci-init="0">
                <div class="ci-header">
                    <div class="ci-header__text">
                        <h2 class="ci-title">
                            <span class="ci-title__first"><?php echo esc_html($atts['title']); ?></span>
                            <span class="ci-title__accent"><?php echo esc_html($atts['title_accent']); ?></span>
                        </h2>
                        <p class="ci-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
                    </div>
                    <div class="ci-header__tools">
                        <div class="ci-searchbar">
                            <div class="ci-input-wrap">
                                <svg class="ci-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input type="text" id="ci-search-input" class="ci-searchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
                                <button id="ci-clear-btn" class="ci-clear-btn" type="button" title="Clear search" aria-label="Clear search">×</button>
                            </div>
                            <button id="ci-search-btn" class="ci-searchbar__btn" type="button">SEARCH</button>
                            <button id="ci-sort-toggle" class="ci-searchbar__filter" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="ci-sort-menu" title="Sort">
                                <span class="ci-filter__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="4" y1="6" x2="20" y2="6"></line>
                                        <line x1="4" y1="12" x2="16" y2="12"></line>
                                        <line x1="4" y1="18" x2="12" y2="18"></line>
                                    </svg>
                                </span>
                            </button>
                            <div id="ci-sort-menu" class="ci-sort-menu" role="menu" aria-hidden="true">
                                <button class="ci-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="ci-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="ci-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="ci-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="ci-grid" class="ci-grid" aria-live="polite"></section>

                <div class="ci-loadmore-wrap">
                    <button id="ci-loadmore" class="ci-loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="ci-loadmore__arrow">›</span>
                    </button>
                </div>
            </section>
            <?php
            return ob_get_clean();
        }

        public function ajax_query() {
            $per_page = isset($_REQUEST['per_page']) ? max(1, (int) $_REQUEST['per_page']) : 6;
            $page     = isset($_REQUEST['page']) ? max(1, (int) $_REQUEST['page']) : 1;
            $search   = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
            $orderby  = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'date';
            $order    = isset($_REQUEST['order']) ? strtolower(sanitize_key($_REQUEST['order'])) : 'desc';

            if (!in_array($orderby, array('date','title','ID','modified'))) $orderby = 'date';
            if (!in_array($order, array('asc','desc'))) $order = 'desc';

            $args = array(
                'post_type'      => 'core_integration',
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'orderby'        => $orderby,
                'order'          => $order,
                's'              => $search,
                'no_found_rows'  => false,
            );

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

        private function serialize_post($p) {
            $title = get_the_title($p);
            $link  = get_permalink($p);
            $excerpt = get_the_excerpt($p);

            $icon = '';
            $icon_id = get_post_thumbnail_id($p);
            if ($icon_id) {
                $img = wp_get_attachment_image_src($icon_id, 'thumbnail');
                if ($img && is_array($img)) {
                    $icon = $img[0];
                }
            }

            return array(
                'id'       => (int) $p->ID,
                'title'    => $title,
                'link'     => $link,
                'excerpt'  => $excerpt ?: '',
                'icon'     => $icon,
            );
        }

        private function inline_css() {
            return <<<CSS
.ci-wrap{max-width:1200px;margin:0 auto;padding:24px 16px 48px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.ci-header{display:grid;grid-template-columns:1fr 520px;gap:24px;align-items:start;margin-bottom:24px}
@media (max-width:1024px){.ci-header{grid-template-columns:1fr}}
.ci-title{font-size:36px;line-height:1.2;margin:0;font-weight:800}
.ci-title__first{color:#111}
.ci-title__accent{color:#3E54E8;margin-left:8px}
.ci-subtitle{margin:12px 0 0;color:#555;max-width:480px;line-height:1.5}
.ci-header__tools{display:flex;justify-content:flex-end}
.ci-searchbar{display:flex;gap:12px;align-items:center}
.ci-input-wrap{position:relative;display:flex;align-items:center}
.ci-search-icon{position:absolute;left:16px;color:#999}
.ci-searchbar__input{border:1px solid #E0E0E0;border-radius:999px;padding:12px 40px 12px 44px;font-size:14px;outline:none;min-width:280px;background:#fff}
.ci-searchbar__input:focus{border-color:#3E54E8}
@media (max-width:640px){.ci-searchbar__input{min-width:200px}}
.ci-clear-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:24px;height:24px;border-radius:999px;border:none;background:#E0E0E0;color:#666;display:none;cursor:pointer;font-size:16px;line-height:22px;text-align:center}
.ci-clear-btn:hover{background:#ccc}
.ci-searchbar__btn{background:#3E54E8;color:#fff;border:none;border-radius:10px;font-weight:600;padding:12px 20px;cursor:pointer;font-size:14px}
.ci-searchbar__btn:hover{background:#2d43d6}
.ci-searchbar__filter{background:#fff;border:1px solid #E0E0E0;border-radius:10px;padding:10px 12px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.ci-searchbar__filter:hover{background:#f5f5f5}
.ci-filter__icon{display:flex;color:#666}
.ci-sort-menu{position:absolute;margin-top:8px;right:16px;background:#fff;border:1px solid #E0E0E0;box-shadow:0 6px 20px rgba(0,0,0,.08);border-radius:12px;display:none;min-width:180px;z-index:100;overflow:hidden}
.ci-sort-menu[aria-hidden="false"]{display:block}
.ci-sort-menu__item{display:block;width:100%;text-align:left;padding:12px 16px;border:none;background:#fff;cursor:pointer;font-size:14px}
.ci-sort-menu__item:hover{background:#F7F7F7}
.ci-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
@media (max-width:1024px){.ci-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:640px){.ci-grid{grid-template-columns:1fr}}
.ci-card{background:#fff;border-radius:16px;border:1px solid #EEE;box-shadow:0 4px 12px rgba(0,0,0,.04);overflow:hidden;padding:28px;transition:box-shadow .2s,transform .2s}
.ci-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.ci-card--featured{background:linear-gradient(135deg,#1e3a8a 0%,#3b5998 50%,#667eea 100%);color:#fff}
.ci-card--featured .ci-card__title{color:#fff}
.ci-card--featured .ci-card__title::after{background:rgba(255,255,255,.3)}
.ci-card--featured .ci-card__desc{color:rgba(255,255,255,.85)}
.ci-card--featured .ci-card__cta{color:#fff}
.ci-card__icon{width:48px;height:48px;margin-bottom:20px}
.ci-card__icon img{width:100%;height:100%;object-fit:contain}
.ci-card__title{font-size:20px;font-weight:700;margin:0 0 16px;color:#111;position:relative;padding-bottom:16px}
.ci-card__title::after{content:'';position:absolute;bottom:0;left:0;width:60px;height:2px;background:#3E54E8}
.ci-card__desc{font-size:14px;line-height:1.6;color:#555;margin:0 0 20px;min-height:60px}
.ci-card__cta{font-weight:600;font-size:13px;color:#3E54E8;text-decoration:none;text-transform:uppercase;letter-spacing:.5px}
.ci-card__cta:hover{text-decoration:underline}
.ci-card--skeleton{height:240px;background:linear-gradient(90deg,#f2f2f2 25%,#e9e9e9 37%,#f2f2f2 63%);background-size:400% 100%;animation:ci-shine 1.2s ease infinite;border-radius:16px}
@keyframes ci-shine{0%{background-position:0% 0}100%{background-position:100% 0}}
.ci-loadmore-wrap{display:flex;justify-content:center;padding:40px 0}
.ci-loadmore{display:inline-flex;align-items:center;gap:12px;background:#111827;color:#fff;border:none;border-radius:10px;padding:14px 24px;font-weight:600;cursor:pointer;font-size:14px}
.ci-loadmore:disabled{opacity:.5;cursor:not-allowed}
.ci-loadmore__arrow{display:inline-flex;align-items:center;justify-content:center;background:#000;color:#fff;border-radius:8px;padding:6px 12px;font-size:18px}
.ci-empty{text-align:center;color:#777;grid-column:1 / -1;padding:48px 24px}
CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    var cfg = window.CoreIntegrationsData || {};
    var container = null, page = 1, totalPages = 1, loading = false, initialized = false;
    var currentSearch = '', currentOrderby = 'date', currentOrder = 'desc';

    function el(id){ return document.getElementById(id); }
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }

    function tryInit(){
        container = document.getElementById('core-integrations-module');
        if (!container || initialized) return;
        if (container.getAttribute('data-ci-init') === '1') return;
        container.setAttribute('data-ci-init', '1');
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
                if (n.nodeType === 1 && (n.id === 'core-integrations-module' || n.querySelector && n.querySelector('#core-integrations-module'))) {
                    tryInit();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    function bindEvents(){
        var searchInput = el('ci-search-input');
        var clearBtn = el('ci-clear-btn');
        var searchBtn = el('ci-search-btn');
        var sortToggle = el('ci-sort-toggle');
        var sortMenu = el('ci-sort-menu');
        var loadMoreBtn = el('ci-loadmore');

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

            qsa('.ci-sort-menu__item', sortMenu).forEach(function(item){
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
        var searchInput = el('ci-search-input');
        currentSearch = searchInput ? searchInput.value.trim() : '';
        fetchPosts(1, true);
    }

    function fetchPosts(pageNum, replace){
        if (loading) return;
        loading = true;

        var grid = el('ci-grid');
        var loadMoreBtn = el('ci-loadmore');

        if (replace) {
            grid.innerHTML = '<div class="ci-card--skeleton"></div><div class="ci-card--skeleton"></div><div class="ci-card--skeleton"></div>';
        }

        var params = new URLSearchParams({
            action: 'core_integrations_query',
            page: pageNum,
            per_page: cfg.perPage || 6,
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
                    data.posts.forEach(function(post, idx){
                        grid.insertAdjacentHTML('beforeend', renderCard(post, replace && idx === 0 && pageNum === 1));
                    });
                } else if (replace) {
                    grid.innerHTML = '<p class="ci-empty">No integrations found.</p>';
                }

                loadMoreBtn.disabled = page >= totalPages;
                loading = false;
            })
            .catch(function(){
                loading = false;
                if (replace) grid.innerHTML = '<p class="ci-empty">Error loading integrations.</p>';
            });
    }

    function renderCard(post, featured){
        var iconHtml = post.icon ? '<img src="'+escHtml(post.icon)+'" alt="">' : '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#3E54E8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
        var featuredClass = featured ? ' ci-card--featured' : '';
        return '<article class="ci-card'+featuredClass+'">' +
            '<div class="ci-card__icon">'+iconHtml+'</div>' +
            '<h3 class="ci-card__title">'+escHtml(post.title)+'</h3>' +
            '<p class="ci-card__desc">'+escHtml(post.excerpt)+'</p>' +
            '<a href="'+escHtml(post.link)+'" class="ci-card__cta">Learn More</a>' +
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

    new Core_Integrations_Module();
}
