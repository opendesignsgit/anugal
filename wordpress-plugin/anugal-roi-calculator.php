<?php
/**
 * Plugin Name: Anugal ROI Calculator
 * Plugin URI: https://anugal.com
 * Description: ROI Calculator for Anugal Identity Governance Platform. Use shortcode [anugal_roi_calculator] to display.
 * Version: 1.0.0
 * Author: Anugal
 * Author URI: https://anugal.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: anugal-roi-calculator
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('ANUGAL_ROI_CALCULATOR_VERSION', '1.0.0');

/**
 * Plugin directory path.
 */
define('ANUGAL_ROI_CALCULATOR_PATH', plugin_dir_path(__FILE__));

/**
 * Plugin directory URL.
 */
define('ANUGAL_ROI_CALCULATOR_URL', plugin_dir_url(__FILE__));

/**
 * Enqueue plugin styles and scripts.
 */
function anugal_roi_calculator_enqueue_assets() {
    // Only enqueue on pages with the shortcode
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'anugal_roi_calculator')) {
        // Enqueue CSS
        wp_enqueue_style(
            'anugal-roi-calculator-css',
            ANUGAL_ROI_CALCULATOR_URL . 'assets/css/styles.css',
            array(),
            ANUGAL_ROI_CALCULATOR_VERSION
        );

        // Enqueue Calculator JS
        wp_enqueue_script(
            'anugal-roi-calculator-engine',
            ANUGAL_ROI_CALCULATOR_URL . 'assets/js/calculator.js',
            array(),
            ANUGAL_ROI_CALCULATOR_VERSION,
            true
        );

        // Enqueue App JS
        wp_enqueue_script(
            'anugal-roi-calculator-app',
            ANUGAL_ROI_CALCULATOR_URL . 'assets/js/app.js',
            array('anugal-roi-calculator-engine'),
            ANUGAL_ROI_CALCULATOR_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'anugal_roi_calculator_enqueue_assets');

/**
 * Register the shortcode.
 */
function anugal_roi_calculator_shortcode($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'title' => 'Company Profile',
    ), $atts, 'anugal_roi_calculator');

    // Start output buffering
    ob_start();
    
    // Include the template
    include ANUGAL_ROI_CALCULATOR_PATH . 'templates/calculator-template.php';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('anugal_roi_calculator', 'anugal_roi_calculator_shortcode');

/**
 * Add settings link on plugin page.
 */
function anugal_roi_calculator_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=anugal-roi-calculator') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'anugal_roi_calculator_settings_link');
