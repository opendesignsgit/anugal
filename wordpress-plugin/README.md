# Anugal ROI Calculator - WordPress Plugin

A WordPress plugin that provides a shortcode to display an ROI (Return on Investment) calculator for Anugal's Identity Governance and Administration (IGA) platform.

## Installation

1. Upload the `anugal-roi-calculator` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the shortcode `[anugal_roi_calculator]` to any page or post

## Usage

### Basic Shortcode

```
[anugal_roi_calculator]
```

### Shortcode with Custom Title

```
[anugal_roi_calculator title="ROI Calculator"]
```

## Features

- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Real-time Calculations**: Instant ROI calculations based on user inputs
- **Multi-currency Support**: Automatic EUR/USD conversion based on region
- **Direct Download**: CSV report downloads directly without popup forms
- **No External Dependencies**: All calculations run client-side
- **WordPress Integration**: Clean shortcode integration with proper asset enqueuing

## Input Fields

The calculator requires the following inputs:

1. **Region** (required) - Geographic location for currency display
2. **Total Number of Employees** (required) - All full-time employees and contractors
3. **No. of Applications to Govern** (required) - Number of apps to integrate
4. **% of People Who Operate or Approve Access** (optional, default: 10%)
5. **% of Identities Tracked for Audit Only** (optional, default: 0%)
6. **Access Review Frequency** (optional, default: 2x per year)
7. **Days per Review Cycle** (optional, default: 7 days)
8. **Daily Access Tickets** (optional, auto-calculated if left blank)

## Output Metrics

The calculator displays five key metrics:

1. **Operational Efficiency Gained** - Annual savings in currency and hours
2. **Subscription Cost (Annual)** - Recurring annual cost
3. **Implementation Cost (One-Time)** - One-time deployment cost
4. **Return on Investment Year 1** - Net benefit and ROI percentage
5. **Return on Investment 3 Years** - Cumulative net benefit and ROI percentage

## Calculation Algorithm

### Subscription Pricing

**Small Organizations (≤500 employees):**
- Non-CLI identities: €4.00/month
- CLI identities: €0.75/month

**Large Organizations (>500 employees):**
- Access Manager identities: €4.00/month
- Regular identities: €2.00/month
- CLI identities: €0.75/month

### Implementation Cost

```
Implementation Cost = €12,500 + (€2,500 × Number of Applications)
```

### Operational Efficiency

**Ticket Savings:**
- Baseline: Daily tickets × 260 days × 15 minutes per ticket
- Efficiency gain: 30% through automation

**Review Savings:**
- Reviewer pool: 10% of employees (≤500) or AM count (>500)
- Active reviewers: 35% of pool participate per cycle
- Baseline: Cycles × Days × 8 hours × Active reviewers
- Efficiency gain: 40% through streamlined workflows

**Monetization:**
- Hours saved valued at €50/hour (fully loaded cost)

### ROI Calculations

```
Year 1 ROI = (Annual Savings - Year 1 Cost) / Year 1 Cost × 100%
3-Year ROI = (3-Year Savings - 3-Year Cost) / 3-Year Cost × 100%
```

## File Structure

```
anugal-roi-calculator/
├── anugal-roi-calculator.php    # Main plugin file
├── readme.txt                    # WordPress plugin readme
├── README.md                     # This file
├── assets/
│   ├── css/
│   │   └── styles.css           # Calculator styles
│   └── js/
│       ├── calculator.js        # Calculation engine
│       └── app.js               # UI interactions
└── templates/
    └── calculator-template.php  # HTML template
```

## Customization

### Styling

Modify `assets/css/styles.css` to customize the appearance. All classes are prefixed with `anugal-roi-` to avoid conflicts.

### Exchange Rate

Update the exchange rate in `assets/js/calculator.js`:

```javascript
this.FX_RATE_EUR_TO_USD = 1.08; // Update this value
```

### Efficiency Assumptions

Modify assumptions in `assets/js/calculator.js`:

```javascript
this.TICKET_EFFICIENCY_GAIN = 0.30;  // 30% ticket efficiency
this.REVIEW_EFFICIENCY_GAIN = 0.40;  // 40% review efficiency
this.COST_PER_HOUR_EUR = 50;         // €50 per hour
```

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Security

- No data is sent to external servers
- All calculations run client-side
- Input validation prevents invalid data
- WordPress nonce verification (if forms extended)

## Changelog

### 1.0.0 (2026-02)
- Initial release
- WordPress shortcode implementation
- Direct CSV download without popup
- Responsive design
- Multi-currency support

## Support

For support, please visit: https://anugal.com/support

## License

GPL-2.0+
