<?php
/**
 * ROI Calculator module (include from your plugin index.php)
 * - Auto-derives inputs from No. of Employees
 * - Calculates Time Saved and Cost Saved (monthly + annual)
 * - "Download Report" opens a form modal; on submit, generates PDF (jsPDF CDN) with CSV fallback
 */

if (!defined('ABSPATH')) exit;
if (defined('ROI_CALC_INC_LOADED')) return;
define('ROI_CALC_INC_LOADED', true);

if (!class_exists('ROI_Calculator_Module')) {
  class ROI_Calculator_Module {

    public function __construct() {
      add_shortcode('roi_calculator', array($this, 'shortcode'));
    }

    public function shortcode($atts = array()) {
      $atts = shortcode_atts(array(
        'title'    => 'Company',
        'accent'   => 'Profile',
      ), $atts, 'roi_calculator');

      // Styles
      wp_register_style('roi-calculator-inline-style', false, array(), '1.0.1');
      wp_enqueue_style('roi-calculator-inline-style');
      wp_add_inline_style('roi-calculator-inline-style', $this->inline_css());

      // Scripts
      wp_register_script('roi-calculator-inline-script', false, array(), '1.0.1', true);
      wp_enqueue_script('roi-calculator-inline-script');

      // Config for logic; tweak factors as needed
      $cfg = array(
        'derive' => array(
          'daysPerAccessReview_base' => 5,
          'daysPerAccessReview_perEmp' => 0.5,
          'birthrightApps_perEmp' => 2.0,
          'onboards_perEmp' => 0.5,
          'offboards_perEmp' => 0.2,
          'tickets_perEmp' => 0.6
        ),
        'efficiency' => array(
          'review' => 0.50,
          'birthright' => 0.35,
          'onboarding' => 0.40,
          'offboarding' => 0.50,
          'tickets' => 0.40
        ),
        'baselines' => array(
          'birthright_minutes' => 15,
          'onboarding_minutes' => 60,
          'offboarding_minutes' => 40
        ),
        'hourlyCostDefault' => 75.0
      );
      wp_add_inline_script('roi-calculator-inline-script', 'window.RoiCalcConfig = ' . wp_json_encode($cfg) . ';', 'before');
      wp_add_inline_script('roi-calculator-inline-script', $this->inline_js(), 'after');

      // Markup
      ob_start(); ?>
      <section id="roi-calculator" class="roi-wrap" data-roi-init="0">
        <div class="roi-grid">
          <div class="roi-left">
            <h2 class="roi-title">
              <span class="roi-title__main"><?php echo esc_html($atts['title']); ?></span>
              <span class="roi-title__accent"><?php echo esc_html($atts['accent']); ?></span>
            </h2>

            <div class="roi-form">
              <div class="roi-row">
                <label class="roi-label" for="roi-employees">No. of Employees</label>
                <input id="roi-employees" type="number" min="1" step="1" value="10" class="roi-input">
                <label class="roi-label" for="roi-review-cycles">Access Review cycles per year</label>
                <input id="roi-review-cycles" type="number" min="1" step="1" value="1" class="roi-input">
              </div>

              <div class="roi-row">
                <label class="roi-label" for="roi-days-per-review">Days spent per Access Review</label>
                <input id="roi-days-per-review" type="number" min="1" step="1" value="10" class="roi-input">
                <label class="roi-label" for="roi-birthright-apps">No. of Birthright Applications (per month)</label>
                <input id="roi-birthright-apps" type="number" min="0" step="1" value="20" class="roi-input">
              </div>

              <div class="roi-row">
                <label class="roi-label" for="roi-onboards">No. of Onboardings per month</label>
                <input id="roi-onboards" type="number" min="0" step="1" value="5" class="roi-input">
                <label class="roi-label" for="roi-offboards">No. of Offboardings per month</label>
                <input id="roi-offboards" type="number" min="0" step="1" value="2" class="roi-input">
              </div>

              <div class="roi-row">
                <label class="roi-label" for="roi-tickets">Daily Access Tickets</label>
                <input id="roi-tickets" type="number" min="0" step="1" value="6" class="roi-input">
                <label class="roi-label" for="roi-minutes-per-ticket">Minutes per Access Ticket</label>
                <input id="roi-minutes-per-ticket" type="number" min="1" step="1" value="10" class="roi-input">
              </div>

              <div class="roi-row">
                <label class="roi-label" for="roi-hourly-cost">Avg hourly cost ($)</label>
                <input id="roi-hourly-cost" type="number" min="1" step="1" value="75" class="roi-input">
                <div class="roi-spacer"></div>
              </div>

              <div class="roi-actions">
                <button id="roi-calc-btn" class="roi-btn roi-btn--primary" type="button">CALCULATE ROI</button>
                <button id="roi-calc-arrow" class="roi-btn roi-btn--arrow" type="button">></button>
              </div>
            </div>
          </div>

          <div class="roi-right">
            <div class="roi-card">
              <div class="roi-card__grid">
                <div class="roi-card__cell">
                  <div class="roi-metric__label">Cost Saved (Annual)</div>
                  <div class="roi-metric__value"><span>$</span><span id="roi-cost-annual">0</span></div>
                </div>
                <div class="roi-card__cell">
                  <div class="roi-metric__label">Cost Saved (Monthly)</div>
                  <div class="roi-metric__value"><span>$</span><span id="roi-cost-monthly">0</span></div>
                </div>
                <div class="roi-card__cell">
                  <div class="roi-metric__label">Time Saved (Annual)</div>
                  <div class="roi-metric__value"><span id="roi-time-annual">0</span> <span class="roi-metric__suffix">hrs</span></div>
                </div>
                <div class="roi-card__cell">
                  <div class="roi-metric__label">Time Saved (Monthly)</div>
                  <div class="roi-metric__value"><span id="roi-time-monthly">0</span> <span class="roi-metric__suffix">hrs</span></div>
                </div>
              </div>

              <div class="roi-download">
                <button id="roi-download-btn" class="roi-btn roi-btn--download" type="button">DOWNLOAD REPORT</button>
                <button id="roi-download-arrow" class="roi-btn roi-btn--arrow" type="button">></button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal -->
        <div id="roi-modal" class="roi-modal" aria-hidden="true" role="dialog" aria-modal="true">
          <div class="roi-modal__overlay" data-close="1"></div>
          <div class="roi-modal__dialog">
            <button class="roi-modal__close" type="button" data-close="1">x</button>
            <h3 class="roi-modal__title">Download ROI Report</h3>
            <form id="roi-modal-form" class="roi-modal__form">
              <div class="roi-modal__row">
                <label for="roi-name">Full Name</label>
                <input id="roi-name" type="text" required>
              </div>
              <div class="roi-modal__row">
                <label for="roi-email">Work Email</label>
                <input id="roi-email" type="email" required>
              </div>
              <div class="roi-modal__row">
                <label for="roi-company">Company</label>
                <input id="roi-company" type="text" required>
              </div>
              <div class="roi-modal__row">
                <label for="roi-role">Job Title</label>
                <input id="roi-role" type="text">
              </div>
              <div class="roi-modal__actions">
                <button id="roi-submit-report" class="roi-btn roi-btn--primary" type="submit">Generate & Download</button>
              </div>
            </form>
          </div>
        </div>
      </section>
      <?php
      return ob_get_clean();
    }

    private function inline_css() {
      return <<<'CSS'
.roi-wrap{max-width:1200px;margin:0 auto;padding:24px 16px 64px}
.roi-grid{display:grid;grid-template-columns:1fr 460px;gap:32px}
@media (max-width:1024px){.roi-grid{grid-template-columns:1fr}}
.roi-title{font-size:34px;font-weight:800;margin:0 0 12px}
.roi-title__accent{color:#3E54E8;margin-left:8px}
.roi-form{display:flex;flex-direction:column;gap:14px}
.roi-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.roi-label{font-size:14px;color:#444;margin-bottom:6px;display:block}
.roi-input{border:1px solid #E0E0E0;border-radius:10px;padding:12px 14px;font-size:14px;outline:none;background:#fff}
.roi-spacer{height:0}
.roi-actions{display:flex;gap:12px;margin-top:10px}
.roi-btn{border:none;border-radius:10px;padding:12px 18px;font-weight:700;cursor:pointer}
.roi-btn--primary{background:#111827;color:#fff}
.roi-btn--arrow{background:#000;color:#fff;border-radius:8px}
.roi-btn--download{background:#3E54E8;color:#fff;border-radius:12px}
.roi-right{display:flex;align-items:flex-start}
.roi-card{width:100%;background:linear-gradient(135deg, #2C3E96 0%, #6B57E9 100%);border-radius:14px;color:#fff;box-shadow:0 12px 24px rgba(0,0,0,0.2);padding:24px}
.roi-card__grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;border-bottom:1px solid rgba(255,255,255,0.2);padding-bottom:18px;margin-bottom:18px}
.roi-metric__label{font-size:13px;opacity:.85;margin-bottom:6px}
.roi-metric__value{font-size:28px;font-weight:800;display:flex;align-items:baseline;gap:6px}
.roi-metric__suffix{font-size:14px;font-weight:700;opacity:.85}
.roi-download{display:flex;gap:12px;justify-content:flex-start;margin-top:8px}
.roi-modal{position:fixed;inset:0;display:none}
.roi-modal[aria-hidden="false"]{display:block}
.roi-modal__overlay{position:absolute;inset:0;background:rgba(0,0,0,.5)}
.roi-modal__dialog{position:relative;margin:60px auto;background:#fff;border-radius:12px;max-width:520px;padding:20px}
.roi-modal__close{position:absolute;right:10px;top:10px;width:28px;height:28px;border-radius:999px;border:1px solid #ddd;background:#fff;cursor:pointer}
.roi-modal__title{margin:0 0 12px;font-size:20px;font-weight:800;color:#111}
.roi-modal__form{display:flex;flex-direction:column;gap:12px}
.roi-modal__row label{display:block;font-size:13px;color:#444;margin-bottom:6px}
.roi-modal__row input{width:100%;border:1px solid #E0E0E0;border-radius:10px;padding:10px 12px;font-size:14px}
.roi-modal__actions{display:flex;justify-content:flex-end;margin-top:8px}
CSS;
    }

    private function inline_js() {
      // NOWDOC to prevent PHP interpolation; ES5-safe
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
    catch(e){ var x = Math.round(n)+''; return x.replace(/\\B(?=(\\d{3})+(?!\\d))/g, ','); }
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
        } catch(e) { /* fallthrough to CSV */ }
      }
      try {
        var csv = 'Section,Label,Value\\n';
        var u = data.user || {};
        csv += 'User,Name,' + (u.name||'') + '\\n';
        csv += 'User,Email,' + (u.email||'') + '\\n';
        csv += 'User,Company,' + (u.company||'') + '\\n';
        csv += 'User,Job Title,' + (u.role||'') + '\\n';
        var inp2 = data.inputs;
        csv += 'Input,Employees,' + inp2.employees + '\\n';
        csv += 'Input,Review cycles/year,' + inp2.cycles + '\\n';
        csv += 'Input,Days per review,' + inp2.daysReview + '\\n';
        csv += 'Input,Birthright apps/month,' + inp2.birthrightPerMonth + '\\n';
        csv += 'Input,Onboardings/month,' + inp2.onboardsPerMonth + '\\n';
        csv += 'Input,Offboardings/month,' + inp2.offboardsPerMonth + '\\n';
        csv += 'Input,Access tickets/day,' + inp2.ticketsPerDay + '\\n';
        csv += 'Input,Minutes per ticket,' + inp2.minutesPerTicket + '\\n';
        csv += 'Input,Avg hourly cost,' + inp2.hourlyCost + '\\n';
        csv += 'Result,Time Saved (Annual hrs),' + fmtHours(data.hoursAnnual) + '\\n';
        csv += 'Result,Time Saved (Monthly hrs),' + fmtHours(data.hoursMonthly) + '\\n';
        csv += 'Result,Cost Saved (Annual),' + fmtMoney(data.costAnnual) + '\\n';
        csv += 'Result,Cost Saved (Monthly),' + fmtMoney(data.costMonthly) + '\\n';

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
    container.addEventListener('input', function(e){
      var t = e.target;
      if (!t) return;
      if (t.id === 'roi-employees') {
        autoDeriveAll();
      }
    });

    container.addEventListener('click', function(e){
      var t = e.target;

      if (t && (t.id === 'roi-calc-btn' || (t.closest && t.closest('#roi-calc-btn')))) {
        e.preventDefault();
        calculateAndRender();
        return;
      }
      if (t && (t.id === 'roi-calc-arrow' || (t.closest && t.closest('#roi-calc-arrow')))) {
        e.preventDefault();
        calculateAndRender();
        return;
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

    var form = el('roi-modal-form');
    if (form) {
      form.addEventListener('submit', function(ev){
        ev.preventDefault();
        var name = (el('roi-name').value || '');
        var email = (el('roi-email').value || '');
        var company = (el('roi-company').value || '');
        var role = (el('roi-role').value || '');
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
  }
})();
JS;
    }
  }

  new ROI_Calculator_Module();
}