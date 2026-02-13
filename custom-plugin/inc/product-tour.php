<?php
/**
 * Plugin Name: Product Tours (Single File, ES5-safe JS)
 * Description: Single-file plugin for Product Tours CPT, taxonomy, REST filters, and front-end UI with chips, multi/single selection, deep links, and Load More. JS rewritten to ES5 to avoid parser issues.
 * Author: opendesignsgit
 * Version: 1.0.2
 */

if (!defined('ABSPATH')) exit;

class Product_Tours_Single_File {
    const CPT            = 'product_tour';
    const CPT_SLUG       = 'product-tours';
    const CPT_REST_BASE  = 'product-tours';

    const TAX            = 'product_tour_category';
    const TAX_SLUG       = 'product-tours-category';
    const TAX_REST_BASE  = 'product-tours-category';

    public function __construct() {
        add_action('init', array($this, 'register_cpt_tax'));
        add_action('init', array($this, 'register_rewrite'));
        add_filter('post_type_link', array($this, 'cpt_permalink'), 10, 2);

        add_shortcode('product_tours', array($this, 'shortcode'));

        add_action('rest_api_init', array($this, 'allow_tax_query_rest'));

        register_activation_hook(__FILE__, function () {
            $this->register_cpt_tax();
            $this->register_rewrite();
            flush_rewrite_rules();
        });
        register_deactivation_hook(__FILE__, function () {
            flush_rewrite_rules();
        });
    }

    public function register_cpt_tax() {
        register_post_type(self::CPT, array(
            'labels' => array(
                'name'               => 'Product Tours',
                'singular_name'      => 'Product Tour',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Product Tour',
                'edit_item'          => 'Edit Product Tour',
                'new_item'           => 'New Product Tour',
                'view_item'          => 'View Product Tour',
                'search_items'       => 'Search Product Tours',
                'not_found'          => 'No Product Tours found',
                'not_found_in_trash' => 'No Product Tours found in Trash',
                'menu_name'          => 'Product Tours',
            ),
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'has_archive'   => true,
            'rewrite'       => array('slug' => self::CPT_SLUG, 'with_front' => false),
            'supports'      => array('title', 'editor', 'excerpt', 'thumbnail'),
            'show_in_rest'  => true,
            'rest_base'     => self::CPT_REST_BASE,
            'menu_icon'     => 'dashicons-playlist-video',
        ));

        register_taxonomy(self::TAX, array(self::CPT), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'              => 'Product Tour Categories',
                'singular_name'     => 'Product Tour Category',
                'search_items'      => 'Search Categories',
                'all_items'         => 'All Categories',
                'edit_item'         => 'Edit Category',
                'update_item'       => 'Update Category',
                'add_new_item'      => 'Add New Category',
                'new_item_name'     => 'New Category Name',
                'menu_name'         => 'Product Tour Categories',
            ),
            'show_ui'            => true,
            'show_admin_column'  => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => self::TAX_SLUG, 'with_front' => false),
            'show_in_rest'       => true,
            'rest_base'          => self::TAX_REST_BASE,
        ));
    }

    public function register_rewrite() {
        add_rewrite_rule('^' . self::CPT_SLUG . '/?$', 'index.php?post_type=' . self::CPT, 'top');
    }

    public function cpt_permalink($permalink, $post) {
        if ($post->post_type === self::CPT) {
            return home_url('/' . self::CPT_SLUG . '/' . $post->post_name . '/');
        }
        return $permalink;
    }

    public function allow_tax_query_rest() {
        add_filter('rest_' . self::CPT . '_collection_params', function ($params) {
            $params[self::TAX_REST_BASE] = array(
                'description' => 'Filter Product Tours by Product Tour Category IDs (comma-separated).',
                'type'        => 'string',
                'required'    => false,
            );
            return $params;
        });

        add_filter('rest_' . self::CPT . '_query', function ($args, $request) {
            $tax_param = $request->get_param(self::TAX_REST_BASE);
            if (!empty($tax_param)) {
                $ids = array_filter(array_map('absint', explode(',', $tax_param)));
                if (!empty($ids)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => self::TAX,
                            'field'    => 'term_id',
                            'terms'    => $ids,
                        )
                    );
                }
            }
            return $args;
        }, 10, 2);
    }

    public function shortcode($atts = array()) {
        $atts = shortcode_atts(array(
            'mode'      => 'multi',
            'per_page'  => 6,
            'title'     => 'Product Tours',
            'subtitle'  => 'Discover Anugal’s next‑gen IGA platform with quick, self‑guided product walkthroughs.',
            'label'     => 'Product Tours',
        ), $atts, 'product_tours');

        $css = $this->get_inline_css();
        $js  = $this->get_inline_js();

        wp_register_style('pt-inline-style', false, array(), '1.0.2');
        wp_enqueue_style('pt-inline-style');
        wp_add_inline_style('pt-inline-style', $css);

        wp_register_script('pt-inline-script', false, array(), '1.0.2', true);
        wp_enqueue_script('pt-inline-script');

        $data = array(
            'siteUrl'     => home_url(),
            'restUrl'     => esc_url_raw(get_rest_url()),
            'perPage'     => (int) $atts['per_page'],
            'mode'        => ($atts['mode'] === 'single') ? 'single' : 'multi',
            'cptRestBase' => self::CPT_REST_BASE,
            'taxRestBase' => self::TAX_REST_BASE,
            'hero'        => array(
                'kicker'   => $atts['label'],
                'title'    => $atts['title'],
                'subtitle' => $atts['subtitle'],
            ),
        );
        wp_add_inline_script('pt-inline-script', 'window.ProductToursData = ' . wp_json_encode($data) . ';', 'before');
        wp_add_inline_script('pt-inline-script', $js, 'after');

        ob_start();
        ?>
        <!--<section class="pt-hero">
            <div class="pt-hero__kicker"><?php echo esc_html($data['hero']['kicker']); ?></div>
            <h1 class="pt-hero__title"><?php echo esc_html($data['hero']['title']); ?></h1>
            <p class="pt-hero__subtitle"><?php echo esc_html($data['hero']['subtitle']); ?></p>
        </section>-->

        <section class="pt-filters">
            <div class="pt-chips" id="pt-chips"></div>
        </section>

        <section class="pt-grid" id="pt-grid" aria-live="polite"></section>

        <div class="pt-loadmore-wrap loadmore-wrap">
            <button id="pt-loadmore" class="pt-loadmore loadmore" disabled>
                <span>Load More</span>
                <span class="pt-loadmore__arrow loadmore__arrow"><img src="https://dev.opendesignsin.com/anugal-wp/wp-content/uploads/2026/01/emore-arrow-img1.png" alt=""/></span>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_inline_css() {
        return <<<CSS

CSS;
    }

    private function get_inline_js() {
        // ES5-safe: no optional chaining, no backticks, no arrow functions
        return <<<JS
(function () {
  var cfg = window.ProductToursData || {};
  var restRoot = (cfg.restUrl || '').replace(/\\/\$/, '');
  if (!restRoot && window.wpApiSettings && window.wpApiSettings.root) {
    restRoot = window.wpApiSettings.root.replace(/\\/\$/, '');
  }
  var CPT = cfg.cptRestBase;
  var TAX = cfg.taxRestBase;
  var perPage = cfg.perPage || 6;
  var selectionMode = (cfg.mode === 'single') ? 'single' : 'multi';

  var chipsEl = document.getElementById('pt-chips');
  var gridEl = document.getElementById('pt-grid');
  var loadMoreBtn = document.getElementById('pt-loadmore');

  var allTerms = [];
  var selectedSlugs = [];
  var slugToId = {};
  var currentPage = 1;
  var totalPages = 1;
  var loading = false;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  function init() {
    fetchTerms().then(function () {
      initSelectionFromURL();
      renderChips();
      fetchAndRender(true);
      bindLoadMore();
    }).catch(function (e) {
      console.error('Initialization failed', e);
    });
  }

  function getURLSelectedSlugs() {
    var params = new URLSearchParams(window.location.search);
    var allParam = (params.get('all') || '').trim();
    if (!allParam) return [];
    return allParam.split(',').map(function (s) { return s.trim(); }).filter(function (s) { return !!s; });
  }

  function setURLSelectedSlugs(slugs) {
    var url = new URL(window.location.href);
    var params = url.searchParams;
    if (slugs.length) {
      params.set('all', slugs.join(','));
    } else {
      params.delete('all');
    }
    url.search = params.toString();
    window.history.replaceState({}, '', url.toString());
  }

  function fetchTerms() {
    return new Promise(function (resolve, reject) {
      if (!restRoot) {
        reject(new Error('REST root not available'));
        return;
      }
      var url = restRoot + '/wp/v2/' + TAX + '?per_page=100';
      fetch(url).then(function (res) {
        if (!res.ok) throw new Error('Terms fetch failed: ' + res.status);
        return res.json();
      }).then(function (data) {
        allTerms = Array.isArray(data) ? data.map(function (t) {
          return { id: t.id, name: t.name, slug: t.slug };
        }) : [];
        slugToId = {};
        allTerms.forEach(function (t) { slugToId[t.slug] = t.id; });
        resolve();
      }).catch(function (e) {
        console.error('Error fetching terms', e);
        allTerms = [];
        resolve();
      });
    });
  }

  function initSelectionFromURL() {
    var initialSlugs = getURLSelectedSlugs();
    selectedSlugs = initialSlugs.filter(function (s) { return !!slugToId[s]; });
  }

  function renderChips() {
    chipsEl.innerHTML = '';
    function chip(label, slug, opts) {
      opts = opts || {};
      var btn = document.createElement('button');
      btn.className = 'pt-chip';
      btn.type = 'button';
      btn.setAttribute('data-slug', slug);
      btn.textContent = label;
      btn.setAttribute('aria-pressed', isSelected(slug) ? 'true' : 'false');
      if (opts.all) btn.classList.add('pt-chip--all');
      if (isSelected(slug)) btn.classList.add('pt-chip--active');
      btn.addEventListener('click', function () { onChipClick(slug, !!opts.all); });
      return btn;
    }
    chipsEl.appendChild(chip('ALL', '__all__', { all: true }));
    allTerms.forEach(function (t) {
      chipsEl.appendChild(chip(t.name, t.slug));
    });
  }

  function isSelected(slug) {
    if (slug === '__all__') return selectedSlugs.length === 0;
    return selectedSlugs.indexOf(slug) !== -1;
  }

  function onChipClick(slug, isAll) {
    if (isAll) {
      selectedSlugs = [];
      setURLSelectedSlugs(selectedSlugs);
      currentPage = 1;
      renderChips();
      fetchAndRender(true);
      return;
    }
    if (selectionMode === 'single') {
      selectedSlugs = [slug];
    } else {
      var idx = selectedSlugs.indexOf(slug);
      if (idx >= 0) selectedSlugs.splice(idx, 1);
      else selectedSlugs.push(slug);
    }
    setURLSelectedSlugs(selectedSlugs);
    currentPage = 1;
    renderChips();
    fetchAndRender(true);
  }

  function fetchAndRender(reset) {
    if (loading) return;
    loading = true;
    if (reset) {
      gridEl.innerHTML = '<div class="pt-card pt-card--skeleton"></div><div class="pt-card pt-card--skeleton"></div><div class="pt-card pt-card--skeleton"></div>';
      loadMoreBtn.disabled = true;
    }

    var ids = selectedSlugs.map(function (slug) { return slugToId[slug]; }).filter(function (id) { return !!id; });
    var url = restRoot + '/wp/v2/' + CPT + '?per_page=' + perPage + '&page=' + currentPage + '&_embed=1';
    if (ids.length) url += '&' + TAX + '=' + ids.join(',');

    fetch(url).then(function (res) {
      if (!res.ok) throw new Error('Posts fetch failed: ' + res.status);
      totalPages = parseInt(res.headers.get('X-WP-TotalPages') || '1', 10);
      return res.json();
    }).then(function (posts) {
      if (reset) gridEl.innerHTML = '';
      var fragment = document.createDocumentFragment();
      posts.forEach(function (p) { fragment.appendChild(renderCard(p)); });
      gridEl.appendChild(fragment);
      updateLoadMore();
    }).catch(function (e) {
      console.error('Fetch Product Tours error', e);
      if (reset) gridEl.innerHTML = '<div class="pt-empty">No product tours found.</div>';
      totalPages = currentPage;
      updateLoadMore();
    }).finally(function () {
      loading = false;
    });
  }

  function bindLoadMore() {
    loadMoreBtn.addEventListener('click', function () {
      if (loading) return;
      if (currentPage >= totalPages) return;
      currentPage += 1;
      fetchAndRender(false);
    });
  }

  function updateLoadMore() {
    loadMoreBtn.disabled = currentPage >= totalPages;
  }

  function renderCard(post) {
    var title = '';
    if (post && post.title && post.title.rendered) title = sanitizeHTML(post.title.rendered);
    var excerptRaw = '';
    if (post && post.excerpt && post.excerpt.rendered) excerptRaw = post.excerpt.rendered;
    excerptRaw = excerptRaw.replace(/<[^>]+>/g, '');
    var excerpt = truncate(excerptRaw, 160);

    var termName = 'Uncategorized';
    if (post && post._embedded && post._embedded['wp:term']) {
      var flat = [];
      for (var i = 0; i < post._embedded['wp:term'].length; i++) {
        var group = post._embedded['wp:term'][i];
        if (Array.isArray(group)) {
          for (var j = 0; j < group.length; j++) {
            flat.push(group[j]);
          }
        }
      }
      for (var k = 0; k < flat.length; k++) {
        var t = flat[k];
        if (t && t.taxonomy === TAX) { termName = t.name || termName; break; }
      }
    }

    var featured = 'https://via.placeholder.com/768x432?text=Product+Tour';
    if (post && post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0] && post._embedded['wp:featuredmedia'][0].source_url) {
      featured = post._embedded['wp:featuredmedia'][0].source_url;
    }

    var link = post && post.link ? post.link : '#';

    var card = document.createElement('article');
    card.className = 'pt-card';
    card.innerHTML =
      '<a href="' + link + '" class="pt-card__image" aria-label="' + escapeAttr(title) + '">' +
        '<img src="' + featured + '" alt="' + escapeAttr(title) + '">' +
      '</a>' +
      '<div class="pt-card__body">' +
        '<h5 class="pt-card__badge">' + escapeHTML(termName) + '</h5>' +
        '<h3 class="pt-card__title">' + escapeHTML(title) + '</h3>' +
        '<p class="pt-card__excerpt">' + escapeHTML(excerpt) + '</p>' +
        '<a href="' + link + '" class="pt-card__cta">Read More</a>' +
      '</div>';
    return card;
  }

  // Utils
  function truncate(str, n) {
    if (!str) return '';
    return str.length > n ? str.slice(0, n - 1) + '…' : str;
  }
  function sanitizeHTML(html) {
    var div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || '';
  }
  function escapeHTML(str) {
    return String(str).replace(/[&<>"']/g, function (s) {
      return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[s];
    });
  }
  function escapeAttr(str) {
    return escapeHTML(str).replace(/"/g, '&quot;');
  }
})();
JS;
    }
}

new Product_Tours_Single_File();

/**
 * USAGE:
 * 1) Save this file as wp-content/plugins/product-tour.php
 * 2) Activate the plugin in WP Admin.
 * 3) Add the shortcode to a page: [product_tours] or [product_tours mode="single"]
 * 4) Deep links: /product-tours?all=category-slug-1,category-slug-2
 * If archive/REST seems off, Settings → Permalinks → Save to flush rules.
 */