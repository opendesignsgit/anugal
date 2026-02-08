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
                'title'     => 'Recent Blogs',
                'subtitle'  => 'Browse through our recent thoughts and expert perspectives on identity and access management.',
                'per_page'  => 6,
            ), $atts, 'recent_blogs');

            // Styles
            wp_register_style('recent-blogs-inline-style', false, array(), '1.0.5');
            wp_enqueue_style('recent-blogs-inline-style');
            wp_add_inline_style('recent-blogs-inline-style', $this->inline_css());

            // Scripts
            wp_register_script('recent-blogs-inline-script', false, array(), '1.0.5', true);
            wp_enqueue_script('recent-blogs-inline-script');

            $data = array(
                'restUrl'   => esc_url_raw(get_rest_url()),               // e.g. https://domain/subdir/wp-json/
                'siteUrl'   => home_url(),                                 // e.g. https://domain/subdir
                'ajaxUrl'   => admin_url('admin-ajax.php'),                // fallback endpoint
                'perPage'   => max(1, (int) $atts['per_page']),
            );
            wp_add_inline_script('recent-blogs-inline-script', 'window.RecentBlogsData = ' . wp_json_encode($data) . ';', 'before');
            wp_add_inline_script('recent-blogs-inline-script', $this->inline_js(), 'after');

            // Markup (note the container id recent-blogs-module used by MutationObserver)
            ob_start(); ?>
            <section id="recent-blogs-module" class="rb-wrap" data-rb-init="0">
                <div class="rb-header">
                    <div class="rb-header__text">
                        <h2 class="rb-title">
                            <span class="rb-title__first"><?php echo esc_html($atts['title']); ?></span>
                            <span class="rb-title__accent"></span>
                        </h2>
                        <p class="rb-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
                    </div>
                    <div class="rb-header__tools">
                        <div class="rb-searchbar">
                            <div class="rb-input-wrap">
                                <input type="text" id="rb-search-input" class="rb-searchbar__input" placeholder="Search by Keyword" aria-label="Search by Keyword">
                                <button id="rb-clear-btn" class="rb-clear-btn" type="button" title="Clear search" aria-label="Clear search">×</button>
                            </div>
                            <button id="rb-search-btn" class="rb-searchbar__btn" type="button">SEARCH</button>
                            <button id="rb-sort-toggle" class="rb-searchbar__filter" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="rb-sort-menu" title="Sort">
                                <span class="rb-filter__icon">☰</span>
                            </button>
                            <div id="rb-sort-menu" class="rb-sort-menu" role="menu" aria-hidden="true">
                                <button class="rb-sort-menu__item" data-orderby="date" data-order="desc" role="menuitem" type="button">Newest</button>
                                <button class="rb-sort-menu__item" data-orderby="date" data-order="asc" role="menuitem" type="button">Oldest</button>
                                <button class="rb-sort-menu__item" data-orderby="title" data-order="asc" role="menuitem" type="button">Title A–Z</button>
                                <button class="rb-sort-menu__item" data-orderby="title" data-order="desc" role="menuitem" type="button">Title Z–A</button>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="rb-grid" class="rb-grid" aria-live="polite"></section>

                <div class="rb-loadmore-wrap">
                    <button id="rb-loadmore" class="rb-loadmore" disabled type="button">
                        <span>Load More</span>
                        <span class="rb-loadmore__arrow">></span>
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

            return array(
                'id'         => (int) $p->ID,
                'title'      => $title,
                'link'       => $link,
                'date'       => $date,
                'author'     => $author_name,
                'featured'   => $thumb ?: 'https://via.placeholder.com/768x432?text=Blog',
            );
        }

        private function inline_css() {
            return <<<CSS
.rb-wrap{max-width:1200px;margin:0 auto;padding:24px 16px 48px}
.rb-header{display:grid;grid-template-columns:1fr 520px;gap:24px;align-items:start}
@media (max-width:1024px){.rb-header{grid-template-columns:1fr}}
.rb-title{font-size:36px;line-height:1.2;margin:0;font-weight:800}
.rb-title__first{color:#111}
.rb-title__accent::before{content:" Blogs";color:#3E54E8}
.rb-subtitle{margin:12px 0 0;color:#555;max-width:680px}
.rb-header__tools{display:flex;justify-content:flex-end}
.rb-searchbar{display:grid;grid-template-columns:auto auto auto;gap:12px;align-items:center}
.rb-input-wrap{position:relative;display:flex;align-items:center}
.rb-searchbar__input{border:1px solid #E0E0E0;border-radius:999px;padding:12px 40px 12px 16px;font-size:14px;outline:none;min-width:360px}
@media (max-width:640px){.rb-searchbar__input{min-width:240px}}
.rb-clear-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:999px;border:1px solid #E0E0E0;background:#fff;color:#666;display:none;cursor:pointer;font-size:18px;line-height:24px;text-align:center}
.rb-clear-btn:hover{background:#F5F5F5}
.rb-searchbar__btn{background:#3E54E8;color:#fff;border:none;border-radius:12px;font-weight:700;padding:12px 16px;cursor:pointer}
.rb-searchbar__btn:hover{opacity:.95}
.rb-searchbar__filter{background:#fff;border:1px solid #E0E0E0;border-radius:12px;padding:12px 12px;cursor:pointer}
.rb-filter__icon{display:inline-block;font-size:16px}
.rb-sort-menu{position:absolute;margin-top:8px;right:16px;background:#fff;border:1px solid #E0E0E0;box-shadow:0 6px 20px rgba(0,0,0,.08);border-radius:12px;display:none;min-width:180px;z-index:10}
.rb-sort-menu[aria-hidden="false"]{display:block}
.rb-sort-menu__item{display:block;width:100%;text-align:left;padding:10px 12px;border:none;background:#fff;cursor:pointer;font-size:14px}
.rb-sort-menu__item:hover{background:#F7F7F7}
.rb-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:24px}
@media (max-width:1024px){.rb-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:640px){.rb-grid{grid-template-columns:1fr}}
.rb-card{background:#fff;border-radius:12px;border:1px solid #EEE;box-shadow:0 8px 18px rgba(0,0,0,.06);overflow:hidden}
.rb-card__image{display:block;height:180px;background:#f0f0f0}
.rb-card__image img{width:100%;height:100%;object-fit:cover}
.rb-card__body{padding:16px}
.rb-card__title{font-size:18px;line-height:1.35;margin:0 0 8px;color:#222}
.rb-card__meta{font-size:12px;color:#666;display:flex;gap:12px;margin:8px 0 12px}
.rb-card__cta{font-weight:700;font-size:13px;color:#3E54E8;text-decoration:none}
.rb-card--skeleton{height:280px;background:linear-gradient(90deg,#f2f2f2 25%,#e9e9e9 37%,#f2f2f2 63%);background-size:400% 100%;animation:rb-shine 1.2s ease infinite;border-radius:12px}
@keyframes rb-shine{0%{background-position:0% 0}100%{background-position:100% 0}}
.rb-loadmore-wrap{display:flex;justify-content:center;padding:24px 0 48px}
.rb-loadmore{display:inline-flex;align-items:center;gap:12px;background:#111827;color:#fff;border:none;border-radius:10px;padding:12px 18px;font-weight:700;cursor:pointer}
.rb-loadmore:disabled{opacity:.5;cursor:not-allowed}
.rb-loadmore__arrow{display:inline-block;background:#000;color:#fff;border-radius:8px;padding:8px 12px}
.rb-empty{text-align:center;color:#777;grid-column:1 / -1;padding:24px}
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
                '<div class="rb-card__meta"><span>'+escHtml(dateStr)+'</span><span>'+escHtml(post.author)+'</span></div>' +
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