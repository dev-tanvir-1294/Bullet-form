<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Controller for Freankel Bullet Form
 * Namespace: bfmsf/v1
 */
class BFMSF_REST {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register all REST routes
     */
    public function register_routes() {
        $namespace = 'bfmsf/v1';

        // Forms endpoints
        register_rest_route($namespace, '/forms', array(
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_forms'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'create_or_update_form'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
        ));

        register_rest_route($namespace, '/forms/(?P<id>\d+)', array(
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_form'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
            array(
                'methods'             => 'DELETE',
                'callback'            => array($this, 'delete_form'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
        ));

        // Submissions endpoints
        register_rest_route($namespace, '/submissions/(?P<form_id>\d+)', array(
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_submissions'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
        ));

        register_rest_route($namespace, '/submissions/delete/(?P<id>\d+)', array(
            array(
                'methods'             => 'DELETE',
                'callback'            => array($this, 'delete_submission'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
        ));

        register_rest_route($namespace, '/submissions/export/(?P<form_id>\d+)', array(
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'export_csv'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
        ));
    }

    /**
     * Permission check: current user must have manage_options
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    // =========================================================================
    // FORMS
    // =========================================================================

    /**
     * GET /forms — list all forms with field count and submission count
     */
    public function get_forms($request) {
        global $wpdb;
        $forms_table       = $wpdb->prefix . 'bfmsf_forms';
        $fields_table      = $wpdb->prefix . 'bfmsf_form_fields';
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';

        $forms = $wpdb->get_results("SELECT * FROM $forms_table ORDER BY created_date DESC");

        $result = array();
        foreach ($forms as $form) {
            $field_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $fields_table WHERE form_id = %d", $form->id
            ));
            $submission_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $submissions_table WHERE form_id = %d", $form->id
            ));

            $result[] = array(
                'id'               => (int) $form->id,
                'title'            => $form->title,
                'description'      => $form->description,
                'status'           => $form->status,
                'created_date'     => $form->created_date,
                'modified_date'    => $form->modified_date,
                'field_count'      => (int) $field_count,
                'submission_count' => (int) $submission_count,
                'shortcode'        => '[bfmsf_form id="' . $form->id . '"]',
            );
        }

        return rest_ensure_response($result);
    }

    /**
     * GET /forms/{id} — get a single form with its fields grouped by step
     */
    public function get_form($request) {
        global $wpdb;
        $form_id      = (int) $request['id'];
        $forms_table  = $wpdb->prefix . 'bfmsf_forms';
        $fields_table = $wpdb->prefix . 'bfmsf_form_fields';

        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE id = %d", $form_id));

        if (!$form) {
            return new WP_Error('not_found', esc_html__('Form not found', 'frankel-bullet-form'), array('status' => 404));
        }

        $fields = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $fields_table WHERE form_id = %d ORDER BY step_number ASC, field_order ASC",
            $form_id
        ));

        // Group by step
        $steps = array();
        foreach ($fields as $field) {
            $step_num = (int) $field->step_number;
            if (!isset($steps[$step_num])) {
                $steps[$step_num] = array();
            }
            $steps[$step_num][] = array(
                'id'          => (int) $field->id,
                'field_type'  => $field->field_type,
                'field_label' => $field->field_label,
                'field_name'  => $field->field_name,
                'placeholder' => $field->field_placeholder,
                'required'    => (bool) $field->field_required,
                'options'     => $field->field_options ? json_decode($field->field_options, true) : array(),
            );
        }

        $result = array(
            'id'          => (int) $form->id,
            'title'       => $form->title,
            'description' => $form->description,
            'status'      => $form->status,
            'steps'       => $steps,
        );

        return rest_ensure_response($result);
    }

    /**
     * POST /forms — create or update a form
     * Body: { title, description, steps: { 1: [ { field_label, field_name, field_type, placeholder, required, options } ] } }
     */
    public function create_or_update_form($request) {
        global $wpdb;
        $forms_table  = $wpdb->prefix . 'bfmsf_forms';
        $fields_table = $wpdb->prefix . 'bfmsf_form_fields';

        $params      = $request->get_json_params();
        $form_id     = isset($params['id']) ? (int) $params['id'] : 0;
        $title       = sanitize_text_field($params['title'] ?? '');
        $description = sanitize_textarea_field($params['description'] ?? '');
        $steps       = $params['steps'] ?? array();

        if (empty($title)) {
            return new WP_Error('missing_title', esc_html__('Form title is required', 'frankel-bullet-form'), array('status' => 400));
        }

        if ($form_id > 0) {
            // Update existing form
            $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $forms_table WHERE id = %d", $form_id));
            if (!$existing) {
                return new WP_Error('not_found', esc_html__('Form not found', 'frankel-bullet-form'), array('status' => 404));
            }
            $wpdb->update($forms_table, array(
                'title'       => $title,
                'description' => $description,
            ), array('id' => $form_id));
        } else {
            // Create new form
            $wpdb->insert($forms_table, array(
                'title'       => $title,
                'description' => $description,
                'status'      => 'publish',
                'created_by'  => get_current_user_id(),
            ));
            $form_id = $wpdb->insert_id;
        }

        // Delete existing fields and re-insert
        $wpdb->delete($fields_table, array('form_id' => $form_id));

        if (!empty($steps) && is_array($steps)) {
            foreach ($steps as $step_num => $step_fields) {
                if (!is_array($step_fields)) continue;
                $field_order = 0;
                foreach ($step_fields as $field) {
                    if (!is_array($field) || empty($field['field_label']) || empty($field['field_name'])) {
                        continue;
                    }

                    $field_type = sanitize_text_field($field['field_type'] ?? 'text');
                    $options = '';
                    if (in_array($field_type, array('select', 'radio', 'checkbox')) && !empty($field['options'])) {
                        if (is_array($field['options'])) {
                            $options = json_encode(array_map('sanitize_text_field', $field['options']));
                        } else {
                            $options_arr = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $field['options']))));
                            $options = json_encode(array_map('sanitize_text_field', $options_arr));
                        }
                    }

                    $wpdb->insert($fields_table, array(
                        'form_id'           => $form_id,
                        'step_number'       => intval($step_num),
                        'field_order'       => $field_order,
                        'field_type'        => $field_type,
                        'field_label'       => sanitize_text_field($field['field_label']),
                        'field_name'        => sanitize_text_field($field['field_name']),
                        'field_placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
                        'field_required'    => !empty($field['required']) ? 1 : 0,
                        'field_options'     => $options,
                    ));
                    $field_order++;
                }
            }
        }

        return rest_ensure_response(array(
            'id'      => $form_id,
            'message' => esc_html__('Form saved successfully', 'frankel-bullet-form'),
        ));
    }

    /**
     * DELETE /forms/{id} — delete a form and all related data
     */
    public function delete_form($request) {
        global $wpdb;
        $form_id           = (int) $request['id'];
        $forms_table       = $wpdb->prefix . 'bfmsf_forms';
        $fields_table      = $wpdb->prefix . 'bfmsf_form_fields';
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';

        $form = $wpdb->get_row($wpdb->prepare("SELECT id FROM $forms_table WHERE id = %d", $form_id));
        if (!$form) {
            return new WP_Error('not_found', esc_html__('Form not found', 'frankel-bullet-form'), array('status' => 404));
        }

        $wpdb->delete($submissions_table, array('form_id' => $form_id));
        $wpdb->delete($fields_table, array('form_id' => $form_id));
        $wpdb->delete($forms_table, array('id' => $form_id));

        return rest_ensure_response(array('message' => esc_html__('Form deleted', 'frankel-bullet-form')));
    }

    // =========================================================================
    // SUBMISSIONS
    // =========================================================================

    /**
     * GET /submissions/{form_id}?page=1&per_page=20
     */
    public function get_submissions($request) {
        global $wpdb;
        $form_id           = (int) $request['form_id'];
        $page              = (int) ($request->get_param('page') ?? 1);
        $per_page          = (int) ($request->get_param('per_page') ?? 20);
        $offset            = ($page - 1) * $per_page;
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
        $fields_table      = $wpdb->prefix . 'bfmsf_form_fields';

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $submissions_table WHERE form_id = %d", $form_id
        ));

        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $submissions_table WHERE form_id = %d ORDER BY submitted_date DESC LIMIT %d OFFSET %d",
            $form_id, $per_page, $offset
        ));

        // Get fields for label mapping
        $fields = $wpdb->get_results($wpdb->prepare(
            "SELECT field_name, field_label FROM $fields_table WHERE form_id = %d ORDER BY step_number ASC, field_order ASC",
            $form_id
        ));

        $field_map = array();
        foreach ($fields as $f) {
            $field_map[$f->field_name] = $f->field_label;
        }

        $items = array();
        foreach ($submissions as $sub) {
            $data = json_decode($sub->submission_data, true);
            $items[] = array(
                'id'             => (int) $sub->id,
                'form_id'        => (int) $sub->form_id,
                'data'           => is_array($data) ? $data : array(),
                'submitted_date' => $sub->submitted_date,
                'user_ip'        => $sub->user_ip,
            );
        }

        return rest_ensure_response(array(
            'items'      => $items,
            'total'      => (int) $total,
            'page'       => $page,
            'per_page'   => $per_page,
            'total_pages' => (int) ceil($total / $per_page),
            'field_map'  => $field_map,
        ));
    }

    /**
     * DELETE /submissions/delete/{id}
     */
    public function delete_submission($request) {
        global $wpdb;
        $id                = (int) $request['id'];
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';

        $result = $wpdb->delete($submissions_table, array('id' => $id));

        if ($result) {
            return rest_ensure_response(array('message' => esc_html__('Submission deleted', 'frankel-bullet-form')));
        }
        return new WP_Error('delete_failed', esc_html__('Could not delete submission', 'frankel-bullet-form'), array('status' => 500));
    }

    /**
     * GET /submissions/export/{form_id} — direct CSV download
     */
    public function export_csv($request) {
        global $wpdb;
        $form_id           = (int) $request['form_id'];
        $forms_table       = $wpdb->prefix . 'bfmsf_forms';
        $fields_table      = $wpdb->prefix . 'bfmsf_form_fields';
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';

        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE id = %d", $form_id));
        if (!$form) {
            return new WP_Error('not_found', esc_html__('Form not found', 'frankel-bullet-form'), array('status' => 404));
        }

        $fields = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $fields_table WHERE form_id = %d ORDER BY step_number ASC, field_order ASC",
            $form_id
        ));

        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $submissions_table WHERE form_id = %d ORDER BY submitted_date DESC",
            $form_id
        ));

        $filename = 'form-submissions-' . $form_id . '-' . gmdate('Y-m-d-H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Header row
        $headers = array('Submission ID', 'Submitted Date', 'User IP');
        foreach ($fields as $field) {
            $headers[] = $field->field_label;
        }
        fputcsv($output, $headers);

        // Data rows
        foreach ($submissions as $submission) {
            $data = json_decode($submission->submission_data, true);
            $row = array($submission->id, $submission->submitted_date, $submission->user_ip);
            foreach ($fields as $field) {
                $value = isset($data[$field->field_name]) ? $data[$field->field_name] : '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $row[] = $value;
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
