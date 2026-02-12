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
    
    // Admin Email Frequency Options
    const OPTION_ADMIN_EMAIL_FREQUENCY = 'roi_admin_email_frequency';
    const FREQ_FIRST_ONLY = 'first';
    const FREQ_EVERY_CALC = 'every';
    
    // Display Options
    const OPTION_SHOW_DOLLAR_VALUE = 'roi_show_dollar_value';
    const OPTION_SHOW_DOWNLOAD_BUTTON = 'roi_show_download_button';
    const OPTION_SHOW_LOW_ROI = 'roi_show_low_roi';
    
    // Customer Email Frequency Options
    const OPTION_CUSTOMER_EMAIL_FREQUENCY = 'roi_customer_email_frequency';
    
    // SMTP Configuration Options
    const OPTION_SMTP_ENABLED = 'roi_smtp_enabled';
    const OPTION_SMTP_HOST = 'roi_smtp_host';
    const OPTION_SMTP_PORT = 'roi_smtp_port';
    const OPTION_SMTP_ENCRYPTION = 'roi_smtp_encryption';
    const OPTION_SMTP_AUTH = 'roi_smtp_auth';
    const OPTION_SMTP_USERNAME = 'roi_smtp_username';
    const OPTION_SMTP_PASSWORD = 'roi_smtp_password';
    const OPTION_SMTP_FROM_EMAIL = 'roi_smtp_from_email';
    const OPTION_SMTP_FROM_NAME = 'roi_smtp_from_name';

    public function __construct() {
      add_action('init', array($this, 'register_post_type'));
      add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
      add_action('admin_menu', array($this, 'add_settings_page'));
      add_action('admin_init', array($this, 'register_settings'));
      add_action('wp_ajax_roi_submit_calculation', array($this, 'handle_submission'));
      add_action('wp_ajax_nopriv_roi_submit_calculation', array($this, 'handle_submission'));
      add_action('wp_ajax_roi_download_report', array($this, 'handle_download_report'));
      add_action('wp_ajax_nopriv_roi_download_report', array($this, 'handle_download_report'));
      
      // Configure PHPMailer to use SMTP if enabled
      add_action('phpmailer_init', array($this, 'configure_smtp'));
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
      
      add_meta_box(
        'roi_calculation_history',
        'Calculation History',
        array($this, 'render_history_meta_box'),
        self::POST_TYPE,
        'normal',
        'low'
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
     * Render the calculation history meta box
     */
    public function render_history_meta_box($post) {
      $history = get_post_meta($post->ID, '_roi_calculation_history', true);
      $count = get_post_meta($post->ID, '_roi_calculation_count', true);
      
      if (!is_array($history) || empty($history)) {
        echo '<p>No calculation history available.</p>';
        return;
      }
      ?>
      <p><strong>Total calculations:</strong> <?php echo esc_html($count ?: count($history)); ?></p>
      <style>
        .roi-history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .roi-history-table th, .roi-history-table td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        .roi-history-table th { background: #f9f9f9; font-weight: 600; }
        .roi-history-item { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .roi-history-header { background: #f5f5f5; padding: 10px; font-weight: 600; cursor: pointer; }
        .roi-history-content { padding: 10px; display: none; }
        .roi-history-item.open .roi-history-content { display: block; }
      </style>
      <div class="roi-history-list">
        <?php 
        $history = array_reverse($history); // Show newest first
        foreach ($history as $index => $calc): 
          $timestamp = $calc['timestamp'] ?? '';
          $data = $calc['data'] ?? array();
          $results = $calc['results'] ?? array();
        ?>
        <div class="roi-history-item">
          <div class="roi-history-header" onclick="this.parentElement.classList.toggle('open')">
            #<?php echo esc_html(count($history) - $index); ?> - <?php echo esc_html($timestamp); ?>
            (ROI Year 1: <?php echo esc_html(number_format((float)($results['roi_year1'] ?? 0), 1)); ?>%)
          </div>
          <div class="roi-history-content">
            <table class="roi-history-table">
              <tr><th colspan="2">Inputs</th></tr>
              <tr><td>Employees</td><td><?php echo esc_html($data['employees'] ?? ''); ?></td></tr>
              <tr><td>Applications</td><td><?php echo esc_html($data['apps'] ?? ''); ?></td></tr>
              <tr><td>AM%</td><td><?php echo esc_html($data['am_percent'] ?? ''); ?>%</td></tr>
              <tr><td>CLI%</td><td><?php echo esc_html($data['cli_percent'] ?? ''); ?>%</td></tr>
              <tr><th colspan="2">Results</th></tr>
              <tr><td>Hours Saved</td><td><?php echo esc_html(number_format((float)($results['hours_saved'] ?? 0), 0)); ?></td></tr>
              <tr><td>Annual Savings</td><td>€<?php echo esc_html(number_format((float)($results['annual_savings_eur'] ?? 0), 0)); ?></td></tr>
              <tr><td>Year 1 ROI</td><td><?php echo esc_html(number_format((float)($results['roi_year1'] ?? 0), 1)); ?>%</td></tr>
              <tr><td>3-Year ROI</td><td><?php echo esc_html(number_format((float)($results['roi_3year'] ?? 0), 1)); ?>%</td></tr>
            </table>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
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
      // Admin email setting
      register_setting('roi_calculator_settings', self::OPTION_ADMIN_EMAIL, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default' => get_option('admin_email'),
      ));
      
      // Admin email frequency setting
      register_setting('roi_calculator_settings', self::OPTION_ADMIN_EMAIL_FREQUENCY, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => self::FREQ_FIRST_ONLY,
      ));
      
      // Customer email frequency setting
      register_setting('roi_calculator_settings', self::OPTION_CUSTOMER_EMAIL_FREQUENCY, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => self::FREQ_FIRST_ONLY,
      ));
      
      // Display options
      register_setting('roi_calculator_settings', self::OPTION_SHOW_DOLLAR_VALUE, array(
        'type' => 'boolean',
        'default' => true,
      ));
      register_setting('roi_calculator_settings', self::OPTION_SHOW_DOWNLOAD_BUTTON, array(
        'type' => 'boolean',
        'default' => true,
      ));
      register_setting('roi_calculator_settings', self::OPTION_SHOW_LOW_ROI, array(
        'type' => 'boolean',
        'default' => true,
      ));
      
      // SMTP settings
      register_setting('roi_calculator_settings', self::OPTION_SMTP_ENABLED, array(
        'type' => 'boolean',
        'default' => false,
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_HOST, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_PORT, array(
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 587,
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_ENCRYPTION, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'tls',
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_AUTH, array(
        'type' => 'boolean',
        'default' => true,
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_USERNAME, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_PASSWORD, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_FROM_EMAIL, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default' => '',
      ));
      register_setting('roi_calculator_settings', self::OPTION_SMTP_FROM_NAME, array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Anugal',
      ));
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
      if (!current_user_can('manage_options')) {
        return;
      }
      
      $smtp_enabled = get_option(self::OPTION_SMTP_ENABLED, false);
      $smtp_host = get_option(self::OPTION_SMTP_HOST, '');
      $smtp_port = get_option(self::OPTION_SMTP_PORT, 587);
      $smtp_encryption = get_option(self::OPTION_SMTP_ENCRYPTION, 'tls');
      $smtp_auth = get_option(self::OPTION_SMTP_AUTH, true);
      $smtp_username = get_option(self::OPTION_SMTP_USERNAME, '');
      $smtp_password = get_option(self::OPTION_SMTP_PASSWORD, '');
      $smtp_from_email = get_option(self::OPTION_SMTP_FROM_EMAIL, '');
      $smtp_from_name = get_option(self::OPTION_SMTP_FROM_NAME, 'Anugal');
      ?>
      <div class="wrap">
        <h1>ROI Calculator Settings</h1>
        <form method="post" action="options.php">
          <?php settings_fields('roi_calculator_settings'); ?>
          
          <h2>Notification Settings</h2>
          <table class="form-table">
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_ADMIN_EMAIL); ?>">Admin Notification Email</label></th>
              <td>
                <input type="email" id="<?php echo esc_attr(self::OPTION_ADMIN_EMAIL); ?>" name="<?php echo esc_attr(self::OPTION_ADMIN_EMAIL); ?>" 
                       value="<?php echo esc_attr(get_option(self::OPTION_ADMIN_EMAIL, get_option('admin_email'))); ?>" 
                       class="regular-text">
                <p class="description">Email address to receive ROI calculation submissions. Defaults to the WordPress admin email.</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_ADMIN_EMAIL_FREQUENCY); ?>">Admin Email Frequency</label></th>
              <td>
                <?php $freq = get_option(self::OPTION_ADMIN_EMAIL_FREQUENCY, self::FREQ_FIRST_ONLY); ?>
                <select id="<?php echo esc_attr(self::OPTION_ADMIN_EMAIL_FREQUENCY); ?>" name="<?php echo esc_attr(self::OPTION_ADMIN_EMAIL_FREQUENCY); ?>">
                  <option value="<?php echo esc_attr(self::FREQ_FIRST_ONLY); ?>" <?php selected($freq, self::FREQ_FIRST_ONLY); ?>>First submission only</option>
                  <option value="<?php echo esc_attr(self::FREQ_EVERY_CALC); ?>" <?php selected($freq, self::FREQ_EVERY_CALC); ?>>Every calculation</option>
                </select>
                <p class="description">When to send emails to admin. "First submission only" sends email once per unique user email.</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_CUSTOMER_EMAIL_FREQUENCY); ?>">Customer Email Frequency</label></th>
              <td>
                <?php $cust_freq = get_option(self::OPTION_CUSTOMER_EMAIL_FREQUENCY, self::FREQ_FIRST_ONLY); ?>
                <select id="<?php echo esc_attr(self::OPTION_CUSTOMER_EMAIL_FREQUENCY); ?>" name="<?php echo esc_attr(self::OPTION_CUSTOMER_EMAIL_FREQUENCY); ?>">
                  <option value="<?php echo esc_attr(self::FREQ_FIRST_ONLY); ?>" <?php selected($cust_freq, self::FREQ_FIRST_ONLY); ?>>First submission only</option>
                  <option value="<?php echo esc_attr(self::FREQ_EVERY_CALC); ?>" <?php selected($cust_freq, self::FREQ_EVERY_CALC); ?>>Every calculation</option>
                </select>
                <p class="description">When to send emails to the customer. "First submission only" sends email once per unique user email.</p>
              </td>
            </tr>
          </table>
          
          <h2>Display Options</h2>
          <table class="form-table">
            <tr>
              <th scope="row">Show Dollar Value in Result</th>
              <td>
                <label>
                  <input type="checkbox" name="<?php echo esc_attr(self::OPTION_SHOW_DOLLAR_VALUE); ?>" value="1" <?php checked(get_option(self::OPTION_SHOW_DOLLAR_VALUE, true), true); ?>>
                  Show dollar (USD) conversion values alongside primary currency in results
                </label>
                <p class="description">When enabled, the ROI results card will display USD equivalent amounts.</p>
              </td>
            </tr>
            <tr>
              <th scope="row">Show Download Report Button</th>
              <td>
                <label>
                  <input type="checkbox" name="<?php echo esc_attr(self::OPTION_SHOW_DOWNLOAD_BUTTON); ?>" value="1" <?php checked(get_option(self::OPTION_SHOW_DOWNLOAD_BUTTON, true), true); ?>>
                  Show the "Download Report" button in the results card
                </label>
                <p class="description">When enabled, users can download a CSV report and receive it via email.</p>
              </td>
            </tr>
            <tr>
              <th scope="row">Show Low ROI Status</th>
              <td>
                <label>
                  <input type="checkbox" name="<?php echo esc_attr(self::OPTION_SHOW_LOW_ROI); ?>" value="1" <?php checked(get_option(self::OPTION_SHOW_LOW_ROI, true), true); ?>>
                  Show the Return on Investment status indicator when the value is "Low ROI"
                </label>
                <p class="description">When disabled, the ROI status badge will be hidden if the calculated ROI is negative (Low ROI). Other status levels (Moderate, Strong, Excellent) are always shown.</p>
              </td>
            </tr>
          </table>
          
          <h2>SMTP Configuration</h2>
          <p class="description">Configure SMTP settings to send emails via an external mail server instead of PHP's mail() function.</p>
          
          <table class="form-table">
            <tr>
              <th scope="row">Enable SMTP</th>
              <td>
                <label>
                  <input type="checkbox" name="<?php echo esc_attr(self::OPTION_SMTP_ENABLED); ?>" value="1" <?php checked($smtp_enabled, true); ?>>
                  Use SMTP to send emails
                </label>
                <p class="description">Enable this to send emails via SMTP server instead of PHP mail().</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_HOST); ?>">SMTP Host</label></th>
              <td>
                <input type="text" id="<?php echo esc_attr(self::OPTION_SMTP_HOST); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_HOST); ?>" 
                       value="<?php echo esc_attr($smtp_host); ?>" class="regular-text" placeholder="smtp.example.com">
                <p class="description">SMTP server hostname (e.g., smtp.gmail.com, smtp.office365.com)</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_PORT); ?>">SMTP Port</label></th>
              <td>
                <input type="number" id="<?php echo esc_attr(self::OPTION_SMTP_PORT); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_PORT); ?>" 
                       value="<?php echo esc_attr($smtp_port); ?>" class="small-text" min="1" max="65535">
                <p class="description">Common ports: 587 (TLS), 465 (SSL), 25 (unsecured)</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_ENCRYPTION); ?>">Encryption</label></th>
              <td>
                <select id="<?php echo esc_attr(self::OPTION_SMTP_ENCRYPTION); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_ENCRYPTION); ?>">
                  <option value="tls" <?php selected($smtp_encryption, 'tls'); ?>>TLS</option>
                  <option value="ssl" <?php selected($smtp_encryption, 'ssl'); ?>>SSL</option>
                  <option value="" <?php selected($smtp_encryption, ''); ?>>None</option>
                </select>
                <p class="description">TLS is recommended for port 587, SSL for port 465</p>
              </td>
            </tr>
            <tr>
              <th scope="row">Authentication</th>
              <td>
                <label>
                  <input type="checkbox" name="<?php echo esc_attr(self::OPTION_SMTP_AUTH); ?>" value="1" <?php checked($smtp_auth, true); ?>>
                  Require authentication
                </label>
                <p class="description">Most SMTP servers require authentication.</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_USERNAME); ?>">SMTP Username</label></th>
              <td>
                <input type="text" id="<?php echo esc_attr(self::OPTION_SMTP_USERNAME); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_USERNAME); ?>" 
                       value="<?php echo esc_attr($smtp_username); ?>" class="regular-text" autocomplete="off">
                <p class="description">Usually your email address</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_PASSWORD); ?>">SMTP Password</label></th>
              <td>
                <input type="password" id="<?php echo esc_attr(self::OPTION_SMTP_PASSWORD); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_PASSWORD); ?>" 
                       value="<?php echo esc_attr($smtp_password); ?>" class="regular-text" autocomplete="new-password">
                <p class="description">For Gmail, use an App Password. <strong>Note:</strong> Password is stored in the database.</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_FROM_EMAIL); ?>">From Email</label></th>
              <td>
                <input type="email" id="<?php echo esc_attr(self::OPTION_SMTP_FROM_EMAIL); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_FROM_EMAIL); ?>" 
                       value="<?php echo esc_attr($smtp_from_email); ?>" class="regular-text" placeholder="noreply@example.com">
                <p class="description">Email address shown as the sender. Leave empty to use WordPress admin email.</p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="<?php echo esc_attr(self::OPTION_SMTP_FROM_NAME); ?>">From Name</label></th>
              <td>
                <input type="text" id="<?php echo esc_attr(self::OPTION_SMTP_FROM_NAME); ?>" name="<?php echo esc_attr(self::OPTION_SMTP_FROM_NAME); ?>" 
                       value="<?php echo esc_attr($smtp_from_name); ?>" class="regular-text" placeholder="Anugal">
                <p class="description">Name shown as the sender</p>
              </td>
            </tr>
          </table>
          
          <?php submit_button(); ?>
        </form>
      </div>
      <?php
    }

    /**
     * Configure PHPMailer to use SMTP
     */
    public function configure_smtp($phpmailer) {
      $smtp_enabled = get_option(self::OPTION_SMTP_ENABLED, false);
      
      if (!$smtp_enabled) {
        return;
      }
      
      $smtp_host = get_option(self::OPTION_SMTP_HOST, '');
      if (empty($smtp_host)) {
        return;
      }
      
      // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
      $phpmailer->isSMTP();
      $phpmailer->Host = $smtp_host;
      $phpmailer->Port = get_option(self::OPTION_SMTP_PORT, 587);
      
      $encryption = get_option(self::OPTION_SMTP_ENCRYPTION, 'tls');
      if (!empty($encryption)) {
        $phpmailer->SMTPSecure = $encryption;
      }
      
      $smtp_auth = get_option(self::OPTION_SMTP_AUTH, true);
      $phpmailer->SMTPAuth = (bool) $smtp_auth;
      
      if ($smtp_auth) {
        $phpmailer->Username = get_option(self::OPTION_SMTP_USERNAME, '');
        $phpmailer->Password = get_option(self::OPTION_SMTP_PASSWORD, '');
      }
      
      $from_email = get_option(self::OPTION_SMTP_FROM_EMAIL, '');
      $from_name = get_option(self::OPTION_SMTP_FROM_NAME, 'Anugal');
      
      if (!empty($from_email)) {
        $phpmailer->setFrom($from_email, $from_name);
      }
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

      // Check for existing submission by email (deduplication)
      $existing_post = $this->find_submission_by_email($data['email']);
      $is_new_submission = empty($existing_post);
      
      if ($existing_post) {
        // Update existing post
        $post_id = $existing_post->ID;
        wp_update_post(array(
          'ID'         => $post_id,
          'post_title' => sprintf('%s - %s (updated %s)', $data['company'], $data['name'], wp_date('Y-m-d H:i')),
        ));
      } else {
        // Create new post
        $post_id = wp_insert_post(array(
          'post_type'   => self::POST_TYPE,
          'post_status' => 'publish',
          'post_title'  => sprintf('%s - %s (%s)', $data['company'], $data['name'], wp_date('Y-m-d H:i')),
        ));
      }

      if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to save submission'));
        return;
      }

      // Save meta data (always update with latest values)
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
      
      // Track calculation history
      $history = get_post_meta($post_id, '_roi_calculation_history', true);
      if (!is_array($history)) {
        $history = array();
      }
      $history[] = array(
        'timestamp' => current_time('mysql'),
        'data'      => $data,
        'results'   => $results,
      );
      update_post_meta($post_id, '_roi_calculation_history', $history);
      update_post_meta($post_id, '_roi_calculation_count', count($history));

      // Send emails based on settings
      $admin_freq = get_option(self::OPTION_ADMIN_EMAIL_FREQUENCY, self::FREQ_FIRST_ONLY);
      $should_send_admin_email = ($admin_freq === self::FREQ_EVERY_CALC) || $is_new_submission;
      
      // Customer email based on frequency setting
      $customer_freq = get_option(self::OPTION_CUSTOMER_EMAIL_FREQUENCY, self::FREQ_FIRST_ONLY);
      $should_send_customer_email = ($customer_freq === self::FREQ_EVERY_CALC) || $is_new_submission;
      
      // Admin email based on frequency setting
      if ($should_send_admin_email) {
        $this->send_admin_email($data, $results);
      }
      
      // Customer email based on frequency setting
      if ($should_send_customer_email && !empty($data['email'])) {
        $this->send_user_email($data, $results);
      }

      wp_send_json_success(array(
        'message'        => $is_new_submission ? 'Submission saved' : 'Calculation updated',
        'post_id'        => $post_id,
        'is_new'         => $is_new_submission,
        'calc_count'     => count($history),
      ));
    }
    
    /**
     * Find existing submission by email
     */
    private function find_submission_by_email($email) {
      $query = new WP_Query(array(
        'post_type'      => self::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => array(
          array(
            'key'   => '_roi_email',
            'value' => $email,
          ),
        ),
      ));
      
      if ($query->have_posts()) {
        return $query->posts[0];
      }
      
      return null;
    }
    
    /**
     * Handle download report AJAX
     */
    public function handle_download_report() {
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

      $results = array(
        'hours_saved'        => isset($_POST['hours_saved']) ? floatval($_POST['hours_saved']) : 0,
        'annual_savings_eur' => isset($_POST['annual_savings_eur']) ? floatval($_POST['annual_savings_eur']) : 0,
        'subscription_eur'   => isset($_POST['subscription_eur']) ? floatval($_POST['subscription_eur']) : 0,
        'implementation_eur' => isset($_POST['implementation_eur']) ? floatval($_POST['implementation_eur']) : 0,
        'roi_year1'          => isset($_POST['roi_year1']) ? floatval($_POST['roi_year1']) : 0,
        'roi_3year'          => isset($_POST['roi_3year']) ? floatval($_POST['roi_3year']) : 0,
        'payback'            => isset($_POST['payback']) ? sanitize_text_field(wp_unslash($_POST['payback'])) : 'N/A',
      );

      // Send email to user
      $this->send_user_email($data, $results);
      
      // Build CSV content
      $csv = $this->build_csv_report($data, $results);

      wp_send_json_success(array(
        'message'  => 'Report sent to your email',
        'csv'      => $csv,
        'filename' => sprintf('anugal-roi-report-%s.csv', sanitize_file_name($data['company'])),
      ));
    }
    
    /**
     * Build CSV report content
     */
    private function build_csv_report($data, $results) {
      $lines = array();
      
      // Header
      $lines[] = 'Anugal ROI Calculator Report';
      $lines[] = 'Generated: ' . wp_date('Y-m-d H:i:s');
      $lines[] = '';
      
      // Contact Info
      $lines[] = 'Contact Information';
      $lines[] = 'Name,' . $data['name'];
      $lines[] = 'Email,' . $data['email'];
      $lines[] = 'Phone,' . $data['phone'];
      $lines[] = 'Company,' . $data['company'];
      $lines[] = 'Region,' . $data['region'];
      $lines[] = '';
      
      // Input Parameters
      $lines[] = 'Input Parameters';
      $lines[] = 'Total Employees,' . $data['employees'];
      $lines[] = 'Applications to Govern,' . $data['apps'];
      $lines[] = 'AM Percentage,' . $data['am_percent'] . '%';
      $lines[] = 'CLI Percentage,' . $data['cli_percent'] . '%';
      $lines[] = 'Review Cycles/Year,' . $data['review_cycles'];
      $lines[] = 'Days per Review,' . $data['days_per_review'];
      $lines[] = 'Daily Access Tickets,' . $data['daily_tickets'];
      $lines[] = '';
      
      // Results
      $lines[] = 'Calculation Results';
      $lines[] = 'Hours Saved (Annual),' . number_format($results['hours_saved'], 0);
      $lines[] = 'Annual Savings (EUR),' . number_format($results['annual_savings_eur'], 0);
      $lines[] = 'Annual Subscription (EUR),' . number_format($results['subscription_eur'], 0);
      $lines[] = 'Implementation Cost (EUR),' . number_format($results['implementation_eur'], 0);
      $lines[] = 'Year 1 ROI,' . number_format($results['roi_year1'], 1) . '%';
      $lines[] = '3-Year ROI,' . number_format($results['roi_3year'], 1) . '%';
      $lines[] = 'Payback Period,' . $results['payback'];
      
      return implode("\n", $lines);
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
      $show_low_roi = get_option(self::OPTION_SHOW_LOW_ROI, true);
      $year1_is_low = (float) $results['roi_year1'] < 0;
      $three_year_is_low = (float) $results['roi_3year'] < 0;
      
      // For customer emails, determine whether to show ROI values
      $show_year1_roi = $is_admin || !$year1_is_low || $show_low_roi;
      $show_3year_roi = $is_admin || !$three_year_is_low || $show_low_roi;
      
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
              <?php if ($show_year1_roi): ?>
              <div style="margin-bottom:15px">
                <div class="highlight-value"><?php echo $roi_year1; ?>%</div>
                <div>Year 1 ROI</div>
              </div>
              <?php endif; ?>
              <?php if ($show_3year_roi): ?>
              <div>
                <div class="highlight-value"><?php echo $roi_3year; ?>%</div>
                <div>3-Year ROI</div>
              </div>
              <?php endif; ?>
              <?php if (!$show_year1_roi && !$show_3year_roi): ?>
              <div>
                <div>ROI results are being calculated for your scenario.</div>
                <div style="margin-top:10px">Please contact us to discuss your investment options.</div>
              </div>
              <?php endif; ?>
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
