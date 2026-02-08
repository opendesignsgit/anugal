<?php
/**
 * ROI Calculator module (include from your plugin index.php)
 * - Implements the complete Anugal ROI calculation algorithm
 * - Calculates subscription costs, implementation costs, and ROI
 * - Supports region-based currency display (EUR/USD)
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
      wp_register_style('roi-calculator-inline-style', false, array(), '1.0.2');
      wp_enqueue_style('roi-calculator-inline-style');
      wp_add_inline_style('roi-calculator-inline-style', $this->inline_css());

      // Scripts
      wp_register_script('roi-calculator-inline-script', false, array(), '1.0.2', true);
      wp_enqueue_script('roi-calculator-inline-script');
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

            <form id="roi-form" class="roi-form" novalidate>
              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-name">Name<span class="roi-required">*</span></label>
                  <input id="roi-name" type="text" class="roi-input" required>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-phone">Phone Number<span class="roi-required">*</span></label>
                  <input id="roi-phone" type="tel" class="roi-input" required>
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-email">Work Email<span class="roi-required">*</span></label>
                  <input id="roi-email" type="email" class="roi-input" required>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-company">Company Name<span class="roi-required">*</span></label>
                  <input id="roi-company" type="text" class="roi-input" required>
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-region">Region<span class="roi-required">*</span></label>
                  <select id="roi-region" class="roi-input roi-select" required>
                    <option value="">Select Region</option>
                    <option value="US">United States</option>
                    <option value="EU">Europe</option>
                    <option value="UK">United Kingdom</option>
                    <option value="APAC">Asia Pacific</option>
                    <option value="MEA">Middle East & Africa</option>
                    <option value="LATAM">Latin America</option>
                  </select>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-employees">Total Number of Employees<span class="roi-required">*</span></label>
                  <input id="roi-employees" type="number" min="1" step="1" class="roi-input" required>
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-apps">No. of Applications you want to Govern<span class="roi-required">*</span></label>
                  <input id="roi-apps" type="number" min="1" step="1" class="roi-input" required>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-am-percent">% of People Who Operate or Approve Access</label>
                  <input id="roi-am-percent" type="number" min="0" max="100" step="1" class="roi-input" placeholder="Default: 10%">
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-cli-percent">% of Identities Tracked for Audit only</label>
                  <input id="roi-cli-percent" type="number" min="0" max="100" step="1" class="roi-input" placeholder="Default: 0%">
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-review-cycles">How often do you Run Access Reviews?<span class="roi-required">*</span></label>
                  <select id="roi-review-cycles" class="roi-input roi-select" required>
                    <option value="">Select Frequency</option>
                    <option value="1">Once a year</option>
                    <option value="2">Twice a year</option>
                    <option value="4">Quarterly</option>
                    <option value="12">Monthly</option>
                  </select>
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-days-per-review">Approximate Days Spent Per Access Review Cycle</label>
                  <input id="roi-days-per-review" type="number" min="1" max="30" step="1" class="roi-input" placeholder="Default: 7 days">
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-daily-tickets">Approximate No. of Access related Tickets Per Day</label>
                  <input id="roi-daily-tickets" type="number" min="0" step="1" class="roi-input" placeholder="Auto-derived if empty">
                </div>
              </div>

              <div class="roi-actions">
                <button id="roi-calc-btn" class="roi-btn roi-btn--primary" type="button">CALCULATE ROI</button>
                <button id="roi-calc-arrow" class="roi-btn roi-btn--arrow" type="button" aria-label="Calculate ROI">&rarr;</button>
              </div>

              <div id="roi-errors" class="roi-errors" aria-live="polite"></div>
            </form>
          </div>

          <div class="roi-right">
            <div class="roi-card">
              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span class="roi-card__currency" id="roi-efficiency-currency">$</span>
                  <span id="roi-efficiency-value">0</span>
                </div>
                <div class="roi-card__label">Operational Efficiency<br>Gained</div>
                <div class="roi-card__sublabel" id="roi-efficiency-hours"></div>
              </div>
              <div class="roi-card__divider"></div>

              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span class="roi-card__currency" id="roi-subscription-currency">€</span>
                  <span id="roi-subscription-value">0</span>
                </div>
                <div class="roi-card__label">Subscription Cost<br>(Annual)</div>
                <div class="roi-card__sublabel" id="roi-subscription-alt"></div>
              </div>
              <div class="roi-card__divider"></div>

              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span class="roi-card__currency" id="roi-impl-currency">€</span>
                  <span id="roi-impl-value">0</span>
                </div>
                <div class="roi-card__label">Implementation Cost<br>(One-Time)</div>
                <div class="roi-card__sublabel" id="roi-impl-alt"></div>
              </div>
              <div class="roi-card__divider"></div>

              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span id="roi-year1-value">0</span>
                  <span class="roi-card__percent">%</span>
                </div>
                <div class="roi-card__label">Return on Investment<br>Year 1</div>
                <div class="roi-card__sublabel" id="roi-year1-net"></div>
              </div>
              <div class="roi-card__divider"></div>

              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span id="roi-3year-value">0</span>
                  <span class="roi-card__percent">%</span>
                </div>
                <div class="roi-card__label">Return on Investment<br>3 Years</div>
                <div class="roi-card__sublabel" id="roi-3year-net"></div>
              </div>
              <div class="roi-card__divider"></div>

              <div class="roi-card__item">
                <div class="roi-card__value roi-card__value--small">
                  <span id="roi-payback-value">-</span>
                </div>
                <div class="roi-card__label">Payback Period</div>
                <div class="roi-card__sublabel" id="roi-payback-note"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <?php
      return ob_get_clean();
    }

    private function inline_css() {
      return <<<'CSS'
.roi-wrap{max-width:1200px;margin:0 auto;padding:24px 16px 64px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif}
.roi-grid{display:grid;grid-template-columns:1fr 380px;gap:48px;align-items:start}
@media (max-width:1024px){.roi-grid{grid-template-columns:1fr;gap:32px}}
.roi-title{font-size:36px;font-weight:800;margin:0 0 24px;color:#111}
.roi-title__accent{color:#3E54E8;margin-left:8px}
.roi-form{display:flex;flex-direction:column;gap:18px}
.roi-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media (max-width:600px){.roi-row{grid-template-columns:1fr}}
.roi-field{display:flex;flex-direction:column}
.roi-label{font-size:14px;color:#333;margin-bottom:8px;font-weight:500}
.roi-required{color:#E53935;margin-left:2px}
.roi-input{border:1px solid #E0E0E0;border-radius:8px;padding:12px 14px;font-size:14px;outline:none;background:#fff;transition:border-color 0.2s,box-shadow 0.2s}
.roi-input:focus{border-color:#3E54E8;box-shadow:0 0 0 3px rgba(62,84,232,0.1)}
.roi-input.roi-input--error{border-color:#E53935}
.roi-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px}
.roi-actions{display:flex;gap:12px;margin-top:8px}
.roi-btn{border:none;border-radius:8px;padding:14px 24px;font-weight:700;cursor:pointer;font-size:14px;transition:background-color 0.2s,transform 0.1s}
.roi-btn:active{transform:scale(0.98)}
.roi-btn--primary{background:#111827;color:#fff}
.roi-btn--primary:hover{background:#1F2937}
.roi-btn--arrow{background:#111827;color:#fff;width:48px;height:48px;display:flex;align-items:center;justify-content:center;padding:0;font-size:18px}
.roi-btn--arrow:hover{background:#1F2937}
.roi-errors{margin-top:12px;color:#E53935;font-size:13px;min-height:20px}
.roi-right{display:flex;align-items:flex-start;position:sticky;top:24px}
@media (max-width:1024px){.roi-right{position:static}}
.roi-card{width:100%;background:linear-gradient(160deg, #1E3A8A 0%, #4338CA 50%, #6366F1 100%);border-radius:16px;color:#fff;box-shadow:0 16px 40px rgba(0,0,0,0.25);padding:28px 24px;display:flex;flex-direction:column}
.roi-card__item{text-align:center;padding:16px 0}
.roi-card__divider{height:1px;background:rgba(255,255,255,0.15);margin:0 -24px}
.roi-card__value{font-size:42px;font-weight:800;display:flex;align-items:baseline;justify-content:center;gap:4px;line-height:1.1}
.roi-card__value--small{font-size:28px}
.roi-card__currency{font-size:20px;font-weight:600;opacity:0.9}
.roi-card__percent{font-size:20px;font-weight:600;opacity:0.9;margin-left:2px}
.roi-card__label{font-size:14px;opacity:0.9;margin-top:8px;line-height:1.4}
.roi-card__sublabel{font-size:12px;opacity:0.7;margin-top:4px}
CSS;
    }

    private function inline_js() {
      return <<<'JS'
(function(){
  "use strict";

  // Constants from the algorithm specification
  var CONSTANTS = {
    DEFAULT_AM_PERCENT: 0.10,
    DEFAULT_CLI_PERCENT: 0.00,
    DEFAULT_REVIEW_CYCLES: 2,
    DEFAULT_DAYS_PER_REVIEW: 7,
    HOURS_PER_DAY: 8,
    WORK_DAYS_PER_YEAR: 260,
    AVG_TICKET_MINUTES: 15,
    TICKET_EFFICIENCY_GAIN: 0.30,
    REVIEW_EFFICIENCY_GAIN: 0.40,
    REVIEW_PARTICIPATION_FACTOR: 0.35,
    COST_PER_HOUR_EUR: 50,
    TICKETS_PER_100_EMPLOYEES: 2,
    REVIEWER_POOL_PERCENT: 0.10,
    // EUR to USD rate for estimation purposes only - actual rates may vary
    EUR_TO_USD_RATE: 1.08
  };

  var container = null;
  var initialized = false;

  function el(id) {
    return document.getElementById(id);
  }

  function tryInit() {
    container = document.getElementById('roi-calculator');
    if (!container || initialized) return;
    container.setAttribute('data-roi-init', '1');
    bindEvents();
    initialized = true;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInit);
  } else {
    tryInit();
  }

  function getInputs() {
    return {
      region: (el('roi-region').value || '').trim(),
      employee_count: parseInt(el('roi-employees').value, 10) || 0,
      connected_apps: parseInt(el('roi-apps').value, 10) || 0,
      am_percent: el('roi-am-percent').value !== '' ? parseFloat(el('roi-am-percent').value) / 100 : null,
      cli_percent: el('roi-cli-percent').value !== '' ? parseFloat(el('roi-cli-percent').value) / 100 : null,
      review_cycles_per_year: parseInt(el('roi-review-cycles').value, 10) || 0,
      days_per_review: el('roi-days-per-review').value !== '' ? parseInt(el('roi-days-per-review').value, 10) : null,
      daily_access_tickets: el('roi-daily-tickets').value !== '' ? parseInt(el('roi-daily-tickets').value, 10) : null
    };
  }

  function applyDefaults(inputs) {
    var result = Object.assign({}, inputs);
    
    if (result.am_percent === null) {
      result.am_percent = CONSTANTS.DEFAULT_AM_PERCENT;
    }
    if (result.cli_percent === null) {
      result.cli_percent = CONSTANTS.DEFAULT_CLI_PERCENT;
    }
    if (!result.review_cycles_per_year) {
      result.review_cycles_per_year = CONSTANTS.DEFAULT_REVIEW_CYCLES;
    }
    if (result.days_per_review === null) {
      result.days_per_review = CONSTANTS.DEFAULT_DAYS_PER_REVIEW;
    }
    
    return result;
  }

  function validateInputs(rawInputs) {
    var errors = [];
    var inputs = applyDefaults(rawInputs);

    if (!el('roi-name').value.trim()) {
      errors.push({field: 'roi-name', message: 'Name is required'});
    }
    if (!el('roi-phone').value.trim()) {
      errors.push({field: 'roi-phone', message: 'Phone Number is required'});
    }
    if (!el('roi-email').value.trim()) {
      errors.push({field: 'roi-email', message: 'Work Email is required'});
    }
    if (!el('roi-company').value.trim()) {
      errors.push({field: 'roi-company', message: 'Company Name is required'});
    }
    if (!rawInputs.region) {
      errors.push({field: 'roi-region', message: 'Region is required'});
    }
    if (rawInputs.employee_count < 1) {
      errors.push({field: 'roi-employees', message: 'Total Number of Employees must be at least 1'});
    }
    if (rawInputs.connected_apps < 1) {
      errors.push({field: 'roi-apps', message: 'Number of Applications to Govern must be at least 1'});
    }
    if (rawInputs.am_percent !== null && (rawInputs.am_percent < 0 || rawInputs.am_percent > 1)) {
      errors.push({field: 'roi-am-percent', message: 'AM% must be between 0 and 100'});
    }
    if (rawInputs.cli_percent !== null && (rawInputs.cli_percent < 0 || rawInputs.cli_percent > 1)) {
      errors.push({field: 'roi-cli-percent', message: 'CLI% must be between 0 and 100'});
    }
    // Check AM + CLI after defaults are applied
    if ((inputs.am_percent + inputs.cli_percent) > 1) {
      errors.push({field: 'roi-am-percent', message: 'AM% + CLI% cannot exceed 100%'});
      errors.push({field: 'roi-cli-percent', message: ''});
    }
    var validCycles = [1, 2, 4, 12];
    if (validCycles.indexOf(rawInputs.review_cycles_per_year) === -1) {
      errors.push({field: 'roi-review-cycles', message: 'Access Review frequency must be selected'});
    }
    if (rawInputs.days_per_review !== null && (rawInputs.days_per_review < 1 || rawInputs.days_per_review > 30)) {
      errors.push({field: 'roi-days-per-review', message: 'Days per Access Review must be between 1 and 30'});
    }
    if (rawInputs.daily_access_tickets !== null && rawInputs.daily_access_tickets < 0) {
      errors.push({field: 'roi-daily-tickets', message: 'Daily Access Tickets cannot be negative'});
    }

    return errors;
  }

  function calculate(rawInputs) {
    var inputs = applyDefaults(rawInputs);
    
    // Step 4: Derive daily tickets if blank
    var daily_tickets;
    if (rawInputs.daily_access_tickets === null) {
      daily_tickets = Math.ceil(inputs.employee_count / 100) * CONSTANTS.TICKETS_PER_100_EMPLOYEES;
    } else {
      daily_tickets = rawInputs.daily_access_tickets;
    }
    
    // Step 5: Define identity counts
    var total_identities = inputs.employee_count;
    var cli_count = Math.round(total_identities * inputs.cli_percent);
    
    // Step 6: Subscription pricing
    var monthly_subscription_eur;
    var am_count = 0;
    var non_cli_count = 0;
    var id_count = 0;
    
    if (inputs.employee_count <= 500) {
      non_cli_count = total_identities - cli_count;
      monthly_subscription_eur = (non_cli_count * 4.00) + (cli_count * 0.75);
    } else {
      am_count = Math.round(total_identities * inputs.am_percent);
      id_count = total_identities - am_count - cli_count;
      monthly_subscription_eur = (am_count * 4.00) + (id_count * 2.00) + (cli_count * 0.75);
    }
    
    // Step 7: Annual and multi-year costs
    var annual_subscription_eur = monthly_subscription_eur * 12;
    var implementation_cost_eur = 12500 + (2500 * inputs.connected_apps);
    var year1_cost_eur = annual_subscription_eur + implementation_cost_eur;
    var cost_3y_eur = implementation_cost_eur + (annual_subscription_eur * 3);
    
    // Step 8: Savings model
    // Ticket hours saved
    var ticket_hours_baseline = daily_tickets * CONSTANTS.WORK_DAYS_PER_YEAR * (CONSTANTS.AVG_TICKET_MINUTES / 60);
    var ticket_hours_saved = ticket_hours_baseline * CONSTANTS.TICKET_EFFICIENCY_GAIN;
    
    // Review hours saved
    var reviewer_pool;
    if (inputs.employee_count <= 500) {
      reviewer_pool = Math.round(total_identities * CONSTANTS.REVIEWER_POOL_PERCENT);
    } else {
      reviewer_pool = am_count;
    }
    
    var active_reviewers = reviewer_pool * CONSTANTS.REVIEW_PARTICIPATION_FACTOR;
    var review_hours_baseline = inputs.review_cycles_per_year * inputs.days_per_review * CONSTANTS.HOURS_PER_DAY * active_reviewers;
    var review_hours_saved = review_hours_baseline * CONSTANTS.REVIEW_EFFICIENCY_GAIN;
    
    // Total annual hours saved
    var hours_saved_annual = ticket_hours_saved + review_hours_saved;
    
    // Step 9: Convert hours to euros
    var annual_savings_eur = hours_saved_annual * CONSTANTS.COST_PER_HOUR_EUR;
    
    // Step 10: ROI calculations
    var net_year1_eur = annual_savings_eur - year1_cost_eur;
    var roi_year1 = (net_year1_eur / year1_cost_eur) * 100;
    
    var savings_3y_eur = annual_savings_eur * 3;
    var net_3y_eur = savings_3y_eur - cost_3y_eur;
    var roi_3y = (net_3y_eur / cost_3y_eur) * 100;
    
    // Calculate payback period (years to break even)
    // Net annual benefit after Year 1 = annual_savings - annual_subscription
    var annual_net_benefit = annual_savings_eur - annual_subscription_eur;
    var payback_years = null;
    if (annual_net_benefit > 0) {
      // Payback = implementation_cost / annual_net_benefit
      payback_years = implementation_cost_eur / annual_net_benefit;
    }
    
    // Step 11: USD conversion
    var fx_rate = CONSTANTS.EUR_TO_USD_RATE;
    var annual_savings_usd = annual_savings_eur * fx_rate;
    var annual_subscription_usd = annual_subscription_eur * fx_rate;
    var implementation_cost_usd = implementation_cost_eur * fx_rate;
    var net_3y_usd = net_3y_eur * fx_rate;
    var net_year1_usd = net_year1_eur * fx_rate;
    
    return {
      region: inputs.region,
      hours_saved_annual: hours_saved_annual,
      annual_savings_eur: annual_savings_eur,
      annual_savings_usd: annual_savings_usd,
      annual_subscription_eur: annual_subscription_eur,
      annual_subscription_usd: annual_subscription_usd,
      implementation_cost_eur: implementation_cost_eur,
      implementation_cost_usd: implementation_cost_usd,
      year1_cost_eur: year1_cost_eur,
      roi_year1: roi_year1,
      roi_3y: roi_3y,
      net_year1_eur: net_year1_eur,
      net_year1_usd: net_year1_usd,
      net_3y_eur: net_3y_eur,
      net_3y_usd: net_3y_usd,
      payback_years: payback_years,
      annual_net_benefit: annual_net_benefit
    };
  }

  function formatNumber(n) {
    var rounded = Math.round(n);
    try {
      return rounded.toLocaleString('en-US');
    } catch (e) {
      return String(rounded).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
  }

  function formatPercent(n) {
    return Math.round(n);
  }

  function renderResults(result) {
    var isUS = result.region === 'US';
    
    // Operational Efficiency Gained (in hours saved * cost per hour)
    if (isUS) {
      el('roi-efficiency-currency').innerText = '$';
      el('roi-efficiency-value').innerText = formatNumber(result.annual_savings_usd);
    } else {
      el('roi-efficiency-currency').innerText = '€';
      el('roi-efficiency-value').innerText = formatNumber(result.annual_savings_eur);
    }
    el('roi-efficiency-hours').innerText = formatNumber(result.hours_saved_annual) + ' hours saved/year';
    
    // Subscription Cost (Annual)
    if (isUS) {
      el('roi-subscription-currency').innerText = '$';
      el('roi-subscription-value').innerText = formatNumber(result.annual_subscription_usd);
      el('roi-subscription-alt').innerText = '≈ €' + formatNumber(result.annual_subscription_eur);
    } else {
      el('roi-subscription-currency').innerText = '€';
      el('roi-subscription-value').innerText = formatNumber(result.annual_subscription_eur);
      el('roi-subscription-alt').innerText = '≈ $' + formatNumber(result.annual_subscription_usd);
    }
    
    // Implementation Cost (One-Time)
    if (isUS) {
      el('roi-impl-currency').innerText = '$';
      el('roi-impl-value').innerText = formatNumber(result.implementation_cost_usd);
      el('roi-impl-alt').innerText = '≈ €' + formatNumber(result.implementation_cost_eur);
    } else {
      el('roi-impl-currency').innerText = '€';
      el('roi-impl-value').innerText = formatNumber(result.implementation_cost_eur);
      el('roi-impl-alt').innerText = '≈ $' + formatNumber(result.implementation_cost_usd);
    }
    
    // ROI Year 1 with net value
    el('roi-year1-value').innerText = formatPercent(result.roi_year1);
    var netYear1 = isUS ? result.net_year1_usd : result.net_year1_eur;
    var currSymbol = isUS ? '$' : '€';
    if (netYear1 >= 0) {
      el('roi-year1-net').innerText = 'Net: ' + currSymbol + formatNumber(netYear1);
    } else {
      el('roi-year1-net').innerText = 'Net: -' + currSymbol + formatNumber(Math.abs(netYear1));
    }
    
    // ROI 3 Years with net value
    el('roi-3year-value').innerText = formatPercent(result.roi_3y);
    var net3Year = isUS ? result.net_3y_usd : result.net_3y_eur;
    if (net3Year >= 0) {
      el('roi-3year-net').innerText = 'Net: ' + currSymbol + formatNumber(net3Year);
    } else {
      el('roi-3year-net').innerText = 'Net: -' + currSymbol + formatNumber(Math.abs(net3Year));
    }
    
    // Payback Period
    if (result.payback_years !== null && result.payback_years > 0) {
      if (result.payback_years < 1) {
        el('roi-payback-value').innerText = Math.round(result.payback_years * 12) + ' months';
      } else if (result.payback_years <= 10) {
        el('roi-payback-value').innerText = result.payback_years.toFixed(1) + ' years';
      } else {
        el('roi-payback-value').innerText = '10+ years';
      }
      el('roi-payback-note').innerText = 'Time to recover implementation cost';
    } else {
      el('roi-payback-value').innerText = 'N/A';
      el('roi-payback-note').innerText = 'Savings do not cover annual costs';
    }
  }

  function showErrors(errors) {
    var errorsEl = el('roi-errors');
    if (errors.length === 0) {
      errorsEl.innerHTML = '';
      return;
    }
    var messages = [];
    for (var i = 0; i < errors.length; i++) {
      if (errors[i].message) {
        messages.push(errors[i].message);
      }
    }
    errorsEl.innerHTML = messages.join('<br>');
  }

  function clearFieldErrors() {
    var inputs = container.querySelectorAll('.roi-input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].classList.remove('roi-input--error');
    }
  }

  function highlightErrors(errors) {
    clearFieldErrors();
    
    for (var i = 0; i < errors.length; i++) {
      var field = el(errors[i].field);
      if (field) {
        field.classList.add('roi-input--error');
      }
    }
  }

  function handleCalculate() {
    var inputs = getInputs();
    var errors = validateInputs(inputs);
    
    if (errors.length > 0) {
      showErrors(errors);
      highlightErrors(errors);
      return;
    }
    
    showErrors([]);
    clearFieldErrors();
    
    var result = calculate(inputs);
    renderResults(result);
  }

  function bindEvents() {
    var calcBtn = el('roi-calc-btn');
    var arrowBtn = el('roi-calc-arrow');
    
    if (calcBtn) {
      calcBtn.addEventListener('click', function(e) {
        e.preventDefault();
        handleCalculate();
      });
    }
    
    if (arrowBtn) {
      arrowBtn.addEventListener('click', function(e) {
        e.preventDefault();
        handleCalculate();
      });
    }
    
    // Clear errors on input
    container.addEventListener('input', function(e) {
      if (e.target && e.target.classList.contains('roi-input')) {
        e.target.classList.remove('roi-input--error');
      }
    });
  }
})();
JS;
    }
  }

  new ROI_Calculator_Module();
}