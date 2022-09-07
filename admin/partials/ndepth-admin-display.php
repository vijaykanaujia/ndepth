<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       vijay.thoughtsole.in
 * @since      1.0.0
 *
 * @package    Ndepth
 * @subpackage Ndepth/admin/partials
 */
 
 // WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class N_depth_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $this->prepare_bulk_actions();
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
        $data = $this->table_data($search,$perPage,$currentPage);

        usort($data, array(&$this, 'sort_data'));

        $totalItems = $this->count_data();

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));

        // $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id'          => 'ID',
            'name'       => 'Name',
            'value' => 'Value',
            'parent'        => 'Parent',
            'image_id'        => 'Image',
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('name' => array('name', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data($search, $perPage,$currentPage)
    {
        global $wpdb;
        $data = [];
        $where = '';
        if($search){
            $where = " WHERE CONCAT_WS(`name`, `value`, `sub_tag`) LIKE '%".$search."%'";
        }
        $offset = (($currentPage - 1) * $perPage);
        $table_name = "{$wpdb->prefix}ndepth";
        $query = "SELECT * FROM `{$table_name}` $where LIMIT {$offset}, {$perPage}";

        $result =  $wpdb->get_results($query);

        foreach ($result as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
                'value' => $row->value,
                'parent' => $row->parent_id,
                'image_id' => $row->image_id
            ];
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
                $actions = array(
                    'edit'      => sprintf('<a href="?page=%s&action=%s&edit_id=%s">Edit</a>','ndepth/admin/partials/ndepth-admin-add.php','edit',$item['id']),
                    'delete'    => sprintf('<a href="?page=%s&action=%s&data_ids[]=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
                );
            return sprintf('%1$s %2$s', $item[$column_name], $this->row_actions($actions) );
                break;
            case 'image_id':
                if($item[$column_name]){
                    return wp_get_attachment_image( $item[$column_name], 'medium', false, array( 'id' => 'ndepth-preview-image' ) );
                }else{
                    return '<img src="'.plugin_dir_url(__DIR__).'images/faker.png" class="ndepth-preview-image">';
                }
                break;
            default:
                return $item[$column_name];
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'name';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }
        return -$result;
    }

    /**
     * This only puts the dropdown menu and the apply button above and below the table
     *
     * @return Mixed
     */
    protected function get_bulk_actions() {
        return [
            'delete' => 'Delete'
        ];
    }

    /**
     * The checkboxes for the rows have to be defined separately
     *
     * @return Mixed
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="data_ids[]" value="%s" />', $item['id']
        );    
    }

    /**
     * if data empty then show message
     *
     * @return Mixed
     */
    public function no_items() {
        _e( 'No Info found, dude.' );
    }

    /**
     * count total number of data
     *
     * @return Mixed
     */
    public function count_data() {
        global $wpdb;
        $table_name = "{$wpdb->prefix}ndepth";
        $query = "SELECT * FROM `{$table_name}`";
        $wpdb->get_results($query);
        return $wpdb->num_rows;
    }

    /**
     * delete bulk action
     *
     * @return Mixed
     */
    private function prepare_bulk_actions(){
        $action = $this->current_action();
        global $wpdb;
        $table_name = "{$wpdb->prefix}ndepth";
        if($action = 'delete'){
            $query = "SELECT * FROM `{$table_name}`";
            $ids = implode( ',', array_map( 'absint', isset($_REQUEST['data_ids']) ? $_REQUEST['data_ids'] : []));
            $wpdb->query( "DELETE FROM `{$table_name}` WHERE id IN($ids)" );
        }
    }
}
$classObj = new N_depth_Table();
$classObj->prepare_items();
?>
<div class="wrap">
    <div id="icon-users" class="icon32"></div>
    <h2>NDepth Listing</h2>
    <form method="post">
        <?php $classObj->search_box('search', 'search_box'); ?>
        <?php $classObj->display();  ?>
    </form>
</div>
