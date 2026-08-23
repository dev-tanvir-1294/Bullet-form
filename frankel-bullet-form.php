<?php
/**
 * Plugin Name: Bullet Forms
 * Description: Bullet Forms lets you build multi-step forms, collect submissions, and export them to CSV.
 * Version: 1.1.5
 * Author: Md. Tanvir Ahmed (mdtanvirahmed)
 * Author URI: https://i-am-tanvir.netlify.app/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frankel-bullet-form
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('BFMSF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BFMSF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BFMSF_VERSION', '1.1.5');

// Include necessary files
require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-settings.php';
require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-core.php';
require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-admin.php';
require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-frontend.php';
require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-submissions.php';
require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-rest.php';

// Initialize plugin
function bfmsf_init()
{
    // Initialize core
    new BFMSF_Core();

    // Initialize admin
    if (is_admin()) {
        new BFMSF_Admin();
    }

    // Initialize frontend
    new BFMSF_Frontend();

    // Initialize submissions
    new BFMSF_Submissions();

    // Initialize REST API
    new BFMSF_REST();
}

add_action('plugins_loaded', 'bfmsf_init');

// Activation hook
register_activation_hook(__FILE__, 'bfmsf_activate');

function bfmsf_activate()
{
    // Create database table for submissions
    require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-db.php';
    BFMSF_DB::create_tables();

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'bfmsf_deactivate');

function bfmsf_deactivate()
{
    flush_rewrite_rules();
}

// Cleanup when a form post is permanently deleted
add_action('before_delete_post', 'bfmsf_delete_post_cleanup');

function bfmsf_delete_post_cleanup($post_id)
{
    if (get_post_type($post_id) !== 'bfmsf_form') {
        return;
    }

    global $wpdb;
    $forms_table = $wpdb->prefix . 'bfmsf_forms';
    $fields_table = $wpdb->prefix . 'bfmsf_form_fields';
    $submissions_table = $wpdb->prefix . 'bfmsf_submissions';

    $wpdb->delete($submissions_table, array('form_id' => $post_id));
    $wpdb->delete($fields_table, array('form_id' => $post_id));
    $wpdb->delete($forms_table, array('id' => $post_id));
}