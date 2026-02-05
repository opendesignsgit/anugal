/**
 * ROI Calculator Application for WordPress
 * Handles UI interactions and direct report generation (no popup)
 */

// Global state
let calculationResults = null;
const calculator = new ROICalculator();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('anuaglRoiForm')) {
        initializeForm();
    }
});

/**
 * Initialize form interactions
 */
function initializeForm() {
    const form = document.getElementById('anuaglRoiForm');
    const employeeInput = document.getElementById('anugal_employee_count');
    const downloadBtn = document.getElementById('anugal_downloadReport');
    
    if (!form || !employeeInput) return;
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        calculateROI();
    });
    
    // Auto-update dependent fields when employee count changes
    employeeInput.addEventListener('input', function() {
        updateDependentFields();
    });
    
    // Handle direct download (no modal)
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (calculationResults) {
                generateAndDownloadReport();
            }
        });
    }
}

/**
 * Update fields that depend on employee count
 */
function updateDependentFields() {
    const employeeCount = parseInt(document.getElementById('anugal_employee_count').value);
    const dailyTicketsInput = document.getElementById('anugal_daily_tickets');
    
    // If daily tickets is empty, show auto-calculated value as placeholder
    if (dailyTicketsInput && !dailyTicketsInput.value && employeeCount > 0) {
        const estimated = Math.ceil(employeeCount / 100) * 2;
        dailyTicketsInput.placeholder = `Auto: ~${estimated}`;
    }
}

/**
 * Perform ROI calculation
 */
function calculateROI() {
    try {
        // Gather inputs
        const inputs = {
            region: document.getElementById('anugal_region').value,
            employee_count: document.getElementById('anugal_employee_count').value,
            connected_apps: document.getElementById('anugal_connected_apps').value,
            am_percent: document.getElementById('anugal_am_percent').value,
            cli_percent: document.getElementById('anugal_cli_percent').value,
            review_cycles: document.getElementById('anugal_review_cycles').value,
            days_per_review: document.getElementById('anugal_days_per_review').value,
            daily_tickets: document.getElementById('anugal_daily_tickets').value
        };
        
        // Perform calculation
        calculationResults = calculator.calculate(inputs);
        
        // Update UI
        displayResults(calculationResults);
        
        // Enable download button
        const downloadBtn = document.getElementById('anugal_downloadReport');
        if (downloadBtn) {
            downloadBtn.disabled = false;
        }
        
        // Scroll to results on mobile
        if (window.innerWidth < 1024) {
            const resultsSection = document.querySelector('.anugal-roi-results-section');
            if (resultsSection) {
                resultsSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
        
    } catch (error) {
        alert('Error calculating ROI: ' + error.message);
        console.error('Calculation error:', error);
    }
}

/**
 * Display calculation results in the UI
 */
function displayResults(results) {
    // Operational Efficiency
    const opEffValue = results.operational_efficiency.display === 'usd' 
        ? results.operational_efficiency.usd 
        : results.operational_efficiency.eur;
    const opEffCurrency = results.operational_efficiency.display === 'usd' ? 'USD' : 'EUR';
    const opEffElem = document.getElementById('anugal_operational_efficiency');
    if (opEffElem) {
        opEffElem.textContent = calculator.formatCurrency(opEffValue, opEffCurrency);
    }
    
    // Subscription Cost
    const subValue = results.subscription_cost.display === 'usd' 
        ? results.subscription_cost.usd 
        : results.subscription_cost.eur;
    const subCurrency = results.subscription_cost.display === 'usd' ? 'USD' : 'EUR';
    const subElem = document.getElementById('anugal_subscription_cost');
    if (subElem) {
        subElem.textContent = calculator.formatCurrency(subValue, subCurrency);
    }
    
    // Implementation Cost
    const implValue = results.implementation_cost.display === 'usd' 
        ? results.implementation_cost.usd 
        : results.implementation_cost.eur;
    const implCurrency = results.implementation_cost.display === 'usd' ? 'USD' : 'EUR';
    const implElem = document.getElementById('anugal_implementation_cost');
    if (implElem) {
        implElem.textContent = calculator.formatCurrency(implValue, implCurrency);
    }
    
    // ROI Year 1
    const roi1Value = results.roi_year1.display === 'usd' 
        ? results.roi_year1.net_usd 
        : results.roi_year1.net_eur;
    const roi1Currency = results.roi_year1.display === 'usd' ? 'USD' : 'EUR';
    const roi1Elem = document.getElementById('anugal_roi_year1');
    if (roi1Elem) {
        roi1Elem.innerHTML = 
            calculator.formatCurrency(roi1Value, roi1Currency) + 
            '<br><small style="font-size: 14px; opacity: 0.8;">(' + 
            calculator.formatPercent(results.roi_year1.percent) + ' ROI)</small>';
    }
    
    // ROI 3 Years
    const roi3Value = results.roi_3year.display === 'usd' 
        ? results.roi_3year.net_usd 
        : results.roi_3year.net_eur;
    const roi3Currency = results.roi_3year.display === 'usd' ? 'USD' : 'EUR';
    const roi3Elem = document.getElementById('anugal_roi_3year');
    if (roi3Elem) {
        roi3Elem.innerHTML = 
            calculator.formatCurrency(roi3Value, roi3Currency) + 
            '<br><small style="font-size: 14px; opacity: 0.8;">(' + 
            calculator.formatPercent(results.roi_3year.percent) + ' ROI)</small>';
    }
}

/**
 * Generate and download report directly (no modal popup)
 */
function generateAndDownloadReport() {
    try {
        if (!calculationResults) {
            alert('Please calculate ROI first');
            return;
        }
        
        const results = calculationResults;
        const currency = results.operational_efficiency.display === 'usd' ? 'USD' : 'EUR';
        
        // Build CSV content
        let csv = 'Anugal ROI Calculator Report\n\n';
        
        // Report metadata
        csv += `Report Date,${new Date().toLocaleDateString()}\n`;
        csv += `Report Time,${new Date().toLocaleTimeString()}\n\n`;
        
        // Input Parameters
        csv += 'Input Parameters\n';
        const inputs = results.raw.inputs;
        csv += `Region,${inputs.region}\n`;
        csv += `Total Employees,${inputs.employee_count}\n`;
        csv += `Applications to Govern,${inputs.connected_apps}\n`;
        csv += `% Operators/Approvers,${(inputs.am_percent * 100).toFixed(1)}%\n`;
        csv += `% Audit-Only Identities,${(inputs.cli_percent * 100).toFixed(1)}%\n`;
        csv += `Access Review Cycles/Year,${inputs.review_cycles_per_year}\n`;
        csv += `Days per Review Cycle,${inputs.days_per_review}\n`;
        csv += `Daily Access Tickets,${inputs.daily_access_tickets}\n\n`;
        
        // Results
        csv += 'Results\n';
        const opEffValue = currency === 'USD' ? results.operational_efficiency.usd : results.operational_efficiency.eur;
        const subValue = currency === 'USD' ? results.subscription_cost.usd : results.subscription_cost.eur;
        const implValue = currency === 'USD' ? results.implementation_cost.usd : results.implementation_cost.eur;
        const roi1Value = currency === 'USD' ? results.roi_year1.net_usd : results.roi_year1.net_eur;
        const roi3Value = currency === 'USD' ? results.roi_3year.net_usd : results.roi_3year.net_eur;
        
        csv += `Operational Efficiency Gained,${calculator.formatCurrency(opEffValue, currency)}\n`;
        csv += `Annual Hours Saved,${results.hours_saved_annual} hours\n`;
        csv += `Annual Subscription Cost,${calculator.formatCurrency(subValue, currency)}\n`;
        csv += `One-Time Implementation Cost,${calculator.formatCurrency(implValue, currency)}\n`;
        csv += `Year 1 Net Benefit,${calculator.formatCurrency(roi1Value, currency)}\n`;
        csv += `Year 1 ROI,${calculator.formatPercent(results.roi_year1.percent)}\n`;
        csv += `3-Year Net Benefit,${calculator.formatCurrency(roi3Value, currency)}\n`;
        csv += `3-Year ROI,${calculator.formatPercent(results.roi_3year.percent)}\n\n`;
        
        // Breakdown
        csv += 'Detailed Breakdown\n';
        csv += `Ticket Hours Saved Annually,${Math.round(results.raw.savings.ticket_hours_saved)} hours\n`;
        csv += `Review Hours Saved Annually,${Math.round(results.raw.savings.review_hours_saved)} hours\n`;
        csv += `Total Hours Saved Annually,${results.hours_saved_annual} hours\n`;
        
        // Download CSV
        downloadFile(csv, `Anugal_ROI_Report_${new Date().getTime()}.csv`, 'text/csv');
        
    } catch (error) {
        alert('Error generating report: ' + error.message);
        console.error('Report generation error:', error);
    }
}

/**
 * Download a file
 */
function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
