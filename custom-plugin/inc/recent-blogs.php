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
  var cfg = window.RoiCalcConfig || {};
  var derive = cfg.derive || {};
  var eff = cfg.efficiency || {};
  var base = cfg.baselines || {};
  var hourlyDefault = cfg.hourlyCostDefault || 75;

  var container = null, initialized = false;

  function el(id){ return document.getElementById(id); }

  function tryInit(){
    container = document.getElementById('roi-calculator');
    if (!container || initialized) return;
    container.setAttribute('data-roi-init', '1');
    bindDelegated();
    autoDeriveAll();
    calculateAndRender();
    initialized = true;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInit);
  } else {
    tryInit();
  }

  function autoDeriveAll(){
    var E = parseInt((el('roi-employees').value || '0'), 10);
    if (!E || E < 1) E = 1;

    var dBase = parseFloat(derive.daysPerAccessReview_base || 5);
    var dPer = parseFloat(derive.daysPerAccessReview_perEmp || 0.5);
    var daysReview = Math.max(1, Math.round(dBase + dPer * E));
    el('roi-days-per-review').value = daysReview;

    var br = Math.max(0, Math.round((derive.birthrightApps_perEmp || 2) * E));
    el('roi-birthright-apps').value = br;

    var onb = Math.max(0, Math.round((derive.onboards_perEmp || 0.5) * E));
    el('roi-onboards').value = onb;

    var offb = Math.max(0, Math.round((derive.offboards_perEmp || 0.2) * E));
    el('roi-offboards').value = offb;

    var tix = Math.max(0, Math.round((derive.tickets_perEmp || 0.6) * E));
    el('roi-tickets').value = tix;

    if (!el('roi-hourly-cost').value) el('roi-hourly-cost').value = hourlyDefault;
  }

  function calculate(){
    var E = parseInt((el('roi-employees').value || '0'), 10);
    var cycles = parseInt((el('roi-review-cycles').value || '0'), 10);
    var daysReview = parseFloat(el('roi-days-per-review').value || '0');
    var birthrightPerMonth = parseInt((el('roi-birthright-apps').value || '0'), 10);
    var onboardsPerMonth = parseInt((el('roi-onboards').value || '0'), 10);
    var offboardsPerMonth = parseInt((el('roi-offboards').value || '0'), 10);
    var ticketsPerDay = parseInt((el('roi-tickets').value || '0'), 10);
    var minutesPerTicket = parseFloat(el('roi-minutes-per-ticket').value || '0');
    var hourlyCost = parseFloat(el('roi-hourly-cost').value || hourlyDefault);

    var effReview = eff.review || 0.50;
    var effBR = eff.birthright || 0.35;
    var effOnb = eff.onboarding || 0.40;
    var effOffb = eff.offboarding || 0.50;
    var effTix = eff.tickets || 0.40;

    var brMin = base.birthright_minutes || 15;
    var onbMin = base.onboarding_minutes || 60;
    var offbMin = base.offboarding_minutes || 40;

    var reviewHoursSavedAnnual = (daysReview * 8 * cycles) * effReview;
    var brHoursSavedAnnual = ((birthrightPerMonth * 12) * brMin * effBR) / 60;
    var onbHoursSavedAnnual = ((onboardsPerMonth * 12) * onbMin * effOnb) / 60;
    var offbHoursSavedAnnual = ((offboardsPerMonth * 12) * offbMin * effOffb) / 60;
    var workDaysYear = 264;
    var tixHoursSavedAnnual = ((ticketsPerDay * workDaysYear) * minutesPerTicket * effTix) / 60;

    var totalHoursAnnual = reviewHoursSavedAnnual + brHoursSavedAnnual + onbHoursSavedAnnual + offbHoursSavedAnnual + tixHoursSavedAnnual;
    var totalHoursMonthly = totalHoursAnnual / 12;

    var costAnnual = totalHoursAnnual * hourlyCost;
    var costMonthly = costAnnual / 12;

    return {
      hoursAnnual: totalHoursAnnual,
      hoursMonthly: totalHoursMonthly,
      costAnnual: costAnnual,
      costMonthly: costMonthly,
      inputs: {
        employees: E, cycles: cycles, daysReview: daysReview, birthrightPerMonth: birthrightPerMonth,
        onboardsPerMonth: onboardsPerMonth, offboardsPerMonth: offboardsPerMonth, ticketsPerDay: ticketsPerDay,
        minutesPerTicket: minutesPerTicket, hourlyCost: hourlyCost
      }
    };
  }

  function fmtMoney(n){
    try { return Math.round(n).toLocaleString('en-US'); }
    catch(e){ var x = Math.round(n)+''; return x.replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
  }
  function fmtHours(n){ return Math.round(n); }

  function calculateAndRender(){
    var r = calculate();
    el('roi-time-annual').innerText = fmtHours(r.hoursAnnual);
    el('roi-time-monthly').innerText = fmtHours(r.hoursMonthly);
    el('roi-cost-annual').innerText = fmtMoney(r.costAnnual);
    el('roi-cost-monthly').innerText = fmtMoney(r.costMonthly);
    return r;
  }

  function openModal(){ var m = el('roi-modal'); if (m){ m.setAttribute('aria-hidden', 'false'); } }
  function closeModal(){ var m = el('roi-modal'); if (m){ m.setAttribute('aria-hidden', 'true'); } }

  function downloadReport(data){
    function loadJsPDF(cb){
      if (window.jspdf || window.jsPDF) return cb();
      var s = document.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
      s.onload = function(){ cb(); };
      s.onerror = function(){ cb(new Error('cdn_failed')); };
      document.head.appendChild(s);
    }

    loadJsPDF(function(err){
      if (!err && (window.jspdf || window.jsPDF)) {
        try {
          var jsPdf = window.jspdf || window.jsPDF;
          var doc = new jsPdf.jsPDF({ unit: 'pt', format: 'a4' });

          var y = 40, lh = 20;
          doc.setFont('helvetica','bold'); doc.setFontSize(16);
          doc.text('ROI Report', 40, y); y += lh;
          doc.setFont('helvetica','normal'); doc.setFontSize(12);
          doc.text('Name: ' + (data.user.name||''), 40, y); y += lh;
          doc.text('Email: ' + (data.user.email||''), 40, y); y += lh;
          doc.text('Company: ' + (data.user.company||''), 40, y); y += lh;
          if (data.user.role) { doc.text('Job Title: ' + data.user.role, 40, y); y += lh; }
          y += 8;

          doc.setFont('helvetica','bold'); doc.text('Inputs', 40, y); y += lh;
          doc.setFont('helvetica','normal');
          var inp = data.inputs;
          var lines = [
            'Employees: ' + inp.employees,
            'Review cycles/year: ' + inp.cycles,
            'Days per review: ' + inp.daysReview,
            'Birthright apps/month: ' + inp.birthrightPerMonth,
            'Onboardings/month: ' + inp.onboardsPerMonth,
            'Offboardings/month: ' + inp.offboardsPerMonth,
            'Access tickets/day: ' + inp.ticketsPerDay,
            'Minutes per ticket: ' + inp.minutesPerTicket,
            'Avg hourly cost ($): ' + inp.hourlyCost
          ];
          for (var i=0;i<lines.length;i++){ doc.text(lines[i], 40, y); y += lh; }
          y += 8;

          doc.setFont('helvetica','bold'); doc.text('Results', 40, y); y += lh;
          doc.setFont('helvetica','normal');
          doc.text('Time Saved (Annual): ' + fmtHours(data.hoursAnnual) + ' hrs', 40, y); y += lh;
          doc.text('Time Saved (Monthly): ' + fmtHours(data.hoursMonthly) + ' hrs', 40, y); y += lh;
          doc.text('Cost Saved (Annual): $' + fmtMoney(data.costAnnual), 40, y); y += lh;
          doc.text('Cost Saved (Monthly): $' + fmtMoney(data.costMonthly), 40, y); y += lh;

          doc.save('roi-report.pdf');
          return;
        } catch(e) { /* CSV fallback */ }
      }
      try {
        var csv = 'Section,Label,Value\n';
        var u = data.user || {};
        csv += 'User,Name,' + (u.name||'') + '\n';
        csv += 'User,Email,' + (u.email||'') + '\n';
        csv += 'User,Company,' + (u.company||'') + '\n';
        csv += 'User,Job Title,' + (u.role||'') + '\n';
        var inp2 = data.inputs;
        csv += 'Input,Employees,' + inp2.employees + '\n';
        csv += 'Input,Review cycles/year,' + inp2.cycles + '\n';
        csv += 'Input,Days per review,' + inp2.daysReview + '\n';
        csv += 'Input,Birthright apps/month,' + inp2.birthrightPerMonth + '\n';
        csv += 'Input,Onboardings/month,' + inp2.onboardsPerMonth + '\n';
        csv += 'Input,Offboardings/month,' + inp2.offboardsPerMonth + '\n';
        csv += 'Input,Access tickets/day,' + inp2.ticketsPerDay + '\n';
        csv += 'Input,Minutes per ticket,' + inp2.minutesPerTicket + '\n';
        csv += 'Input,Avg hourly cost,' + inp2.hourlyCost + '\n';
        csv += 'Result,Time Saved (Annual hrs),' + fmtHours(data.hoursAnnual) + '\n';
        csv += 'Result,Time Saved (Monthly hrs),' + fmtHours(data.hoursMonthly) + '\n';
        csv += 'Result,Cost Saved (Annual),' + fmtMoney(data.costAnnual) + '\n';
        csv += 'Result,Cost Saved (Monthly),' + fmtMoney(data.costMonthly) + '\n';

        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = 'roi-report.csv';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
      } catch(e) {}
    });
  }

  function bindDelegated(){
    // Inputs that trigger derives
    container.addEventListener('input', function(e){
      var t = e.target;
      if (!t) return;
      if (t.id === 'roi-employees') { autoDeriveAll(); }
    });

    // Clicks: open/close/calc actions
    container.addEventListener('click', function(e){
      var t = e.target;

      if (t && (t.id === 'roi-calc-btn' || (t.closest && t.closest('#roi-calc-btn')))) {
        e.preventDefault(); calculateAndRender(); return;
      }
      if (t && (t.id === 'roi-calc-arrow' || (t.closest && t.closest('#roi-calc-arrow')))) {
        e.preventDefault(); calculateAndRender(); return;
      }
      if (t && (t.id === 'roi-download-btn' || (t.closest && t.closest('#roi-download-btn')))) {
        e.preventDefault(); openModal(); return;
      }
      if (t && (t.id === 'roi-download-arrow' || (t.closest && t.closest('#roi-download-arrow')))) {
        e.preventDefault(); openModal(); return;
      }
      if (t && t.dataset && t.dataset.close === '1') {
        e.preventDefault(); closeModal(); return;
      }
    });

    // Delegated submit (so it works even if the form gets re-rendered)
    container.addEventListener('submit', function(ev){
      var form = ev.target;
      if (!form || form.id !== 'roi-modal-form') return;
      // If required inputs are empty, browser will block submit before this fires.
      ev.preventDefault();
      var name = (el('roi-name') && el('roi-name').value) || '';
      var email = (el('roi-email') && el('roi-email').value) || '';
      var company = (el('roi-company') && el('roi-company').value) || '';
      var role = (el('roi-role') && el('roi-role').value) || '';
      var res = calculate();
      var payload = {
        user: { name: name, email: email, company: company, role: role },
        inputs: res.inputs,
        hoursAnnual: res.hoursAnnual,
        hoursMonthly: res.hoursMonthly,
        costAnnual: res.costAnnual,
        costMonthly: res.costMonthly
      };
      closeModal();
      downloadReport(payload);
    });
  }
})();
JS;
}
    }

    new Recent_Blogs_Module();
}