<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Bullet_Form
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop custom database tables in reverse order to handle foreign keys
$tables = array(
    $wpdb->prefix . 'bfmsf_submissions',
    $wpdb->prefix . 'bfmsf_form_fields',
    $wpdb->prefix . 'bfmsf_forms'
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}
