# Anugal ROI Calculator

A responsive web-based ROI (Return on Investment) calculator for Anugal's Identity Governance and Administration (IGA) platform.

## Overview

This calculator helps organizations estimate the operational efficiency gains and financial returns from implementing Anugal's IGA solution. It calculates cost savings across access reviews and access ticket management, comparing these against subscription and implementation costs.

## Features

- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Real-time Calculations**: Instant ROI calculations based on user inputs
- **Multi-currency Support**: Automatic EUR/USD conversion based on region
- **Report Generation**: Download detailed CSV reports with all inputs and results
- **Accessibility**: WCAG-compliant form with proper labels and ARIA attributes
- **Validation**: Client-side input validation with helpful error messages

## Getting Started

### Prerequisites

- Modern web browser (Chrome, Firefox, Safari, Edge)
- No server-side dependencies required (pure client-side application)

### Installation

1. Clone or download the repository
2. Open `roi-calculator/index.html` in a web browser

That's it! No build process or dependencies to install.

### Hosting

To deploy on a web server:

1. Copy the entire `roi-calculator` folder to your web server
2. Ensure the folder structure is maintained
3. Access via `https://yourdomain.com/roi-calculator/`

## Usage

### Input Fields

The calculator requires the following inputs:

1. **Contact Information** (for report generation):
   - Name
   - Phone Number
   - Work Email
   - Company Name

2. **Organization Details**:
   - **Region**: Geographic location (affects currency display)
   - **Total Number of Employees**: All full-time employees and contractors
   - **No. of Applications to Govern**: Number of apps to integrate

3. **Access Management Details**:
   - **% of People Who Operate or Approve Access**: Percentage of employees who manage access (default: 10%)
   - **% of Identities Tracked for Audit Only**: Percentage of identities needing only audit trails (default: 0%)
   - **Access Review Frequency**: How often reviews are conducted (default: 2x per year)
   - **Days per Review Cycle**: Duration of each review cycle (default: 7 days)
   - **Daily Access Tickets**: Number of access-related tickets per day (auto-calculated if left blank)

### Calculation

1. Fill in all required fields (marked with *)
2. Optional fields will use sensible defaults if left blank
3. Click **CALCULATE ROI** to see results
4. Results appear in the right-side card with five key metrics:
   - Operational Efficiency Gained (annual savings)
   - Subscription Cost (annual)
   - Implementation Cost (one-time)
   - Return on Investment - Year 1
   - Return on Investment - 3 Years

### Downloading Reports

1. After calculating ROI, the **DOWNLOAD REPORT** button becomes enabled
2. Click the button to open the report form
3. Fill in contact details (if not already provided)
4. Click **Generate Report** to download a CSV file
5. The CSV includes all inputs, results, and detailed breakdowns

## Algorithm

The calculator implements the algorithm specified in `Anugal ROI Calculator - Algorithm.docx`. Key aspects:

### Subscription Pricing

- **≤500 employees**:
  - Non-CLI identities: €4/month
  - CLI (audit-only) identities: €0.75/month

- **>500 employees**:
  - Access Manager identities: €4/month
  - Regular identities: €2/month
  - CLI identities: €0.75/month

### Implementation Cost

```
Implementation Cost = €12,500 + (€2,500 × Number of Applications)
```

### Operational Efficiency

**Ticket Savings**:
- Baseline: Daily tickets × 260 working days × 15 minutes per ticket
- Savings: 30% efficiency gain through automation

**Review Savings**:
- Reviewer pool: 10% of employees (≤500) or AM count (>500)
- Active reviewers: 35% of pool participate per cycle
- Baseline: Cycles × Days × 8 hours × Active reviewers
- Savings: 40% efficiency gain through streamlined workflows

**Monetization**:
- Hours saved valued at €50/hour (fully loaded cost)

### ROI Calculation

**Year 1**:
```
ROI Year 1 = (Annual Savings - Year 1 Cost) / Year 1 Cost × 100%
Where Year 1 Cost = Annual Subscription + Implementation Cost
```

**3-Year**:
```
ROI 3-Year = (3-Year Savings - 3-Year Cost) / 3-Year Cost × 100%
Where 3-Year Cost = (Annual Subscription × 3) + Implementation Cost
```

## File Structure

```
roi-calculator/
├── index.html          # Main HTML structure
├── css/
│   └── styles.css      # All styling and responsive design
├── js/
│   ├── calculator.js   # ROI calculation engine
│   └── app.js          # UI interactions and report generation
└── README.md           # This file
```

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Assumptions and Limitations

1. **Currency**: All calculations performed in EUR, USD shown for display only
2. **Exchange Rate**: Fixed at 1.08 EUR/USD (can be updated in calculator.js)
3. **Working Days**: 260 days per year assumed
4. **Efficiency Gains**: Conservative estimates (30% for tickets, 40% for reviews)
5. **Cost per Hour**: €50 fully loaded blended rate
6. **Report Format**: CSV only (PDF generation requires external library)

## Customization

### Updating Exchange Rates

Edit `js/calculator.js`:

```javascript
this.FX_RATE_EUR_TO_USD = 1.08; // Update this value
```

### Modifying Efficiency Assumptions

Edit constants in `js/calculator.js`:

```javascript
this.TICKET_EFFICIENCY_GAIN = 0.30;  // 30% ticket efficiency
this.REVIEW_EFFICIENCY_GAIN = 0.40;  // 40% review efficiency
this.COST_PER_HOUR_EUR = 50;         // €50 per hour
```

### Styling

All visual styling is in `css/styles.css`. The design follows the reference image (`roi-calculator.png`) with:
- Dark blue gradient results card (#2d3561 to #1e2347)
- Blue accent color for highlights (#5b7cff)
- Responsive breakpoints at 1024px, 768px, and 480px

## Testing

### Manual Testing

1. **Example Calculation** (from algorithm document):
   - Region: US
   - Employees: 420
   - Applications: 5
   - AM %: 10%
   - CLI %: 15%
   - Review cycles: 2
   - Days per review: 7
   - Daily tickets: 10

   Expected results:
   - Annual subscription: ~€17,703 (~$19,119)
   - Implementation: €25,000 ($27,000)
   - Hours saved: ~854 hours/year
   - Year 1 ROI: ~0%
   - 3-Year ROI: ~105%

2. **Validation Tests**:
   - Enter negative employee count → Error message
   - Enter AM% + CLI% > 100% → Error message
   - Leave optional fields blank → Defaults applied
   - Change employee count → Daily tickets placeholder updates

### Unit Testing

To add unit tests, include a testing framework (e.g., Jest) and test the `ROICalculator` class methods:

```javascript
const calculator = new ROICalculator();
const results = calculator.calculate({
    region: 'US',
    employee_count: 420,
    connected_apps: 5,
    // ... other inputs
});
// Assert expected values
```

## Maintenance

### Common Updates

1. **Pricing Changes**: Update subscription rates in `calculateSubscription()` method
2. **Algorithm Changes**: Modify calculation methods in `calculator.js`
3. **New Fields**: Add HTML inputs, update `calculate()` method, and adjust validation
4. **UI Changes**: Modify `index.html` and `styles.css`

### Version History

- **v1.0.0** (2026-02): Initial release
  - Core ROI calculation engine
  - Responsive UI matching design
  - CSV report generation
  - Mobile-responsive layout

## Support

For issues or questions:
1. Check this README for common solutions
2. Review the algorithm document for calculation logic
3. Inspect browser console for JavaScript errors
4. Verify all inputs are valid

## License

Proprietary - Anugal © 2026

## Credits

- Design: Based on `roi-calculator.png`
- Algorithm: Specified in `Anugal ROI Calculator - Algorithm.docx`
- Implementation: Anugal Development Team
