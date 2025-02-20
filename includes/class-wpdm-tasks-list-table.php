<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WPDM_Tasks_List_Table extends WP_List_Table {
    private $tasks;
    
    public function __construct() {
        parent::__construct(array(
            'singular' => '任务',
            'plural'   => '任务',
            'ajax'     => false
        ));
        
        $this->tasks = new WPDM_Tasks();
    }
    
    public function get_columns() {
        return array(
            'cb'          => '<input type="checkbox" />',
            'title'       => '标题',
            'category'    => '类别',
            'status'      => '状态',
            'start_time'  => '开始时间',
            'end_time'    => '结束时间'
        );
    }
    
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array(
            'title'      => array('title', true),
            'category'   => array('category', true),
            'status'     => array('status', true),
            'start_time' => array('start_time', true),
            'end_time'   => array('end_time', true)
        );
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        
        $args = array(
            'limit'  => $per_page,
            'offset' => $offset
        );
        
        $this->items = $this->tasks->get_all($args);
        $total_items = $this->tasks->count_tasks();
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
    
    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
                return esc_html($item->title);
            case 'category':
                global $wpdb;
                $category = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}wpdm_category WHERE id = %d",
                    $item->category_id
                ));
                return esc_html($category);
            case 'status':
                $status_map = array(
                    'pending' => '待处理',
                    'in_progress' => '进行中',
                    'completed' => '已完成',
                    'cancelled' => '已取消'
                );
                return isset($status_map[$item->status]) ? $status_map[$item->status] : $item->status;
            case 'start_time':
                return $item->start_time ? date_i18n('Y-m-d H:i', strtotime($item->start_time)) : '—';
            case 'end_time':
                return $item->end_time ? date_i18n('Y-m-d H:i', strtotime($item->end_time)) : '—';
            default:
                return print_r($item, true);
        }
    }
    
    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="task[]" value="%s" />',
            $item->id
        );
    }
    
    protected function column_title($item) {
        $actions = array(
            'edit'   => sprintf('<a href="?page=dhs-daily-manager-edit&id=%s">编辑</a>', $item->id),
            'delete' => sprintf(
                '<a href="?page=dhs-daily-manager&action=delete&id=%s&_wpnonce=%s" onclick="return confirm(\'确定要删除此任务吗？\');">删除</a>',
                $item->id,
                wp_create_nonce('delete_task_' . $item->id)
            )
        );
        
        return sprintf(
            '%1$s %2$s',
            '<strong>' . esc_html($item->title) . '</strong>',
            $this->row_actions($actions)
        );
    }
}
