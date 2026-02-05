# Anugal ROI Calculator WordPress Plugin - Installation Guide

## Quick Installation

### Method 1: Upload via WordPress Admin

1. Download the `wordpress-plugin` folder and rename it to `anugal-roi-calculator`
2. Create a ZIP file of the `anugal-roi-calculator` folder
3. In WordPress admin, go to **Plugins > Add New > Upload Plugin**
4. Click **Choose File** and select the ZIP file
5. Click **Install Now**
6. Click **Activate Plugin**

### Method 2: Manual Upload via FTP

1. Download the `wordpress-plugin` folder and rename it to `anugal-roi-calculator`
2. Upload the entire `anugal-roi-calculator` folder to `/wp-content/plugins/`
3. In WordPress admin, go to **Plugins**
4. Find "Anugal ROI Calculator" and click **Activate**

### Method 3: Direct Server Access

```bash
# Navigate to plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Copy the plugin folder (rename wordpress-plugin to anugal-roi-calculator)
cp -r /path/to/repository/wordpress-plugin anugal-roi-calculator

# Set proper permissions
chmod -R 755 anugal-roi-calculator

# Activate via WordPress admin or WP-CLI
wp plugin activate anugal-roi-calculator
```

## Using the Shortcode

### Basic Usage

Add to any page or post:

```
[anugal_roi_calculator]
```

### With Custom Title

```
[anugal_roi_calculator title="ROI Calculator"]
```

### Example Page Setup

1. Create a new page: **Pages > Add New**
2. Title: "ROI Calculator"
3. In the content editor, add the shortcode: `[anugal_roi_calculator]`
4. Publish the page
5. View the page to see the calculator

### Gutenberg Block Editor

1. Add a **Shortcode Block**
2. Enter: `[anugal_roi_calculator]`
3. Preview or publish

### Classic Editor

1. Switch to **Text** mode
2. Add: `[anugal_roi_calculator]`
3. Switch back to **Visual** mode (optional)
4. Preview or publish

## Customization

### Styling

To match your theme's design, you can add custom CSS in:
**Appearance > Customize > Additional CSS**

Example customizations:

```css
/* Change primary button color */
.anugal-roi-btn-primary {
    background: #your-color !important;
}

/* Adjust results card gradient */
.anugal-roi-results-card {
    background: linear-gradient(135deg, #your-color1 0%, #your-color2 100%) !important;
}

/* Modify form width */
.anugal-roi-container {
    max-width: 1200px !important;
}
```

### Exchange Rate

To update the EUR/USD exchange rate:

1. Navigate to: `/wp-content/plugins/anugal-roi-calculator/assets/js/calculator.js`
2. Find line: `this.FX_RATE_EUR_TO_USD = 1.08;`
3. Update to current rate: `this.FX_RATE_EUR_TO_USD = 1.10;`
4. Clear any caching

### Efficiency Assumptions

To modify calculation assumptions in `calculator.js`:

```javascript
// Line ~15-20
this.TICKET_EFFICIENCY_GAIN = 0.30;  // 30% ticket efficiency
this.REVIEW_EFFICIENCY_GAIN = 0.40;  // 40% review efficiency  
this.COST_PER_HOUR_EUR = 50;         // €50 per hour
```

## Verification

After installation, verify the plugin is working:

1. **Check Plugin is Active**
   - Go to **Plugins** in WordPress admin
   - Verify "Anugal ROI Calculator" shows as **Active**

2. **Test on a Page**
   - Create a test page with the shortcode
   - View the page
   - You should see the calculator form and results card

3. **Test Calculation**
   - Fill in the form with sample data:
     - Region: US
     - Employees: 420
     - Applications: 5
     - Leave other fields default
   - Click **CALCULATE ROI**
   - Results should appear in the right panel

4. **Test Download**
   - After calculating, click **DOWNLOAD REPORT**
   - A CSV file should download immediately (no popup)

## Troubleshooting

### Shortcode Not Working

**Problem**: Shortcode appears as text `[anugal_roi_calculator]` on the page

**Solutions**:
1. Verify plugin is activated
2. Clear WordPress cache (if using caching plugin)
3. Try deactivating and reactivating the plugin
4. Check for PHP errors in debug log

### Styling Issues

**Problem**: Calculator doesn't look right or conflicts with theme

**Solutions**:
1. Check browser console for CSS errors
2. Verify CSS file is loading: `/wp-content/plugins/anugal-roi-calculator/assets/css/styles.css`
3. Add `!important` to custom CSS if theme styles conflict
4. Try a different WordPress theme temporarily to isolate issue

### JavaScript Not Working

**Problem**: Calculator doesn't calculate or download doesn't work

**Solutions**:
1. Check browser console for JavaScript errors
2. Verify JS files are loading:
   - `calculator.js`
   - `app.js`
3. Deactivate other plugins to check for conflicts
4. Clear browser cache
5. Try in incognito/private browsing mode

### Download Not Working

**Problem**: CSV doesn't download when clicking button

**Solutions**:
1. Check browser console for errors
2. Verify you've clicked **CALCULATE ROI** first
3. Check browser's download settings/permissions
4. Try a different browser
5. Disable popup blockers

## File Structure Reference

```
anugal-roi-calculator/
├── anugal-roi-calculator.php    # Main plugin file (registers shortcode)
├── readme.txt                    # WordPress.org plugin readme
├── README.md                     # Developer documentation
├── INSTALL.md                    # This file
├── assets/
│   ├── css/
│   │   └── styles.css           # All calculator styles (namespaced)
│   └── js/
│       ├── calculator.js        # ROI calculation engine
│       └── app.js               # UI interactions & download
└── templates/
    └── calculator-template.php  # HTML template for shortcode
```

## Support

For issues or questions:

1. Check this installation guide
2. Review troubleshooting section
3. Check browser console for errors
4. Visit: https://anugal.com/support

## Uninstallation

To remove the plugin:

1. In WordPress admin, go to **Plugins**
2. Find "Anugal ROI Calculator"
3. Click **Deactivate**
4. Click **Delete**
5. Confirm deletion

The plugin will be completely removed. No database entries are created, so there's nothing else to clean up.

## Version Information

- **Plugin Version**: 1.0.0
- **WordPress Version**: 5.0+
- **PHP Version**: 7.0+
- **Tested Up To**: WordPress 6.4

## License

GPL-2.0+
