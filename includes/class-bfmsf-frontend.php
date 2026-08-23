<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend class for Multi-Step Form Builder
 */

class BFMSF_Frontend {
    
    public function __construct() {
        add_shortcode('bfmsf_form', array($this, 'render_form_shortcode'));
        add_action('wp_ajax_BFMSF_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_BFMSF_submit_form', array($this, 'handle_form_submission'));
    }




    /**
 * Get country codes with labels (for phone field)
 */
private function get_country_codes() {
    return array(
        'US' => '🇺🇸 +1 (US)',
        'GB' => '🇬🇧 +44 (UK)',
        'IN' => '🇮🇳 +91 (India)',
        'AU' => '🇦🇺 +61 (Australia)',
        'CA' => '🇨🇦 +1 (Canada)',
        'DE' => '🇩🇪 +49 (Germany)',
        'BD' => 'BD +88 (Bangladesh)',
        'FR' => '🇫🇷 +33 (France)',
        'IT' => '🇮🇹 +39 (Italy)',
        'ES' => '🇪🇸 +34 (Spain)',
        'BR' => '🇧🇷 +55 (Brazil)',
        'CN' => '🇨🇳 +86 (China)',
        'JP' => '🇯🇵 +81 (Japan)',
        'KR' => '🇰🇷 +82 (South Korea)',
        'RU' => '🇷🇺 +7 (Russia)',
        'ZA' => '🇿🇦 +27 (South Africa)',
        'NG' => '🇳🇬 +234 (Nigeria)',
        'MX' => '🇲🇽 +52 (Mexico)',
        'AR' => '🇦🇷 +54 (Argentina)',
        'EG' => '🇪🇬 +20 (Egypt)',
        'SA' => '🇸🇦 +966 (Saudi Arabia)',
    );
}



    
    /**
     * Render form shortcode
     */
    public function render_form_shortcode($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $form_id = intval($atts['id']);
        
        if ($form_id === 0) {
            return '<p>' . esc_html__('Invalid form ID', 'frankel-bullet-form') . '</p>';
        }
        
        global $wpdb;
        $forms_table = $wpdb->prefix . 'bfmsf_forms';
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE id = %d", $form_id));

        if (!$form) {
            $post = get_post($form_id);
            if ($post && $post->post_type === 'bfmsf_form') {
                $form = (object) array(
                    'id' => (int) $post->ID,
                    'title' => $post->post_title,
                    'description' => $post->post_content,
                );
            }
        }

        if (!$form) {
            return '<p>' . esc_html__('Form not found', 'frankel-bullet-form') . '</p>';
        }
        
        $form_style = BFMSF_Settings::get_style($form_id);
        $form_settings = BFMSF_Settings::get_settings($form_id);

        $fields = $this->get_fields_from_saved_form($form_id);

        // Enqueue captcha scripts on-demand if any captcha field is present
        foreach ($fields as $field) {
            if ($field->field_type === 'hcaptcha') {
                wp_enqueue_script('hcaptcha-api', 'https://js.hcaptcha.com/1/api.js', array(), null, true);
            } elseif ($field->field_type === 'recaptcha') {
                wp_enqueue_script('google-recaptcha-api', 'https://www.google.com/recaptcha/api.js', array(), null, true);
            } elseif ($field->field_type === 'turnstile') {
                wp_enqueue_script('cloudflare-turnstile-api', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true);
            }
        }

        if (empty($fields)) {
            $fields_table = $wpdb->prefix . 'bfmsf_form_fields';
            $fields = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $fields_table WHERE form_id = %d ORDER BY step_number ASC, field_order ASC",
                $form_id
            ));
            foreach ($fields as $field) {
                $field->row_id    = 'legacy_' . $field->id;
                $field->slot_flex = 1;
            }
        }
        
        $steps = array();
        $max_step = 0;
        foreach ($fields as $field) {
            $step_num = $field->step_number;
            if (!isset($steps[$step_num])) {
                $steps[$step_num] = array();
            }

            $row_key = isset($field->row_id) ? $field->row_id : 'row_' . $field->id;
            if (!isset($steps[$step_num][$row_key])) {
                $steps[$step_num][$row_key] = array();
            }
            $steps[$step_num][$row_key][] = $field;

            $max_step = max($max_step, $step_num);
        }

        if (empty($steps)) {
            return '<p>' . esc_html__('No fields in this form', 'frankel-bullet-form') . '</p>';
        }

        $primary_color          = sanitize_hex_color($form_style['primary_color'] ?? '#4361ee');
        $border_color           = sanitize_hex_color($form_style['border_color'] ?? '#d1d5db');
        $container_border_color = sanitize_hex_color($form_style['container_border_color'] ?? '#e2e8f0');

        $custom_css = sprintf(
            '.bfmsf-form-wrapper[data-form-id="%d"] { border: none !important; }
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-container { box-shadow: none !important; }
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-field-group input,
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-field-group select,
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-field-group textarea { border: 1px solid %s !important; }
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-field-group input:focus,
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-field-group select:focus,
            .bfmsf-form-wrapper[data-form-id="%d"] .bfmsf-form-field-group textarea:focus { border-color: %s !important; }',
            $form_id,
            $form_id, // container box-shadow only
            $form_id, $form_id, $form_id, esc_attr($border_color),
            $form_id, $form_id, $form_id, esc_attr($primary_color)
        );
        wp_add_inline_style( 'bfmsf-frontend-style', $custom_css );
                
        ob_start();
        include BFMSF_PLUGIN_DIR . 'templates/form-display.php';
        return ob_get_clean();
    }

    private function get_layout_configs() {
        return array(
            '1-full'       => array('flexes' => array(1)),
            '2-equal'      => array('flexes' => array(1, 1)),
            '3-equal'      => array('flexes' => array(1, 1, 1)),
            '4-equal'      => array('flexes' => array(1, 1, 1, 1)),
            '1-2wide'      => array('flexes' => array(1, 2)),
            '2wide-1'      => array('flexes' => array(2, 1)),
            'sidebar-main' => array('flexes' => array(1, 3)),
        );
    }
    
    private function get_fields_from_saved_form($form_id) {
        $rows_raw = BFMSF_Settings::get_rows($form_id);
        $defs_raw = BFMSF_Settings::get_field_defs($form_id);
        
        $rows = json_decode($rows_raw, true);
        $field_defs = json_decode($defs_raw, true);

        if (isset($rows['rows']) && is_array($rows['rows'])) {
            $rows = $rows['rows'];
        }
        if (isset($field_defs['fieldDefs']) && is_array($field_defs['fieldDefs'])) {
            $field_defs = $field_defs['fieldDefs'];
        }

        if (!is_array($rows) || !is_array($field_defs) || empty($rows)) {
            return array();
        }

        $type_map = array(
            'name' => 'text',
            'email' => 'email',
            // 'phone' => 'tel',
            'url' => 'url',
            'address' => 'text',
            'text' => 'text',
            'textarea' => 'textarea',
            'dropdown' => 'select',
            'checkboxes' => 'checkbox',
            'radio' => 'radio',
            'image' => 'file',
            'number' => 'number',
            'date' => 'date',
            'checkbox' => 'checkbox',
            'product' => 'select',
            'price' => 'number',
            'quantity' => 'number',
            'coupon' => 'text',
            'payment_method' => 'radio',
            'file' => 'file',
            // New types
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
            // 'confirm' => 'checkbox',
            'hcaptcha' => 'hcaptcha',
            'hidden' => 'hidden',
            'recaptcha' => 'recaptcha',
            'antispam' => 'text',
            'star_rating' => 'number',
            'turnstile' => 'turnstile',
        );

        $layout_configs = $this->get_layout_configs();

        $fields = array();
        $order = 0;

        foreach ($rows as $row_index => $row) {
            $row_id = isset($row['id']) ? $row['id'] : 'row_' . $row_index;
            $layout = isset($row['layout']) ? $row['layout'] : '2-equal';
            $cfg    = isset($layout_configs[$layout]) ? $layout_configs[$layout] : $layout_configs['2-equal'];

            $slots = isset($row['slots']) && is_array($row['slots']) ? $row['slots'] : array();

            foreach ($slots as $slot_index => $field_id) {
                if (empty($field_id) || !isset($field_defs[$field_id])) {
                    continue;
                }

                $field = $field_defs[$field_id];
                $field_obj = new stdClass();

                


                $field_obj->id = $field['id'] ?? $field_id;
                $field_obj->form_id = $form_id;
                $field_obj->step_number = isset($row['step']) ? intval($row['step']) : 1;
                $field_obj->field_order = $order++;

                $field_obj->row_id    = $row_id;
                $field_obj->slot_flex = isset($cfg['flexes'][$slot_index]) ? $cfg['flexes'][$slot_index] : 1;

                $raw_type = $field['type'] ?? 'text';
                $field_obj->field_type = $type_map[$raw_type] ?? $raw_type;

                $field_obj->field_label = sanitize_text_field($field['label'] ?? ($field['name'] ?? ''));
                $field_obj->field_name = sanitize_key($field['name'] ?? $field_id);
                $field_obj->field_placeholder = sanitize_text_field($field['placeholder'] ?? '');
                $field_obj->field_required = !empty($field['required']) ? 1 : 0;

                $field_obj->confirmationText = $field['confirmationText'] ?? '';

                $field_obj->defaultChecked = $field['defaultChecked'] ?? false;

                $field_obj->cssClass   = $field['cssClass']   ?? '';
                // $field_obj->customCss  = $field['customCss']  ?? '';



                if (!empty($field['conditions']) && is_array($field['conditions'])) {
                    $field_obj->field_conditions = wp_json_encode($field['conditions']);
                } else {
                    $field_obj->field_conditions = '';
                }

                if (!empty($field['options']) && is_array($field['options'])) {
                    $field_obj->field_options = wp_json_encode(array_map('sanitize_text_field', $field['options']));
                } else {
                    $field_obj->field_options = '';
                }

                $fields[] = $field_obj;
            }
        }

        return $fields;
    }
    
    public function handle_form_submission() {
        check_ajax_referer('BFMSF_nonce', 'nonce');

        $form_id       = intval($_POST['form_id']);
        $form_data     = isset($_POST['form_data']) ? map_deep(wp_unslash($_POST['form_data']), 'sanitize_text_field') : array();
        $captcha_token = isset($_POST['captcha_token']) ? sanitize_text_field(wp_unslash($_POST['captcha_token'])) : '';
        $captcha_type  = isset($_POST['captcha_type'])  ? sanitize_key(wp_unslash($_POST['captcha_type']))          : '';

        // Honeypot check — silently drop spam submissions.
        if (!empty($_POST['honeypot'])) {
            wp_send_json_error(array('message' => esc_html__('Submission blocked as spam.', 'frankel-bullet-form')));
        }


        
        // === NEW: Combine phone country + number ===

        foreach ($form_data as $key => $value) {
            if (is_array($value) && isset($value['country']) && isset($value['number'])) {
                $form_data[$key] = $value['country'] . ' ' . $value['number'];
            }
        }



        // CAPTCHA verification — performed before any data is processed.
        if (!empty($captcha_type) && !empty($captcha_token)) {
            require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-spam.php';
            if (!BFMSF_Spam::verify_captcha($captcha_type, $captcha_token, $form_id)) {
                wp_send_json_error(array('message' => esc_html__('CAPTCHA verification failed. Please try again.', 'frankel-bullet-form')));
            }
        }
        
        global $wpdb;
        $forms_table = $wpdb->prefix . 'bfmsf_forms';
        $fields_table = $wpdb->prefix . 'bfmsf_form_fields';
        $submissions_table = $wpdb->prefix . 'bfmsf_submissions';
        
        $form = $wpdb->get_row($wpdb->prepare("SELECT id FROM $forms_table WHERE id = %d", $form_id));
        if (!$form) {
            $post = get_post($form_id);
            if (!$post || $post->post_type !== 'bfmsf_form') {
                wp_send_json_error(array('message' => esc_html__('Form not found', 'frankel-bullet-form')));
            }
        }

        $form_settings = BFMSF_Settings::get_settings($form_id);

        // Enforce draft status — do not accept submissions for inactive forms.
        if (($form_settings['status'] ?? 'active') === 'draft') {
            wp_send_json_error(array('message' => esc_html__('This form is not currently accepting submissions.', 'frankel-bullet-form')));
        }

        // Enforce require login.
        if (!empty($form_settings['require_login']) && !is_user_logged_in()) {
            wp_send_json_error(array('message' => esc_html__('You must be logged in to submit this form.', 'frankel-bullet-form')));
        }

        // Enforce submission limit.
        $submission_limit = isset($form_settings['submission_limit']) ? intval($form_settings['submission_limit']) : 0;
        if ($submission_limit > 0) {
            $existing_count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $submissions_table WHERE form_id = %d",
                $form_id
            ));
            if ($existing_count >= $submission_limit) {
                wp_send_json_error(array('message' => esc_html__('This form has reached its submission limit.', 'frankel-bullet-form')));
            }
        }

        $db_fields = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $fields_table WHERE form_id = %d",
            $form_id
        ));
        
        $validated_data = array();
        $uploader = null;

        foreach ($db_fields as $field) {
            $field_name = $field->field_name;
            $field_type = $field->field_type;
            $is_required = (bool)$field->field_required;

            $uploaded_file = null;
            if ($field_type === 'file' && isset($_FILES['form_data']['tmp_name'][$field_name])) {
                $uploaded_file = array(
                    'name'     => isset($_FILES['form_data']['name'][$field_name]) ? $_FILES['form_data']['name'][$field_name] : '',
                    'type'     => isset($_FILES['form_data']['type'][$field_name]) ? $_FILES['form_data']['type'][$field_name] : '',
                    'tmp_name' => $_FILES['form_data']['tmp_name'][$field_name],
                    'error'    => isset($_FILES['form_data']['error'][$field_name]) ? $_FILES['form_data']['error'][$field_name] : UPLOAD_ERR_NO_FILE,
                    'size'     => isset($_FILES['form_data']['size'][$field_name]) ? $_FILES['form_data']['size'][$field_name] : 0,
                );
            }

            if ($is_required) {
                $value_missing = (!isset($form_data[$field_name]) || $form_data[$field_name] === '' || (is_array($form_data[$field_name]) && empty($form_data[$field_name])));
                if ($field_type === 'file') {
                    $value_missing = (!$uploaded_file || $uploaded_file['error'] !== UPLOAD_ERR_OK);
                }
                if ($value_missing) {
                    wp_send_json_error(array('message' => sprintf(esc_html__('%s is required.', 'frankel-bullet-form'), esc_html($field->field_label))));
                }
            }

            if ($field_type === 'file' && $uploaded_file && $uploaded_file['error'] === UPLOAD_ERR_OK) {
                if (!$uploader) {
                    require_once BFMSF_PLUGIN_DIR . 'includes/class-bfmsf-upload.php';
                    $uploader = new BFMSF_Upload();
                }
                $upload_result = $uploader->handle_upload($uploaded_file, $field_name);
                if (is_wp_error($upload_result)) {
                    wp_send_json_error(array('message' => $upload_result->get_error_message()));
                }
                $validated_data[$field_name] = $upload_result;
                continue;
            }

            if (isset($form_data[$field_name])) {
                $raw_value = $form_data[$field_name];

                if (is_array($raw_value)) {
                    $sanitized_value = array_map('sanitize_text_field', $raw_value);
                } else {
                    if ($field_type === 'email') {
                        $sanitized_value = sanitize_email($raw_value);
                        if ($is_required && !is_email($sanitized_value)) {
                            wp_send_json_error(array('message' => sprintf(esc_html__('Please enter a valid email address for %s.', 'frankel-bullet-form'), esc_html($field->field_label))));
                        }
                    } elseif ($field_type === 'textarea') {
                        $sanitized_value = sanitize_textarea_field($raw_value);
                    } else {
                        $sanitized_value = sanitize_text_field($raw_value);
                    }
                }

                $validated_data[$field_name] = $sanitized_value;
            }
        }
        
        $user_ip = $this->get_user_ip();
        
        $result = $wpdb->insert($submissions_table, array(
            'form_id' => $form_id,
            'submission_data' => json_encode($validated_data),
            'user_ip' => $user_ip,
            'submitted_date' => current_time('mysql'),
        ));
        
        if ($result) {
            // Email notification
            $this->maybe_send_email_notification($form_settings, $validated_data, $form_id);

            // Outgoing webhooks (Google Sheets / Zapier / HubSpot)
            $this->maybe_dispatch_webhooks($form_settings, $validated_data, $form_id);

            $response = array(
                'message'           => esc_html__('Form submitted successfully!', 'frankel-bullet-form'),
                'confirmation_type' => $form_settings['confirmation_type'] ?? 'message',
            );
            if (($form_settings['confirmation_type'] ?? 'message') === 'redirect' && !empty($form_settings['redirect_url'])) {
                $response['redirect_url'] = esc_url_raw($form_settings['redirect_url']);
            } else {
                $response['confirmation_message'] = $form_settings['confirmation_message'] ?? esc_html__('Thank you for your submission!', 'frankel-bullet-form');
            }

            wp_send_json_success($response);
        } else {
            wp_send_json_error(array('message' => esc_html__('Error submitting form', 'frankel-bullet-form')));
        }
    }

    /**
     * Send an email notification for a new submission, based on saved form settings.
     * Silently does nothing if no recipient is configured.
     */
    private function maybe_send_email_notification($form_settings, $validated_data, $form_id) {
        $recipient = isset($form_settings['email_recipient']) ? sanitize_email($form_settings['email_recipient']) : '';
        if (empty($recipient) || !is_email($recipient)) {
            return;
        }

        $subject = isset($form_settings['email_subject']) && $form_settings['email_subject'] !== ''
            ? sanitize_text_field($form_settings['email_subject'])
            : esc_html__('New Form Submission', 'frankel-bullet-form');

        $from_name = isset($form_settings['email_from_name']) && $form_settings['email_from_name'] !== ''
            ? sanitize_text_field($form_settings['email_from_name'])
            : get_bloginfo('name');

        $lines = array();
        foreach ($validated_data as $field_name => $value) {
            $display_value = is_array($value) ? implode(', ', $value) : (string) $value;
            $lines[] = sanitize_text_field($field_name) . ': ' . sanitize_text_field($display_value);
        }
        $body = implode("\n", $lines);

        $admin_email = get_option('admin_email');
        $headers = array();
        if ($from_name) {
            $headers[] = 'From: ' . $from_name . ' <' . $admin_email . '>';
        }

        wp_mail($recipient, $subject, $body, $headers);
    }

    /**
     * Dispatch submission data to any enabled integration webhooks.
     * Each integration is independent and failures are silently ignored
     * (a non-blocking outbound request) so a broken webhook never breaks
     * the user's form submission experience.
     */
    private function maybe_dispatch_webhooks($form_settings, $validated_data, $form_id) {
        $payload = array(
            'form_id'   => $form_id,
            'data'      => $validated_data,
            'timestamp' => current_time('mysql'),
        );

        $webhooks = array(
            'integration_google_sheets' => 'google_sheets_webhook_url',
            'integration_zapier'        => 'zapier_webhook_url',
            'integration_hubspot'       => 'hubspot_webhook_url',
        );

        foreach ($webhooks as $toggle_key => $url_key) {
            if (empty($form_settings[$toggle_key])) {
                continue;
            }
            $url = isset($form_settings[$url_key]) ? esc_url_raw($form_settings[$url_key]) : '';
            if (empty($url)) {
                continue;
            }
            wp_remote_post($url, array(
                'method'    => 'POST',
                'timeout'   => 8,
                'blocking'  => false, // fire-and-forget so it never slows down the user's submission
                'headers'   => array('Content-Type' => 'application/json'),
                'body'      => wp_json_encode($payload),
            ));
        }
    }
    
    private function get_user_ip() {
        $ip = '0.0.0.0';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return '0.0.0.0';
    }
}