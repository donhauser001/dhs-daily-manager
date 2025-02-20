<?php

/**
 * Plugin Name: DHS Daily Manager
 * Description: 个人日常管理插件，支持任务嵌套、类别自定义和多种视图
 * Version: 1.0.0
 * Author: 安宁
 * Text Domain: dhs-daily-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WPDM_VERSION', '1.0.0');
define('WPDM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPDM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Activation hook
register_activation_hook(__FILE__, 'wpdm_activate');

function wpdm_activate() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Set connection charset
    $wpdb->query("SET NAMES utf8mb4");
    $wpdb->query("SET CHARACTER SET utf8mb4");
    $wpdb->query("SET character_set_connection=utf8mb4");
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // First create tables directly
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdm_tasks");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdm_category");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdm_task_content");

    // Create tables directly first
    $wpdb->query("CREATE TABLE {$wpdb->prefix}wpdm_tasks (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        category_id bigint(20) NOT NULL,
        parent_id bigint(20) DEFAULT NULL,
        type varchar(50) NOT NULL,
        value longtext NOT NULL,
        start_time datetime DEFAULT NULL,
        end_time datetime DEFAULT NULL,
        repeat_type varchar(50) DEFAULT NULL,
        repeat_detail varchar(255) DEFAULT NULL,
        status varchar(50) NOT NULL,
        notes longtext,
        PRIMARY KEY (id)
    ) $charset_collate");

    $wpdb->query("CREATE TABLE {$wpdb->prefix}wpdm_category (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        parent_id bigint(20) DEFAULT NULL,
        level tinyint(4) NOT NULL,
        description longtext,
        PRIMARY KEY (id)
    ) $charset_collate");

    $wpdb->query("CREATE TABLE {$wpdb->prefix}wpdm_task_content (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        task_id bigint(20) NOT NULL,
        content_type varchar(50) NOT NULL,
        content longtext NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate");

    // Then use dbDelta to ensure proper structure
    // Tasks table
    $sql_tasks = "CREATE TABLE {$wpdb->prefix}wpdm_tasks (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        category_id bigint(20) NOT NULL,
        parent_id bigint(20),
        type varchar(50) NOT NULL,
        value longtext NOT NULL,
        start_time datetime,
        end_time datetime,
        repeat_type varchar(50),
        repeat_detail varchar(255),
        status varchar(50) NOT NULL,
        notes longtext,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB $charset_collate;";

    $result = dbDelta($sql_tasks);
    error_log('DHS Daily Manager - Tasks Table Creation Result: ' . print_r($result, true));
    if ($wpdb->last_error) {
        error_log('DHS Daily Manager - Tasks Table Error: ' . $wpdb->last_error);
    }

    // Category table
    $sql_category = "CREATE TABLE {$wpdb->prefix}wpdm_category (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        parent_id bigint(20),
        level tinyint(4) NOT NULL,
        description longtext,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB $charset_collate;";

    $result = dbDelta($sql_category);
    error_log('DHS Daily Manager - Category Table Creation Result: ' . print_r($result, true));
    if ($wpdb->last_error) {
        error_log('DHS Daily Manager - Category Table Error: ' . $wpdb->last_error);
    }

    // Content table
    $sql_content = "CREATE TABLE {$wpdb->prefix}wpdm_task_content (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        task_id bigint(20) NOT NULL,
        content_type varchar(50) NOT NULL,
        content longtext NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB $charset_collate;";

    $result = dbDelta($sql_content);
    error_log('DHS Daily Manager - Content Table Creation Result: ' . print_r($result, true));
    if ($wpdb->last_error) {
        error_log('DHS Daily Manager - Content Table Error: ' . $wpdb->last_error);
    }
    
    // Log any database errors
    if ($wpdb->last_error) {
        error_log('DHS Daily Manager - Database Error: ' . $wpdb->last_error);
        return;
    }

    // Insert default categories using direct SQL to ensure proper UTF-8 encoding
    $wpdb->query("INSERT INTO {$wpdb->prefix}wpdm_category (name, level, description) VALUES 
        ('生活', 1, '日常生活相关的任务'),
        ('工作', 1, '工作相关的任务和项目'),
        ('兴趣', 1, '兴趣爱好相关的活动')
    ");
    }
}

// Load required files
require_once WPDM_PLUGIN_DIR . 'includes/class-wpdm-tasks.php';
require_once WPDM_PLUGIN_DIR . 'includes/class-wpdm-category.php';
require_once WPDM_PLUGIN_DIR . 'includes/class-wpdm-content.php';
