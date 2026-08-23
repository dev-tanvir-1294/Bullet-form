<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Field Template for AJAX
 */

if (!isset($step) || !isset($field_type)) {
    return;
}

$field_order = isset($_POST['field_order']) ? intval($_POST['field_order']) : 0;
$field_type = isset($_POST['field_type']) ? sanitize_text_field($_POST['field_type']) : 'text';
?>

<div class="bfmsf-field-item">
    <div class="bfmsf-field-header">
        <span class="bfmsf-field-drag">⋮⋮</span>
        <span class="bfmsf-field-label"><?php esc_html_e('New Field', 'frankel-bullet-form'); ?></span>
        <span class="bfmsf-field-type"><?php echo esc_html($field_type); ?></span>
        <button type="button" class="bfmsf-remove-field">×</button>
    </div>
    
    <div class="bfmsf-field-config">
        <div class="bfmsf-field-row">
            <label><?php esc_html_e('Field Label:', 'frankel-bullet-form'); ?></label>
            <input type="text" name="bfmsf_steps[<?php echo esc_attr( (string) $step ); ?>][<?php echo esc_attr( (string) $field_order ); ?>][label]" value="">
        </div>
        
        <div class="bfmsf-field-row">
            <label><?php esc_html_e('Field Name:', 'frankel-bullet-form'); ?></label>
            <input type="text" name="bfmsf_steps[<?php echo esc_attr( (string) $step ); ?>][<?php echo esc_attr( (string) $field_order ); ?>][name]" value="field_<?php echo esc_attr( (string) $step ); ?>_<?php echo esc_attr( (string) $field_order ); ?>">
        </div>
        
        <div class="bfmsf-field-row">
            <label><?php esc_html_e('Field Type:', 'frankel-bullet-form'); ?></label>
            <select name="bfmsf_steps[<?php echo esc_attr( (string) $step ); ?>][<?php echo esc_attr( (string) $field_order ); ?>][type]">
                <option value="text" <?php selected($field_type, 'text'); ?>><?php esc_html_e('Text', 'frankel-bullet-form'); ?></option>
                <option value="email" <?php selected($field_type, 'email'); ?>><?php esc_html_e('Email', 'frankel-bullet-form'); ?></option>
                <option value="textarea" <?php selected($field_type, 'textarea'); ?>><?php esc_html_e('Text Area', 'frankel-bullet-form'); ?></option>
                <option value="select" <?php selected($field_type, 'select'); ?>><?php esc_html_e('Select', 'frankel-bullet-form'); ?></option>
                <option value="radio" <?php selected($field_type, 'radio'); ?>><?php esc_html_e('Radio', 'frankel-bullet-form'); ?></option>
                <option value="checkbox" <?php selected($field_type, 'checkbox'); ?>><?php esc_html_e('Checkbox', 'frankel-bullet-form'); ?></option>
                <option value="tel" <?php selected($field_type, 'tel'); ?>><?php esc_html_e('Phone', 'frankel-bullet-form'); ?></option>
                <option value="number" <?php selected($field_type, 'number'); ?>><?php esc_html_e('Number', 'frankel-bullet-form'); ?></option>
                <option value="date" <?php selected($field_type, 'date'); ?>><?php esc_html_e('Date', 'frankel-bullet-form'); ?></option>
                <!-- New field types -->
                <option value="datetime" <?php selected($field_type, 'datetime'); ?>><?php esc_html_e('Date/Time', 'frankel-bullet-form'); ?></option>
                <option value="select_image" <?php selected($field_type, 'select_image'); ?>><?php esc_html_e('Select Image', 'frankel-bullet-form'); ?></option>
                <option value="multiselect" <?php selected($field_type, 'multiselect'); ?>><?php esc_html_e('Multi-Select', 'frankel-bullet-form'); ?></option>
                <option value="signature" <?php selected($field_type, 'signature'); ?>><?php esc_html_e('Signature', 'frankel-bullet-form'); ?></option>
                <option value="city" <?php selected($field_type, 'city'); ?>><?php esc_html_e('City', 'frankel-bullet-form'); ?></option>
                <option value="first_name" <?php selected($field_type, 'first_name'); ?>><?php esc_html_e('First Name', 'frankel-bullet-form'); ?></option>
                <option value="last_name" <?php selected($field_type, 'last_name'); ?>><?php esc_html_e('Last Name', 'frankel-bullet-form'); ?></option>
                <option value="country" <?php selected($field_type, 'country'); ?>><?php esc_html_e('Country', 'frankel-bullet-form'); ?></option>
                <option value="us_states" <?php selected($field_type, 'us_states'); ?>><?php esc_html_e('US States', 'frankel-bullet-form'); ?></option>
                <option value="zip" <?php selected($field_type, 'zip'); ?>><?php esc_html_e('Zip', 'frankel-bullet-form'); ?></option>
                <option value="html" <?php selected($field_type, 'html'); ?>><?php esc_html_e('HTML', 'frankel-bullet-form'); ?></option>
                <option value="repeatable" <?php selected($field_type, 'repeatable'); ?>><?php esc_html_e('Repeatable Fieldset', 'frankel-bullet-form'); ?></option>
                <option value="divider" <?php selected($field_type, 'divider'); ?>><?php esc_html_e('Divider', 'frankel-bullet-form'); ?></option>
                <option value="confirm" <?php selected($field_type, 'confirm'); ?>><?php esc_html_e('Confirm', 'frankel-bullet-form'); ?></option>
                <option value="hcaptcha" <?php selected($field_type, 'hcaptcha'); ?>><?php esc_html_e('hCaptcha', 'frankel-bullet-form'); ?></option>
                <option value="hidden" <?php selected($field_type, 'hidden'); ?>><?php esc_html_e('Hidden', 'frankel-bullet-form'); ?></option>
                <option value="recaptcha" <?php selected($field_type, 'recaptcha'); ?>><?php esc_html_e('Recaptcha v2', 'frankel-bullet-form'); ?></option>
                <option value="antispam" <?php selected($field_type, 'antispam'); ?>><?php esc_html_e('Anti-Spam', 'frankel-bullet-form'); ?></option>
                <option value="star_rating" <?php selected($field_type, 'star_rating'); ?>><?php esc_html_e('Star Rating', 'frankel-bullet-form'); ?></option>
                <option value="turnstile" <?php selected($field_type, 'turnstile'); ?>><?php esc_html_e('Cloudflare Turnstile', 'frankel-bullet-form'); ?></option>
            </select>
        </div>
        
        <div class="bfmsf-field-row bfmsf-options-row" style="<?php echo in_array($field_type, array('select', 'radio', 'checkbox', 'payment_method', 'multiselect', 'select_image', 'us_states')) ? '' : 'display:none;'; ?>">
            <label><?php esc_html_e('Options (one per line):', 'frankel-bullet-form'); ?></label>
            <textarea name="bfmsf_steps[<?php echo esc_attr( (string) $step ); ?>][<?php echo esc_attr( (string) $field_order ); ?>][options]"></textarea>
        </div>
        
        <div class="bfmsf-field-row">
            <label><?php esc_html_e('Placeholder:', 'frankel-bullet-form'); ?></label>
            <input type="text" name="bfmsf_steps[<?php echo esc_attr( (string) $step ); ?>][<?php echo esc_attr( (string) $field_order ); ?>][placeholder]" value="">
        </div>
        
        <div class="bfmsf-field-row">
            <label>
                <input type="checkbox" name="bfmsf_steps[<?php echo esc_attr( (string) $step ); ?>][<?php echo esc_attr( (string) $field_order ); ?>][required]">
                <?php esc_html_e('Required', 'frankel-bullet-form'); ?>
            </label>
        </div>
    </div>
</div>