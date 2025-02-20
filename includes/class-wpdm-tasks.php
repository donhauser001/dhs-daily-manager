<?php
class WPDM_Tasks {
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'wpdm_tasks';
    }
    
    public function create($data) {
        $defaults = array(
            'title' => '',
            'category_id' => 0,
            'parent_id' => null,
            'type' => 'task',
            'value' => '',
            'start_time' => null,
            'end_time' => null,
            'repeat_type' => null,
            'repeat_detail' => null,
            'status' => 'pending',
            'notes' => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        return $this->wpdb->insert($this->table_name, $data);
    }
    
    public function get($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
    }
    
    public function get_children($parent_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE parent_id = %d ORDER BY start_time ASC", $parent_id)
        );
    }
    
    public function get_all($args = array()) {
        $defaults = array(
            'orderby' => 'start_time',
            'order' => 'ASC',
            'category_id' => null,
            'status' => null,
            'parent_id' => null,
            'limit' => null,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $values = array();
        
        if (!is_null($args['category_id'])) {
            $where[] = 'category_id = %d';
            $values[] = $args['category_id'];
        }
        
        if (!is_null($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }
        
        if (!is_null($args['parent_id'])) {
            $where[] = 'parent_id = %d';
            $values[] = $args['parent_id'];
        }
        
        $sql = "SELECT * FROM {$this->table_name} WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if (!is_null($args['limit'])) {
            $sql .= " LIMIT %d OFFSET %d";
            $values[] = $args['limit'];
            $values[] = $args['offset'];
        }
        
        if (!empty($values)) {
            $sql = $this->wpdb->prepare($sql, $values);
        }
        
        return $this->wpdb->get_results($sql);
    }
    
    public function update($id, $data) {
        return $this->wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id)
        );
    }
    
    public function delete($id) {
        // First delete all child tasks
        $children = $this->get_children($id);
        foreach ($children as $child) {
            $this->delete($child->id);
        }
        
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => $id)
        );
    }
    
    public function count_tasks($args = array()) {
        $defaults = array(
            'category_id' => null,
            'status' => null,
            'parent_id' => null
        );
        
        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $values = array();
        
        if (!is_null($args['category_id'])) {
            $where[] = 'category_id = %d';
            $values[] = $args['category_id'];
        }
        
        if (!is_null($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }
        
        if (!is_null($args['parent_id'])) {
            $where[] = 'parent_id = %d';
            $values[] = $args['parent_id'];
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE " . implode(' AND ', $where);
        
        if (!empty($values)) {
            $sql = $this->wpdb->prepare($sql, $values);
        }
        
        return $this->wpdb->get_var($sql);
    }
}
