<?php
if (!defined('ABSPATH')) {
    exit;
}

// Load the tasks list table
require_once WPDM_PLUGIN_DIR . 'includes/class-wpdm-tasks-list-table.php';
$list_table = new WPDM_Tasks_List_Table();
$list_table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">任务管理</h1>
    <a href="?page=dhs-daily-manager-add" class="page-title-action">添加新任务</a>
    
    <form method="post">
        <?php $list_table->display(); ?>
    </form>
</div>
