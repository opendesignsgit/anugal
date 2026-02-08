<?php
/**
 * ROI Calculator Submission Custom Post Type
 * - Stores ROI calculation submissions
 * - Triggers email notifications
 * - Admin settings for email configuration
 */

if (!defined('ABSPATH')) exit;
if (defined('ROI_SUBMISSION_CPT_LOADED')) return;
define('ROI_SUBMISSION_CPT_LOADED', true);

if (!class_exists('ROI_Submission_CPT')) {
  class ROI_Submission_CPT {
    
    const POST_TYPE = 'roi_submission';
    const OPTION_ADMIN_EMAIL = 'roi_calculator_admin_email';
    const NONCE_ACTION = 'roi_submission_nonce';

    public function __construct() {
      add_action('init', array($this, 'register_post_type'));
      add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
      add_action('admin_menu', array($this, 'add_settings_page'));
      add_action('admin_init', array($this, 'register_settings'));
      add_action('wp_ajax_roi_submit_calculation', array($this, 'handle_submission'));
      add_action('wp_ajax_nopriv_roi_submit_calculation', array($this, 'handle_submission'));
      
      // Add AJAX URL to the calculator
      add_action('wp_enqueue_scripts', array($this, 'localize_script'));
    }

    /**
     * Register the ROI Submission custom post type
     */
    public function register_post_type() {
      $labels = array(
        'name'               => 'ROI Submissions',
        'singular_name'      => 'ROI Submission',
        'menu_name'          => 'ROI Submissions',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Submission',
        'edit_item'          => 'View Submission',
        'new_item'           => 'New Submission',
        'view_item'          => 'View Submission',
        'search_items'       => 'Search Submissions',
        'not_found'          => 'No submissions found',
        'not_found_in_trash' => 'No submissions found in trash',
      );

      $args = array(
        'labels'              => $labels,
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => false,
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 30,
        'menu_icon'           => 'dashicons-chart-line',
        'supports'            => array('title'),
        'capabilities'        => array(
          'create_posts' => false, // Remove "Add New" button
        ),
        'map_meta_cap'        => true,
      );

      register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Add meta boxes to display submission details
     */
    public function add_meta_boxes() {
      add_meta_box(
        'roi_submission_details',
        'Submission Details',
        array($this, 'render_details_meta_box'),
        self::POST_TYPE,
        'normal',
        'high'
      );
      
      add_meta_box(
        'roi_calculation_results',
        'Calculation Results',
        array($this, 'render_results_meta_box'),
        self::POST_TYPE,
        'normal',
        'default'
      );
    }

    /**
     * Render the submission details meta box
     */
    public function render_details_meta_box($post) {
      $meta = get_post_meta($post->ID);
      ?>
      <style>
        .roi-meta-table { width: 100%; border-collapse: collapse; }
        .roi-meta-table th, .roi-meta-table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        .roi-meta-table th { width: 200px; font-weight: 600; background: #f9f9f9; }
      </style>
      <table class="roi-meta-table">
        <tr><th>Name</th><td><?php echo esc_html($meta['_roi_name'][0] ?? ''); ?></td></tr>
        <tr><th>Phone</th><td><?php echo esc_html($meta['_roi_phone'][0] ?? ''); ?></td></tr>
        <tr><th>Email</th><td><?php echo esc_html($meta['_roi_email'][0] ?? ''); ?></td></tr>
        <tr><th>Company</th><td><?php echo esc_html($meta['_roi_company'][0] ?? ''); ?></td></tr>
        <tr><th>Region</th><td><?php echo esc_html($meta['_roi_region'][0] ?? ''); ?></td></tr>
        <tr><th>Employees</th><td><?php echo esc_html($meta['_roi_employees'][0] ?? ''); ?></td></tr>
        <tr><th>Applications</th><td><?php echo esc_html($meta['_roi_apps'][0] ?? ''); ?></td></tr>
        <tr><th>AM%</th><td><?php echo esc_html($meta['_roi_am_percent'][0] ?? ''); ?>%</td></tr>
        <tr><th>CLI%</th><td><?php echo esc_html($meta['_roi_cli_percent'][0] ?? ''); ?>%</td></tr>
        <tr><th>Review Cycles/Year</th><td><?php echo esc_html($meta['_roi_review_cycles'][0] ?? ''); ?></td></tr>
        <tr><th>Days per Review</th><td><?php echo esc_html($meta['_roi_days_per_review'][0] ?? ''); ?></td></tr>
        <tr><th>Daily Tickets</th><td><?php echo esc_html($meta['_roi_daily_tickets'][0] ?? ''); ?></td></tr>
        <tr><th>Submitted At</th><td><?php echo esc_html(get_the_date('Y-m-d H:i:s', $post)); ?></td></tr>
      </table>
      <?php
    }

    /**
     * Render the calculation results meta box
     */
    public function render_results_meta_box($post) {
      $meta = get_post_meta($post->ID);
      ?>
      <table class="roi-meta-table">
        <tr><th>Hours Saved (Annual)</th><td><?php echo esc_html(number_format((float)($meta['_roi_hours_saved'][0] ?? 0), 0)); ?> hours</td></tr>
        <tr><th>Annual Savings (EUR)</th><td>€<?php echo esc_html(number_format((float)($meta['_roi_annual_savings_eur'][0] ?? 0), 0)); ?></td></tr>
        <tr><th>Annual Subscription (EUR)</th><td>€<?php echo esc_html(number_format((float)($meta['_roi_subscription_eur'][0] ?? 0), 0)); ?></td></tr>
        <tr><th>Implementation Cost (EUR)</th><td>€<?php echo esc_html(number_format((float)($meta['_roi_implementation_eur'][0] ?? 0), 0)); ?></td></tr>
        <tr><th>Year 1 ROI</th><td><?php echo esc_html(number_format((float)($meta['_roi_year1'][0] ?? 0), 1)); ?>%</td></tr>
        <tr><th>3-Year ROI</th><td><?php echo esc_html(number_format((float)($meta['_roi_3year'][0] ?? 0), 1)); ?>%</td></tr>
        <tr><th>Payback Period</th><td><?php echo esc_html($meta['_roi_payback'][0] ?? 'N/A'); ?></td></tr>
      </table>
      <?php
    }

    /**
     * Add settings page under the CPT menu
     */
    public function add_settings_page() {
      add_submenu_page(
        'edit.php?post_type=' . self::POST_TYPE,
        'ROI Calculator Settings',
        'Settings',
        'manage_options',
        'roi-calculator-settings',
        array($this, 'render_settings_page')
      );
    }

    /**
     * Register settings
     */
    public function register_settings() {
      register_setting('roi_calculator_settings', self::OPTION_ADMIN_EMAIL, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default' => get_option('admin_email'),
      ));
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
      if (!current_user_can('manage_options')) {
        return;
      }
      ?>
      <div class="wrap">
        <h1>ROI Calculator Settings</h1>
        <form method="post" action="options.php">
          <?php settings_fields('roi_calculator_settings'); ?>
          <table class="form-table">
            <tr>
              <th scope="row"><label for="<?php echo self::OPTION_ADMIN_EMAIL; ?>">Admin Notification Email</label></th>
              <td>
                <input type="email" id="<?php echo self::OPTION_ADMIN_EMAIL; ?>" name="<?php echo self::OPTION_ADMIN_EMAIL; ?>" 
                       value="<?php echo esc_attr(get_option(self::OPTION_ADMIN_EMAIL, get_option('admin_email'))); ?>" 
                       class="regular-text">
                <p class="description">Email address to receive ROI calculation submissions. Defaults to the WordPress admin email.</p>
              </td>
            </tr>
          </table>
          <?php submit_button(); ?>
        </form>
      </div>
      <?php
    }

    /**
     * Localize script with AJAX URL
     */
    public function localize_script() {
      wp_localize_script('roi-calculator-inline-script', 'roiCalculatorAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce(self::NONCE_ACTION),
      ));
    }

    /**
     * Handle AJAX submission
     */
    public function handle_submission() {
      // Verify nonce
      if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), self::NONCE_ACTION)) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
      }

      // Sanitize input data
      $data = array(
        'name'           => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '',
        'phone'          => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
        'email'          => isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '',
        'company'        => isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '',
        'region'         => isset($_POST['region']) ? sanitize_text_field(wp_unslash($_POST['region'])) : '',
        'employees'      => isset($_POST['employees']) ? absint($_POST['employees']) : 0,
        'apps'           => isset($_POST['apps']) ? absint($_POST['apps']) : 0,
        'am_percent'     => isset($_POST['am_percent']) ? floatval($_POST['am_percent']) : 0,
        'cli_percent'    => isset($_POST['cli_percent']) ? floatval($_POST['cli_percent']) : 0,
        'review_cycles'  => isset($_POST['review_cycles']) ? absint($_POST['review_cycles']) : 0,
        'days_per_review'=> isset($_POST['days_per_review']) ? absint($_POST['days_per_review']) : 0,
        'daily_tickets'  => isset($_POST['daily_tickets']) ? absint($_POST['daily_tickets']) : 0,
      );

      // Results from frontend calculation
      $results = array(
        'hours_saved'        => isset($_POST['hours_saved']) ? floatval($_POST['hours_saved']) : 0,
        'annual_savings_eur' => isset($_POST['annual_savings_eur']) ? floatval($_POST['annual_savings_eur']) : 0,
        'subscription_eur'   => isset($_POST['subscription_eur']) ? floatval($_POST['subscription_eur']) : 0,
        'implementation_eur' => isset($_POST['implementation_eur']) ? floatval($_POST['implementation_eur']) : 0,
        'roi_year1'          => isset($_POST['roi_year1']) ? floatval($_POST['roi_year1']) : 0,
        'roi_3year'          => isset($_POST['roi_3year']) ? floatval($_POST['roi_3year']) : 0,
        'payback'            => isset($_POST['payback']) ? sanitize_text_field(wp_unslash($_POST['payback'])) : 'N/A',
      );

      // Validate required fields
      if (empty($data['name']) || empty($data['email']) || empty($data['company'])) {
        wp_send_json_error(array('message' => 'Required fields are missing'));
        return;
      }

      // Create post
      $post_id = wp_insert_post(array(
        'post_type'   => self::POST_TYPE,
        'post_status' => 'publish',
        'post_title'  => sprintf('%s - %s (%s)', $data['company'], $data['name'], wp_date('Y-m-d H:i')),
      ));

      if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to save submission'));
        return;
      }

      // Save meta data
      update_post_meta($post_id, '_roi_name', $data['name']);
      update_post_meta($post_id, '_roi_phone', $data['phone']);
      update_post_meta($post_id, '_roi_email', $data['email']);
      update_post_meta($post_id, '_roi_company', $data['company']);
      update_post_meta($post_id, '_roi_region', $data['region']);
      update_post_meta($post_id, '_roi_employees', $data['employees']);
      update_post_meta($post_id, '_roi_apps', $data['apps']);
      update_post_meta($post_id, '_roi_am_percent', $data['am_percent']);
      update_post_meta($post_id, '_roi_cli_percent', $data['cli_percent']);
      update_post_meta($post_id, '_roi_review_cycles', $data['review_cycles']);
      update_post_meta($post_id, '_roi_days_per_review', $data['days_per_review']);
      update_post_meta($post_id, '_roi_daily_tickets', $data['daily_tickets']);
      
      // Save results
      update_post_meta($post_id, '_roi_hours_saved', $results['hours_saved']);
      update_post_meta($post_id, '_roi_annual_savings_eur', $results['annual_savings_eur']);
      update_post_meta($post_id, '_roi_subscription_eur', $results['subscription_eur']);
      update_post_meta($post_id, '_roi_implementation_eur', $results['implementation_eur']);
      update_post_meta($post_id, '_roi_year1', $results['roi_year1']);
      update_post_meta($post_id, '_roi_3year', $results['roi_3year']);
      update_post_meta($post_id, '_roi_payback', $results['payback']);

      // Send emails
      $this->send_user_email($data, $results);
      $this->send_admin_email($data, $results);

      wp_send_json_success(array(
        'message' => 'Submission saved and emails sent',
        'post_id' => $post_id,
      ));
    }

    /**
     * Send email to user
     */
    private function send_user_email($data, $results) {
      $to = $data['email'];
      $subject = 'Your Anugal ROI Calculation Results';
      
      $message = $this->build_email_content($data, $results, false);
      
      $headers = array('Content-Type: text/html; charset=UTF-8');
      
      wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Send email to admin
     */
    private function send_admin_email($data, $results) {
      $admin_email = get_option(self::OPTION_ADMIN_EMAIL, get_option('admin_email'));
      
      if (empty($admin_email)) {
        return;
      }
      
      $subject = sprintf('New ROI Calculation: %s (%s)', $data['company'], $data['name']);
      
      $message = $this->build_email_content($data, $results, true);
      
      $headers = array('Content-Type: text/html; charset=UTF-8');
      
      wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * Build email content
     */
    private function build_email_content($data, $results, $is_admin) {
      $roi_year1 = number_format($results['roi_year1'], 1);
      $roi_3year = number_format($results['roi_3year'], 1);
      
      ob_start();
      ?>
      <!DOCTYPE html>
      <html>
      <head>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: linear-gradient(135deg, #1E3A8A 0%, #4338CA 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
          .content { background: #f9f9f9; padding: 30px; border: 1px solid #eee; }
          .section { margin-bottom: 25px; }
          .section-title { font-size: 16px; font-weight: bold; color: #1E3A8A; margin-bottom: 10px; border-bottom: 2px solid #1E3A8A; padding-bottom: 5px; }
          .result-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 15px; }
          .result-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
          .result-label { color: #666; }
          .result-value { font-weight: bold; color: #1E3A8A; }
          .highlight { background: #1E3A8A; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; }
          .highlight-value { font-size: 32px; font-weight: bold; }
          .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1 style="margin:0">Anugal ROI Calculator</h1>
            <p style="margin:10px 0 0"><?php echo $is_admin ? 'New Submission Received' : 'Your ROI Results'; ?></p>
          </div>
          
          <div class="content">
            <?php if ($is_admin): ?>
            <div class="section">
              <div class="section-title">Contact Information</div>
              <div class="result-box">
                <div class="result-row"><span class="result-label">Name</span><span class="result-value"><?php echo esc_html($data['name']); ?></span></div>
                <div class="result-row"><span class="result-label">Email</span><span class="result-value"><?php echo esc_html($data['email']); ?></span></div>
                <div class="result-row"><span class="result-label">Phone</span><span class="result-value"><?php echo esc_html($data['phone']); ?></span></div>
                <div class="result-row"><span class="result-label">Company</span><span class="result-value"><?php echo esc_html($data['company']); ?></span></div>
                <div class="result-row"><span class="result-label">Region</span><span class="result-value"><?php echo esc_html($data['region']); ?></span></div>
              </div>
            </div>
            
            <div class="section">
              <div class="section-title">Organisation Details</div>
              <div class="result-box">
                <div class="result-row"><span class="result-label">Employees</span><span class="result-value"><?php echo esc_html(number_format($data['employees'])); ?></span></div>
                <div class="result-row"><span class="result-label">Applications</span><span class="result-value"><?php echo esc_html($data['apps']); ?></span></div>
                <div class="result-row"><span class="result-label">AM%</span><span class="result-value"><?php echo esc_html($data['am_percent']); ?>%</span></div>
                <div class="result-row"><span class="result-label">CLI%</span><span class="result-value"><?php echo esc_html($data['cli_percent']); ?>%</span></div>
              </div>
            </div>
            <?php else: ?>
            <p>Dear <?php echo esc_html($data['name']); ?>,</p>
            <p>Thank you for using the Anugal ROI Calculator. Here are your results:</p>
            <?php endif; ?>
            
            <div class="highlight">
              <div style="margin-bottom:15px">
                <div class="highlight-value"><?php echo $roi_year1; ?>%</div>
                <div>Year 1 ROI</div>
              </div>
              <div>
                <div class="highlight-value"><?php echo $roi_3year; ?>%</div>
                <div>3-Year ROI</div>
              </div>
            </div>
            
            <div class="section">
              <div class="section-title">Detailed Results</div>
              <div class="result-box">
                <div class="result-row"><span class="result-label">Hours Saved (Annual)</span><span class="result-value"><?php echo esc_html(number_format($results['hours_saved'], 0)); ?> hours</span></div>
                <div class="result-row"><span class="result-label">Annual Savings</span><span class="result-value">€<?php echo esc_html(number_format($results['annual_savings_eur'], 0)); ?></span></div>
                <div class="result-row"><span class="result-label">Annual Subscription</span><span class="result-value">€<?php echo esc_html(number_format($results['subscription_eur'], 0)); ?>/year</span></div>
                <div class="result-row"><span class="result-label">Implementation Cost</span><span class="result-value">€<?php echo esc_html(number_format($results['implementation_eur'], 0)); ?></span></div>
                <div class="result-row"><span class="result-label">Payback Period</span><span class="result-value"><?php echo esc_html($results['payback']); ?></span></div>
              </div>
            </div>
            
            <?php if (!$is_admin): ?>
            <p>If you have any questions or would like to discuss these results, please don't hesitate to contact us.</p>
            <?php endif; ?>
          </div>
          
          <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Anugal. All rights reserved.</p>
          </div>
        </div>
      </body>
      </html>
      <?php
      return ob_get_clean();
    }
  }

  new ROI_Submission_CPT();
}
