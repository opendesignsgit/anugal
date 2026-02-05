/**
 * ROI Calculator Application
 * Handles UI interactions and report generation
 */

// Global state
let calculationResults = null;
const calculator = new ROICalculator();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeModal();
});

/**
 * Initialize form interactions
 */
function initializeForm() {
    const form = document.getElementById('roiForm');
    const employeeInput = document.getElementById('employee_count');
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        calculateROI();
    });
    
    // Auto-update dependent fields when employee count changes
    employeeInput.addEventListener('input', function() {
        updateDependentFields();
    });
}

/**
 * Update fields that depend on employee count
 */
function updateDependentFields() {
    const employeeCount = parseInt(document.getElementById('employee_count').value);
    const dailyTicketsInput = document.getElementById('daily_tickets');
    
    // If daily tickets is empty, show auto-calculated value as placeholder
    if (!dailyTicketsInput.value && employeeCount > 0) {
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
            region: document.getElementById('region').value,
            employee_count: document.getElementById('employee_count').value,
            connected_apps: document.getElementById('connected_apps').value,
            am_percent: document.getElementById('am_percent').value,
            cli_percent: document.getElementById('cli_percent').value,
            review_cycles: document.getElementById('review_cycles').value,
            days_per_review: document.getElementById('days_per_review').value,
            daily_tickets: document.getElementById('daily_tickets').value
        };
        
        // Perform calculation
        calculationResults = calculator.calculate(inputs);
        
        // Update UI
        displayResults(calculationResults);
        
        // Enable download button
        document.getElementById('downloadReport').disabled = false;
        
        // Scroll to results on mobile
        if (window.innerWidth < 1024) {
            document.querySelector('.results-section').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
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
    document.getElementById('operational_efficiency').textContent = 
        calculator.formatCurrency(opEffValue, opEffCurrency);
    
    // Subscription Cost
    const subValue = results.subscription_cost.display === 'usd' 
        ? results.subscription_cost.usd 
        : results.subscription_cost.eur;
    const subCurrency = results.subscription_cost.display === 'usd' ? 'USD' : 'EUR';
    document.getElementById('subscription_cost').textContent = 
        calculator.formatCurrency(subValue, subCurrency);
    
    // Implementation Cost
    const implValue = results.implementation_cost.display === 'usd' 
        ? results.implementation_cost.usd 
        : results.implementation_cost.eur;
    const implCurrency = results.implementation_cost.display === 'usd' ? 'USD' : 'EUR';
    document.getElementById('implementation_cost').textContent = 
        calculator.formatCurrency(implValue, implCurrency);
    
    // ROI Year 1
    const roi1Value = results.roi_year1.display === 'usd' 
        ? results.roi_year1.net_usd 
        : results.roi_year1.net_eur;
    const roi1Currency = results.roi_year1.display === 'usd' ? 'USD' : 'EUR';
    document.getElementById('roi_year1').innerHTML = 
        calculator.formatCurrency(roi1Value, roi1Currency) + 
        '<br><small style="font-size: 14px; opacity: 0.8;">(' + 
        calculator.formatPercent(results.roi_year1.percent) + ' ROI)</small>';
    
    // ROI 3 Years
    const roi3Value = results.roi_3year.display === 'usd' 
        ? results.roi_3year.net_usd 
        : results.roi_3year.net_eur;
    const roi3Currency = results.roi_3year.display === 'usd' ? 'USD' : 'EUR';
    document.getElementById('roi_3year').innerHTML = 
        calculator.formatCurrency(roi3Value, roi3Currency) + 
        '<br><small style="font-size: 14px; opacity: 0.8;">(' + 
        calculator.formatPercent(results.roi_3year.percent) + ' ROI)</small>';
}

/**
 * Initialize modal interactions
 */
function initializeModal() {
    const modal = document.getElementById('downloadModal');
    const downloadBtn = document.getElementById('downloadReport');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelDownload');
    const downloadForm = document.getElementById('downloadForm');
    
    // Open modal
    downloadBtn.addEventListener('click', function() {
        if (calculationResults) {
            modal.classList.add('active');
        }
    });
    
    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    
    cancelBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    
    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
    
    // Handle form submission
    downloadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
}

/**
 * Generate and download report
 */
function generateReport() {
    try {
        const reportData = {
            name: document.getElementById('report_name').value,
            email: document.getElementById('report_email').value,
            company: document.getElementById('report_company').value,
            jobTitle: document.getElementById('report_title').value
        };
        
        // Generate CSV report (fallback)
        generateCSVReport(reportData);
        
        // Close modal
        document.getElementById('downloadModal').classList.remove('active');
        
        // Reset form
        document.getElementById('downloadForm').reset();
        
    } catch (error) {
        alert('Error generating report: ' + error.message);
        console.error('Report generation error:', error);
    }
}

/**
 * Generate CSV report
 */
function generateCSVReport(reportData) {
    const results = calculationResults;
    const currency = results.operational_efficiency.display === 'usd' ? 'USD' : 'EUR';
    
    // Build CSV content
    let csv = 'Anugal ROI Calculator Report\n\n';
    
    // Contact Information
    csv += 'Contact Information\n';
    csv += `Name,${reportData.name}\n`;
    csv += `Email,${reportData.email}\n`;
    csv += `Company,${reportData.company}\n`;
    csv += `Job Title,${reportData.jobTitle}\n`;
    csv += `Report Date,${new Date().toLocaleDateString()}\n\n`;
    
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
    csv += `Daily Access Tickets,${inputs.daily_access_tickets || 'Auto-calculated'}\n\n`;
    
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
