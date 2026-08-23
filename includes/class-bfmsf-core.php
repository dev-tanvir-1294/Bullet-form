<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core class for Bullet Form Builder
 */
class BFMSF_Core {

    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        // Redirect WP's default CPT edit screens to our custom builder
        add_action('load-post.php', array($this, 'redirect_to_builder'));
        add_action('load-post-new.php', array($this, 'redirect_to_new_builder'));
    }

    /**
     * Register the bfmsf_form custom post type
     * show_in_menu is false — we manage our own menu
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Forms', 'Post Type General Name', 'frankel-bullet-form'),
            'singular_name'      => _x('Form', 'Post Type Singular Name', 'frankel-bullet-form'),
            'menu_name'          => esc_html__('Bullet Forms', 'frankel-bullet-form'),
            'all_items'          => esc_html__('All Forms', 'frankel-bullet-form'),
            'add_new_item'       => esc_html__('Add New Form', 'frankel-bullet-form'),
            'edit_item'          => esc_html__('Edit Form', 'frankel-bullet-form'),
            'not_found'          => esc_html__('No forms found', 'frankel-bullet-form'),
            'not_found_in_trash' => esc_html__('No forms found in Trash', 'frankel-bullet-form'),
        );

        $args = array(
            'label'               => esc_html__('Forms', 'frankel-bullet-form'),
            'labels'              => $labels,
            'supports'            => array('title'),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // Hidden — we use our own menu
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
        );

        register_post_type('bfmsf_form', $args);
    }

    /**
     * Add custom admin menu pages
     */
    public function add_admin_menu() {
        // Top-level menu
        add_menu_page(
            esc_html__('Bullet Forms', 'frankel-bullet-form'),
            esc_html__('Bullet Forms', 'frankel-bullet-form'),
            'manage_options',
            'bfmsf-forms',
            array($this, 'forms_list_page'),
            'dashicons-feedback',
            58
        );

        // All Forms (same as top-level)
        add_submenu_page(
            'bfmsf-forms',
            esc_html__('All Forms', 'frankel-bullet-form'),
            esc_html__('All Forms', 'frankel-bullet-form'),
            'manage_options',
            'bfmsf-forms',
            array($this, 'forms_list_page')
        );

        // Form Builder (add/edit — hidden from nav, but accessible)
        add_submenu_page(
            'bfmsf-forms',
            esc_html__('Form Builder', 'frankel-bullet-form'),
            esc_html__('Add New', 'frankel-bullet-form'),
            'manage_options',
            'bfmsf-builder',
            array($this, 'builder_page')
        );

        // Submissions page
        add_submenu_page(
            'bfmsf-forms',
            esc_html__('Submissions', 'frankel-bullet-form'),
            esc_html__('Submissions', 'frankel-bullet-form'),
            'manage_options',
            'bfmsf-submissions',
            array($this, 'submissions_page')
        );
    }

    /**
     * Forms list page callback
     */
    public function forms_list_page() {
        include BFMSF_PLUGIN_DIR . 'templates/forms-list.php';
    }

    /**
     * Builder page callback
     */
    public function builder_page() {
        include BFMSF_PLUGIN_DIR . 'templates/form-builder.php';
    }

    /**
     * Submissions page callback
     */
    public function submissions_page() {
        include BFMSF_PLUGIN_DIR . 'templates/submissions-page.php';
    }

    /**
     * Redirect WP's edit post screen (post.php) to our builder for bfmsf_form
     */
    public function redirect_to_builder() {
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        if ($post_id && get_post_type($post_id) === 'bfmsf_form') {
            wp_safe_redirect(admin_url('admin.php?page=bfmsf-builder&form_id=' . $post_id));
            exit;
        }
    }

    /**
     * Redirect WP's new post screen (post-new.php) to our builder for bfmsf_form
     */
    public function redirect_to_new_builder() {
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'bfmsf_form') {
            wp_safe_redirect(admin_url('admin.php?page=bfmsf-builder'));
            exit;
        }
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'bfmsf-frontend-style',
            BFMSF_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            BFMSF_VERSION
        );
        wp_enqueue_script(
            'bfmsf-frontend-script',
            BFMSF_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            BFMSF_VERSION,
            true
        );
        wp_localize_script('bfmsf-frontend-script', 'BFMSF_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('BFMSF_nonce'),
        ));
    }

    /**
     * Enqueue admin assets — only on our plugin pages
     */
    public function enqueue_admin_assets($hook) {
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

        if ($page !== 'bfmsf-builder' && $page !== 'bfmsf-forms' && $page !== 'bfmsf-submissions') {
            return;
        }

        // Base admin CSS (used on both pages)
        wp_enqueue_style(
            'bfmsf-admin-style',
            BFMSF_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            BFMSF_VERSION
        );

        $needs_builder_script = in_array($page, array('bfmsf-builder', 'bfmsf-submissions'), true);

        if ($needs_builder_script) {
            if ($page === 'bfmsf-builder') {
                // Drag-and-drop and color picker dependencies
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('jquery-ui-droppable');
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
                wp_enqueue_media(); // For background image upload
            }

            wp_enqueue_script(
                'bfmsf-admin-script',
                BFMSF_PLUGIN_URL . 'assets/js/admin.js',
                $page === 'bfmsf-builder'
                    ? array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-color-picker')
                    : array('jquery'),
                BFMSF_VERSION,
                true
            );

            $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

            wp_localize_script('bfmsf-admin-script', 'BFMSF_admin', array(
                'ajax_url'   => admin_url('admin-ajax.php'),
                'nonce'      => wp_create_nonce('BFMSF_admin_nonce'),
                'form_id'    => $form_id,
                'plugin_url' => BFMSF_PLUGIN_URL,
                'admin_url'  => admin_url(),
                'forms_url'  => admin_url('admin.php?page=bfmsf-forms'),
            ));

            if ( $page === 'bfmsf-builder' ) {
                // Add body class without an inline <script> tag in the template
                wp_add_inline_script( 'bfmsf-admin-script', 'document.body.classList.add("bfmsf-builder-page");', 'before' );

                // Prepare builder data
                $form_id    = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
                $post_title = 'Untitled Form';
                $rows_data  = array();
                $field_defs = array();
                $canvas_meta= array('title' => '', 'subtitle' => '');
                $settings   = BFMSF_Settings::get_settings(0);
                $style      = BFMSF_Settings::get_style(0);

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
                $canvas_title    = $canvas_meta['title']    ?: $post_title;
                $canvas_subtitle = $canvas_meta['subtitle'] ?: '';

                $bfmsf_builder_inline_data = wp_json_encode(array(
                    'rows'           => $rows_data,
                    'fieldDefs'      => $field_defs,
                    'settings'       => $settings,
                    'style'          => $style,
                    'canvasTitle'    => $canvas_title,
                    'canvasSubtitle' => $canvas_subtitle,
                ));

                if ( ! empty( $bfmsf_builder_inline_data ) ) {
                    wp_add_inline_script( 'bfmsf-admin-script', 'window.BFMSF_builder_data = ' . $bfmsf_builder_inline_data . ';', 'before' );
                }
            }
        }
    }
}
