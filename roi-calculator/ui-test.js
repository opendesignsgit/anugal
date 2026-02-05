// Simple UI element verification
const fs = require('fs');
const html = fs.readFileSync('index.html', 'utf8');

console.log('UI Component Verification');
console.log('=========================\n');

// Check for required form fields
const fields = [
    'name', 'phone', 'email', 'company', 'region',
    'employee_count', 'connected_apps', 'am_percent',
    'cli_percent', 'review_cycles', 'days_per_review',
    'daily_tickets'
];

let allFieldsPresent = true;
fields.forEach(field => {
    if (html.includes(`id="${field}"`)) {
        console.log(`✓ Field present: ${field}`);
    } else {
        console.log(`✗ Field missing: ${field}`);
        allFieldsPresent = false;
    }
});

console.log('\nUI Elements:');
console.log(html.includes('CALCULATE ROI') ? '✓ CALCULATE ROI button' : '✗ Missing CALCULATE ROI');
console.log(html.includes('DOWNLOAD REPORT') ? '✓ DOWNLOAD REPORT button' : '✗ Missing DOWNLOAD REPORT');
console.log(html.includes('downloadModal') ? '✓ Download modal' : '✗ Missing modal');
console.log(html.includes('operational_efficiency') ? '✓ Operational efficiency output' : '✗ Missing output');
console.log(html.includes('subscription_cost') ? '✓ Subscription cost output' : '✗ Missing output');
console.log(html.includes('implementation_cost') ? '✓ Implementation cost output' : '✗ Missing output');
console.log(html.includes('roi_year1') ? '✓ ROI Year 1 output' : '✗ Missing output');
console.log(html.includes('roi_3year') ? '✓ ROI 3-Year output' : '✗ Missing output');

console.log('\nStyling:');
const css = fs.readFileSync('css/styles.css', 'utf8');
console.log(css.includes('results-card') ? '✓ Results card styles' : '✗ Missing card styles');
console.log(css.includes('@media') ? '✓ Responsive breakpoints' : '✗ Missing responsive design');
console.log(css.includes('modal') ? '✓ Modal styles' : '✗ Missing modal styles');

console.log('\nJavaScript:');
const appJs = fs.readFileSync('js/app.js', 'utf8');
console.log(appJs.includes('calculateROI') ? '✓ ROI calculation function' : '✗ Missing calculation');
console.log(appJs.includes('generateReport') ? '✓ Report generation' : '✗ Missing report generation');
console.log(appJs.includes('displayResults') ? '✓ Display results function' : '✗ Missing display function');

console.log('\n' + (allFieldsPresent ? '✓ All UI components present!' : '✗ Some components missing'));
