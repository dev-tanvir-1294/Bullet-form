<?php
if (!defined('ABSPATH')) exit;

class BFMSF_Analytics {
    public static function track_view($form_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'bfmsf_analytics';
        $today = current_time('Y-m-d');
        $wpdb->query($wpdb->prepare(
            "INSERT INTO $table (form_id, date, views) VALUES (%d, %s, 1) 
             ON DUPLICATE KEY UPDATE views = views + 1",
            $form_id, $today
        ));
    }

    public static function track_submission($form_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'bfmsf_analytics';
        $today = current_time('Y-m-d');
        $wpdb->query($wpdb->prepare(
            "INSERT INTO $table (form_id, date, submissions) VALUES (%d, %s, 1) 
             ON DUPLICATE KEY UPDATE submissions = submissions + 1",
            $form_id, $today
        ));
    }

    public static function get_stats($form_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'bfmsf_analytics';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE form_id = %d ORDER BY date DESC LIMIT 30",
            $form_id
        ));
    }
}