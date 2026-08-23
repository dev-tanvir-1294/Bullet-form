<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Submissions class for Multi-Step Form Builder
 */

class BFMSF_Submissions {
    
    public function __construct() {
        add_action('wp_ajax_bfmsf_delete_submission', array($this, 'delete_submission'));
        add_action('wp_ajax_bfmsf_view_submission', array($this, 'view_submission'));
    }
    
    /**
     * Get submissions for a form
     */
    public function get_submissions($form_id, $limit = 50, $offset = 0) {
        global $wpdb;
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $submissions_table WHERE form_id = %d ORDER BY submitted_date DESC LIMIT %d OFFSET %d",
            $form_id,
            $limit,
            $offset
        ));
    }
    
    /**
     * Get submission count
     */
    public function get_submission_count($form_id) {
        global $wpdb;
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $submissions_table WHERE form_id = %d",
            $form_id
        ));
    }
    
    /**
     * Delete submission
     */
    public function delete_submission() {
        check_ajax_referer('BFMSF_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized', 'frankel-bullet-form'));
        }
        
        $submission_id = intval($_POST['submission_id']);
        
        global $wpdb;
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
        
        $result = $wpdb->delete($submissions_table, array('id' => $submission_id));
        
        if ($result) {
            wp_send_json_success(array('message' => esc_html__('Submission deleted', 'frankel-bullet-form')));
        } else {
            wp_send_json_error(array('message' => esc_html__('Error deleting submission', 'frankel-bullet-form')));
        }
    }
    
    /**
     * View submission
     */
    public function view_submission() {
    check_ajax_referer('BFMSF_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(esc_html__('Unauthorized', 'frankel-bullet-form'));
    }
    
    $submission_id = intval($_POST['submission_id']);
    
    global $wpdb;
    $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
    $fields_table = $wpdb->prefix . 'bfmsf_form_fields';
    
    $submission = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $submissions_table WHERE id = %d",
        $submission_id
    ));
    
    if (!$submission) {
        wp_send_json_error(esc_html__('Submission not found', 'frankel-bullet-form'));
    }
    
    $data = json_decode($submission->submission_data, true);
    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT field_name, field_label FROM $fields_table WHERE form_id = %d ORDER BY field_order ASC",
        $submission->form_id
    ));
    
    // Build field rows
    $rows = '';
    foreach ($fields as $field) {
        $value = isset($data[$field->field_name]) ? $data[$field->field_name] : '';
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        // Display "(empty)" for blank values
        $display_value = ($value === '' || $value === null) ? '<span class="bfmsf-empty-value">(empty)</span>' : esc_html($value);
        $rows .= '<tr>';
        $rows .= '<td>' . esc_html($field->field_label) . '</td>';
        $rows .= '<td>' . $display_value . '</td>';
        $rows .= '</tr>';
    }
    
    // Build the HTML with a clean structure
    $html = '<div class="bfmsf-submission-details-wrap">';
    $html .= '<table class="bfmsf-submission-details">';
    $html .= '<thead><tr><th>' . esc_html__('Field', 'frankel-bullet-form') . '</th><th>' . esc_html__('Value', 'frankel-bullet-form') . '</th></tr></thead>';
    $html .= '<tbody>' . $rows . '</tbody>';
    $html .= '</table>';
    $html .= '<div class="bfmsf-submission-meta">';
    $html .= '<span><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html__('Submitted:', 'frankel-bullet-form') . ' ' . esc_html($submission->submitted_date) . '</span>';
    $html .= '<span><span class="dashicons dashicons-location"></span> ' . esc_html__('IP Address:', 'frankel-bullet-form') . ' ' . esc_html($submission->user_ip) . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    
    wp_send_json_success(array('html' => $html));
}
}



