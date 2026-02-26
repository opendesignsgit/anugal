<?php
/**
 * Case Studies module
 * - Search + Sort + Load More
 * - REST by default with admin-ajax fallback
 * - Card grid layout with images, titles, descriptions and "Read More" links
 */

if (!defined('ABSPATH')) exit;
if (defined('CASE_STUDIES_INC_LOADED')) return;
define('CASE_STUDIES_INC_LOADED', true);

// Register Custom Post Type
add_action('init', 'cpt_register_case_study');
function cpt_register_case_study() {
    $labels = array(
        'name'               => __('Case Studies', 'custom-post-type-ui'),
        'singular_name'      => __('Case Study', 'custom-post-type-ui'),
        'menu_name'          => __('Case Studies', 'custom-post-type-ui'),
        'add_new'            => __('Add New', 'custom-post-type-ui'),
        'add_new_item'       => __('Add New Case Study', 'custom-post-type-ui'),
        'edit_item'          => __('Edit Case Study', 'custom-post-type-ui'),
        'new_item'           => __('New Case Study', 'custom-post-type-ui'),
        'view_item'          => __('View Case Study', 'custom-post-type-ui'),
        'search_items'       => __('Search Case Studies', 'custom-post-type-ui'),
        'not_found'          => __('No Case Studies found', 'custom-post-type-ui'),
        'not_found_in_trash' => __('No Case Studies found in Trash', 'custom-post-type-ui'),
    );

    $args = array(
        'label'               => __('Case Studies', 'custom-post-type-ui'),
        'labels'              => $labels,
        'menu_icon'           => 'dashicons-book',
        'description'         => 'Case studies and customer success stories',
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_rest'        => true,
        'rest_base'           => 'case-studies',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'has_archive'         => false,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'delete_with_user'    => false,
        'exclude_from_search' => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'rewrite'             => array('slug' => 'case-study', 'with_front' => true),
        'query_var'           => true,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_graphql'     => false,
    );

    register_post_type('case_study', $args);

    // Register taxonomy for categories
    register_taxonomy('case_study_category', 'case_study', array(
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
            'slug' => 'case-study-category',
            'with_front' => false,
            'hierarchical' => true
        ),
    ));
}

if (!class_exists('Case_Studies_Module')) {
    class Case_Studies_Module {

        public function __construct() {
            add_shortcode('case_studies', array($this, 'shortcode'));
            add_action('wp_ajax_case_studies_query', array($this, 'ajax_query'));
            add_action('wp_ajax_nopriv_case_studies_query', array($this, 'ajax_query'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'title'          => 'Recent',
                'title_accent'   => 'Case Studies',
                'subtitle'       => 'Explore how we have helped organizations across industries achieve their goals.',
                'per_page'       => 6,
                'excerpt_length' => 120,
                'category'       => '',
            ), $atts, 'case_studies');

            // Styles
            wp_register_style('case-studies-inline-style', false, array(), '1.0.0');
            wp_enqueue_style('case-studies-inline-style');
            wp_add_inline_style('case-studies-inline-style', $this->inline_css());

            // Scripts
            wp_register_script('case-studies-inline-script', false, array(), '1.0.0', true);
            wp_enqueue_script('case-studies-inline-script');

            $data = array(
                'restUrl'       => esc_url_raw(get_rest_url()),
                'siteUrl'       => home_url(),
                'ajaxUrl'       => admin_url('admin-ajax.php'),
                'perPage'       => max(1, (int) $atts['per_page']),
                'excerptLength' => max(1, (int) $atts['excerpt_length']),
                'category'      => sanitize_text_field($atts['category']),
            );
            wp_add_inline_script('case-studies-inline-script', 'window.CaseStudiesData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('case-studies-inline-script', $this->inline_js(), 'after');

            ob_start(); ?>
            <section id="case-studies-module" class="cs-wrap" data-cs-init="0">
                <div class="cs-header postlistHead">
                    <div class="cs-header__text comtitlestb plhText">
                        <h2 class="cs-title plhTitle">
                            <span class="cs-title__first plhTitleFirst"><?php echo esc_html($atts['title']); ?></span>
                            <span class="cs-title__accent plhTitleAccent"><?php echo esc_html($atts['title_accent']); ?></span>
                        </h2>
                        <p class="cs-subtitle plhsubTitle"><?php echo esc_html($atts['subtitle']); ?></p>
                    </div>
                    <div class="cs-header__tools plhTools">
                        <div class="cs-searchbar plhSearchbar">
                            <div class="plhinSearwrap">
                                <svg class="cs-search-icon plhsearchicon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <div class="cs-input-wrap plhinputwrap">
                                    <input type="text" id="cs-search-input" class="plhsearchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
                                    <button id="cs-clear-btn" class="cs-clear-btn plhclearbtn" type="button" title="Clear search" aria-label="Clear search">×</button>
                                </div>
                                <button id="cs-search-btn" class="cs-searchbar__btn plhSearchbarbtn" type="button">SEARCH</button>
                            </div>
                            <button id="cs-sort-toggle" class="cs-searchbar__filter plhfilterbtn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="cs-sort-menu" title="Sort">
                                <img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/02/FunnelSimple.png" alt=""/>
                            </button>
                            <div id="cs-sort-menu" class="cs-sort-menu plhsortmenu" role="menu" aria-hidden="true">
                                <button class="cs-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="cs-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="cs-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="cs-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="cs-grid" class="cs-grid" aria-live="polite"></section>

                <div class="cs-loadmore-wrap loadmore-wrap">
                    <button id="cs-loadmore" class="cs-loadmore loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="cs-loadmore__arrow loadmore__arrow"><img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/01/emore-arrow-img1.png" alt=""/></span>
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
                'post_type'      => 'case_study',
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
                        'taxonomy' => 'case_study_category',
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
                'featured' => $thumb ?: 'https://via.placeholder.com/768x432?text=Case+Study',
            );
        }

        private function inline_css() {
            return <<<CSS

.cs-empty{text-align:center;color:#777;grid-column:1 / -1;padding:48px 24px}
CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    var cfg = window.CaseStudiesData || {};
    var container = null, page = 1, totalPages = 1, loading = false, initialized = false;
    var currentSearch = '', currentOrderby = 'date', currentOrder = 'desc';

    function el(id){ return document.getElementById(id); }
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }

    function tryInit(){
        container = document.getElementById('case-studies-module');
        if (!container || initialized) return;
        if (container.getAttribute('data-cs-init') === '1') return;
        container.setAttribute('data-cs-init', '1');
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
                if (n.nodeType === 1 && (n.id === 'case-studies-module' || n.querySelector && n.querySelector('#case-studies-module'))) {
                    tryInit();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    function bindEvents(){
        var searchInput = el('cs-search-input');
        var clearBtn    = el('cs-clear-btn');
        var searchBtn   = el('cs-search-btn');
        var sortToggle  = el('cs-sort-toggle');
        var sortMenu    = el('cs-sort-menu');
        var loadMoreBtn = el('cs-loadmore');

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

            qsa('.cs-sort-menu__item', sortMenu).forEach(function(item){
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
        var searchInput = el('cs-search-input');
        currentSearch = searchInput ? searchInput.value.trim() : '';
        fetchPosts(1, true);
    }

    function fetchPosts(pageNum, replace){
        if (loading) return;
        loading = true;

        var grid        = el('cs-grid');
        var loadMoreBtn = el('cs-loadmore');

        if (replace) {
            grid.innerHTML = '<div class="cs-card--skeleton"></div><div class="cs-card--skeleton"></div><div class="cs-card--skeleton"></div>';
        }

        var params = new URLSearchParams({
            action:         'case_studies_query',
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
                    grid.innerHTML = '<p class="cs-empty">No case studies found.</p>';
                }

                loadMoreBtn.disabled = page >= totalPages;
                loading = false;
            })
            .catch(function(){
                loading = false;
                if (replace) grid.innerHTML = '<p class="cs-empty">Error loading case studies.</p>';
            });
    }

    function renderCard(post){
        return '<article class="cs-card">' +
            '<a href="'+escHtml(post.link)+'" class="cs-card__image"><img src="'+escHtml(post.featured)+'" alt="'+escHtml(post.title)+'"></a>' +
            '<div class="cs-card__body">' +
                '<h3 class="cs-card__title">'+escHtml(post.title)+'</h3>' +
                '<p class="cs-card__desc">'+escHtml(post.excerpt)+'</p>' +
                '<a href="'+escHtml(post.link)+'" class="cs-card__cta">Read More</a>' +
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

    new Case_Studies_Module();
}
