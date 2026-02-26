<?php
/**
 * Solution Briefs module
 * - Search + Sort + Load More
 * - REST by default with admin-ajax fallback
 * - Card grid layout with images, titles, descriptions and "Read More" links
 */

if (!defined('ABSPATH')) exit;
if (defined('SOLUTION_BRIEFS_INC_LOADED')) return;
define('SOLUTION_BRIEFS_INC_LOADED', true);

// Register Custom Post Type
add_action('init', 'cpt_register_solution_brief');
function cpt_register_solution_brief() {
    $labels = array(
        'name'               => __('Solution Briefs', 'custom-post-type-ui'),
        'singular_name'      => __('Solution Brief', 'custom-post-type-ui'),
        'menu_name'          => __('Solution Briefs', 'custom-post-type-ui'),
        'add_new'            => __('Add New', 'custom-post-type-ui'),
        'add_new_item'       => __('Add New Solution Brief', 'custom-post-type-ui'),
        'edit_item'          => __('Edit Solution Brief', 'custom-post-type-ui'),
        'new_item'           => __('New Solution Brief', 'custom-post-type-ui'),
        'view_item'          => __('View Solution Brief', 'custom-post-type-ui'),
        'search_items'       => __('Search Solution Briefs', 'custom-post-type-ui'),
        'not_found'          => __('No Solution Briefs found', 'custom-post-type-ui'),
        'not_found_in_trash' => __('No Solution Briefs found in Trash', 'custom-post-type-ui'),
    );

    $args = array(
        'label'               => __('Solution Briefs', 'custom-post-type-ui'),
        'labels'              => $labels,
        'menu_icon'           => 'dashicons-media-text',
        'description'         => 'Solution briefs and product overviews',
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_rest'        => true,
        'rest_base'           => 'solution-briefs',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'has_archive'         => false,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'delete_with_user'    => false,
        'exclude_from_search' => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'rewrite'             => array('slug' => 'solution-brief', 'with_front' => true),
        'query_var'           => true,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_graphql'     => false,
    );

    register_post_type('solution_brief', $args);

    // Register taxonomy for categories
    register_taxonomy('solution_brief_category', 'solution_brief', array(
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
            'slug' => 'solution-brief-category',
            'with_front' => false,
            'hierarchical' => true
        ),
    ));
}

if (!class_exists('Solution_Briefs_Module')) {
    class Solution_Briefs_Module {

        public function __construct() {
            add_shortcode('solution_briefs', array($this, 'shortcode'));
            add_action('wp_ajax_solution_briefs_query', array($this, 'ajax_query'));
            add_action('wp_ajax_nopriv_solution_briefs_query', array($this, 'ajax_query'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'title'          => 'Recent',
                'title_accent'   => 'Solution Briefs',
                'subtitle'       => 'Discover concise overviews of our solutions and how they address your business challenges.',
                'per_page'       => 6,
                'excerpt_length' => 120,
                'category'       => '',
            ), $atts, 'solution_briefs');

            // Styles
            wp_register_style('solution-briefs-inline-style', false, array(), '1.0.0');
            wp_enqueue_style('solution-briefs-inline-style');
            wp_add_inline_style('solution-briefs-inline-style', $this->inline_css());

            // Scripts
            wp_register_script('solution-briefs-inline-script', false, array(), '1.0.0', true);
            wp_enqueue_script('solution-briefs-inline-script');

            $data = array(
                'restUrl'       => esc_url_raw(get_rest_url()),
                'siteUrl'       => home_url(),
                'ajaxUrl'       => admin_url('admin-ajax.php'),
                'perPage'       => max(1, (int) $atts['per_page']),
                'excerptLength' => max(1, (int) $atts['excerpt_length']),
                'category'      => sanitize_text_field($atts['category']),
            );
            wp_add_inline_script('solution-briefs-inline-script', 'window.SolutionBriefsData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('solution-briefs-inline-script', $this->inline_js(), 'after');

            ob_start(); ?>
            <section id="solution-briefs-module" class="sb-wrap" data-sb-init="0">
                <div class="sb-header postlistHead">
                    <div class="sb-header__text comtitlestb plhText">
                        <h2 class="sb-title plhTitle">
                            <span class="sb-title__first plhTitleFirst"><?php echo esc_html($atts['title']); ?></span>
                            <span class="sb-title__accent plhTitleAccent"><?php echo esc_html($atts['title_accent']); ?></span>
                        </h2>
                        <p class="sb-subtitle plhsubTitle"><?php echo esc_html($atts['subtitle']); ?></p>
                    </div>
                    <div class="sb-header__tools plhTools">
                        <div class="sb-searchbar plhSearchbar">
                            <div class="plhinSearwrap">
                                <svg class="sb-search-icon plhsearchicon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <div class="sb-input-wrap plhinputwrap">
                                    <input type="text" id="sb-search-input" class="plhsearchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
                                    <button id="sb-clear-btn" class="sb-clear-btn plhclearbtn" type="button" title="Clear search" aria-label="Clear search">×</button>
                                </div>
                                <button id="sb-search-btn" class="sb-searchbar__btn plhSearchbarbtn" type="button">SEARCH</button>
                            </div>
                            <button id="sb-sort-toggle" class="sb-searchbar__filter plhfilterbtn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="sb-sort-menu" title="Sort">
                                <img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/02/FunnelSimple.png" alt=""/>
                            </button>
                            <div id="sb-sort-menu" class="sb-sort-menu plhsortmenu" role="menu" aria-hidden="true">
                                <button class="sb-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="sb-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="sb-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="sb-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="sb-grid" class="sb-grid" aria-live="polite"></section>

                <div class="sb-loadmore-wrap loadmore-wrap">
                    <button id="sb-loadmore" class="sb-loadmore loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="sb-loadmore__arrow loadmore__arrow"><img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/01/emore-arrow-img1.png" alt=""/></span>
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
                'post_type'      => 'solution_brief',
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
                        'taxonomy' => 'solution_brief_category',
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
            $title   = get_the_title($p);
            $link    = get_permalink($p);
            $excerpt = get_the_excerpt($p);

            if (!empty($excerpt) && mb_strlen($excerpt) > $excerpt_length) {
                $excerpt = mb_substr($excerpt, 0, $excerpt_length);
                $last_space = mb_strrpos($excerpt, ' ');
                if ($last_space !== false && $last_space > mb_strlen($excerpt) * 0.8) {
                    $excerpt = mb_substr($excerpt, 0, $last_space);
                }
                $excerpt = rtrim($excerpt, '.,!? ') . '...';
            }

            $thumb    = '';
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
                'featured' => $thumb ?: 'https://via.placeholder.com/768x432?text=Solution+Brief',
            );
        }

        private function inline_css() {
            return <<<CSS

.sb-empty{text-align:center;color:#777;grid-column:1 / -1;padding:48px 24px}
CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    var cfg = window.SolutionBriefsData || {};
    var container = null, page = 1, totalPages = 1, loading = false, initialized = false;
    var currentSearch = '', currentOrderby = 'date', currentOrder = 'desc';

    function el(id){ return document.getElementById(id); }
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }

    function tryInit(){
        container = document.getElementById('solution-briefs-module');
        if (!container || initialized) return;
        if (container.getAttribute('data-sb-init') === '1') return;
        container.setAttribute('data-sb-init', '1');
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
                if (n.nodeType === 1 && (n.id === 'solution-briefs-module' || n.querySelector && n.querySelector('#solution-briefs-module'))) {
                    tryInit();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    function bindEvents(){
        var searchInput = el('sb-search-input');
        var clearBtn    = el('sb-clear-btn');
        var searchBtn   = el('sb-search-btn');
        var sortToggle  = el('sb-sort-toggle');
        var sortMenu    = el('sb-sort-menu');
        var loadMoreBtn = el('sb-loadmore');

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

            qsa('.sb-sort-menu__item', sortMenu).forEach(function(item){
                item.addEventListener('click', function(){
                    currentOrderby = this.dataset.orderby;
                    currentOrder   = this.dataset.order;
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
        var searchInput = el('sb-search-input');
        currentSearch = searchInput ? searchInput.value.trim() : '';
        fetchPosts(1, true);
    }

    function fetchPosts(pageNum, replace){
        if (loading) return;
        loading = true;

        var grid        = el('sb-grid');
        var loadMoreBtn = el('sb-loadmore');

        if (replace) {
            grid.innerHTML = '<div class="sb-card--skeleton"></div><div class="sb-card--skeleton"></div><div class="sb-card--skeleton"></div>';
        }

        var params = new URLSearchParams({
            action:         'solution_briefs_query',
            page:           pageNum,
            per_page:       cfg.perPage || 6,
            excerpt_length: cfg.excerptLength || 120,
            category:       cfg.category || '',
            search:         currentSearch,
            orderby:        currentOrderby,
            order:          currentOrder
        });

        fetch(cfg.ajaxUrl + '?' + params.toString())
            .then(function(r){ return r.json(); })
            .then(function(data){
                page       = data.page || 1;
                totalPages = data.totalPages || 1;

                if (replace) grid.innerHTML = '';

                if (data.posts && data.posts.length) {
                    data.posts.forEach(function(post){
                        grid.insertAdjacentHTML('beforeend', renderCard(post));
                    });
                } else if (replace) {
                    grid.innerHTML = '<p class="sb-empty">No solution briefs found.</p>';
                }

                loadMoreBtn.disabled = page >= totalPages;
                loading = false;
            })
            .catch(function(){
                loading = false;
                if (replace) grid.innerHTML = '<p class="sb-empty">Error loading solution briefs.</p>';
            });
    }

    function renderCard(post){
        return '<article class="sb-card">' +
            '<a href="'+escHtml(post.link)+'" class="sb-card__image"><img src="'+escHtml(post.featured)+'" alt="'+escHtml(post.title)+'"></a>' +
            '<div class="sb-card__body">' +
                '<h3 class="sb-card__title">'+escHtml(post.title)+'</h3>' +
                '<p class="sb-card__desc">'+escHtml(post.excerpt)+'</p>' +
                '<a href="'+escHtml(post.link)+'" class="sb-card__cta">Read More</a>' +
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

    new Solution_Briefs_Module();
}
