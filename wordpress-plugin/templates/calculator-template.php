<?php
/**
 * Template for ROI Calculator Shortcode
 * 
 * @package Anugal_ROI_Calculator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="anugal-roi-container">
    <div class="anugal-roi-form-section">
        <h2 class="anugal-roi-form-title">
            <?php echo esc_html($atts['title']); ?> <span class="anugal-roi-highlight">Calculator</span>
        </h2>
        
        <form id="anuaglRoiForm" class="anugal-roi-form">
            <div class="anugal-roi-form-row">
                <div class="anugal-roi-form-group">
                    <label for="anugal_region">Region<span class="anugal-roi-required">*</span></label>
                    <select id="anugal_region" name="region" required>
                        <option value="">Select Region</option>
                        <option value="EU">Europe</option>
                        <option value="US">United States</option>
                        <option value="APAC">Asia Pacific</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                <div class="anugal-roi-form-group">
                    <label for="anugal_employee_count">Total Number of Employees<span class="anugal-roi-required">*</span></label>
                    <input type="number" id="anugal_employee_count" name="employee_count" min="1" required>
                </div>
            </div>

            <div class="anugal-roi-form-row">
                <div class="anugal-roi-form-group">
                    <label for="anugal_connected_apps">No. of Applications you want to Govern<span class="anugal-roi-required">*</span></label>
                    <input type="number" id="anugal_connected_apps" name="connected_apps" min="1" required>
                </div>
                <div class="anugal-roi-form-group">
                    <label for="anugal_am_percent">% of People Who Operate or Approve Access</label>
                    <input type="number" id="anugal_am_percent" name="am_percent" min="0" max="100" step="0.1" placeholder="10">
                </div>
            </div>

            <div class="anugal-roi-form-row">
                <div class="anugal-roi-form-group">
                    <label for="anugal_cli_percent">% of Identities Tracked for Audit only</label>
                    <input type="number" id="anugal_cli_percent" name="cli_percent" min="0" max="100" step="0.1" placeholder="0">
                </div>
                <div class="anugal-roi-form-group">
                    <label for="anugal_review_cycles">How often do you Run Access Reviews?</label>
                    <select id="anugal_review_cycles" name="review_cycles">
                        <option value="">Select Frequency</option>
                        <option value="1">Annually (1x per year)</option>
                        <option value="2" selected>Semi-Annually (2x per year)</option>
                        <option value="4">Quarterly (4x per year)</option>
                        <option value="12">Monthly (12x per year)</option>
                    </select>
                </div>
            </div>

            <div class="anugal-roi-form-row">
                <div class="anugal-roi-form-group">
                    <label for="anugal_days_per_review">Approximate Days Spent Per Access Review Cycle</label>
                    <input type="number" id="anugal_days_per_review" name="days_per_review" min="1" max="30" placeholder="7">
                </div>
                <div class="anugal-roi-form-group">
                    <label for="anugal_daily_tickets">Approximate No. of Access related Tickets Per Day</label>
                    <input type="number" id="anugal_daily_tickets" name="daily_tickets" min="0" step="0.1" placeholder="Auto-calculated">
                </div>
            </div>

            <div class="anugal-roi-form-actions">
                <button type="submit" class="anugal-roi-btn-primary">CALCULATE ROI</button>
            </div>
        </form>
    </div>

    <div class="anugal-roi-results-section">
        <div class="anugal-roi-results-card">
            <div class="anugal-roi-result-item">
                <div class="anugal-roi-result-value" id="anugal_operational_efficiency">$0</div>
                <div class="anugal-roi-result-label">Operational Efficiency<br>Gained</div>
            </div>
            <div class="anugal-roi-result-divider"></div>
            
            <div class="anugal-roi-result-item">
                <div class="anugal-roi-result-value" id="anugal_subscription_cost">$0</div>
                <div class="anugal-roi-result-label">Subscription Cost<br>(Annual)</div>
            </div>
            <div class="anugal-roi-result-divider"></div>
            
            <div class="anugal-roi-result-item">
                <div class="anugal-roi-result-value" id="anugal_implementation_cost">$0</div>
                <div class="anugal-roi-result-label">Implementation Cost<br>(One-Time)</div>
            </div>
            <div class="anugal-roi-result-divider"></div>
            
            <div class="anugal-roi-result-item">
                <div class="anugal-roi-result-value" id="anugal_roi_year1">$0</div>
                <div class="anugal-roi-result-label">Return on Investment<br>Year 1</div>
            </div>
            <div class="anugal-roi-result-divider"></div>
            
            <div class="anugal-roi-result-item">
                <div class="anugal-roi-result-value" id="anugal_roi_3year">$0</div>
                <div class="anugal-roi-result-label">Return on Investment<br>3 Years</div>
            </div>

            <div class="anugal-roi-download-section">
                <button type="button" class="anugal-roi-btn-download" id="anugal_downloadReport" disabled>
                    <span class="anugal-roi-download-icon">→</span>
                    DOWNLOAD REPORT
                </button>
            </div>
        </div>
    </div>
</div>
