<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Form Display Template (Frontend)
 */
?>

<?php
$form_style = isset($form_style) && is_array($form_style) ? $form_style : array();
$primary_color = sanitize_hex_color($form_style['primary_color'] ?? '#4361ee');
$button_bg_color = sanitize_hex_color($form_style['button_bg_color'] ?? $primary_color);
$button_text_color = sanitize_hex_color($form_style['button_text_color'] ?? '#ffffff');
$text_color = sanitize_hex_color($form_style['text_color'] ?? '#1f2937');
$container_bg = sanitize_hex_color($form_style['container_bg'] ?? ($form_style['bg_color'] ?? '#ffffff'));
$border_color = sanitize_hex_color($form_style['border_color'] ?? '#d1d5db');
$container_border_color = sanitize_hex_color($form_style['container_border_color'] ?? '#e2e8f0');
$font_family = sanitize_text_field($form_style['primary_font'] ?? 'Inter');
$border_radius = absint($form_style['border_radius'] ?? 6);
$padding = absint($form_style['padding'] ?? 30);
$heading_size = absint($form_style['heading_size'] ?? 24);
$body_font_size = absint($form_style['body_font_size'] ?? 14);
$line_height = floatval($form_style['line_height'] ?? 1.6);
$label_alignment = sanitize_text_field($form_style['label_alignment'] ?? 'top');

// --- NEW: Variable to collect custom CSS from each field ---
$custom_css_output = '';

$style_attr = sprintf(
    'font-family:%s; --bfmsf-primary-color:%s; --bfmsf-text-color:%s; --bfmsf-button-bg-color:%s; --bfmsf-button-text-color:%s; --bfmsf-container-bg:%s; --bfmsf-border-radius:%dpx; --bfmsf-padding:%dpx; --bfmsf-heading-size:%dpx; --bfmsf-body-font-size:%dpx; --bfmsf-line-height:%.2f; --bfmsf-border-color:%s; --bfmsf-container-border-color:%s;',
    esc_attr($font_family),
    esc_attr($primary_color),
    esc_attr($text_color),
    esc_attr($button_bg_color),
    esc_attr($button_text_color),
    esc_attr($container_bg),
    $border_radius,
    $padding,
    $heading_size,
    $body_font_size,
    $line_height,
    esc_attr($border_color),
    esc_attr($container_border_color)
);
?>



<div class="bfmsf-form-wrapper" data-form-id="<?php echo esc_attr($form_id); ?>" style="<?php echo esc_attr($style_attr); ?>">
    <div class="bfmsf-form-container bfmsf-label-alignment-<?php echo esc_attr($label_alignment); ?> " style="border: 2px solid var(--bfmsf-container-border-color,#e2e8f0);">
        <?php if (!empty($form->title)): ?>
                <h2 class="bfmsf-form-title"><?php echo esc_html($form->title); ?></h2>
        <?php endif; ?>
        
        <?php if (!empty($form->description)): ?>
                <div class="bfmsf-form-description"><?php echo wp_kses_post($form->description); ?></div>
        <?php endif; ?>
        
        <div class="bfmsf-progress-bar">
            <div class="bfmsf-progress-fill" style="width: 0%;"></div>
        </div>
        
        <form class="bfmsf-form" method="POST" enctype="multipart/form-data">
            <div class="bfmsf-form-steps">
                <?php foreach ($steps as $step_num => $step_rows): ?>
                        <div class="bfmsf-form-step" data-step="<?php echo esc_attr($step_num); ?>" <?php echo $step_num !== 1 ? 'style="display:none;"' : ''; ?>>
                            <div class="bfmsf-step-title">
                                <?php printf(esc_html__('Step %d of %d', 'frankel-bullet-form'), (int) $step_num, (int) $max_step); ?>
                            </div>
                        
                            <div class="bfmsf-form-fields">
                                <?php foreach ($step_rows as $row_fields): ?>
                                        <div class="bfmsf-form-row">
                                            <?php foreach ($row_fields as $field): ?>
                                                    <?php
                                                    $flex = isset($field->slot_flex) ? floatval($field->slot_flex) : 1;
                                                    $style = 'flex:' . $flex . ';';

                                                    $data_cond_attr = '';
                                                    if (!empty($field->field_conditions)) {
                                                        $data_cond_attr = ' data-bfmsf-conditions="' . esc_attr($field->field_conditions) . '"';
                                                        $style .= ' display:none;';
                                                    }
                                                    ?>
                                                    <div class="bfmsf-form-field-group <?php echo esc_attr($field->cssClass ?? ''); ?>" data-field-id="<?php echo esc_attr($field->id); ?>" style="<?php echo esc_attr($style); ?>"<?php echo $data_cond_attr; ?>>
                                                        <?php if ($field->field_type === 'textarea'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <textarea 
                                                                    id="BFMSF_field_<?php echo esc_attr($field->id); ?>" 
                                                                    name="form_data[<?php echo esc_attr($field->field_name); ?>]"
                                                                    placeholder="<?php echo esc_attr($field->field_placeholder); ?>"
                                                                    <?php echo $field->field_required ? 'required' : ''; ?>
                                                                ></textarea>



                                                        <?php elseif ($field->field_type === 'phone'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?><?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <div class="bfmsf-phone-wrapper">
                                                                    <select name="form_data[<?php echo esc_attr($field->field_name); ?>][country]" class="bfmsf-phone-country" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                        <?php 
                                                                        $countries = $this->get_country_codes();
                                                                        // You can set a default country from field settings if you like, or fallback to 'US'
                                                                        $default = 'US';
                                                                        foreach ($countries as $code => $label): ?>
                                                                            <option value="<?php echo esc_attr($code); ?>" <?php selected($code, $default); ?>><?php echo esc_html($label); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <input type="tel" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>][number]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                </div>





                                            
                                                        <?php elseif ($field->field_type === 'select'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <select 
                                                                    id="BFMSF_field_<?php echo esc_attr($field->id); ?>" 
                                                                    name="form_data[<?php echo esc_attr($field->field_name); ?>]"
                                                                    <?php echo $field->field_required ? 'required' : ''; ?>
                                                                >
                                                                    <option value=""><?php esc_html_e('Select an option', 'frankel-bullet-form'); ?></option>
                                                                    <?php
                                                                    $options = json_decode($field->field_options, true);
                                                                    if (is_array($options)):
                                                                        foreach ($options as $option):
                                                                            ?>
                                                                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                                                                        <?php
                                                                        endforeach;
                                                                    endif;
                                                                    ?>
                                                                </select>
                                            
                                                        <?php elseif ($field->field_type === 'radio'): ?>
                                                                <label><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <div class="bfmsf-radio-group">
                                                                    <?php
                                                                    $options = json_decode($field->field_options, true);
                                                                    if (is_array($options)):
                                                                        foreach ($options as $idx => $option):
                                                                            ?>
                                                                                <label class="bfmsf-radio-label">
                                                                                    <input type="radio" name="form_data[<?php echo esc_attr($field->field_name); ?>]" value="<?php echo esc_attr($option); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                                    <?php echo esc_html($option); ?>
                                                                                </label>
                                                                        <?php
                                                                        endforeach;
                                                                    endif;
                                                                    ?>
                                                                </div>
                                            
                                                        <?php elseif ($field->field_type === 'checkbox'): 
                                                                $options = json_decode($field->field_options, true);
                                                                if (is_array($options) && count($options) > 1):
                                                                    // Multiple checkboxes (group)
                                                                    ?>
                                                                    <label><?php echo esc_html($field->field_label); ?> <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                    <div class="bfmsf-checkbox-group">
                                                                        <?php foreach ($options as $option): ?>
                                                                            <label class="bfmsf-checkbox-label">
                                                                                <input type="checkbox" name="form_data[<?php echo esc_attr($field->field_name); ?>][]" value="<?php echo esc_attr($option); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                                <span class="bfmsf-custom-checkbox"></span>
                                                                                <?php echo esc_html($option); ?>
                                                                            </label>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php else: 
                                                                    // Single checkbox (custom styled)
                                                                    $checked = !empty($field->defaultChecked) ? 'checked' : '';
                                                                    ?>
                                                                    <label class="bfmsf-checkbox-label confirm-field">
                                                                        <input type="checkbox" name="form_data[<?php echo esc_attr($field->field_name); ?>]" value="1" <?php echo $checked; ?> <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                        <span class="bfmsf-custom-checkbox"></span>
                                                                        <?php echo esc_html($field->field_label); ?>
                                                                        <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?>
                                                                    </label>
                                                                <?php endif; ?>


                                            
                                                     
                                                                <?php elseif ($field->field_type === 'product'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <?php
                                                                $products = array();
                                                                if (class_exists('WooCommerce')) {
                                                                    $args = array(
                                                                        'post_type' => 'product',
                                                                        'posts_per_page' => -1,
                                                                        'orderby' => 'title',
                                                                        'order' => 'ASC',
                                                                    );
                                                                    $product_posts = get_posts($args);
                                                                    foreach ($product_posts as $p) {
                                                                        $_product = wc_get_product($p->ID);
                                                                        $label = $p->post_title . ' (' . wc_price($_product->get_price()) . ')';
                                                                        $products[$p->ID] = $label;
                                                                    }
                                                                }
                                                                ?>
                                                                <select id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                    <option value=""><?php esc_html_e('Choose a product', 'frankel-bullet-form'); ?></option>
                                                                    <?php foreach ($products as $id => $label): ?>
                                                                            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>

                                                        <?php elseif ($field->field_type === 'price'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="number" step="0.01" min="0" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'quantity'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="number" step="1" min="1" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'coupon'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="text" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'payment_method'): ?>
                                                                <label><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <div class="bfmsf-radio-group">
                                                                    <?php
                                                                    $options = json_decode($field->field_options, true);
                                                                    if (!is_array($options)) {
                                                                        $options = array('Credit Card', 'PayPal', 'Bank Transfer');
                                                                    }
                                                                    foreach ($options as $option): ?>
                                                                            <label class="bfmsf-radio-label">
                                                                                <input type="radio" name="form_data[<?php echo esc_attr($field->field_name); ?>]" value="<?php echo esc_attr($option); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                                <?php echo esc_html($option); ?>
                                                                            </label>
                                                                    <?php endforeach; ?>
                                                                </div>

                                                        <?php elseif ($field->field_type === 'file'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="file" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'datetime'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="datetime-local" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'select_image'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <select id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                    <option value=""><?php esc_html_e('Select an image', 'frankel-bullet-form'); ?></option>
                                                                    <?php
                                                                    $options = json_decode($field->field_options, true);
                                                                    if (is_array($options)) {
                                                                        foreach ($options as $option) {
                                                                            if (is_array($option) && isset($option['label']) && isset($option['image_url'])) {
                                                                                echo '<option value="' . esc_attr($option['label']) . '" data-image="' . esc_url($option['image_url']) . '">' . esc_html($option['label']) . '</option>';
                                                                            } else {
                                                                                echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>

                                                        <?php elseif ($field->field_type === 'multiselect'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <select id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>][]" multiple <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                    <?php
                                                                    $options = json_decode($field->field_options, true);
                                                                    if (is_array($options)) {
                                                                        foreach ($options as $option) {
                                                                            echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>

                                                        <?php elseif ($field->field_type === 'signature'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <canvas id="BFMSF_signature_<?php echo esc_attr($field->id); ?>" style="border:1px solid #ddd;width:100%;height:150px;background:#f9f9f9;"></canvas>
                                                                <input type="hidden" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                <p class="bfmsf-hint"><?php esc_html_e('Sign above using your mouse or touch', 'frankel-bullet-form'); ?></p>

                                                        <?php elseif ($field->field_type === 'city'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="text" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'first_name'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="text" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'last_name'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="text" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'country'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <select id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                    <option value=""><?php esc_html_e('Select a country', 'frankel-bullet-form'); ?></option>
                                                                    <?php
                                                                    $countries = array('Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cabo Verde', 'Cambodia', 'Cameroon', 'Canada', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Macedonia', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Korea', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe');
                                                                    foreach ($countries as $country) {
                                                                        echo '<option value="' . esc_attr($country) . '">' . esc_html($country) . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>

                                                        <?php elseif ($field->field_type === 'us_states'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <select id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                    <option value=""><?php esc_html_e('Select a state', 'frankel-bullet-form'); ?></option>
                                                                    <?php
                                                                    $states = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming');
                                                                    foreach ($states as $abbr => $name) {
                                                                        echo '<option value="' . esc_attr($abbr) . '">' . esc_html($name) . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>

                                                        <?php elseif ($field->field_type === 'zip'): ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input type="text" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>

                                                        <?php elseif ($field->field_type === 'html'): ?>
                                                                <div class="bfmsf-html-field">
                                                                    <?php echo wp_kses_post($field->field_label); ?>
                                                                </div>

                                                        <?php elseif ($field->field_type === 'repeatable'): ?>
                                                                <div class="bfmsf-repeatable-field" data-field-id="<?php echo esc_attr($field->id); ?>">
                                                                    <label><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                    <div class="bfmsf-repeatable-items">
                                                                        <div class="bfmsf-repeatable-item">
                                                                            <input type="text" name="form_data[<?php echo esc_attr($field->field_name); ?>][]" placeholder="<?php echo esc_attr($field->field_placeholder); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                            <button type="button" class="bfmsf-repeatable-remove">×</button>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" class="bfmsf-repeatable-add">+ Add</button>
                                                                </div>

                                                        <?php elseif ($field->field_type === 'divider'): ?>
                                                                <hr class="bfmsf-divider">

                                                        <?php elseif ($field->field_type === 'confirm'): ?>
                                                            <?php if (!empty($field->confirmationText)): ?>
                                                                <div class="bfmsf-confirmation-text">
                                                                    <?php echo wp_kses_post($field->confirmationText); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <label class="bfmsf-checkbox-label confirm-field">
                                                                <input type="checkbox" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" value="1" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                <span class="bfmsf-custom-checkbox"></span>
                                                                <?php echo esc_html($field->field_label); ?>
                                                                <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?>
                                                            </label>

                                                        <?php elseif ($field->field_type === 'hcaptcha'): ?>
                                                                <div class="bfmsf-hcaptcha">
                                                                    <div class="h-captcha" data-sitekey="<?php echo esc_attr($form_settings['hcaptcha_site_key'] ?? ''); ?>"></div>
                                                                </div>

                                                        <?php elseif ($field->field_type === 'hidden'): ?>
                                                                <input type="hidden" id="BFMSF_field_<?php echo esc_attr($field->id); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" value="<?php echo esc_attr($field->field_placeholder); ?>">

                                                        <?php elseif ($field->field_type === 'recaptcha'): ?>
                                                                <div class="bfmsf-recaptcha">
                                                                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($form_settings['recaptcha_site_key'] ?? ''); ?>"></div>
                                                                </div>

                                                        <?php elseif ($field->field_type === 'antispam'): ?>
                                                                <div style="display:none;">
                                                                    <label for="bfmsf_hp_<?php echo esc_attr($field->id); ?>"><?php esc_html_e('Leave this field empty', 'frankel-bullet-form'); ?></label>
                                                                    <input type="text" id="bfmsf_hp_<?php echo esc_attr($field->id); ?>" name="bfmsf_hp_<?php echo esc_attr($field->field_name); ?>" value="">
                                                                </div>

                                                        <?php elseif ($field->field_type === 'star_rating'): ?>
                                                                <label><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <div class="bfmsf-star-rating" data-field-name="form_data[<?php echo esc_attr($field->field_name); ?>]">
                                                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                                                            <input type="radio" id="bfmsf_star_<?php echo esc_attr($field->id . '_' . $i); ?>" name="form_data[<?php echo esc_attr($field->field_name); ?>]" value="<?php echo esc_attr($i); ?>" <?php echo $field->field_required ? 'required' : ''; ?>>
                                                                            <label for="bfmsf_star_<?php echo esc_attr($field->id . '_' . $i); ?>" title="<?php echo esc_attr($i); ?> stars">★</label>
                                                                    <?php endfor; ?>
                                                                </div>

                                                        <?php elseif ($field->field_type === 'turnstile'): ?>
                                                                <div class="bfmsf-turnstile">
                                                                    <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($form_settings['turnstile_site_key'] ?? ''); ?>"></div>
                                                                </div>

                                                        <?php else: ?>
                                                                <label for="BFMSF_field_<?php echo esc_attr($field->id); ?>"><?php echo esc_html($field->field_label); ?>                 <?php echo $field->field_required ? '<span class="required">*</span>' : ''; ?></label>
                                                                <input 
                                                                    type="<?php echo esc_attr($field->field_type); ?>" 
                                                                    id="BFMSF_field_<?php echo esc_attr($field->id); ?>" 
                                                                    name="form_data[<?php echo esc_attr($field->field_name); ?>]"
                                                                    placeholder="<?php echo esc_attr($field->field_placeholder); ?>"
                                                                    <?php echo $field->field_required ? 'required' : ''; ?>
                                                                >
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php
                                                    // --- NEW: Collect custom CSS for this field if any ---
                                                    if (!empty($field->customCss)) {
                                                        $custom_css_output .= $field->customCss . "\n";
                                                    }
                                                    ?>

                                            <?php endforeach; ?>
                                        </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
            
            <div class="bfmsf-form-navigation">
                <button type="button" class="bfmsf-btn bfmsf-prev-btn" style="display:none; background-color: <?php echo esc_attr($primary_color); ?>; color: #ffffff; border-color: <?php echo esc_attr($primary_color); ?>;"><?php esc_html_e('← Previous', 'frankel-bullet-form'); ?></button>
                <button type="button" class="bfmsf-btn bfmsf-next-btn" style="background-color: <?php echo esc_attr($primary_color); ?>; color: #ffffff; border-color: <?php echo esc_attr($primary_color); ?>;"><?php esc_html_e('Next →', 'frankel-bullet-form'); ?></button>
                <button type="submit" class="bfmsf-btn bfmsf-submit-btn" style="display:none; background-color: <?php echo esc_attr($button_bg_color); ?>; color: <?php echo esc_attr($button_text_color); ?>; border-color: <?php echo esc_attr($button_bg_color); ?>;"><?php esc_html_e('Submit', 'frankel-bullet-form'); ?></button>
            </div>
            
            <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
            <?php wp_nonce_field('BFMSF_nonce', 'nonce'); ?>
        </form>
        
        <div class="bfmsf-success-message" style="display:none;">
            <p><?php esc_html_e('Thank you! Your form has been submitted successfully.', 'frankel-bullet-form'); ?></p>
        </div>
    </div>

  
</div>