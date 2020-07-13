<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Links_List_Table extends WP_List_Table
{

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'clients_page_blsm-dispute-list', //Singular label
            'plural' => 'clients_page_blsm-dispute-lists', //plural label, also this well be one of the table css class
            'ajax' => false, //We won't support Ajax for this table
        ));
    }

    public function extra_tablenav($which)
    {
        if ($which == "top") {
            //The code that goes before the table is here
            //   echo"Hello, I'm before the table";
        }
        if ($which == "bottom") {
            //The code that goes after the table is there
            //   echo"Hi, I'm after the table";
        }
    }

    public function get_columns()
    {
        return $columns = array(
            'id' => __('Dispute ID'),
            'title' => __('Title'),
            'user_id' => __('Created By'),
            'role' => __('Role'),
            'order_id' => __('Sub Order ID'),
            'order_id_main' => __('Order ID'),
            'status_name' => __('Status'),
            'created_at' => __('Date'),
            'action' => __('Action'),

        );
    }

    public function get_sortable_columns()
    {
        return $sortable = array(
            'id' => array('id', true),
            'user_id' => array('user_id', true),
            'role' => array('role', true),
            'status_name' => array('status', true),
            'created_at' => array('created_at', true)
        );
    }

    public function prepare_items()
    {
        global $wpdb, $_wp_column_headers;
        $this->delete_dispute();
        $user_id = get_current_user_id();

        $tableprefix = $wpdb->prefix;
  
   
        $search = $_REQUEST["search"];
        $where = '';
         $table_name_status = BuyLockSmithDealsCustomizationAddon::blsd_status_table_name();
         $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
         $table_name_message_table = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
        if($search!=''){
         $where = " and ($table_name.id Like '%$search%' OR "
                 //. "$table_name_message_table.title Like '%$search%'"
                 . " (select count(id) from  $table_name_message_table where $table_name_message_table.dispute_id=$table_name.id and title Like '%$search%' limit 1)"
                 . " OR $table_name_status.name Like '%$search%'  )  ";   
        }
        
        $vendor_id = $vendor->id;
        
        
         $query = "SELECT $table_name.*,$table_name_status.name as status_name "
                . ", (select title from  $table_name_message_table where $table_name_message_table.dispute_id=$table_name.id limit 1) as title FROM $table_name"
         . " inner join $table_name_status on $table_name_status.id=$table_name.status "
         . " WHERE 1 $where ";
                    $results_total = $users = $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
             

        
        $user_list_total = $results_total;

        $page = 1;

        $per_page = get_option('posts_per_page');

        $total = count($user_list_total);

        $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'id';
        $order = !empty($_GET["order"]) ? $_GET["order"] : 'DESC';

        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : '';
        //Page Number
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $offset = ($paged - 1) * $per_page;

        $query .= " order by $orderby $order limit $per_page offset $offset";
        //$user_list = $users = $wpdb->get_results($sql);
        
         $dispute_list = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
        

        $results_data = [];

       
        $counter = 1;
        if ($page > 1) {
            $counter = ($paged * $offset) - $offset + 1;
        }
       
        $this->set_pagination_args(array(
            "total_items" => $total,
            "total_pages" => ceil($total / $per_page),
            "per_page" => $per_page,
        ));
        //The pagination links are automatically built according to those parameters

        /* -- Register the Columns -- */
        $columns = $this->get_columns();

        $this->_column_headers = array(
            $this->get_columns(),
            array(), //hidden columns if applicable
            $this->get_sortable_columns(),
        );

        /* -- Fetch the items -- */

        $this->items = $dispute_list;
    }

    public function display_rows()
    {
       
       

        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list($columns, $hidden) = $this->get_column_info();
        $columns = $this->get_columns();

        //Loop for each record
        if (!empty($records)) {
            foreach ($records as $rec) {

                //Open the line
                echo '<tr id="record_' . $rec['id'] . '">';
                foreach ($columns as $column_name => $column_display_name) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if (in_array($column_name, $hidden)) {
                        $style = ' style="display:none;"';
                    }

                    $attributes = $class . $style;

                    
                   $name = BuyLockSmithDealsCustomizationAddon::blsd_get_userFullName($rec['user_id']);
                    
                  $parent_id = wp_get_post_parent_id($rec['order_id'] );
                  
                    //Display the cell
                    switch ($column_name) {
                        case "id":echo '<td ' . $attributes . '>' . stripslashes($rec['id']) . '</td>';
                            break;
                        case "title":echo '<td ' . $attributes . '>' . stripslashes($rec['title']) . '</td>';
                            break;
                        case "user_id":echo '<td ' . $attributes . '>' . stripslashes($name) . '</td>';
                            break;
                        case "role":echo '<td ' . $attributes . '>' . stripslashes($rec['role']) . '</td>';
                            break;
                        case "order_id":echo '<td ' . $attributes . '>' . stripslashes($rec['order_id']) . '</td>';
                            break;
                        case "order_id_main":echo '<td ' . $attributes . '>' . stripslashes($parent_id) . '</td>';
                            break;
                        case "status_name":echo '<td ' . $attributes . '>' . stripslashes($rec['status_name']) . '</td>';
                            break;
                        case "created_at":echo '<td ' . $attributes . '>' . date('F d, Y H:i A', strtotime($rec['created_at'])) . '</td>';
                            break;
                        
                        case "action":echo '<td ' . $attributes . '>'
                            . '<a href="' . home_url() . '/wp-admin/admin.php?page=blsm-dispute-detail&id=' . $rec['id'] . '">View</a>  '                            
                       //     . '<a class="delete_dispute" href="' . home_url() . '/wp-admin/admin.php?page=blsm-dispute-list&delete_dispute=' . $rec['id'] . '">Delete</a>'
                                . '</td>';
                            break;
                    }

                }

                //Close the line
                echo '</tr>';
            }
        }
    }
    private function delete_dispute()
    {
        if (isset($_GET['delete_dispute'])) {
            if ($_GET['delete_dispute'] != '') {

                $client_id = $_GET['delete_dispute'];
                $personal_trainer_id = get_user_meta($client_id, 'personal_trainer_id', true);
                $user_id = get_current_user_id();
                if ($personal_trainer_id == $user_id) {

                    require_once ABSPATH . 'wp-admin/includes/user.php';
                    wp_delete_user($client_id, $client_id);

                }
            }
        }
    }
}
