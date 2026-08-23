<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class for Bullet Form Builder
 */
class BFMSF_Admin {

    public function __construct() {
        add_action('admin_init',                  array($this, 'handle_get_delete_form'));
        add_action('wp_ajax_bfmsf_save_builder',  array($this, 'ajax_save_builder'));
        add_action('wp_ajax_bfmsf_delete_form',   array($this, 'ajax_delete_form'));
        add_action('wp_ajax_bfmsf_duplicate_form',array($this, 'ajax_duplicate_form'));
        add_action('wp_ajax_BFMSF_export_csv',    array($this, 'ajax_export_csv'));
        add_action('wp_ajax_bfmsf_preview_form',  array($this, 'ajax_preview_form'));
    }

    /**
     * Handle delete form action via GET request.
     */
    public function handle_get_delete_form() {
        if (isset($_GET['page']) && $_GET['page'] === 'bfmsf-forms' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['form_id']) && isset($_GET['_wpnonce'])) {
            $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
            $nonce   = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
            if (wp_verify_nonce($nonce, 'bfmsf_delete_form_' . $form_id) && current_user_can('manage_options')) {
                $del_id = $form_id;
                wp_delete_post($del_id, true);
                global $wpdb;
                $wpdb->delete($wpdb->prefix . 'bfmsf_submissions', array('form_id' => $del_id));
                $wpdb->delete($wpdb->prefix . 'bfmsf_form_fields', array('form_id' => $del_id));
                $wpdb->delete($wpdb->prefix . 'bfmsf_forms', array('id' => $del_id));
                wp_safe_redirect( admin_url( 'admin.php?page=bfmsf-forms&deleted=1' ) );
                exit;
            }
        }
    }

    /**
     * AJAX: Save builder state (rows + field defs + settings + style + canvas meta)
     */
    public function ajax_save_builder() {
        check_ajax_referer('BFMSF_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $form_id        = intval($_POST['form_id'] ?? 0);
        $form_title     = sanitize_text_field(wp_unslash($_POST['form_title'] ?? 'Untitled Form'));
        $canvas_title   = sanitize_text_field(wp_unslash($_POST['canvas_title'] ?? ''));
        $canvas_subtitle= sanitize_text_field(wp_unslash($_POST['canvas_subtitle'] ?? ''));
        $rows_json      = wp_unslash($_POST['rows'] ?? '[]');
        $field_defs_json= wp_unslash($_POST['field_defs'] ?? '{}');
        $settings_raw   = wp_unslash($_POST['settings'] ?? '{}');
        $style_raw      = wp_unslash($_POST['style'] ?? '{}');

        // ── Server‑side lock to prevent duplicate form creation ──
        $lock_key = 'bfmsf_save_lock_' . get_current_user_id();
        if ( $form_id == 0 ) {
            if ( get_transient( $lock_key ) ) {
                wp_send_json_error( array( 'message' => 'A form is already being saved. Please wait.' ) );
            }
            set_transient( $lock_key, 1, 30 );
        }

        // Ensure JSON is actually JSON
        if (!is_string($rows_json) || empty($rows_json) || $rows_json === 'undefined') {
            $rows_json = '[]';
        }
        if (!is_string($field_defs_json) || empty($field_defs_json) || $field_defs_json === 'undefined') {
            $field_defs_json = '{}';
        }

        // Validate JSON before saving
        $rows_test = json_decode($rows_json, true);
        $defs_test = json_decode($field_defs_json, true);
        if (!is_array($rows_test) || !is_array($defs_test)) {
            delete_transient( $lock_key );
            wp_send_json_error(array('message' => 'Invalid JSON data'));
        }

        $settings_data = json_decode($settings_raw, true);
        $style_data    = json_decode($style_raw, true);
        if (!is_array($settings_data)) $settings_data = array();
        if (!is_array($style_data))    $style_data    = array();

        $settings_data = $this->sanitize_settings($settings_data);
        $style_data    = $this->sanitize_style($style_data);

        if ($form_id) {
            $result = wp_update_post(array(
                'ID'          => $form_id,
                'post_title'  => $form_title ?: 'Untitled Form',
                'post_status' => 'publish',
                'post_type'   => 'bfmsf_form',
            ), true);
            if (is_wp_error($result)) {
                delete_transient( $lock_key );
                wp_send_json_error(array('message' => $result->get_error_message()));
            }
        } else {
            $form_id = wp_insert_post(array(
                'post_title'  => $form_title ?: 'Untitled Form',
                'post_type'   => 'bfmsf_form',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ), true);
            if (is_wp_error($form_id)) {
                delete_transient( $lock_key );
                wp_send_json_error(array('message' => $form_id->get_error_message()));
            }
            // Ensure entry in bfmsf_forms table
            global $wpdb;
            $forms_table = $wpdb->prefix . 'bfmsf_forms';
            $wpdb->insert($forms_table, array(
                'id'         => $form_id,
                'title'      => $form_title,
                'status'     => 'publish',
                'created_by' => get_current_user_id(),
            ));
        }

        // Save new-format data
        BFMSF_Settings::save_rows($form_id, $rows_json);
        BFMSF_Settings::save_field_defs($form_id, $field_defs_json);
        BFMSF_Settings::save_canvas_meta($form_id, $canvas_title, $canvas_subtitle);
        BFMSF_Settings::save_settings($form_id, $settings_data);
        BFMSF_Settings::save_style($form_id, $style_data);

        // Sync to DB fields table for frontend compatibility
        $this->sync_fields_to_db($form_id, $rows_json, $field_defs_json);

        // Update bfmsf_forms table
        global $wpdb;
        $forms_table = $wpdb->prefix . 'bfmsf_forms';
        $existing_form = $wpdb->get_var($wpdb->prepare(
            'SELECT id FROM ' . $forms_table . ' WHERE id = %d LIMIT 1',
            $form_id
        ));
        if ($existing_form !== null) {
            $wpdb->update($forms_table, array('title' => $form_title), array('id' => $form_id));
        } else {
            $wpdb->insert($forms_table, array(
                'id'         => $form_id,
                'title'      => $form_title,
                'status'     => 'publish',
                'created_by' => get_current_user_id(),
            ));
        }

        delete_transient( $lock_key );

        wp_send_json_success(array('form_id' => $form_id, 'message' => 'Form saved!'));
    }

    /**
     * Sync layout rows → DB fields table (ordered by row/slot position)
     */
    private function sync_fields_to_db($form_id, $rows_json, $field_defs_json) {
        global $wpdb;
        $fields_table = $wpdb->prefix . 'bfmsf_form_fields';
        $wpdb->delete($fields_table, array('form_id' => $form_id));

        $rows      = json_decode($rows_json, true);
        $field_defs= json_decode($field_defs_json, true);

        // LOG: check decoded data
        error_log('BFMSF sync: rows decoded = ' . print_r($rows, true));
        error_log('BFMSF sync: field_defs decoded = ' . print_r($field_defs, true));

        if (!is_array($rows) || !is_array($field_defs)) {
            error_log('BFMSF sync: rows or field_defs is not an array – exiting');
            return;
        }

        $type_map = array(
            'name'=>'text','email'=>'email','phone'=>'tel','url'=>'url',
            'address'=>'text','text'=>'text','textarea'=>'textarea',
            'dropdown'=>'select','checkboxes'=>'checkbox','radio'=>'radio',
            'image'=>'file','number'=>'number','date'=>'date','checkbox'=>'checkbox',
            'product' => 'select',
            'price' => 'number',
            'quantity' => 'number',
            'coupon' => 'text',
            'payment_method' => 'radio',
            'file' => 'file',
            'datetime' => 'datetime-local',
            'select_image' => 'select',
            'multiselect' => 'select',
            'signature' => 'text',
            'city' => 'text',
            'first_name' => 'text',
            'last_name' => 'text',
            'country' => 'select',
            'us_states' => 'select',
            'zip' => 'text',
            'html' => 'text',
            'repeatable' => 'text',
            'divider' => 'text',
            'confirm' => 'checkbox',
            'hcaptcha' => 'hcaptcha',
            'hidden' => 'hidden',
            'recaptcha' => 'recaptcha',
            'antispam' => 'text',
            'star_rating' => 'number',
            'turnstile' => 'turnstile',
        );

        $order = 0;
        foreach ($rows as $row) {
            $step_number = !empty($row['step']) ? intval($row['step']) : 1;
            $slots = $row['slots'] ?? array();
            foreach ($slots as $fieldId) {
                if (empty($fieldId) || !isset($field_defs[$fieldId])) continue;
                $f = $field_defs[$fieldId];
                $db_type = $type_map[$f['type'] ?? 'text'] ?? 'text';
                $options = '';
                if (!empty($f['options']) && is_array($f['options'])) {
                    $sanitized_options = array();
                    foreach ($f['options'] as $option) {
                        if (is_array($option)) {
                            $sanitized_options[] = array_map('sanitize_text_field', $option);
                        } else {
                            $sanitized_options[] = sanitize_text_field((string) $option);
                        }
                    }
                    $options = wp_json_encode($sanitized_options);
                }
                $wpdb->insert($fields_table, array(
                    'form_id'           => $form_id,
                    'step_number'       => $step_number,
                    'field_order'       => $order++,
                    'field_type'        => $db_type,
                    'field_label'       => sanitize_text_field($f['label'] ?? ''),
                    'field_name'        => sanitize_key($fieldId),
                    'field_placeholder' => sanitize_text_field($f['placeholder'] ?? ''),
                    'field_required'    => !empty($f['required']) ? 1 : 0,
                    'field_options'     => $options,
                ));
            }
        }
    }

    private function sanitize_settings($data) {
        $s = array();
        foreach (array('form_name','status','confirmation_type','email_recipient','email_subject','email_from_name','submission_limit','api_token','api_endpoint','hcaptcha_site_key','hcaptcha_secret','recaptcha_site_key','recaptcha_secret','turnstile_site_key','turnstile_secret') as $k) {
            if (isset($data[$k])) $s[$k] = sanitize_text_field($data[$k]);
        }
        foreach (array('description','confirmation_message') as $k) {
            if (isset($data[$k])) $s[$k] = sanitize_textarea_field($data[$k]);
        }
        if (isset($data['redirect_url'])) $s['redirect_url'] = esc_url_raw($data['redirect_url']);
        foreach (array('require_login','integration_google_sheets','integration_zapier','integration_hubspot') as $k) {
            $s[$k] = !empty($data[$k]);
        }
        foreach (array('google_sheets_webhook_url','zapier_webhook_url','hubspot_webhook_url') as $k) {
            if (isset($data[$k])) $s[$k] = esc_url_raw($data[$k]);
        }
        return $s;
    }

    /**
     * Sanitize style data – all color values are validated as hex colors.
     */
    private function sanitize_style($data) {
        $s = array();

        // Text fields
        foreach (array('theme','input_style','label_alignment','bg_repeat','bg_size','bg_position','button_text','primary_font','secondary_font') as $k) {
            if (isset($data[$k])) $s[$k] = sanitize_text_field($data[$k]);
        }

        // Color fields – sanitize_hex_color returns null if invalid, so we fallback to defaults.
        $color_defaults = array(
            'primary_color'   => '#4361ee',
            'secondary_color' => '#3a0ca3',
            'text_color'      => '#1f2937',
            'button_bg_color' => '#4361ee',
            'button_text_color' => '#ffffff',
            'border_color'    => '#d1d5db',
            'container_border_color' => '#e2e8f0',
        );
        foreach ($color_defaults as $key => $default) {
            if (isset($data[$key])) {
                $s[$key] = sanitize_hex_color($data[$key]) ?: $default;
            } else {
                $s[$key] = $default;
            }
        }

        // Numeric fields
        foreach (array('heading_size','body_font_size','line_height','font_weight','border_radius','padding','margin','field_spacing') as $k) {
            if (isset($data[$k])) $s[$k] = floatval($data[$k]);
        }

        // Other fields
        $s['button_border'] = sanitize_text_field($data['button_border'] ?? '');
        $s['bg_image']      = esc_url_raw($data['bg_image'] ?? '');
        $s['button_hover']  = !empty($data['button_hover']);

        return $s;
    }

    public function ajax_delete_form() {
        check_ajax_referer('BFMSF_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(array('message'=>'Unauthorized'));
        $form_id = intval($_POST['form_id'] ?? 0);
        if (!$form_id) wp_send_json_error(array('message'=>'Invalid ID'));
        wp_delete_post($form_id, true);
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'bfmsf_submissions', array('form_id'=>$form_id));
        $wpdb->delete($wpdb->prefix.'bfmsf_form_fields', array('form_id'=>$form_id));
        $wpdb->delete($wpdb->prefix.'bfmsf_forms',       array('id'=>$form_id));
        wp_send_json_success(array('message'=>'Form deleted!'));
    }

    public function ajax_duplicate_form() {
        check_ajax_referer('BFMSF_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(array('message'=>'Unauthorized'));
        $form_id = intval($_POST['form_id'] ?? 0);
        $post = get_post($form_id);
        if (!$post) wp_send_json_error(array('message'=>'Not found'));
        $new_id = wp_insert_post(array(
            'post_title'  => $post->post_title.' (Copy)',
            'post_type'   => 'bfmsf_form',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ), true);
        if (is_wp_error($new_id)) wp_send_json_error(array('message'=>$new_id->get_error_message()));
        BFMSF_Settings::save_rows($new_id, BFMSF_Settings::get_rows($form_id));
        BFMSF_Settings::save_field_defs($new_id, BFMSF_Settings::get_field_defs($form_id));
        BFMSF_Settings::save_settings($new_id, BFMSF_Settings::get_settings($form_id));
        BFMSF_Settings::save_style($new_id, BFMSF_Settings::get_style($form_id));
        $this->sync_fields_to_db($new_id, BFMSF_Settings::get_rows($new_id), BFMSF_Settings::get_field_defs($new_id));
        wp_send_json_success(array('form_id'=>$new_id));
    }

    /**
     * AJAX: Render form HTML for the builder preview popup.
     */
    public function ajax_preview_form() {
        check_ajax_referer( 'BFMSF_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $form_id = intval( $_POST['form_id'] ?? 0 );
        if ( ! $form_id ) {
            wp_send_json_error( array( 'message' => 'Invalid form ID.' ) );
        }

        if ( ! class_exists( 'BFMSF_Frontend' ) ) {
            wp_send_json_error( array( 'message' => 'Frontend class not available.' ) );
        }

        $frontend = new BFMSF_Frontend();
        $html     = $frontend->render_form_shortcode( array( 'id' => $form_id ) );

        if ( empty( $html ) ) {
            wp_send_json_error( array( 'message' => 'Could not render form. Make sure the form has at least one field.' ) );
        }

        $css_path     = BFMSF_PLUGIN_DIR . 'assets/css/frontend.css';
        $frontend_css = file_exists( $css_path ) ? file_get_contents( $css_path ) : '';

        wp_send_json_success( array(
            'html'         => $html,
            'frontend_css' => $frontend_css,
        ) );
    }

    public function ajax_export_csv() {
        check_ajax_referer('BFMSF_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $form_id = isset($_REQUEST['form_id']) ? intval($_REQUEST['form_id']) : 0;
        global $wpdb;
        $fields_table      = $wpdb->prefix.'bfmsf_form_fields';
        $submissions_table = $wpdb->prefix.'bfmsf_submissions';
        $fields = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $fields_table WHERE form_id=%d ORDER BY field_order ASC", $form_id
        ));
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $submissions_table WHERE form_id=%d ORDER BY submitted_date DESC", $form_id
        ));
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="entries-'.$form_id.'-'.date('Y-m-d').'.csv"');
        $out = fopen('php://output','w');
        $hdr = array('ID','Date','IP');
        foreach ($fields as $f) $hdr[] = $f->field_label;
        fputcsv($out, $hdr);
        foreach ($submissions as $sub) {
            $data = json_decode($sub->submission_data, true);
            $row  = array($sub->id, $sub->submitted_date, $sub->user_ip);
            foreach ($fields as $f) $row[] = $data[$f->field_name] ?? '';
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
}