<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings helper class for Bullet Form Builder
 */
class BFMSF_Settings {

    /* ── Fields (new format: JSON object keyed by fieldId) ── */
    public static function get_field_defs($form_id) {
        if (!$form_id) return '{}';
        $v = get_post_meta($form_id, '_bfmsf_field_defs', true);
        return (!empty($v) && is_string($v)) ? $v : '{}';
    }
    public static function save_field_defs($form_id, $json) {
        update_post_meta($form_id, '_bfmsf_field_defs', $json);
    }

    /* ── Rows (JSON array of row objects) ── */
    public static function get_rows($form_id) {
        if (!$form_id) return '[]';
        $v = get_post_meta($form_id, '_bfmsf_rows', true);
        return (!empty($v) && is_string($v)) ? $v : '[]';
    }
    public static function save_rows($form_id, $json) {
        update_post_meta($form_id, '_bfmsf_rows', $json);
    }

    /* ── Form canvas title & subtitle ── */
    public static function get_canvas_meta($form_id) {
        return array(
            'title'    => get_post_meta($form_id, '_bfmsf_canvas_title', true) ?: '',
            'subtitle' => get_post_meta($form_id, '_bfmsf_canvas_subtitle', true) ?: '',
        );
    }
    public static function save_canvas_meta($form_id, $title, $subtitle) {
        update_post_meta($form_id, '_bfmsf_canvas_title', sanitize_text_field($title));
        update_post_meta($form_id, '_bfmsf_canvas_subtitle', sanitize_text_field($subtitle));
    }

    /* ── Legacy fields JSON (kept for frontend compatibility) ── */
    public static function get_fields($form_id) {
        if (!$form_id) return '[]';
        $v = get_post_meta($form_id, '_bfmsf_fields', true);
        return (!empty($v) && is_string($v)) ? $v : '[]';
    }
    public static function save_fields($form_id, $json) {
        update_post_meta($form_id, '_bfmsf_fields', $json);
    }

    /* ── Form settings ── */
    public static function get_settings($form_id) {
        $defaults = array(
            'form_name'                  => '',
            'description'                => '',
            'status'                     => 'active',
            'confirmation_type'          => 'message',
            'confirmation_message'       => 'Thank you for your submission!',
            'redirect_url'               => '',
            'email_recipient'            => get_option('admin_email'),
            'email_subject'              => 'New Form Submission',
            'email_from_name'            => get_bloginfo('name'),
            'require_login'              => false,
            'submission_limit'           => '',
            'integration_google_sheets'  => false,
            'integration_zapier'         => false,
            'integration_hubspot'        => false,
            'google_sheets_webhook_url'  => '',
            'zapier_webhook_url'         => '',
            'hubspot_webhook_url'        => '',
            'api_token'                  => '',
            'api_endpoint'               => '',
        );
        if (!$form_id) return $defaults;
        $saved = get_post_meta($form_id, '_bfmsf_settings', true);
        if (!is_array($saved)) $saved = array();
        return array_merge($defaults, $saved);
    }
    public static function save_settings($form_id, $settings) {
        if (!is_array($settings)) return;
        update_post_meta($form_id, '_bfmsf_settings', $settings);
    }

    /* ── Style settings ── */
    public static function get_style($form_id) {
        $defaults = array(
            'theme'           => 'default',
            'primary_color'   => '#4361ee',
            'secondary_color' => '#3a0ca3',
            'text_color'      => '#1f2937',
            'primary_font'    => 'Inter',
            'secondary_font'  => 'Inter',
            'heading_size'    => 24,
            'body_font_size'  => 14,
            'line_height'     => 1.6,
            'font_weight'     => 400,
            'border_radius'   => 6,
            'input_style'     => 'outlined',
            'padding'         => 10,
            'margin'          => 16,
            'button_text'     => 'Submit',
            'button_bg_color' => '#4361ee',
            'button_text_color' => '#ffffff',
            'button_border'   => '',
            'button_hover'    => true,
            'field_spacing'   => 16,
            'label_alignment' => 'top',
            'bg_image'        => '',
            'bg_repeat'       => 'no-repeat',
            'bg_size'         => 'cover',
            'bg_position'     => 'center',
            'border_color'    => '#d1d5db',
            'container_border_color' => '#e2e8f0',
        );
        
        if (!$form_id) return $defaults;
        $saved = get_post_meta($form_id, '_bfmsf_style', true);
        if (!is_array($saved)) $saved = array();
        return array_merge($defaults, $saved);
    }
    public static function save_style($form_id, $style) {
        if (!is_array($style)) return;
        update_post_meta($form_id, '_bfmsf_style', $style);
    }

    /* ── All forms ── */
    public static function get_all_forms($args = array()) {
        $defaults = array(
            'post_type'      => 'bfmsf_form',
            'post_status'    => array('publish', 'draft'),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        return get_posts(array_merge($defaults, $args));
    }

    /* ── Submission count ── */
    public static function get_submission_count($form_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'bfmsf_submissions';
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE form_id = %d", $form_id
        ));
    }

    /* ── Unread submission notifications ── */
    public static function get_unread_submission_count($form_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'bfmsf_submissions';
        $read  = get_option('bfmsf_read_submissions', array());
        if (!is_array($read)) $read = array();
        $last = isset($read[$form_id]) ? (int) $read[$form_id] : 0;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE form_id = %d AND id > %d",
            $form_id,
            $last
        ));
    }

    public static function get_total_unread_submissions() {
        $total = 0;
        foreach (self::get_all_forms() as $form) {
            $total += self::get_unread_submission_count($form->ID);
        }
        return $total;
    }

    public static function mark_submissions_read($form_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'bfmsf_submissions';
        $max = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(id) FROM $table WHERE form_id = %d", $form_id
        ));
        if ($max < 1) {
            return;
        }
        $read = get_option('bfmsf_read_submissions', array());
        if (!is_array($read)) $read = array();
        $read[$form_id] = $max;
        update_option('bfmsf_read_submissions', $read);
    }
}