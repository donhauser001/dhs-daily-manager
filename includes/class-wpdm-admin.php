<?php
class WPDM_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
}
