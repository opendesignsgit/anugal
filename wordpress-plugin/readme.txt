=== Anugal ROI Calculator ===
Contributors: anugal
Tags: roi, calculator, identity governance, iga, shortcode
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ROI Calculator for Anugal Identity Governance Platform. Calculate operational efficiency and return on investment.

== Description ==

The Anugal ROI Calculator helps organizations estimate the operational efficiency gains and financial returns from implementing Anugal's Identity Governance and Administration (IGA) platform.

**Features:**

* Responsive design that works on desktop, tablet, and mobile devices
* Real-time ROI calculations based on user inputs
* Multi-currency support (EUR/USD) based on region
* Direct CSV report download
* Easy to use shortcode: `[anugal_roi_calculator]`
* No external dependencies

**Calculation Algorithm:**

The calculator implements a comprehensive algorithm that includes:

* Subscription pricing (conditional based on employee count: ≤500 vs >500)
* Implementation cost calculation
* Operational efficiency modeling (ticket and review automation savings)
* Multi-year ROI projections (Year 1 and 3-year)

**Usage:**

Simply add the shortcode `[anugal_roi_calculator]` to any page or post where you want the calculator to appear.

Optional shortcode parameters:
* `title` - Custom title for the calculator (default: "Company Profile")

Example: `[anugal_roi_calculator title="ROI Calculator"]`

== Installation ==

1. Upload the `anugal-roi-calculator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the shortcode `[anugal_roi_calculator]` to any page or post

== Frequently Asked Questions ==

= How do I display the calculator? =

Add the shortcode `[anugal_roi_calculator]` to any page or post.

= Can I customize the calculator? =

Yes, you can modify the CSS in `assets/css/styles.css` to match your theme.

= Does it require any external services? =

No, the calculator runs entirely client-side in the browser. No data is sent to external servers.

= What currencies are supported? =

The calculator supports EUR and USD. The currency displayed depends on the region selected.

= How is the ROI calculated? =

The calculator follows a comprehensive algorithm that considers:
- Subscription pricing based on company size
- Implementation costs
- Ticket handling efficiency (30% automation gain)
- Access review efficiency (40% automation gain)
- Fully loaded labor cost at €50/hour

== Screenshots ==

1. Calculator form with input fields
2. Results display with ROI metrics
3. Mobile responsive view

== Changelog ==

= 1.0.0 =
* Initial release
* WordPress shortcode implementation
* Direct CSV download (no popup form)
* Responsive design
* Multi-currency support

== Upgrade Notice ==

= 1.0.0 =
Initial release of the Anugal ROI Calculator plugin.

== Algorithm Details ==

**Subscription Pricing:**

For organizations with ≤500 employees:
* Non-CLI identities: €4.00/month
* CLI (audit-only) identities: €0.75/month

For organizations with >500 employees:
* Access Manager identities: €4.00/month
* Regular identities: €2.00/month
* CLI identities: €0.75/month

**Implementation Cost:**
€12,500 + (€2,500 × Number of Applications)

**Operational Efficiency:**

Ticket Savings:
* Baseline: Daily tickets × 260 working days × 15 minutes per ticket
* Efficiency gain: 30% reduction through automation

Review Savings:
* Reviewer pool: 10% of employees (≤500) or AM count (>500)
* Active reviewers: 35% of pool participate per cycle
* Baseline: Cycles × Days × 8 hours × Active reviewers
* Efficiency gain: 40% reduction through streamlined workflows

**Monetization:**
Hours saved valued at €50/hour (fully loaded cost)

**ROI Formulas:**
* Year 1 ROI = (Annual Savings - Year 1 Cost) / Year 1 Cost × 100%
* 3-Year ROI = (3-Year Savings - 3-Year Cost) / 3-Year Cost × 100%

== Support ==

For support, please visit https://anugal.com/support
