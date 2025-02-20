<?php
class WPDM_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_wpdm_add_task', array($this, 'handle_add_task'));
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            '任务管理',
            '任务管理',
            'manage_options',
            'dhs-daily-manager',
            array($this, 'render_tasks_page'),
            'dashicons-calendar-alt'
        );
        
        add_submenu_page(
            'dhs-daily-manager',
            '所有任务',
            '所有任务',
            'manage_options',
            'dhs-daily-manager',
            array($this, 'render_tasks_page')
        );
        
        add_submenu_page(
            'dhs-daily-manager',
            '添加任务',
            '添加任务',
            'manage_options',
            'dhs-daily-manager-add',
            array($this, 'render_add_task_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (!in_array($hook, array('toplevel_page_dhs-daily-manager', 'daily-manager_page_dhs-daily-manager-add'))) {
            return;
        }
        
        wp_enqueue_style(
            'dhs-daily-manager-admin',
            WPDM_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            WPDM_VERSION
        );
        
        wp_enqueue_script(
            'dhs-daily-manager-admin',
            WPDM_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            WPDM_VERSION,
            true
        );
        
        wp_localize_script('dhs-daily-manager-admin', 'wpdm_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpdm_admin_nonce')
        ));
    }
    
    public function render_tasks_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有足够的权限访问此页面。'));
        }
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/tasks.php';
    }
    
    public function render_add_task_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有足够的权限访问此页面。'));
        }
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/add-task.php';
    }

    public function display_admin_notices() {
        if (isset($_GET['message']) && $_GET['message'] === 'task_added') {
            echo '<div class="notice notice-success is-dismissible"><p>任务已成功添加。</p></div>';
        }
        if (isset($_GET['error']) && $_GET['error'] === 'task_add_failed') {
            echo '<div class="notice notice-error is-dismissible"><p>添加任务时发生错误。</p></div>';
        }
    }

    public function handle_add_task() {
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有足够的权限执行此操作。'));
        }

        check_admin_referer('add_task', 'wpdm_nonce');

        $task_data = array(
            'title' => sanitize_text_field($_POST['title']),
            'category_id' => intval($_POST['category_id']),
            'parent_id' => !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null,
            'start_time' => sanitize_text_field($_POST['start_time']),
            'end_time' => sanitize_text_field($_POST['end_time']),
            'status' => sanitize_text_field($_POST['status']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'type' => 'task',
            'value' => ''
        );

        $tasks = new WPDM_Tasks();
        $result = $tasks->create($task_data);

        if ($result) {
            wp_redirect(add_query_arg('message', 'task_added', admin_url('admin.php?page=dhs-daily-manager')));
        } else {
            wp_redirect(add_query_arg('error', 'task_add_failed', wp_get_referer()));
        }
        exit;
    }
}
