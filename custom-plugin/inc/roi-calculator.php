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

      // Scripts - add AJAX config before main script
      wp_register_script('roi-calculator-inline-script', false, array(), '1.0.2', true);
      wp_enqueue_script('roi-calculator-inline-script');
      
      // Inject AJAX configuration before the main script
      $ajax_config = sprintf(
        'var roiCalculatorAjax = %s;',
        wp_json_encode(array(
          'ajaxurl' => admin_url('admin-ajax.php'),
          'nonce'   => wp_create_nonce('roi_submission_nonce'),
        ))
      );
      wp_add_inline_script('roi-calculator-inline-script', $ajax_config, 'before');
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
                  <input id="roi-region" type="text" class="roi-input" placeholder="e.g., United States, Europe, Asia Pacific" required>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-employees">Total Number of Employees<span class="roi-required">*</span></label>
                  <input id="roi-employees" type="number" min="1" max="100000" step="1" class="roi-input" required>
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-apps">No. of Applications you want to Govern<span class="roi-required">*</span></label>
                  <input id="roi-apps" type="number" min="1" max="200" step="1" class="roi-input" required>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-am-percent">% of People Who Operate or Approve Access<span class="roi-required">*</span></label>
                  <input id="roi-am-percent" type="number" min="0" max="100" step="1" class="roi-input" placeholder="e.g., 10" required>
                </div>
              </div>

              <div class="roi-row">
                <div class="roi-field">
                  <label class="roi-label" for="roi-cli-percent">% of Identities Tracked for Audit only<span class="roi-required">*</span></label>
                  <input id="roi-cli-percent" type="number" min="0" max="100" step="1" class="roi-input" placeholder="e.g., 0" required>
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
                  <label class="roi-label" for="roi-days-per-review">Approximate Days Spent Per Access Review Cycle<span class="roi-required">*</span></label>
                  <input id="roi-days-per-review" type="number" min="1" max="30" step="1" class="roi-input" placeholder="e.g., 7" required>
                </div>
                <div class="roi-field">
                  <label class="roi-label" for="roi-daily-tickets">Approximate No. of Access related Tickets Per Day<span class="roi-required">*</span></label>
                  <input id="roi-daily-tickets" type="number" min="0" max="500" step="1" class="roi-input" placeholder="e.g., 10" required>
                </div>
              </div>

              <div class="roi-actions">
                <button id="roi-calc-btn" class="roi-btn roi-btn--primary" type="button">CALCULATE ROI</button>
                <button id="roi-calc-arrow" class="roi-btn roi-btn--arrow" type="button" aria-label="Calculate ROI">&rarr;</button>
              </div>

              <div id="roi-errors" class="roi-errors" aria-live="polite"></div>
              <div id="roi-warnings" class="roi-warnings" aria-live="polite"></div>
            </form>
          </div>

          <div class="roi-right">
            <div class="roi-card">
              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span id="roi-efficiency-value">0</span>
                  <span class="roi-card__unit">hrs</span>
                </div>
                <div class="roi-card__label">Operational Efficiency<br>Gained</div>
                <div class="roi-card__sublabel" id="roi-efficiency-monetary"></div>
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
                <div class="roi-card__status" id="roi-year1-status"></div>
              </div>
              <div class="roi-card__divider"></div>

              <div class="roi-card__item">
                <div class="roi-card__value">
                  <span id="roi-3year-value">0</span>
                  <span class="roi-card__percent">%</span>
                </div>
                <div class="roi-card__label">Return on Investment<br>3 Years</div>
                <div class="roi-card__sublabel" id="roi-3year-net"></div>
                <div class="roi-card__status" id="roi-3year-status"></div>
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
.roi-warnings{margin-top:8px;color:#D97706;font-size:12px;font-style:italic}
.roi-right{display:flex;align-items:flex-start;position:sticky;top:24px}
@media (max-width:1024px){.roi-right{position:static}}
.roi-card{width:100%;background:linear-gradient(160deg, #1E3A8A 0%, #4338CA 50%, #6366F1 100%);border-radius:16px;color:#fff;box-shadow:0 16px 40px rgba(0,0,0,0.25);padding:28px 24px;display:flex;flex-direction:column}
.roi-card__item{text-align:center;padding:16px 0}
.roi-card__divider{height:1px;background:rgba(255,255,255,0.15);margin:0 -24px}
.roi-card__value{font-size:42px;font-weight:800;display:flex;align-items:baseline;justify-content:center;gap:4px;line-height:1.1}
.roi-card__value--small{font-size:28px}
.roi-card__currency{font-size:20px;font-weight:600;opacity:0.9}
.roi-card__unit{font-size:16px;font-weight:600;opacity:0.85}
.roi-card__percent{font-size:20px;font-weight:600;opacity:0.9;margin-left:2px}
.roi-card__label{font-size:14px;opacity:0.9;margin-top:8px;line-height:1.4}
.roi-card__sublabel{font-size:12px;opacity:0.7;margin-top:4px}
.roi-card__status{font-size:11px;font-weight:600;margin-top:6px;padding:3px 10px;border-radius:12px;display:inline-block}
.roi-card__status--low{background:rgba(239,68,68,0.25);color:#FCA5A5}
.roi-card__status--moderate{background:rgba(251,191,36,0.25);color:#FCD34D}
.roi-card__status--strong{background:rgba(34,197,94,0.25);color:#86EFAC}
.roi-card__status--excellent{background:rgba(16,185,129,0.3);color:#6EE7B7}
.roi-field-error{color:#E53935;font-size:11px;margin-top:4px;display:none}
.roi-field{position:relative}
CSS;
    }

    private function inline_js() {
      return <<<'JS'
(function(){
  "use strict";

  // Configuration - can be externalized in future
  var CONFIG = {
    DEBOUNCE_DELAY: 400,
    ANIMATION_DURATION: 750 // Reserved for Phase 2 animated number updates
  };

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

  // Input limits for validation and sanitization
  var INPUT_LIMITS = {
    employee_count: { min: 1, max: 100000 },
    connected_apps: { min: 1, max: 200 },
    am_percent: { min: 0, max: 1 },
    cli_percent: { min: 0, max: 1 },
    days_per_review: { min: 1, max: 30 },
    daily_access_tickets: { min: 0, max: 500 }
  };

  var container = null;
  var initialized = false;
  var debounceTimer = null;
  var lastInputHash = null;

  function el(id) {
    return document.getElementById(id);
  }

  // Clamp utility to keep value within bounds
  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  // Sanitize inputs by clamping values to valid ranges
  function sanitizeInputs(inputs) {
    var result = Object.assign({}, inputs);
    
    // Clamp numeric fields to their valid ranges
    // Note: For employee_count and connected_apps, 0 values are left as-is for validation to catch
    if (result.employee_count > 0) {
      result.employee_count = clamp(result.employee_count, INPUT_LIMITS.employee_count.min, INPUT_LIMITS.employee_count.max);
    }
    if (result.connected_apps > 0) {
      result.connected_apps = clamp(result.connected_apps, INPUT_LIMITS.connected_apps.min, INPUT_LIMITS.connected_apps.max);
    }
    if (result.am_percent !== null) {
      result.am_percent = clamp(result.am_percent, INPUT_LIMITS.am_percent.min, INPUT_LIMITS.am_percent.max);
    }
    if (result.cli_percent !== null) {
      result.cli_percent = clamp(result.cli_percent, INPUT_LIMITS.cli_percent.min, INPUT_LIMITS.cli_percent.max);
    }
    if (result.days_per_review !== null) {
      result.days_per_review = clamp(result.days_per_review, INPUT_LIMITS.days_per_review.min, INPUT_LIMITS.days_per_review.max);
    }
    if (result.daily_access_tickets !== null) {
      result.daily_access_tickets = clamp(result.daily_access_tickets, INPUT_LIMITS.daily_access_tickets.min, INPUT_LIMITS.daily_access_tickets.max);
    }
    
    return result;
  }

  // Get warnings for edge cases (informational, don't block calculation)
  function getWarnings(inputs, result) {
    var warnings = [];
    
    // Large organisation warning
    if (inputs.employee_count > 50000) {
      warnings.push('Large organisation detected (>50,000 employees). Results are estimates.');
    }
    
    // Low efficiency warning
    if (result && result.hours_saved_annual < 50) {
      warnings.push('Very low efficiency savings (<50 hours/year). Consider reviewing input values.');
    }
    
    return warnings;
  }

  // Debounce utility for live calculation
  function debounce(fn, delay) {
    return function() {
      var args = arguments;
      var context = this;
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function() {
        fn.apply(context, args);
      }, delay);
    };
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

  // Normalize inputs - convert null values to 0 (all fields are now required)
  function normalizeInputs(inputs) {
    var result = Object.assign({}, inputs);
    
    if (result.am_percent === null) {
      result.am_percent = 0;
    }
    if (result.cli_percent === null) {
      result.cli_percent = 0;
    }
    if (!result.review_cycles_per_year) {
      result.review_cycles_per_year = 0;
    }
    if (result.days_per_review === null) {
      result.days_per_review = 0;
    }
    if (result.daily_access_tickets === null) {
      result.daily_access_tickets = 0;
    }
    
    return result;
  }

  // Validate a single field and return error if any
  function validateField(fieldId) {
    var field = el(fieldId);
    if (!field) return null;
    
    var value = field.value;
    var trimmedValue = (typeof value === 'string') ? value.trim() : value;
    
    switch(fieldId) {
      case 'roi-name':
        if (!trimmedValue) return {field: fieldId, message: 'Name is required'};
        break;
      case 'roi-phone':
        if (!trimmedValue) return {field: fieldId, message: 'Phone Number is required'};
        break;
      case 'roi-email':
        if (!trimmedValue) return {field: fieldId, message: 'Work Email is required'};
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmedValue)) return {field: fieldId, message: 'Please enter a valid email address'};
        break;
      case 'roi-company':
        if (!trimmedValue) return {field: fieldId, message: 'Company Name is required'};
        break;
      case 'roi-region':
        if (!trimmedValue) return {field: fieldId, message: 'Region is required'};
        break;
      case 'roi-employees':
        var emp = parseInt(value, 10);
        if (!value || isNaN(emp) || emp < 1) return {field: fieldId, message: 'Total Number of Employees must be at least 1'};
        if (emp > 100000) return {field: fieldId, message: 'Maximum 100,000 employees'};
        break;
      case 'roi-apps':
        var apps = parseInt(value, 10);
        if (!value || isNaN(apps) || apps < 1) return {field: fieldId, message: 'Number of Applications must be at least 1'};
        if (apps > 200) return {field: fieldId, message: 'Maximum 200 applications'};
        break;
      case 'roi-am-percent':
        var amPct = parseFloat(value);
        if (value === '' || isNaN(amPct)) return {field: fieldId, message: 'AM% is required'};
        if (amPct < 0 || amPct > 100) return {field: fieldId, message: 'AM% must be between 0 and 100'};
        break;
      case 'roi-cli-percent':
        var cliPct = parseFloat(value);
        if (value === '' || isNaN(cliPct)) return {field: fieldId, message: 'CLI% is required'};
        if (cliPct < 0 || cliPct > 100) return {field: fieldId, message: 'CLI% must be between 0 and 100'};
        break;
      case 'roi-review-cycles':
        var cycles = parseInt(value, 10);
        if (!value || [1, 2, 4, 12].indexOf(cycles) === -1) return {field: fieldId, message: 'Access Review frequency is required'};
        break;
      case 'roi-days-per-review':
        var days = parseInt(value, 10);
        if (value === '' || isNaN(days)) return {field: fieldId, message: 'Days per Access Review is required'};
        if (days < 1 || days > 30) return {field: fieldId, message: 'Days must be between 1 and 30'};
        break;
      case 'roi-daily-tickets':
        var tickets = parseInt(value, 10);
        if (value === '' || isNaN(tickets)) return {field: fieldId, message: 'Daily Access Tickets is required'};
        if (tickets < 0) return {field: fieldId, message: 'Daily Access Tickets cannot be negative'};
        if (tickets > 500) return {field: fieldId, message: 'Maximum 500 tickets per day'};
        break;
    }
    return null;
  }

  // Show error for a specific field
  function showFieldError(fieldId, message) {
    var field = el(fieldId);
    if (!field) return;
    
    field.classList.add('roi-input--error');
    
    // Create or update inline error message
    var errorEl = document.getElementById(fieldId + '-error');
    if (!errorEl) {
      errorEl = document.createElement('div');
      errorEl.id = fieldId + '-error';
      errorEl.className = 'roi-field-error';
      field.parentNode.appendChild(errorEl);
    }
    errorEl.textContent = message;
    errorEl.style.display = 'block';
  }

  // Clear error for a specific field
  function clearFieldError(fieldId) {
    var field = el(fieldId);
    if (!field) return;
    
    field.classList.remove('roi-input--error');
    
    var errorEl = document.getElementById(fieldId + '-error');
    if (errorEl) {
      errorEl.style.display = 'none';
    }
  }

  // Live validation handler for single field
  function handleFieldValidation(fieldId) {
    var error = validateField(fieldId);
    if (error) {
      showFieldError(error.field, error.message);
      return false;
    } else {
      clearFieldError(fieldId);
      return true;
    }
  }

  function validateInputs(rawInputs) {
    var errors = [];

    // Validate all required fields
    var fieldIds = [
      'roi-name', 'roi-phone', 'roi-email', 'roi-company', 'roi-region',
      'roi-employees', 'roi-apps', 'roi-am-percent', 'roi-cli-percent',
      'roi-review-cycles', 'roi-days-per-review', 'roi-daily-tickets'
    ];
    
    for (var i = 0; i < fieldIds.length; i++) {
      var error = validateField(fieldIds[i]);
      if (error) {
        errors.push(error);
      }
    }

    // Cross-field validation: Check AM + CLI doesn't exceed 100%
    var amVal = parseFloat(el('roi-am-percent').value) || 0;
    var cliVal = parseFloat(el('roi-cli-percent').value) || 0;
    if ((amVal + cliVal) > 100) {
      errors.push({field: 'roi-am-percent', message: 'AM% + CLI% cannot exceed 100%'});
      errors.push({field: 'roi-cli-percent', message: ''});
    }

    return errors;
  }

  function calculate(rawInputs) {
    // Sanitize inputs first, then normalize null values
    var sanitized = sanitizeInputs(rawInputs);
    var inputs = normalizeInputs(sanitized);
    
    // All fields are required, so daily_tickets comes from user input
    var daily_tickets = inputs.daily_access_tickets;
    if (daily_tickets === 0 && sanitized.daily_access_tickets === null) {
      // Fallback derivation if somehow null
      daily_tickets = Math.ceil(inputs.employee_count / 100) * CONSTANTS.TICKETS_PER_100_EMPLOYEES;
    } else {
      daily_tickets = inputs.daily_access_tickets; // Use inputs to ensure consistency
    }
    
    // Step 5: Define identity counts
    var total_identities = inputs.employee_count;
    var cli_count = Math.round(total_identities * inputs.cli_percent);
    
    // Step 6: Subscription pricing with safety guards
    var monthly_subscription_eur;
    var am_count = 0;
    var non_cli_count = 0;
    var id_count = 0;
    
    if (inputs.employee_count <= 500) {
      non_cli_count = Math.max(0, total_identities - cli_count); // Safety: prevent negative
      monthly_subscription_eur = (non_cli_count * 4.00) + (cli_count * 0.75);
    } else {
      am_count = Math.round(total_identities * inputs.am_percent);
      id_count = Math.max(0, total_identities - am_count - cli_count); // Safety: prevent negative
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
    
    // Review hours saved with safety guards
    // Note: Minimum reviewer_pool of 1 is intentional - even very small orgs have at least one reviewer
    // This prevents division by zero and reflects realistic minimum staffing
    var reviewer_pool;
    if (inputs.employee_count <= 500) {
      reviewer_pool = Math.max(1, Math.round(total_identities * CONSTANTS.REVIEWER_POOL_PERCENT));
    } else {
      reviewer_pool = Math.max(1, am_count);
    }
    
    var active_reviewers = reviewer_pool * CONSTANTS.REVIEW_PARTICIPATION_FACTOR;
    var review_hours_baseline = inputs.review_cycles_per_year * inputs.days_per_review * CONSTANTS.HOURS_PER_DAY * active_reviewers;
    var review_hours_saved = review_hours_baseline * CONSTANTS.REVIEW_EFFICIENCY_GAIN;
    
    // Total annual hours saved (ensure non-negative)
    var hours_saved_annual = Math.max(0, ticket_hours_saved + review_hours_saved);
    
    // Step 9: Convert hours to euros
    var annual_savings_eur = hours_saved_annual * CONSTANTS.COST_PER_HOUR_EUR;
    
    // Step 10: ROI calculations with division safety
    var net_year1_eur = annual_savings_eur - year1_cost_eur;
    var roi_year1 = year1_cost_eur > 0 ? (net_year1_eur / year1_cost_eur) * 100 : 0; // Division safety
    
    var savings_3y_eur = annual_savings_eur * 3;
    var net_3y_eur = savings_3y_eur - cost_3y_eur;
    var roi_3y = cost_3y_eur > 0 ? (net_3y_eur / cost_3y_eur) * 100 : 0; // Division safety
    
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

  // Enterprise rounding: nearest 100 for subscription/savings, 500 for implementation
  function roundToNearest(n, nearest) {
    return Math.round(n / nearest) * nearest;
  }

  function formatPercent(n) {
    // Handle edge cases to prevent NaN/Infinity display
    // Returns 0 for invalid values as a safe fallback for display
    if (!isFinite(n)) return 0;
    return Math.round(n);
  }

  // Get ROI status indicator based on industry-standard thresholds:
  // - < 0%: Investment not recovered within period (Low)
  // - 0-50%: Partial return, typical for first-year with implementation costs (Moderate)
  // - 50-150%: Healthy return, good investment value (Strong)
  // - > 150%: Exceptional return, high-value scenario (Excellent)
  function getROIStatus(roi) {
    if (roi < 0) {
      return { label: 'Low ROI', className: 'roi-card__status--low' };
    } else if (roi < 50) {
      return { label: 'Moderate ROI', className: 'roi-card__status--moderate' };
    } else if (roi <= 150) {
      return { label: 'Strong ROI', className: 'roi-card__status--strong' };
    } else {
      return { label: 'Excellent ROI', className: 'roi-card__status--excellent' };
    }
  }

  // Format payback period as years + months for better readability
  function formatPaybackPeriod(years) {
    if (years === null || years <= 0 || !isFinite(years)) {
      return null;
    }
    if (years > 10) {
      return '10+ years';
    }
    var wholeYears = Math.floor(years);
    var months = Math.round((years - wholeYears) * 12);
    if (months === 12) {
      wholeYears++;
      months = 0;
    }
    if (wholeYears === 0) {
      return months + ' month' + (months !== 1 ? 's' : '');
    } else if (months === 0) {
      return wholeYears + ' year' + (wholeYears !== 1 ? 's' : '');
    } else {
      return wholeYears + ' yr ' + months + ' mo';
    }
  }

  function renderResults(result) {
    var isUS = result.region === 'US';
    var currSymbol = isUS ? '$' : '€';
    
    // Operational Efficiency Gained - Hours as primary, monetary as secondary
    el('roi-efficiency-value').innerText = formatNumber(Math.round(result.hours_saved_annual));
    var savingsRounded = roundToNearest(isUS ? result.annual_savings_usd : result.annual_savings_eur, 100);
    el('roi-efficiency-monetary').innerText = '≈ ' + currSymbol + formatNumber(savingsRounded) + '/year';
    
    // Subscription Cost (Annual) - rounded to nearest 100
    var subscriptionRounded = roundToNearest(isUS ? result.annual_subscription_usd : result.annual_subscription_eur, 100);
    el('roi-subscription-currency').innerText = currSymbol;
    el('roi-subscription-value').innerText = formatNumber(subscriptionRounded);
    var subscriptionAlt = roundToNearest(isUS ? result.annual_subscription_eur : result.annual_subscription_usd, 100);
    el('roi-subscription-alt').innerText = '≈ ' + (isUS ? '€' : '$') + formatNumber(subscriptionAlt);
    
    // Implementation Cost (One-Time) - rounded to nearest 500
    var implRounded = roundToNearest(isUS ? result.implementation_cost_usd : result.implementation_cost_eur, 500);
    el('roi-impl-currency').innerText = currSymbol;
    el('roi-impl-value').innerText = formatNumber(implRounded);
    var implAlt = roundToNearest(isUS ? result.implementation_cost_eur : result.implementation_cost_usd, 500);
    el('roi-impl-alt').innerText = '≈ ' + (isUS ? '€' : '$') + formatNumber(implAlt);
    
    // ROI Year 1 with net value and status indicator
    var roi1 = formatPercent(result.roi_year1);
    el('roi-year1-value').innerText = roi1;
    var netYear1 = isUS ? result.net_year1_usd : result.net_year1_eur;
    if (netYear1 >= 0) {
      el('roi-year1-net').innerText = 'Net: ' + currSymbol + formatNumber(roundToNearest(netYear1, 100));
    } else {
      // Neutral messaging for negative ROI
      el('roi-year1-net').innerText = 'Investment recovered after Year 1';
    }
    var status1 = getROIStatus(result.roi_year1);
    var statusEl1 = el('roi-year1-status');
    statusEl1.innerText = status1.label;
    statusEl1.className = 'roi-card__status ' + status1.className;
    
    // ROI 3 Years with net value and status indicator
    var roi3 = formatPercent(result.roi_3y);
    el('roi-3year-value').innerText = roi3;
    var net3Year = isUS ? result.net_3y_usd : result.net_3y_eur;
    if (net3Year >= 0) {
      el('roi-3year-net').innerText = 'Net: ' + currSymbol + formatNumber(roundToNearest(net3Year, 100));
    } else {
      el('roi-3year-net').innerText = 'Investment recovered after 3 years';
    }
    var status3 = getROIStatus(result.roi_3y);
    var statusEl3 = el('roi-3year-status');
    statusEl3.innerText = status3.label;
    statusEl3.className = 'roi-card__status ' + status3.className;
    
    // Payback Period - formatted as years + months
    var paybackFormatted = formatPaybackPeriod(result.payback_years);
    if (paybackFormatted) {
      el('roi-payback-value').innerText = paybackFormatted;
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

  function showWarnings(warnings) {
    var warningsEl = el('roi-warnings');
    if (!warningsEl) return;
    if (warnings.length === 0) {
      warningsEl.innerHTML = '';
      return;
    }
    warningsEl.innerHTML = warnings.map(function(w) { return '⚠ ' + w; }).join('<br>');
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

  // Validate output values to ensure they are safe for display
  function safeOutput(result) {
    var safe = Object.assign({}, result);
    
    // Convert any NaN, Infinity, or invalid values to safe defaults
    for (var key in safe) {
      if (typeof safe[key] === 'number') {
        if (!isFinite(safe[key])) {
          safe[key] = 0;
        }
        // Ensure hours are never negative
        if (key === 'hours_saved_annual' && safe[key] < 0) {
          safe[key] = 0;
        }
      }
    }
    
    return safe;
  }

  // Submit calculation to server via AJAX
  function submitCalculation(inputs, result) {
    if (typeof roiCalculatorAjax === 'undefined') {
      console.log('AJAX not available');
      return;
    }
    
    var paybackValue = el('roi-payback-value');
    var paybackText = paybackValue ? paybackValue.innerText : 'N/A';
    
    var formData = new FormData();
    formData.append('action', 'roi_submit_calculation');
    formData.append('nonce', roiCalculatorAjax.nonce);
    
    // Form inputs
    formData.append('name', el('roi-name').value);
    formData.append('phone', el('roi-phone').value);
    formData.append('email', el('roi-email').value);
    formData.append('company', el('roi-company').value);
    formData.append('region', inputs.region);
    formData.append('employees', inputs.employee_count);
    formData.append('apps', inputs.connected_apps);
    formData.append('am_percent', (inputs.am_percent || 0) * 100);
    formData.append('cli_percent', (inputs.cli_percent || 0) * 100);
    formData.append('review_cycles', inputs.review_cycles_per_year);
    formData.append('days_per_review', inputs.days_per_review || 0);
    formData.append('daily_tickets', inputs.daily_access_tickets || 0);
    
    // Calculation results
    formData.append('hours_saved', result.hours_saved_annual);
    formData.append('annual_savings_eur', result.annual_savings_eur);
    formData.append('subscription_eur', result.annual_subscription_eur);
    formData.append('implementation_eur', result.implementation_cost_eur);
    formData.append('roi_year1', result.roi_year1);
    formData.append('roi_3year', result.roi_3y);
    formData.append('payback', paybackText);
    
    fetch(roiCalculatorAjax.ajaxurl, {
      method: 'POST',
      body: formData
    })
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      if (data.success) {
        // Show success message
        showSuccessMessage('Your ROI calculation has been saved. A copy has been sent to your email.');
      }
    })
    .catch(function(error) {
      console.error('Submission error:', error);
    });
  }

  function showSuccessMessage(message) {
    var warningsEl = el('roi-warnings');
    if (warningsEl) {
      warningsEl.innerHTML = '<span style="color:#059669">✓ ' + message + '</span>';
    }
  }

  function handleCalculate() {
    var inputs = getInputs();
    var errors = validateInputs(inputs);
    
    if (errors.length > 0) {
      showErrors(errors);
      showWarnings([]);
      highlightErrors(errors);
      return;
    }
    
    showErrors([]);
    clearFieldErrors();
    
    var result = calculate(inputs);
    var safeResult = safeOutput(result);
    
    // Check for warnings
    var warnings = getWarnings(inputs, safeResult);
    showWarnings(warnings);
    
    renderResults(safeResult);
    
    // Submit to server for storage and email
    submitCalculation(inputs, safeResult);
  }

  // Check if minimum required inputs are present for calculation
  function hasMinimumRequiredInputs(inputs) {
    return inputs.region && 
           inputs.employee_count >= 1 && 
           inputs.connected_apps >= 1 && 
           inputs.review_cycles_per_year;
  }

  // Live calculation handler (for debounced input events)
  function handleLiveCalculate() {
    var inputs = getInputs();
    
    // Create hash of current inputs to avoid redundant calculations
    var inputHash = JSON.stringify(inputs);
    if (inputHash === lastInputHash) {
      return; // Skip if inputs haven't changed
    }
    lastInputHash = inputHash;
    
    // Only run live calculation if required fields have values
    if (hasMinimumRequiredInputs(inputs)) {
      var errors = validateInputs(inputs);
      if (errors.length === 0) {
        var result = calculate(inputs);
        var safeResult = safeOutput(result);
        var warnings = getWarnings(inputs, safeResult);
        showWarnings(warnings);
        renderResults(safeResult);
      }
    }
  }

  var debouncedLiveCalculate = debounce(handleLiveCalculate, CONFIG.DEBOUNCE_DELAY);

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
    
    // Live calculation on input change (debounced)
    container.addEventListener('input', function(e) {
      if (e.target && e.target.classList.contains('roi-input')) {
        e.target.classList.remove('roi-input--error');
        debouncedLiveCalculate();
      }
    });
    
    // Also trigger on select change
    container.addEventListener('change', function(e) {
      if (e.target && e.target.classList.contains('roi-select')) {
        handleFieldValidation(e.target.id);
        debouncedLiveCalculate();
      }
    });
    
    // Live validation on blur
    container.addEventListener('blur', function(e) {
      if (e.target && e.target.classList.contains('roi-input')) {
        handleFieldValidation(e.target.id);
      }
    }, true); // Use capture phase for blur
  }
})();
JS;
    }
  }

  new ROI_Calculator_Module();
}