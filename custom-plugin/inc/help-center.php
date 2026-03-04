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
                <div class="hc-header postlistHead jcenterhead">
                    <div class="hc-header__tools plhTools">
                        <div class="hc-searchbar plhSearchbar">
                            <div class="plhinSearwrap">
								<svg class="hc-search-icon plhsearchicon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<circle cx="11" cy="11" r="8"></circle>
									<path d="m21 21-4.35-4.35"></path>
								</svg>
								<div class="hc-input-wrap plhinputwrap">
									<input type="text" id="hc-search-input" class="plhsearchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
									<button id="hc-clear-btn" class="hc-clear-btn plhclearbtn" type="button" title="Clear search" aria-label="Clear search">×</button>
								</div>
								<button id="hc-search-btn" class="hc-searchbar__btn plhSearchbarbtn" type="button">SEARCH</button>
                            </div>
                            <button id="hc-sort-toggle" class="hc-searchbar__filter plhfilterbtn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="hc-sort-menu" title="Sort">
                               <img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/02/FunnelSimple.png" alt=""/>
                            </button>
                            <div id="hc-sort-menu" class="hc-sort-menu plhsortmenu" role="menu" aria-hidden="true">
                                <button class="hc-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="hc-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="hc-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="hc-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="hc-grid" class="hc-grid" aria-live="polite"></section>

                <div class="hc-loadmore-wrap loadmore-wrap">
                    <button id="hc-loadmore" class="hc-loadmore loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="hc-loadmore__arrow loadmore__arrow"><img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/01/emore-arrow-img1.png" alt=""/></span>
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

// ─────────────────────────────────────────────────────────────────────────────
// Custom link meta field for help_category taxonomy
// ─────────────────────────────────────────────────────────────────────────────

add_action('init', 'hcc_register_category_link_meta');
function hcc_register_category_link_meta() {
    register_term_meta('help_category', 'help_category_link', array(
        'type'              => 'string',
        'description'       => 'Custom link URL for this category card',
        'single'            => true,
        'sanitize_callback' => 'esc_url_raw',
        'show_in_rest'      => true,
    ));
}

// Add field to "Add New Category" form
add_action('help_category_add_form_fields', 'hcc_add_category_link_field');
function hcc_add_category_link_field() {
    ?>
    <div class="form-field">
        <label for="help_category_link"><?php esc_html_e('Custom Link URL'); ?></label>
        <input type="url" name="help_category_link" id="help_category_link" value="">
        <p class="description"><?php esc_html_e('Custom URL used as the permalink for this category card (e.g. /help-center/getting-started/).'); ?></p>
    </div>
    <?php
}

// Add field to "Edit Category" form
add_action('help_category_edit_form_fields', 'hcc_edit_category_link_field', 10, 2);
function hcc_edit_category_link_field($term) {
    $link = get_term_meta($term->term_id, 'help_category_link', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="help_category_link"><?php esc_html_e('Custom Link URL'); ?></label></th>
        <td>
            <input type="url" name="help_category_link" id="help_category_link" value="<?php echo esc_attr($link); ?>">
            <p class="description"><?php esc_html_e('Custom URL used as the permalink for this category card (e.g. /help-center/getting-started/).'); ?></p>
        </td>
    </tr>
    <?php
}

// Save meta on create and edit
add_action('created_help_category', 'hcc_save_category_link_meta');
add_action('edited_help_category', 'hcc_save_category_link_meta');
function hcc_save_category_link_meta($term_id) {
    if (isset($_POST['help_category_link'])) {
        update_term_meta($term_id, 'help_category_link', esc_url_raw(wp_unslash($_POST['help_category_link'])));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// [help_center_categories] shortcode
// ─────────────────────────────────────────────────────────────────────────────

if (!class_exists('Help_Center_Categories_Module')) {
    class Help_Center_Categories_Module {

        public function __construct() {
            add_shortcode('help_center_categories', array($this, 'shortcode'));
            add_action('wp_ajax_hcc_search_suggest', array($this, 'ajax_search_suggest'));
            add_action('wp_ajax_nopriv_hcc_search_suggest', array($this, 'ajax_search_suggest'));
        }

        public function shortcode($atts = array()) {
            $atts = shortcode_atts(array(
                'parent'       => 0,
                'orderby'      => 'name',
                'order'        => 'ASC',
                'hide_empty'   => 'false',
                'explore_text' => 'EXPLORE MORE',
            ), $atts, 'help_center_categories');

            // Enqueue styles & scripts
            wp_register_style('hcc-inline-style', false, array(), '1.0.0');
            wp_enqueue_style('hcc-inline-style');
            wp_add_inline_style('hcc-inline-style', $this->inline_css());

            wp_register_script('hcc-inline-script', false, array(), '1.0.0', true);
            wp_enqueue_script('hcc-inline-script');
            wp_add_inline_script('hcc-inline-script', 'window.HCCData=' . wp_json_encode(array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('hcc_search_suggest'),
            )) . ';', 'before');
            wp_add_inline_script('hcc-inline-script', $this->inline_js(), 'after');

            $terms = get_terms(array(
                'taxonomy'   => 'help_category',
                'hide_empty' => $atts['hide_empty'] === 'true',
                'parent'     => (int) $atts['parent'],
                'orderby'    => sanitize_key($atts['orderby']),
                'order'      => in_array(strtoupper($atts['order']), array('ASC', 'DESC')) ? strtoupper($atts['order']) : 'ASC',
            ));

            $explore_text = esc_html($atts['explore_text']);

            ob_start(); ?>
            <section class="hcc-wrap">

                <div class="hcc-search-bar-wrap">
                    <div class="hcc-search-bar">
                        <svg class="hcc-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <div class="hcc-search-input-wrap">
                            <input type="text" id="hcc-search-input" class="hcc-search-input"
                                   placeholder="Search by Keyword"
                                   aria-label="Search help articles"
                                   autocomplete="off" role="combobox"
                                   aria-expanded="false" aria-controls="hcc-suggest-list">
                            <ul id="hcc-suggest-list" class="hcc-suggest-list"
                                role="listbox" aria-label="Search suggestions"
                                style="display:none;"></ul>
                        </div>
                        <button id="hcc-search-btn" class="hcc-search-btn" type="button">SEARCH</button>
                    </div>
                    <button id="hcc-filter-btn" class="hcc-filter-btn" type="button" aria-label="Filter">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="4" y1="6" x2="20" y2="6"></line>
                            <line x1="7" y1="12" x2="17" y2="12"></line>
                            <line x1="10" y1="18" x2="14" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <?php if (!empty($terms) && !is_wp_error($terms)): ?>
                    <div class="hcc-grid">
                        <?php foreach ($terms as $term):
                            $meta_link   = get_term_meta($term->term_id, 'help_category_link', true);
                            $term_link   = get_term_link($term);
                            $card_href   = !empty($meta_link) ? $meta_link : (is_wp_error($term_link) ? '#' : $term_link);
                            $description = $term->description;
                            ?>
                            <article class="hcc-card">
                                <div class="hcc-card__icon" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                                    </svg>
                                </div>
                                <h3 class="hcc-card__title"><?php echo esc_html($term->name); ?></h3>
                                <hr class="hcc-card__divider">
                                <p class="hcc-card__desc"><?php echo esc_html($description ?: ''); ?></p>
                                <a href="<?php echo esc_url($card_href); ?>" class="hcc-card__cta"><?php echo $explore_text; ?></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="hcc-empty">No categories found.</p>
                <?php endif; ?>

            </section>
            <?php
            return ob_get_clean();
        }

        /**
         * AJAX handler: live search suggestions from help_center posts
         */
        public function ajax_search_suggest() {
            check_ajax_referer('hcc_search_suggest', 'nonce');

            $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

            if (empty($search)) {
                wp_send_json(array());
                return;
            }

            $posts = get_posts(array(
                'post_type'      => 'help_center',
                'post_status'    => 'publish',
                'posts_per_page' => 8,
                's'              => $search,
                'orderby'        => 'relevance',
            ));

            $results = array();
            foreach ($posts as $p) {
                $results[] = array(
                    'title' => get_the_title($p),
                    'link'  => get_permalink($p),
                );
            }

            wp_send_json($results);
        }

        private function inline_css() {
            return <<<CSS
/* ── Help Center Categories Shortcode ── */
.hcc-wrap{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}

/* Search bar */
.hcc-search-bar-wrap{display:flex;align-items:center;gap:12px;max-width:680px;margin:0 auto 48px}
.hcc-search-bar{display:flex;align-items:center;flex:1;background:#fff;border:1.5px solid #E0E3E8;border-radius:8px;height:54px;padding:0 0 0 16px;overflow:visible;position:relative}
.hcc-search-icon{color:#888;flex-shrink:0;margin-right:10px}
.hcc-search-input-wrap{flex:1;align-self:stretch;display:flex;align-items:center}
.hcc-search-input{width:100%;border:none;outline:none;font-size:15px;color:#333;background:transparent;padding:0}
.hcc-search-input::placeholder{color:#999}
.hcc-search-btn{height:54px;padding:0 28px;background:#3E54E8;color:#fff;border:none;font-size:13px;font-weight:700;letter-spacing:.8px;cursor:pointer;flex-shrink:0;border-radius:0 6px 6px 0;transition:background .2s}
.hcc-search-btn:hover{background:#2d44d0}
.hcc-filter-btn{width:54px;height:54px;background:#fff;border:1.5px solid #E0E3E8;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#555;flex-shrink:0;transition:border-color .2s}
.hcc-filter-btn:hover{border-color:#3E54E8;color:#3E54E8}

/* Suggestions dropdown — anchored to .hcc-search-bar (position:relative parent) */
.hcc-suggest-list{position:absolute;top:calc(100% + 6px);left:0;right:0;background:#fff;border:1.5px solid #E0E3E8;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.1);z-index:9999;max-height:300px;overflow-y:auto;list-style:none;margin:0;padding:4px 0}
.hcc-suggest-item{display:block;padding:11px 16px;font-size:14px;color:#333;text-decoration:none;cursor:pointer;border-left:3px solid transparent;transition:background .12s,color .12s,border-color .12s;list-style:none}
.hcc-suggest-item:hover,.hcc-suggest-item--active{background:#F3F5FF;color:#3E54E8;border-left-color:#3E54E8}

/* Cards grid */
.hcc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.hcc-card{background:#fff;border-radius:12px;padding:28px 28px 24px;border:1.5px solid #EAECF0;display:flex;flex-direction:column;transition:box-shadow .2s,border-color .2s}
.hcc-card:hover{box-shadow:0 4px 20px rgba(62,84,232,.1);border-color:#C5CCF7}
.hcc-card__icon{width:50px;height:50px;background:#E8EBFF;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#3E54E8;margin-bottom:20px;flex-shrink:0}
.hcc-card__title{font-size:22px;font-weight:800;color:#0D0E14;margin:0 0 14px;line-height:1.25;letter-spacing:-.3px}
.hcc-card__divider{border:none;border-top:1.5px solid #F0F1F5;margin:0 0 16px}
.hcc-card__desc{font-size:15px;color:#6B7280;line-height:1.65;flex:1;margin:0 0 24px}
.hcc-card__cta{font-size:12px;font-weight:700;letter-spacing:1.2px;color:#3E54E8;text-decoration:none;text-transform:uppercase;margin-top:auto;display:inline-block}
.hcc-card__cta:hover{text-decoration:underline}
.hcc-empty{text-align:center;color:#999;font-size:15px;padding:40px 0}

@media(max-width:1024px){.hcc-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.hcc-grid{grid-template-columns:1fr}.hcc-search-bar-wrap{max-width:100%}}
CSS;
        }

        private function inline_js() {
            return <<<'JS'
(function(){
    'use strict';
    var cfg = window.HCCData || {};
    var input      = document.getElementById('hcc-search-input');
    var suggestBox = document.getElementById('hcc-suggest-list');
    var searchBtn  = document.getElementById('hcc-search-btn');

    if (!input || !suggestBox) return;

    var suggestData   = [];
    var activeIdx     = -1;
    var debounceTimer = null;
    var currentCtrl   = null;

    // ── Fetch suggestions ──────────────────────────────────────────────────
    function fetchSuggestions(query, callback) {
        if (!query) { hideSuggestions(); return; }

        var params = new URLSearchParams({
            action: 'hcc_search_suggest',
            s:      query,
            nonce:  cfg.nonce || ''
        });

        // Abort any in-flight request
        if (currentCtrl && currentCtrl.abort) currentCtrl.abort();
        var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
        currentCtrl = ctrl;

        fetch(cfg.ajaxUrl + '?' + params.toString(), ctrl ? { signal: ctrl.signal } : {})
            .then(function(r){ return r.json(); })
            .then(function(data){
                suggestData = Array.isArray(data) ? data : [];
                renderSuggestions(suggestData);
                if (typeof callback === 'function') callback(suggestData);
            })
            .catch(function(){});
    }

    // ── Render suggestion list ─────────────────────────────────────────────
    function renderSuggestions(items) {
        if (!items.length) { hideSuggestions(); return; }

        var html = '';
        items.forEach(function(item, idx){
            html += '<li class="hcc-suggest-item" role="option" data-idx="' + idx + '" data-href="' + escAttr(item.link) + '">' + escHtml(item.title) + '</li>';
        });
        suggestBox.innerHTML = html;
        suggestBox.style.display = 'block';
        input.setAttribute('aria-expanded', 'true');
        activeIdx = -1;

        suggestBox.querySelectorAll('.hcc-suggest-item').forEach(function(el){
            el.addEventListener('mousedown', function(e){
                e.preventDefault();
                window.location.href = this.getAttribute('data-href');
            });
        });
    }

    function hideSuggestions() {
        suggestBox.style.display = 'none';
        suggestBox.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        activeIdx = -1;
        suggestData = [];
    }

    function updateActive() {
        var items = suggestBox.querySelectorAll('.hcc-suggest-item');
        items.forEach(function(el, idx){
            el.classList.toggle('hcc-suggest-item--active', idx === activeIdx);
        });
    }

    // ── Navigate to first suggestion or active item ────────────────────────
    function doNavigate() {
        var items = suggestBox.querySelectorAll('.hcc-suggest-item');
        if (activeIdx >= 0 && items[activeIdx]) {
            window.location.href = items[activeIdx].getAttribute('data-href');
        } else if (suggestData.length > 0) {
            window.location.href = suggestData[0].link;
        }
    }

    // ── Event listeners ────────────────────────────────────────────────────
    input.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        var val = this.value.trim();
        if (!val) { hideSuggestions(); return; }
        debounceTimer = setTimeout(function(){ fetchSuggestions(val); }, 250);
    });

    input.addEventListener('keydown', function(e){
        var items = suggestBox.querySelectorAll('.hcc-suggest-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, items.length - 1);
            updateActive();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, -1);
            updateActive();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            doNavigate();
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    // 200ms delay allows mousedown on suggestion items to fire before blur hides the list
    input.addEventListener('blur', function(){ setTimeout(hideSuggestions, 200); });

    if (searchBtn) {
        searchBtn.addEventListener('click', function(){
            var val = input.value.trim();
            if (val && !suggestData.length) {
                // Suggestions not yet loaded — fetch then navigate in the callback
                fetchSuggestions(val, function(data){
                    if (data.length) window.location.href = data[0].link;
                });
            } else {
                doNavigate();
            }
        });
    }

    // Close on outside click
    document.addEventListener('click', function(e){
        if (!e.target.closest || !e.target.closest('.hcc-search-bar-wrap')) {
            hideSuggestions();
        }
    });

    // ── Helpers ────────────────────────────────────────────────────────────
    function escHtml(s){
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function escAttr(s){
        if (!s) return '';
        return String(s).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
})();
JS;
        }
    }

    new Help_Center_Categories_Module();
}
