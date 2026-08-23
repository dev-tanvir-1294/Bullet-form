<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Submissions Page Template
 */

global $wpdb;

$forms_table = $wpdb->prefix . 'bfmsf_forms';
$fields_table = $wpdb->prefix . 'bfmsf_form_fields';
$submissions_table = $wpdb->prefix . 'bfmsf_submissions';

$selected_form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
$paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$limit = 50;
$offset = ($paged - 1) * $limit;

// Get all forms
$forms = get_posts([
    'post_type'      => 'bfmsf_form',
    'posts_per_page' => -1,
    'post_status'    => 'any',
]);
// Convert to array with id and title
$forms = array_map(function($p) {
    return (object) ['id' => $p->ID, 'title' => $p->post_title];
}, $forms);

// Get submissions for selected form
$submissions = array();
$total = 0;

if ($selected_form_id > 0) {
    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $submissions_table WHERE form_id = %d",
        $selected_form_id
    ));
    
    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $submissions_table WHERE form_id = %d ORDER BY submitted_date DESC LIMIT %d OFFSET %d",
        $selected_form_id,
        $limit,
        $offset
    ));
    
    // Get form fields for CSV header
    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT field_name, field_label FROM $fields_table WHERE form_id = %d ORDER BY step_number ASC, field_order ASC",
        $selected_form_id
    ));
}

$total_pages = ceil($total / $limit);
?>

<div class="wrap">
    <h1><?php esc_html_e('Form Submissions', 'frankel-bullet-form'); ?></h1>
    
    <div class="bfmsf-submissions-container">
        <form method="get" class="bfmsf-form-filter">
            <input type="hidden" name="page" value="bfmsf-submissions">
            
            <label for="form-id"><?php esc_html_e('Select Form:', 'frankel-bullet-form'); ?></label>
            <select name="form_id" id="form-id">
                <option value=""><?php esc_html_e('-- Select a Form --', 'frankel-bullet-form'); ?></option>
                <?php foreach ($forms as $form): ?>
                    <option value="<?php echo esc_attr( $form->id ); ?>" <?php selected($selected_form_id, $form->id); ?>>
                        <?php echo esc_html($form->title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="button button-primary"><?php esc_html_e('Filter', 'frankel-bullet-form'); ?></button>
            
            <?php if ($selected_form_id > 0): ?>
                <button type="button" class="button bfmsf-export-csv" data-form-id="<?php echo esc_attr( $selected_form_id ); ?>">
                    <?php esc_html_e('Export to CSV', 'frankel-bullet-form'); ?>
                </button>
            <?php endif; ?>
        </form>
        
        <?php if ($selected_form_id > 0): ?>
            <div class="bfmsf-submissions-info">
                <p><?php printf(esc_html__('Total Submissions: %d', 'frankel-bullet-form'), $total); ?></p>
            </div>
            
            <?php if (count($submissions) > 0): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Submission ID', 'frankel-bullet-form'); ?></th>
                            <th><?php esc_html_e('Submitted Date', 'frankel-bullet-form'); ?></th>
                            <th><?php esc_html_e('IP Address', 'frankel-bullet-form'); ?></th>
                            <th><?php esc_html_e('Actions', 'frankel-bullet-form'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?php echo esc_html(absint($submission->id)); ?></td>
                                <td><?php echo esc_html($submission->submitted_date); ?></td>
                                <td><?php echo esc_html($submission->user_ip); ?></td>
                                <td>
                                    <button type="button" class="button button-small bfmsf-view-submission" data-submission-id="<?php echo esc_attr( $submission->id ); ?>">
                                        <?php esc_html_e('View details', 'frankel-bullet-form'); ?>
                                    </button>
                                    <button type="button" class="button button-small button-link-delete bfmsf-delete-submission" data-submission-id="<?php echo esc_attr( $submission->id ); ?>">
                                        <?php esc_html_e('Delete', 'frankel-bullet-form'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            $pagination_links = paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => esc_html__('&laquo; Previous', 'frankel-bullet-form'),
                                'next_text' => esc_html__('Next &raquo;', 'frankel-bullet-form'),
                                'total' => $total_pages,
                                'current' => $paged,
                                'echo' => false
                            ));
                            echo wp_kses_post( $pagination_links );
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p><?php esc_html_e('No submissions found for this form.', 'frankel-bullet-form'); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p><?php esc_html_e('Please select a form to view submissions.', 'frankel-bullet-form'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for viewing submission -->
<div id="bfmsf-view-modal" class="bfmsf-modal" style="display:none;">
    <div class="bfmsf-modal-content">
        <span class="bfmsf-close">&times;</span>
        <h2><?php esc_html_e('Submission Details', 'frankel-bullet-form'); ?></h2>
        <div id="bfmsf-modal-body"></div>
    </div>
</div>
