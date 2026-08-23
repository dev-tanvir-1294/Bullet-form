<?php
if (!defined('ABSPATH')) exit;

class BFMSF_Upload {
    private $upload_dir;
    private $allowed_types = array('jpg','jpeg','png','gif','pdf','doc','docx','txt','zip');
    private $max_size = 5242880; // 5MB

    public function __construct() {
        $upload = wp_upload_dir();
        $this->upload_dir = $upload['basedir'] . '/bullet-forms/';
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }
    }

    public function handle_upload($file, $field_name) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', $file['error']);
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_types)) {
            return new WP_Error('invalid_type', __('File type not allowed.', 'frankel-bullet-form'));
        }
        if ($file['size'] > $this->max_size) {
            return new WP_Error('too_large', __('File too large.', 'frankel-bullet-form'));
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        add_filter('upload_dir', array($this, 'custom_upload_dir'));

        $upload_overrides = array(
            'test_form' => false,
            'unique_filename_callback' => array($this, 'unique_filename_callback')
        );

        $movefile = wp_handle_upload($file, $upload_overrides);

        remove_filter('upload_dir', array($this, 'custom_upload_dir'));

        if ($movefile && !isset($movefile['error'])) {
            return $movefile['url'];
        }
        
        return new WP_Error('move_failed', isset($movefile['error']) ? $movefile['error'] : __('Could not save file.', 'frankel-bullet-form'));
    }

    public function custom_upload_dir($dirs) {
        $dirs['subdir'] = '/bullet-forms';
        $dirs['path'] = $dirs['basedir'] . '/bullet-forms';
        $dirs['url'] = $dirs['baseurl'] . '/bullet-forms';
        return $dirs;
    }

    public function unique_filename_callback($dir, $name, $ext) {
        return uniqid() . strtolower($ext);
    }
}