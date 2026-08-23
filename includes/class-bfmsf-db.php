<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database class for Multi-Step Form Builder
 */

class BFMSF_DB {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Forms table
        $forms_table = $wpdb->prefix . 'bfmsf_forms';
        $forms_sql = "CREATE TABLE IF NOT EXISTS $forms_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description longtext,
            status varchar(20) DEFAULT 'publish',
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            modified_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20),
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Form Fields table
        $fields_table = $wpdb->prefix . 'bfmsf_form_fields';
        $fields_sql = "CREATE TABLE $fields_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            step_number mediumint(9) NOT NULL,
            field_order mediumint(9) NOT NULL,
            field_type varchar(50) NOT NULL,
            field_label varchar(255) NOT NULL,
            field_name varchar(255) NOT NULL,
            field_placeholder varchar(255),
            field_required tinyint(1) DEFAULT 0,
            field_options longtext,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Form Submissions table
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
        $submissions_sql = "CREATE TABLE $submissions_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            submission_data longtext NOT NULL,
            submitted_date datetime DEFAULT CURRENT_TIMESTAMP,
            user_ip varchar(45),
            PRIMARY KEY  (id)
        ) $charset_collate;";


        
        $analytics_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bfmsf_analytics (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            date date NOT NULL,
            views int(11) DEFAULT 0,
            submissions int(11) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY form_date (form_id, date)
        ) $charset_collate;";






        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($forms_sql);
        dbDelta($fields_sql);
        dbDelta($submissions_sql);

        dbDelta($analytics_sql);
    }
}


