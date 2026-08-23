<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Full-Page Form Builder Template — Layout Row System
 */

$form_id    = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
$post_title = 'Untitled Form';
$rows_data  = array();
$field_defs = array();
$canvas_meta= array('title' => '', 'subtitle' => '');
$settings   = BFMSF_Settings::get_settings(0);
$style      = BFMSF_Settings::get_style(0);
$submissions= array();

if ($form_id) {
    $post = get_post($form_id);
    if ($post && $post->post_type === 'bfmsf_form') {
        $post_title   = $post->post_title ?: 'Untitled Form';
        
        $rows_raw = BFMSF_Settings::get_rows($form_id);
        $rows_data = json_decode($rows_raw, true);
        if (!is_array($rows_data)) {
            $rows_data = array();
        }
        if (isset($rows_data['rows']) && is_array($rows_data['rows'])) {
            $rows_data = $rows_data['rows'];
        }
        
        $defs_raw = BFMSF_Settings::get_field_defs($form_id);
        $field_defs = json_decode($defs_raw, true);
        if (!is_array($field_defs)) {
            $field_defs = array();
        }
        if (isset($field_defs['fieldDefs']) && is_array($field_defs['fieldDefs'])) {
            $field_defs = $field_defs['fieldDefs'];
        }
        
        $canvas_meta  = BFMSF_Settings::get_canvas_meta($form_id);
        $settings     = BFMSF_Settings::get_settings($form_id);
        $style        = BFMSF_Settings::get_style($form_id);
    }
}
if ($form_id) {
    global $wpdb;
    $subs_table  = $wpdb->prefix . 'bfmsf_submissions';
    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $subs_table WHERE form_id=%d ORDER BY submitted_date DESC LIMIT 200", $form_id
    ));
}

$canvas_title    = $canvas_meta['title']    ?: $post_title;
$canvas_subtitle = $canvas_meta['subtitle'] ?: '';

$google_fonts = array('Inter','Roboto','Open Sans','Lato','Poppins','Montserrat','Nunito',
                      'Source Sans Pro','Raleway','Playfair Display','Merriweather');
?>

<div id="bfmsf-builder-wrap">

    <!-- HEADER -->
    <div id="bfmsf-builder-header">
        <div class="bfmsf-brand">
            <a href="<?php echo esc_url(admin_url('admin.php?page=bfmsf-forms')); ?>" class="bfmsf-back-link" title="All Forms">
                <span class="dashicons dashicons-arrow-left-alt"></span>
            </a>
            <span class="dashicons dashicons-feedback" style="color:#4361ee;"></span>
            <span id="bfmsf-brand-name"><?php echo esc_html($post_title); ?></span>
        </div>

        <nav class="bfmsf-nav-tabs">
            <button class="bfmsf-main-tab active" data-tab="build">BUILD</button>
            <button class="bfmsf-main-tab" data-tab="settings">SETTINGS</button>
            <button class="bfmsf-main-tab" data-tab="style">STYLE</button>
            <button class="bfmsf-main-tab" data-tab="entries">ENTRIES</button>
        </nav>

        <div class="bfmsf-header-actions">
            <button id="bfmsf-update-btn" class="bfmsf-btn-update">
                <span class="dashicons dashicons-update" style="font-size:13px;line-height:1.5;"></span>
                UPDATE
            </button>
            <button id="bfmsf-embed-btn" class="bfmsf-btn-embed">
                <span class="dashicons dashicons-editor-code" style="font-size:13px;line-height:1.5;"></span>
                EMBED
            </button>
            <button id="bfmsf-share-btn" class="bfmsf-btn-share">
                <span class="dashicons dashicons-share" style="font-size:13px;line-height:1.5;"></span>
                SHARE
            </button>
        </div>
    </div>

    <!-- BODY -->
    <div id="bfmsf-builder-body">

        <!-- LEFT SIDEBAR -->
        <div id="bfmsf-sidebar">
            <div class="bfmsf-sidebar-nav">
                <button class="bfmsf-sidebar-tab active" data-panel="fields">FIELDS</button>
                <button class="bfmsf-sidebar-tab" data-panel="layout">LAYOUT</button>
                <button class="bfmsf-sidebar-tab" data-panel="options">OPTIONS</button>
                <button class="bfmsf-sidebar-tab" data-panel="conditions">CONDITIONS</button>
            </div>

            <div class="bfmsf-sidebar-content">

                <!-- ── FIELDS PANEL ── -->
                <div class="bfmsf-sidebar-panel active" id="panel-fields">
                    
                    <!-- Search Bar -->
                    <div class="bfmsf-search-wrap">
                        <span class="dashicons dashicons-search bfmsf-search-icon"></span>
                        <input type="text" id="bfmsf-field-search" placeholder="Search fields...">
                        <span class="bfmsf-search-clear" id="bfmsf-search-clear" style="display:none;">×</span>
                    </div>

                    <!-- ===== FAVORITE FIELDS ===== -->
                    <div class="bfmsf-field-category favorite-fields" data-category="favorites">
                        <div class="bfmsf-category-header">
                            <span class="bfmsf-category-label">
                                <span class="dashicons dashicons-star-filled" style="color:#f59e0b;"></span>
                                FAVORITE FIELDS
                            </span>
                            <span class="bfmsf-category-toggle" data-toggle="collapse">−</span>
                        </div>
                        <div class="bfmsf-category-fields">
                            <?php
                            $favorites = array(
                                array('text', 'editor-paragraph', 'Text'),
                                array('email', 'email', 'Email'),
                                array('phone', 'phone', 'Phone'),
                                array('dropdown', 'arrow-down-alt2', 'Dropdown'),
                                array('radio', 'marker', 'Radio Buttons'),
                                array('checkboxes', 'yes-alt', 'Checkboxes'),
                                array('number', 'calculator', 'Number'),
                                array('textarea', 'editor-paste-text', 'Paragraph Text'),
                            );
                            foreach ($favorites as $ft): ?>
                                <div class="bfmsf-field-type-card" data-type="<?php echo esc_attr($ft[0]); ?>">
                                    <div class="bfmsf-field-icon field-icon-<?php echo esc_attr($ft[0]); ?>">
                                        <span class="dashicons dashicons-<?php echo esc_attr($ft[1]); ?>"></span>
                                    </div>
                                    <span class="field-name"><?php echo esc_html($ft[2]); ?></span>
                                    <span class="field-badge">★</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ===== COMMON FIELDS ===== -->
                    <div class="bfmsf-field-category" data-category="common">
                        <div class="bfmsf-category-header">
                            <span class="bfmsf-category-label">
                                <span class="dashicons dashicons-admin-tools" style="color:#6366f1;"></span>
                                COMMON FIELDS
                            </span>
                            <span class="bfmsf-category-toggle" data-toggle="collapse">−</span>
                        </div>
                        <div class="bfmsf-category-fields">
                            <?php
                            $common = array(
                                array('text', 'editor-paragraph', 'Single Line Text'),
                                array('textarea', 'editor-paste-text', 'Paragraph Text'),
                                array('dropdown', 'arrow-down-alt2', 'Dropdown'),
                                array('radio', 'marker', 'Radio Buttons'),
                                array('checkboxes', 'yes-alt', 'Checkboxes'),
                                array('number', 'calculator', 'Number'),
                                array('email', 'email', 'Email'),
                                array('phone', 'phone', 'Phone'),
                                array('url', 'admin-site-alt3', 'Website/URL'),
                                array('date', 'calendar-alt', 'Date'),
                                array('datetime', 'clock', 'Date/Time'),
                                array('file', 'upload', 'File Upload'),
                                array('checkbox', 'yes', 'Single Checkbox'),
                                array('hidden', 'hidden', 'Hidden'),
                                array('confirm', 'yes', 'Confirm'),
                                array('signature', 'edit', 'Signature'),
                            );
                            foreach ($common as $ft): ?>
                                <div class="bfmsf-field-type-card" data-type="<?php echo esc_attr($ft[0]); ?>">
                                    <div class="bfmsf-field-icon field-icon-<?php echo esc_attr($ft[0]); ?>">
                                        <span class="dashicons dashicons-<?php echo esc_attr($ft[1]); ?>"></span>
                                    </div>
                                    <span class="field-name"><?php echo esc_html($ft[2]); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ===== USER INFORMATION FIELDS ===== -->
                    <div class="bfmsf-field-category" data-category="user">
                        <div class="bfmsf-category-header">
                            <span class="bfmsf-category-label">
                                <span class="dashicons dashicons-admin-users" style="color:#3b82f6;"></span>
                                USER INFORMATION FIELDS
                            </span>
                            <span class="bfmsf-category-toggle" data-toggle="collapse">−</span>
                        </div>
                        <div class="bfmsf-category-fields">
                            <?php
                            $user_fields = array(
                                array('first_name', 'admin-users', 'First Name'),
                                array('last_name', 'admin-users', 'Last Name'),
                                array('email', 'email', 'Email'),
                                array('phone', 'phone', 'Phone'),
                                array('address', 'location', 'Address'),
                                array('city', 'location-alt', 'City'),
                                array('country', 'admin-site', 'Country'),
                                array('us_states', 'flag', 'US States'),
                                array('zip', 'admin-post', 'Zip'),
                            );
                            foreach ($user_fields as $ft): ?>
                                <div class="bfmsf-field-type-card" data-type="<?php echo esc_attr($ft[0]); ?>">
                                    <div class="bfmsf-field-icon field-icon-<?php echo esc_attr($ft[0]); ?>">
                                        <span class="dashicons dashicons-<?php echo esc_attr($ft[1]); ?>"></span>
                                    </div>
                                    <span class="field-name"><?php echo esc_html($ft[2]); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ===== LAYOUT FIELDS ===== -->
                    <div class="bfmsf-field-category" data-category="layout">
                        <div class="bfmsf-category-header">
                            <span class="bfmsf-category-label">
                                <span class="dashicons dashicons-layout" style="color:#8b5cf6;"></span>
                                LAYOUT FIELDS
                            </span>
                            <span class="bfmsf-category-toggle" data-toggle="collapse">−</span>
                        </div>
                        <div class="bfmsf-category-fields">
                            <?php
                            $layout_fields = array(
                                array('html', 'editor-code', 'HTML'),
                                array('repeatable', 'forms', 'Repeatable Fieldset'),
                                array('divider', 'minus', 'Divider'),
                            );
                            foreach ($layout_fields as $ft): ?>
                                <div class="bfmsf-field-type-card" data-type="<?php echo esc_attr($ft[0]); ?>">
                                    <div class="bfmsf-field-icon field-icon-<?php echo esc_attr($ft[0]); ?>">
                                        <span class="dashicons dashicons-<?php echo esc_attr($ft[1]); ?>"></span>
                                    </div>
                                    <span class="field-name"><?php echo esc_html($ft[2]); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ===== MISCELLANEOUS FIELDS ===== -->
                    <div class="bfmsf-field-category" data-category="misc">
                        <div class="bfmsf-category-header">
                            <span class="bfmsf-category-label">
                                <span class="dashicons dashicons-admin-generic" style="color:#6b7280;"></span>
                                MISCELLANEOUS FIELDS
                            </span>
                            <span class="bfmsf-category-toggle" data-toggle="collapse">−</span>
                        </div>
                        <div class="bfmsf-category-fields">
                            <?php
                            $misc_fields = array(
                                array('star_rating', 'star-filled', 'Star Rating'),
                                array('hcaptcha', 'shield', 'hCaptcha'),
                                array('recaptcha', 'shield', 'Recaptcha v2'),
                                array('turnstile', 'cloud', 'Cloudflare Turnstile'),
                                array('antispam', 'admin-generic', 'Anti-Spam'),
                                array('product', 'cart', 'Product (WooCommerce)'),
                                array('price', 'money', 'Price'),
                                array('quantity', 'editor-ol', 'Quantity'),
                                array('coupon', 'tag', 'Coupon'),
                                array('payment_method', 'credit-card', 'Payment Method'),
                            );
                            foreach ($misc_fields as $ft): ?>
                                <div class="bfmsf-field-type-card" data-type="<?php echo esc_attr($ft[0]); ?>">
                                    <div class="bfmsf-field-icon field-icon-<?php echo esc_attr($ft[0]); ?>">
                                        <span class="dashicons dashicons-<?php echo esc_attr($ft[1]); ?>"></span>
                                    </div>
                                    <span class="field-name"><?php echo esc_html($ft[2]); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Target Hint -->
                    <div class="bfmsf-target-hint" id="bfmsf-target-hint" style="display:none;">
                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                        Click a field above to add it to the selected slot
                    </div>

                </div><!-- /panel-fields -->

                <!-- LAYOUT PANEL -->
                <div class="bfmsf-sidebar-panel" id="panel-layout">
                    <div class="bfmsf-layout-section-header">INSERT COLUMN LAYOUT</div>
                    <p class="bfmsf-layout-hint">Click a layout to insert a multi-column row at the bottom of your form. Then drag or click fields into any column.</p>
                    <div class="bfmsf-layout-grid">
                        <div class="bfmsf-layout-option" data-layout="2-equal"><div class="bfmsf-layout-thumb"><div class="bfmsf-lt-col"></div><div class="bfmsf-lt-col"></div></div><span>2 Columns</span></div>
                        <div class="bfmsf-layout-option" data-layout="3-equal"><div class="bfmsf-layout-thumb"><div class="bfmsf-lt-col"></div><div class="bfmsf-lt-col"></div><div class="bfmsf-lt-col"></div></div><span>3 Columns</span></div>
                        <div class="bfmsf-layout-option" data-layout="4-equal"><div class="bfmsf-layout-thumb"><div class="bfmsf-lt-col"></div><div class="bfmsf-lt-col"></div><div class="bfmsf-lt-col"></div><div class="bfmsf-lt-col"></div></div><span>4 Columns</span></div>
                        <div class="bfmsf-layout-option" data-layout="1-2wide"><div class="bfmsf-layout-thumb"><div class="bfmsf-lt-col" style="flex:1;"></div><div class="bfmsf-lt-col" style="flex:2;"></div></div><span>1 + 2 Wide</span></div>
                        <div class="bfmsf-layout-option" data-layout="2wide-1"><div class="bfmsf-layout-thumb"><div class="bfmsf-lt-col" style="flex:2;"></div><div class="bfmsf-lt-col" style="flex:1;"></div></div><span>2 Wide + 1</span></div>
                        <div class="bfmsf-layout-option" data-layout="sidebar-main"><div class="bfmsf-layout-thumb"><div class="bfmsf-lt-col" style="flex:1;"></div><div class="bfmsf-lt-col" style="flex:3;"></div></div><span>Sidebar + Main</span></div>
                    </div>
                    <div class="bfmsf-layout-section-header" style="margin-top:16px;">CURRENT LAYOUT ROWS</div>
                    <div id="bfmsf-layout-rows-list"><p class="bfmsf-layout-hint" id="bfmsf-no-rows-hint">No rows yet. Click a layout above to add one.</p></div>
                </div>

                <!-- OPTIONS PANEL -->
                <div class="bfmsf-sidebar-panel" id="panel-options">
                    <div class="bfmsf-options-empty">
                        <span class="dashicons dashicons-forms" style="font-size:32px;color:#d1d5db;display:block;margin-bottom:10px;"></span>
                        <p>Click a field in the canvas to configure its options.</p>
                    </div>
                </div>

                <!-- CONDITIONS PANEL -->
                <div class="bfmsf-sidebar-panel" id="panel-conditions">
                    <div class="bfmsf-options-empty">
                        <span class="dashicons dashicons-visibility" style="font-size:32px;color:#d1d5db;display:block;margin-bottom:10px;"></span>
                        <p>Select a field to set conditional logic rules.</p>
                    </div>
                </div>

            </div><!-- /bfmsf-sidebar-content -->
        </div><!-- /bfmsf-sidebar -->

        <!-- SIDEBAR RESIZER -->
        <div id="bfmsf-sidebar-resizer" title="Drag to resize panel"></div>

        <!-- MAIN CONTENT AREA -->
        <div id="bfmsf-content-area">

            <!-- ══ BUILD TAB ══ -->
            <div class="bfmsf-tab-content active" id="tab-build">
                <div class="bfmsf-build-wrap">
                    <div class="bfmsf-canvas-card" id="bfmsf-canvas-card">
                        <div class="bfmsf-canvas-form-header">
                            <h2 class="bfmsf-canvas-form-title" id="bfmsf-canvas-title" contenteditable="true" data-placeholder="Form Title" spellcheck="false"><?php echo esc_html($canvas_title ?: ''); ?></h2>
                            <p class="bfmsf-canvas-form-subtitle" id="bfmsf-canvas-subtitle" contenteditable="true" data-placeholder="Click to add a subtitle..." spellcheck="false"><?php echo esc_html($canvas_subtitle ?: ''); ?></p>
                        </div>
                        <div class="bfmsf-canvas-steps-bar">
                            <div class="bfmsf-canvas-steps" id="bfmsf-canvas-steps-list"></div>
                        </div>
                        <div id="bfmsf-form-rows"></div>
                        <div id="bfmsf-canvas-empty-state">
                            <span class="dashicons dashicons-plus-alt" style="font-size:36px;color:#d1d5db;display:block;margin-bottom:10px;"></span>
                            <p>Select a layout from the <strong>LAYOUT</strong> tab or<br>click a field in <strong>FIELDS</strong> to start building.</p>
                        </div>
                        <div class="bfmsf-canvas-footer">
                            <button id="bfmsf-preview-form-btn" class="bfmsf-preview-form-btn"><span class="dashicons dashicons-visibility" style="font-size:13px;line-height:1.6;"></span> Preview Form</button>
                            <button id="bfmsf-clear-all-btn" class="bfmsf-clear-all-btn"><span class="dashicons dashicons-trash" style="font-size:13px;line-height:1.6;"></span> Clear All</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ SETTINGS TAB ══ -->
            <div class="bfmsf-tab-content" id="tab-settings">
                <div class="bfmsf-tab-heading"><h2>GLOBAL FORM SETTINGS</h2></div>
                <div class="bfmsf-settings-grid">

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Form Name &amp; Description</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <div class="bfmsf-form-group"><label>Form Name</label><input type="text" id="bfmsf-setting-form-name" value="<?php echo esc_attr($settings['form_name']??''); ?>" placeholder="My Contact Form"></div>
                            <div class="bfmsf-form-group"><label>Description</label><textarea id="bfmsf-setting-description" placeholder="Form description..."><?php echo esc_textarea($settings['description']??''); ?></textarea></div>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Form Status</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <label class="bfmsf-radio-label"><input type="radio" name="bfmsf-form-status" value="active" <?php checked($settings['status']??'active','active'); ?>> Active</label>
                            <label class="bfmsf-radio-label"><input type="radio" name="bfmsf-form-status" value="draft" <?php checked($settings['status']??'','draft'); ?>> Draft</label>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Access Control</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <div class="bfmsf-form-group"><label class="bfmsf-checkbox-label"><input type="checkbox" id="bfmsf-setting-require-login" <?php checked(!empty($settings['require_login'])); ?>> Require Login</label></div>
                            <div class="bfmsf-form-group"><label>Submission Limit</label><input type="number" id="bfmsf-setting-submission-limit" value="<?php echo esc_attr($settings['submission_limit']??''); ?>" placeholder="Unlimited"></div>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Post-Submission Options</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <div class="bfmsf-form-group"><label>Confirmation Type</label>
                                <select id="bfmsf-setting-confirmation-type">
                                    <option value="message" <?php selected($settings['confirmation_type']??'message','message'); ?>>Show Message</option>
                                    <option value="redirect" <?php selected($settings['confirmation_type']??'','redirect'); ?>>Redirect to URL</option>
                                </select>
                            </div>
                            <div class="bfmsf-form-group"><label>Message</label><textarea id="bfmsf-setting-message"><?php echo esc_textarea($settings['confirmation_message']??'Thank you for your submission!'); ?></textarea></div>
                            <div class="bfmsf-form-group"><label>Redirect URL</label><input type="url" id="bfmsf-setting-redirect" value="<?php echo esc_attr($settings['redirect_url']??''); ?>" placeholder="https://..."></div>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Email Notifications</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <div class="bfmsf-form-group"><label>Recipient Email</label><input type="email" id="bfmsf-setting-email-recipient" value="<?php echo esc_attr($settings['email_recipient']??get_option('admin_email')); ?>"></div>
                            <div class="bfmsf-form-group"><label>Email Subject</label><input type="text" id="bfmsf-setting-email-subject" value="<?php echo esc_attr($settings['email_subject']??'New Form Submission'); ?>"></div>
                            <div class="bfmsf-form-group"><label>From Name</label><input type="text" id="bfmsf-setting-from-name" value="<?php echo esc_attr($settings['email_from_name']??get_bloginfo('name')); ?>"></div>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Integrations</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <div class="bfmsf-toggle-row">
                                <div class="bfmsf-toggle-label"><span class="integration-icon integration-google">G</span> Google Sheets</div>
                                <label class="bfmsf-toggle"><input type="checkbox" id="bfmsf-setting-google-sheets" <?php checked(!empty($settings['integration_google_sheets'])); ?>><span class="bfmsf-toggle-slider"></span></label>
                            </div>
                            <div class="bfmsf-form-group bfmsf-webhook-url-row" id="bfmsf-google-sheets-webhook-row" style="<?php echo empty($settings['integration_google_sheets']) ? 'display:none;' : ''; ?>">
                                <label>Apps Script Webhook URL</label>
                                <input type="url" id="bfmsf-setting-google-sheets-webhook" value="<?php echo esc_attr($settings['google_sheets_webhook_url']??''); ?>" placeholder="https://script.google.com/macros/s/.../exec">
                            </div>
                            <div class="bfmsf-toggle-row">
                                <div class="bfmsf-toggle-label"><span class="integration-icon integration-zapier">Z</span> Zapier</div>
                                <label class="bfmsf-toggle"><input type="checkbox" id="bfmsf-setting-zapier" <?php checked(!empty($settings['integration_zapier'])); ?>><span class="bfmsf-toggle-slider"></span></label>
                            </div>
                            <div class="bfmsf-form-group bfmsf-webhook-url-row" id="bfmsf-zapier-webhook-row" style="<?php echo empty($settings['integration_zapier']) ? 'display:none;' : ''; ?>">
                                <label>Zapier Webhook URL</label>
                                <input type="url" id="bfmsf-setting-zapier-webhook" value="<?php echo esc_attr($settings['zapier_webhook_url']??''); ?>" placeholder="https://hooks.zapier.com/hooks/catch/...">
                            </div>
                            <div class="bfmsf-toggle-row">
                                <div class="bfmsf-toggle-label"><span class="integration-icon integration-hubspot">H</span> HubSpot</div>
                                <label class="bfmsf-toggle"><input type="checkbox" id="bfmsf-setting-hubspot" <?php checked(!empty($settings['integration_hubspot'])); ?>><span class="bfmsf-toggle-slider"></span></label>
                            </div>
                            <div class="bfmsf-form-group bfmsf-webhook-url-row" id="bfmsf-hubspot-webhook-row" style="<?php echo empty($settings['integration_hubspot']) ? 'display:none;' : ''; ?>">
                                <label>HubSpot Forms Endpoint URL</label>
                                <input type="url" id="bfmsf-setting-hubspot-webhook" value="<?php echo esc_attr($settings['hubspot_webhook_url']??''); ?>" placeholder="https://api.hsforms.com/submissions/v3/integration/submit/...">
                            </div>
                            <p class="bfmsf-layout-hint" style="margin-top:8px;">When enabled, submission data is sent as a webhook POST to the URL above each time this form is submitted.</p>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Spam Protection Keys</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <h4 style="margin-top:0;margin-bottom:8px;font-size:12px;color:#1f2937;border-bottom:1px solid #f3f4f6;padding-bottom:4px;">hCaptcha</h4>
                            <div class="bfmsf-form-group"><label>hCaptcha Site Key</label><input type="text" id="bfmsf-setting-hcaptcha-site-key" value="<?php echo esc_attr($settings['hcaptcha_site_key']??''); ?>" placeholder="Enter site key"></div>
                            <div class="bfmsf-form-group"><label>hCaptcha Secret Key</label><input type="password" id="bfmsf-setting-hcaptcha-secret" value="<?php echo esc_attr($settings['hcaptcha_secret']??''); ?>" placeholder="Enter secret key"></div>

                            <h4 style="margin-top:12px;margin-bottom:8px;font-size:12px;color:#1f2937;border-bottom:1px solid #f3f4f6;padding-bottom:4px;">reCAPTCHA v2</h4>
                            <div class="bfmsf-form-group"><label>reCAPTCHA Site Key</label><input type="text" id="bfmsf-setting-recaptcha-site-key" value="<?php echo esc_attr($settings['recaptcha_site_key']??''); ?>" placeholder="Enter site key"></div>
                            <div class="bfmsf-form-group"><label>reCAPTCHA Secret Key</label><input type="password" id="bfmsf-setting-recaptcha-secret" value="<?php echo esc_attr($settings['recaptcha_secret']??''); ?>" placeholder="Enter secret key"></div>

                            <h4 style="margin-top:12px;margin-bottom:8px;font-size:12px;color:#1f2937;border-bottom:1px solid #f3f4f6;padding-bottom:4px;">Cloudflare Turnstile</h4>
                            <div class="bfmsf-form-group"><label>Turnstile Site Key</label><input type="text" id="bfmsf-setting-turnstile-site-key" value="<?php echo esc_attr($settings['turnstile_site_key']??''); ?>" placeholder="Enter site key"></div>
                            <div class="bfmsf-form-group"><label>Turnstile Secret Key</label><input type="password" id="bfmsf-setting-turnstile-secret" value="<?php echo esc_attr($settings['turnstile_secret']??''); ?>" placeholder="Enter secret key"></div>
                        </div>
                    </div>

                    <div class="bfmsf-settings-panel">
                        <div class="bfmsf-panel-header"><h3>Advanced API</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                        <div class="bfmsf-panel-body">
                            <div class="bfmsf-form-group"><label>Access Token</label><input type="text" id="bfmsf-setting-api-token" value="<?php echo esc_attr($settings['api_token']??''); ?>" placeholder="Access token"></div>
                            <div class="bfmsf-form-group"><label>Endpoint</label><input type="url" id="bfmsf-setting-api-endpoint" value="<?php echo esc_attr($settings['api_endpoint']??''); ?>" placeholder="https://..."></div>
                            <?php if ($form_id): ?>
                            <div class="bfmsf-form-group"><label>REST API URL</label><input type="text" readonly value="<?php echo esc_attr(rest_url('bfmsf/v1/forms/'.$form_id.'/submissions')); ?>" onclick="this.select();"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="bfmsf-save-btn-wrap"><button id="bfmsf-save-settings-btn" class="bfmsf-save-all-btn">Save All Settings</button></div>
            </div>

            <!-- ══ STYLE TAB ══ -->
            <div class="bfmsf-tab-content" id="tab-style">
                <div class="bfmsf-tab-heading"><h2>FORM STYLE &amp; THEME</h2></div>
                <div class="bfmsf-style-grid">
                    <div>
                        <div class="bfmsf-style-panel">
                            <div class="bfmsf-panel-header"><h3>Core Theme</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-theme-grid">
                                    <?php
                                    $themes = array(
                                        array('default','linear-gradient(135deg,#4361ee,#3a0ca3)'),
                                        array('ocean','linear-gradient(135deg,#0093E9,#80D0C7)'),
                                        array('rose','linear-gradient(135deg,#f093fb,#f5576c)'),
                                        array('sky','linear-gradient(135deg,#4facfe,#00f2fe)'),
                                        array('minimal','#f8fafc'),
                                        array('dark','linear-gradient(135deg,#232526,#414345)'),
                                    );
                                    foreach ($themes as $th): ?>
                                    <div class="bfmsf-theme-thumb <?php echo ($style['theme']??'default')===$th[0]?'active':''; ?>" data-theme="<?php echo esc_attr($th[0]); ?>" style="background:<?php echo esc_attr($th[1]); ?>;<?php echo $th[0]==='minimal'?'border:1px solid #e2e8f0;':''; ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="bfmsf-style-panel" style="margin-top:14px;">
                            <div class="bfmsf-panel-header"><h3>Color Palette</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-color-swatches">
                                    <?php $swatches = array('#4361ee','#10b981','#f59e0b','#ef4444','#1e293b','#8b5cf6','#06b6d4','#f97316');
                                    foreach ($swatches as $sw): ?>
                                    <div class="bfmsf-color-swatch <?php echo ($style['primary_color']??'')===$sw?'active':''; ?>" data-color="<?php echo esc_attr($sw); ?>" style="background:<?php echo esc_attr($sw); ?>;" title="<?php echo esc_attr($sw); ?>"></div>
                                    <?php endforeach; ?>
                                    <div class="bfmsf-color-swatch-clear" id="bfmsf-clear-swatch" title="Clear swatch selection">&#10005;</div>
                                </div>
                                <div class="bfmsf-color-input-row">
                                    <label>Primary</label>
                                    <input type="color" id="bfmsf-primary-color" value="<?php echo esc_attr($style['primary_color']??'#4361ee'); ?>">
                                    <input type="text" class="bfmsf-hex-input" id="bfmsf-hex-input" maxlength="7" value="<?php echo esc_attr(ltrim($style['primary_color']??'#4361ee','#')); ?>">
                                </div>
                                <div class="bfmsf-color-input-row" style="margin-top:10px;">
                                    <label>Text Color</label>
                                    <input type="color" id="bfmsf-text-color" value="<?php echo esc_attr($style['text_color']??'#1f2937'); ?>">
                                    <input type="text" class="bfmsf-hex-input" id="bfmsf-text-color-hex" maxlength="7" value="<?php echo esc_attr(ltrim($style['text_color']??'#1f2937','#')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="bfmsf-style-panel">
                            <div class="bfmsf-panel-header"><h3>Typography</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-form-group"><label>Primary Font</label>
                                    <select id="bfmsf-primary-font">
                                        <?php foreach ($google_fonts as $f): ?><option value="<?php echo esc_attr($f); ?>" <?php selected($style['primary_font']??'Inter',$f); ?>><?php echo esc_html($f); ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <?php
                                $ranges = array(
                                    array('bfmsf-heading-size','Heading Size',12,48,$style['heading_size']??24),
                                    array('bfmsf-body-size','Body Font Size',10,24,$style['body_font_size']??14),
                                    array('bfmsf-font-weight','Font Weight',1,9,intval(($style['font_weight']??400)/100)),
                                );
                                foreach ($ranges as $r): ?>
                                <div class="bfmsf-range-row">
                                    <span class="bfmsf-range-label"><?php echo esc_html($r[1]); ?></span>
                                    <input type="range" class="bfmsf-range-input" id="<?php echo esc_attr($r[0]); ?>" min="<?php echo $r[2]; ?>" max="<?php echo $r[3]; ?>" value="<?php echo esc_attr($r[4]); ?>">
                                    <span class="bfmsf-range-value"><?php echo esc_html($r[4]); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="bfmsf-style-panel" style="margin-top:14px;">
                            <div class="bfmsf-panel-header"><h3>Forms &amp; Inputs</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-form-group"><label>Input Style</label>
                                    <div class="bfmsf-style-selector">
                                        <?php foreach (array('filled','outlined','underlined') as $is): ?>
                                        <button class="bfmsf-style-option <?php echo ($style['input_style']??'outlined')===$is?'active':''; ?>" data-group="input-style" data-value="<?php echo esc_attr($is); ?>"><?php echo ucfirst($is); ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php
                                $iranges = array(
                                    array('bfmsf-border-radius','Border Radius',0,24,$style['border_radius']??6),
                                    array('bfmsf-padding','Padding',4,24,$style['padding']??10),
                                    array('bfmsf-field-spacing','Field Spacing',0,40,$style['field_spacing']??16),
                                );
                                foreach ($iranges as $r): ?>
                                <div class="bfmsf-range-row">
                                    <span class="bfmsf-range-label"><?php echo esc_html($r[1]); ?></span>
                                    <input type="range" class="bfmsf-range-input" id="<?php echo esc_attr($r[0]); ?>" min="<?php echo $r[2]; ?>" max="<?php echo $r[3]; ?>" value="<?php echo esc_attr($r[4]); ?>">
                                    <span class="bfmsf-range-value"><?php echo esc_html($r[4]); ?></span>
                                </div>
                                <?php endforeach; ?>
                                <!-- Input Border Color -->
                                <div class="bfmsf-color-input-row" style="margin-top:10px;">
                                    <label>Input Border Color</label>
                                    <input type="color" id="bfmsf-border-color" value="<?php echo esc_attr($style['border_color']??'#d1d5db'); ?>">
                                    <input type="text" class="bfmsf-hex-input" id="bfmsf-border-color-hex" maxlength="7" value="<?php echo esc_attr(ltrim($style['border_color']??'#d1d5db','#')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="bfmsf-style-panel">
                            <div class="bfmsf-panel-header"><h3>Buttons</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-form-group"><label>Text</label><input type="text" id="bfmsf-btn-text" value="<?php echo esc_attr($style['button_text']??'Submit'); ?>"></div>
                                <div class="bfmsf-form-group"><label>Background Color</label>
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <input type="color" id="bfmsf-btn-bg-color" value="<?php echo esc_attr($style['button_bg_color']??'#4361ee'); ?>">
                                        <div id="bfmsf-btn-preview" style="background:<?php echo esc_attr($style['button_bg_color']??'#4361ee'); ?>;flex:1;height:32px;border-radius:6px;"></div>
                                    </div>
                                </div>
                                <div class="bfmsf-form-group"><label>Text Color</label>
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <input type="color" id="bfmsf-btn-text-color" value="<?php echo esc_attr($style['button_text_color']??'#ffffff'); ?>">
                                        <div id="bfmsf-btn-text-preview" style="background:<?php echo esc_attr($style['button_text_color']??'#ffffff'); ?>;flex:1;height:32px;border-radius:6px;border:1px solid #e5e7eb;"></div>
                                    </div>
                                </div>
                                <div class="bfmsf-form-group"><label class="bfmsf-checkbox-label"><input type="checkbox" id="bfmsf-btn-hover" <?php checked(!empty($style['button_hover'])); ?>> Hover States</label></div>
                            </div>
                        </div>
                        <div class="bfmsf-style-panel" style="margin-top:14px;">
                            <div class="bfmsf-panel-header"><h3>Layout</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-form-group"><label>Label Alignment</label>
                                    <div class="bfmsf-alignment-selector">
                                        <?php foreach (array('left','right','top') as $al): ?>
                                        <button class="bfmsf-align-btn <?php echo ($style['label_alignment']??'top')===$al?'active':''; ?>" data-value="<?php echo esc_attr($al); ?>"><?php echo ucfirst($al); ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bfmsf-style-panel" style="margin-top:14px;">
                            <div class="bfmsf-panel-header"><h3>Background</h3><button class="bfmsf-panel-toggle" data-open="true">∧</button></div>
                            <div class="bfmsf-panel-body">
                                <div class="bfmsf-form-group"><label>Image URL</label>
                                    <div style="display:flex;gap:6px;">
                                        <input type="text" id="bfmsf-bg-image" value="<?php echo esc_attr($style['bg_image']??''); ?>" placeholder="Image URL" style="flex:1;">
                                        <button class="bfmsf-upload-btn" id="bfmsf-bg-upload-btn"><span class="dashicons dashicons-upload" style="font-size:14px;line-height:1.5;"></span></button>
                                    </div>
                                </div>
                                <div class="bfmsf-form-group"><label>Size</label>
                                    <select id="bfmsf-bg-size">
                                        <option value="cover" <?php selected($style['bg_size']??'cover','cover'); ?>>Cover</option>
                                        <option value="contain" <?php selected($style['bg_size']??'','contain'); ?>>Contain</option>
                                        <option value="auto" <?php selected($style['bg_size']??'','auto'); ?>>Auto</option>
                                    </select>
                                </div>
                                <!-- Container Border Color -->
                                <div class="bfmsf-color-input-row" style="margin-top:10px;">
                                    <label>Container Border Color</label>
                                    <input type="color" id="bfmsf-container-border-color" value="<?php echo esc_attr($style['container_border_color'] ?? '#e2e8f0'); ?>">
                                    <input type="text" class="bfmsf-hex-input" id="bfmsf-container-border-color-hex" maxlength="7" value="<?php echo esc_attr(ltrim($style['container_border_color'] ?? '#e2e8f0', '#')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bfmsf-save-btn-wrap"><button id="bfmsf-save-style-btn" class="bfmsf-save-all-btn">Save Style Settings</button></div>
            </div>

            <!-- ══ ENTRIES TAB ══ -->
            <div class="bfmsf-tab-content" id="tab-entries">
                <div class="bfmsf-tab-heading"><h2>FORM ENTRIES</h2></div>
                <?php if (!$form_id): ?>
                    <div class="bfmsf-entries-empty"><span class="dashicons dashicons-saved" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:12px;"></span><p>Save the form first to see entries.</p></div>
                <?php elseif (empty($submissions)): ?>
                    <div class="bfmsf-entries-empty">
                        <span class="dashicons dashicons-email-alt" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:12px;"></span>
                        <h3>No entries yet</h3>
                        <p>Share your form to start collecting responses.</p>
                        <code class="bfmsf-inline-shortcode" onclick="navigator.clipboard.writeText(this.textContent);" title="Click to copy">[bfmsf_form id="<?php echo esc_html($form_id); ?>"]</code>
                    </div>
                <?php else: ?>
                    <div class="bfmsf-entries-header-row">
                        <span class="bfmsf-entries-count"><?php echo count($submissions); ?> entr<?php echo count($submissions)===1?'y':'ies'; ?></span>
                        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=BFMSF_export_csv&form_id='.$form_id.'&nonce='.wp_create_nonce('BFMSF_admin_nonce'))); ?>" class="bfmsf-export-csv-btn">
                            <span class="dashicons dashicons-download" style="font-size:14px;line-height:1.5;"></span> Export CSV
                        </a>
                    </div>
                    <div class="bfmsf-entries-table-wrap">
                        <table class="bfmsf-entries-table">
                            <thead><tr><th>#</th><th>Date</th><th>IP</th><th>Preview</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($submissions as $sub):
                                    $sub_data = json_decode($sub->submission_data, true);
                                    $preview_parts = array_slice((array)$sub_data, 0, 2);
                                    $preview_text = implode(', ', array_map(function($v){ return is_array($v)?implode(',',$v):$v; }, $preview_parts));
                                ?>
                                <tr>
                                    <td><?php echo esc_html($sub->id); ?></td>
                                    <td><?php echo esc_html(date('M j, Y g:i A', strtotime($sub->submitted_date))); ?></td>
                                    <td><?php echo esc_html($sub->user_ip); ?></td>
                                    <td><?php echo esc_html(mb_substr($preview_text, 0, 50)); ?></td>
                                    <td><button class="bfmsf-view-entry-btn" data-entry='<?php echo esc_attr(json_encode($sub_data)); ?>' data-id="<?php echo esc_attr($sub->id); ?>">View</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /bfmsf-content-area -->
    </div><!-- /bfmsf-builder-body -->
</div>


<!-- /bfmsf-builder-wrap -->
