<?php
if (!defined('ABSPATH')) {
    exit;
}
$forms = BFMSF_Settings::get_all_forms();
?>

<div class="wrap bfmsf-forms-list-wrap">
    <?php if (isset($_GET['deleted'])): ?>
    <div class="notice notice-success is-dismissible"><p>Form deleted successfully.</p></div>
    <?php endif; ?>

    <div class="bfmsf-page-header">
        <h1 class="bfmsf-page-title">
            <span class="dashicons dashicons-feedback" style="font-size:24px; margin-right:8px; color:#6366f1; vertical-align:middle;"></span>
            Bullet Forms
        </h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=bfmsf-builder')); ?>" class="bfmsf-add-new-btn">
            <span class="dashicons dashicons-plus-alt2" style="font-size:14px;"></span>
            Add New Form
        </a>
    </div>

    <?php if (empty($forms)): ?>
    <div class="bfmsf-empty-state">
        <div class="bfmsf-empty-icon">📋</div>
        <h2>No forms yet</h2>
        <p>Create your first form to start collecting responses.</p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=bfmsf-builder')); ?>" class="bfmsf-add-new-btn">
            Create Your First Form
        </a>
    </div>
    <?php else: ?>
    <div class="bfmsf-forms-table-wrap">
        <table class="bfmsf-forms-table wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:35%">Form Name</th>
                    <th style="width:30%">Shortcode</th>
                    <th style="width:10%">Entries</th>
                    <th style="width:15%">Created</th>
                    <th style="width:10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $form):
                    $count          = BFMSF_Settings::get_submission_count($form->ID);
                    $shortcode      = '[bfmsf_form id="' . $form->ID . '"]';
                    $edit_url       = admin_url('admin.php?page=bfmsf-builder&form_id=' . $form->ID);
                    $submissions_url = admin_url('admin.php?page=bfmsf-submissions&form_id=' . $form->ID);
                    $del_url        = wp_nonce_url(
                        admin_url('admin.php?page=bfmsf-forms&action=delete&form_id=' . $form->ID),
                        'bfmsf_delete_form_' . $form->ID
                    );
                ?>
                <tr>
                    <td>
                        <strong>
                            <a href="<?php echo esc_url($edit_url); ?>" class="bfmsf-form-name-link">
                                <?php echo esc_html($form->post_title ?: 'Untitled Form'); ?>
                            </a>
                        </strong>
                        <div class="bfmsf-row-actions">
                            <a href="<?php echo esc_url($edit_url); ?>">Edit</a>
                            &nbsp;|&nbsp;
                            <a href="<?php echo esc_url($submissions_url); ?>">View details</a>
                            &nbsp;|&nbsp;
                            <a href="<?php echo esc_url($del_url); ?>" class="bfmsf-delete-link" onclick="return confirm('Delete this form and all its entries? This cannot be undone.');">Delete</a>
                        </div>
                    </td>
                    <td>
                        <code class="bfmsf-shortcode-cell" onclick="navigator.clipboard.writeText('<?php echo esc_attr($shortcode); ?>'); this.title='Copied!';" title="Click to copy">
                            <?php echo esc_html($shortcode); ?>
                        </code>
                    </td>
                    <td>
                        <a href="<?php echo esc_url($submissions_url); ?>" class="bfmsf-entry-count-link">
                            <span class="bfmsf-entry-count"><?php echo esc_html($count); ?></span>
                        </a>
                    </td>
                    <td>
                        <?php echo esc_html(date('M j, Y', strtotime($form->post_date))); ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url($edit_url); ?>" class="bfmsf-action-btn bfmsf-action-edit" title="Edit Form">✏️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<?php /* Styles for this page are in assets/css/admin.css */ ?>
