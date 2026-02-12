# ROI Calculator - UI Guide

## Visual Layout

```
┌─────────────────────────────────────────────────────────────────┐
│                    Anugal ROI Calculator                         │
│                                                                  │
│  ┌───────────────────────┐  ┌──────────────────────────────┐  │
│  │   Company Profile     │  │    Results Card (Blue)       │  │
│  │   ─────────────────   │  │                              │  │
│  │                       │  │   $46,092                    │  │
│  │  [Name*        ]      │  │   Operational Efficiency     │  │
│  │  [Phone*       ]      │  │   Gained                     │  │
│  │                       │  │   ──────────────────────     │  │
│  │  [Email*       ]      │  │   $19,119                    │  │
│  │  [Company*     ]      │  │   Subscription Cost          │  │
│  │                       │  │   (Annual)                   │  │
│  │  [Region*   ▼  ]      │  │   ──────────────────────     │  │
│  │  [Employees*   ]      │  │   $27,000                    │  │
│  │                       │  │   Implementation Cost        │  │
│  │  [Apps*        ]      │  │   (One-Time)                 │  │
│  │  [AM %*        ]      │  │   ──────────────────────     │  │
│  │                       │  │   -$27                       │  │
│  │  [CLI %*       ]      │  │   Return on Investment       │  │
│  │  [Reviews* ▼   ]      │  │   Year 1 (-0.1% ROI)         │  │
│  │                       │  │   ──────────────────────     │  │
│  │  [Days/Review* ]      │  │   $53,919                    │  │
│  │  [Daily Tickets]      │  │   Return on Investment       │  │
│  │                       │  │   3 Years (63.9% ROI)        │  │
│  │                       │  │                              │  │
│  │  ┌─────────────────┐ │  │   ┌────────────────────┐     │  │
│  │  │ CALCULATE ROI   │ │  │   │ → DOWNLOAD REPORT  │     │  │
│  │  └─────────────────┘ │  │   └────────────────────┘     │  │
│  │                       │  │                              │  │
│  └───────────────────────┘  └──────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Color Scheme

### Primary Colors
- **Dark Blue Gradient**: `#2d3561` → `#1e2347` (Results card)
- **Accent Blue**: `#5b7cff` (Highlights, "Profile" text)
- **Dark Gray**: `#1a1a1a` (Buttons, text)
- **Light Background**: `#f5f5f5` (Page background)

### Text Colors
- **Primary Text**: `#333`
- **White Text**: `#fff` (On dark backgrounds)
- **Required Asterisk**: `#ff4444`
- **Placeholder**: `#999`

## Typography

### Font Family
```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 
             Roboto, 'Helvetica Neue', Arial, sans-serif;
```

### Font Sizes
- **Page Title**: 32px (Desktop), 24px (Mobile)
- **Result Values**: 42px (Desktop), 28-32px (Mobile)
- **Labels**: 14px
- **Result Labels**: 14px
- **Buttons**: 14px

## Component Breakdown

### 1. Form Section (Left Panel)

#### Header
```html
<h1>Company <span style="color: #5b7cff">Profile</span></h1>
```

#### Input Fields
Each field has:
- Label with optional asterisk for required fields
- Input/select element
- Consistent spacing (20px between rows)
- Two-column grid on desktop, single column on mobile

#### Example Field
```html
<div class="form-group">
    <label for="employee_count">
        Total Number of Employees<span class="required">*</span>
    </label>
    <input type="number" id="employee_count" 
           name="employee_count" min="1" required>
</div>
```

### 2. Results Section (Right Panel)

#### Card Design
- **Background**: Linear gradient with diagonal stripes overlay
- **Padding**: 40px (Desktop), 30px (Mobile)
- **Border Radius**: 12px
- **Shadow**: `0 8px 24px rgba(0, 0, 0, 0.15)`
- **Sticky Position**: Stays in view when scrolling (Desktop only)

#### Result Item Structure
```html
<div class="result-item">
    <div class="result-value">$46,092</div>
    <div class="result-label">Operational Efficiency<br>Gained</div>
</div>
<div class="result-divider"></div>
```

### 3. Buttons

#### Primary Button (CALCULATE ROI)
```css
background: #1a1a1a;
color: white;
padding: 14px 32px;
border-radius: 4px;
font-weight: 600;
letter-spacing: 0.5px;
```

#### Download Button
```css
background: #1a1a1a;
color: white;
display: inline-flex;
align-items: center;
gap: 10px;
```

### 4. Modal (Download Report)

#### Structure
- **Overlay**: Semi-transparent black (`rgba(0, 0, 0, 0.5)`)
- **Content**: White card, 500px max width
- **Animation**: Fade in + slide up
- **Close Options**: X button, cancel button, click outside

#### Form Fields
Same styling as main form:
- Name
- Work Email
- Company
- Job Title

## Responsive Breakpoints

### Desktop (>1024px)
```
┌────────────────┬─────────────┐
│  Form (left)   │ Results     │
│                │ (sticky)    │
└────────────────┴─────────────┘
```

### Tablet (768px - 1024px)
```
┌──────────────────────────────┐
│         Form                 │
├──────────────────────────────┤
│        Results               │
└──────────────────────────────┘
```

### Mobile (<768px)
```
┌──────────┐
│  Form    │
│ (single  │
│ column)  │
├──────────┤
│ Results  │
│ (compact)│
└──────────┘
```

## Interactive States

### Input Focus
```css
border-color: #5b7cff;
outline: none;
```

### Button Hover
```css
background: #333; /* Slightly lighter */
```

### Button Active
```css
transform: scale(0.98);
```

### Disabled State
```css
opacity: 0.5;
cursor: not-allowed;
```

## Animations

### Modal
```css
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
```

### Smooth Scrolling
```javascript
element.scrollIntoView({ 
    behavior: 'smooth',
    block: 'start'
});
```

## Accessibility Features

### Form Labels
```html
<label for="input_id">Label Text</label>
<input id="input_id" name="input_id">
```

### Required Fields
```html
<label>Field Name<span class="required">*</span></label>
<input required>
```

### Keyboard Navigation
- Tab through all interactive elements
- Enter to submit forms
- Escape to close modal
- Focus visible on all interactive elements

### Screen Reader Support
- Semantic HTML structure
- Proper heading hierarchy
- Form labels tied to inputs
- Button text is descriptive

## Grid Layout

### Desktop Form Grid
```css
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
```

### Container Grid
```css
.container {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 40px;
}
```

## Number Formatting

### Currency Display
```javascript
// Example: $46,092
formatCurrency(46092, 'USD')
// Output: "$46,092"
```

### Percentage Display
```javascript
// Example: 63.9%
formatPercent(63.9)
// Output: "63.9%"
```

### Hours Display
```javascript
// Example: 854 hours
formatHours(854)
// Output: "854 hours"
```

## Auto-Update Behavior

When employee count changes:
```javascript
// Update daily tickets placeholder
dailyTicketsInput.placeholder = `Auto: ~${estimated}`;
```

On form submit:
```javascript
// Calculate ROI
calculateROI();
// Scroll to results (mobile)
resultsSection.scrollIntoView({ behavior: 'smooth' });
// Enable download button
downloadButton.disabled = false;
```

## Error Handling

### Validation Messages
```javascript
alert('Error calculating ROI: ' + error.message);
```

### Input Constraints
- `min` attributes on number inputs
- `required` attributes on mandatory fields
- `type` attributes for email, tel validation
- Custom validation for percentage sums

## Performance

### Load Time
- No external resources: <100ms
- All assets local: instant load
- CSS: ~6KB
- JavaScript: ~23KB (calculator + app)
- HTML: ~9KB

### Rendering
- Single page: no navigation
- No heavy frameworks
- Vanilla JavaScript for performance
- CSS Grid for efficient layout

## Browser Support

Tested and working on:
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

Features used:
- CSS Grid
- ES6 (const, let, arrow functions)
- Fetch API (not used, but available)
- FormData API
- Blob API (for downloads)

---

## Quick Reference

### File Locations
- HTML: `roi-calculator/index.html`
- CSS: `roi-calculator/css/styles.css`
- JS: `roi-calculator/js/app.js`, `calculator.js`

### Key Classes
- `.container` - Main layout grid
- `.form-section` - Left panel
- `.results-section` - Right panel
- `.results-card` - Blue gradient card
- `.result-item` - Individual result
- `.modal` - Download modal overlay
- `.btn-primary` - Primary button style

### Key IDs
- `#roiForm` - Main form
- `#employee_count` - Employee input (triggers auto-update)
- `#downloadReport` - Download button
- `#downloadModal` - Modal element
- `#operational_efficiency` - First result value
- `#roi_3year` - Last result value
