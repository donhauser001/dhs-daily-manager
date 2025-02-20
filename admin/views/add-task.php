<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpdm_category ORDER BY name ASC");
?>

<div class="wrap">
    <h1>添加新任务</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('add_task', 'wpdm_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="title">标题</label></th>
                <td><input name="title" type="text" id="title" class="regular-text" required></td>
            </tr>
            
            <tr>
                <th scope="row"><label for="category">类别</label></th>
                <td>
                    <select name="category_id" id="category" required>
                        <option value="">选择类别</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->id); ?>">
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="parent_id">父任务</label></th>
                <td>
                    <select name="parent_id" id="parent_id">
                        <option value="">无父任务</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="start_time">开始时间</label></th>
                <td><input name="start_time" type="datetime-local" id="start_time"></td>
            </tr>
            
            <tr>
                <th scope="row"><label for="end_time">结束时间</label></th>
                <td><input name="end_time" type="datetime-local" id="end_time"></td>
            </tr>
            
            <tr>
                <th scope="row"><label for="status">状态</label></th>
                <td>
                    <select name="status" id="status" required>
                        <option value="pending">待处理</option>
                        <option value="in_progress">进行中</option>
                        <option value="completed">已完成</option>
                        <option value="cancelled">已取消</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="notes">备注</label></th>
                <td><textarea name="notes" id="notes" class="large-text" rows="5"></textarea></td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="添加任务">
        </p>
    </form>
</div>
