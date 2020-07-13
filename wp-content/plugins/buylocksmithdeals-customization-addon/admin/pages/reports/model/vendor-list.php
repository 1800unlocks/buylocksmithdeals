<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Vendor_List_Table extends WP_List_Table
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
            'ID' => __('ID'),
            'user_login' => __('User ID'),
            'display_name' => __('Name'),
            'user_email' => __('Email'),
            'total_deals' => __('Total Assigned Deals'),
            'active_deals' => __('Active Deals')
        );
    }

    public function get_sortable_columns()
    {
        return $sortable = array(
            'ID' => array('id', true),
            'user_login' => array('user_login', true),
            'display_name' => array('display_name', true),
            'user_email' => array('user_email', true),
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
         $vedors_ids = BuyLockSmithDealsCustomizationAdminReport::totalVendorIdList();
         
         $table_name = $wpdb->prefix.'users';
         $table_name_posts = $wpdb->prefix.'posts';
         $table_name_posts_meta = $wpdb->prefix.'postmeta';
         
        if($search!=''){
         $where = " and ($table_name.ID Like '%$search%' "
                 . " OR $table_name.user_login Like '%$search%'"
                 . " OR $table_name.display_name Like '%$search%'"
                 . " OR $table_name.user_email Like '%$search%'"
                
                 . "   )  ";   
        }
        
        
       $postmeta_query = "(select count(*) from $table_name_posts_meta where $table_name_posts_meta.post_id = $table_name_posts.ID and (meta_value<>'draft' and meta_key='status_vendor'))";
       $total_deals = "(select count(ID) from $table_name_posts where $table_name.ID=$table_name_posts.post_author and post_type='product'  and post_status='publish') as total_deals";
       $active_deals = "(select count(ID) from $table_name_posts where $table_name.ID=$table_name_posts.post_author and post_type='product' and post_status='publish' and $postmeta_query ) as active_deals";
        
        $vendor_id_text = implode(',',$vedors_ids);
          $query = "SELECT *, $total_deals, $active_deals  from  $table_name"
                 . " where ID in ($vendor_id_text) $where";
                    $results_total = $users = $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
         
        
        $user_list_total = $results_total;

        $page = 1;

        $per_page = get_option('posts_per_page');

        $total = count($user_list_total);

        $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'ID';
        $order = !empty($_GET["order"]) ? $_GET["order"] : 'DESC';

        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : '';
        //Page Number
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $offset = ($paged - 1) * $per_page;

        $query .= " order by $orderby $order limit $per_page offset $offset";
        //$user_list = $users = $wpdb->get_results($sql);
        
         $report_list = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
        

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

        $this->items = $report_list;
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
                echo '<tr id="record_' . $rec['ID'] . '">';
                foreach ($columns as $column_name => $column_display_name) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if (in_array($column_name, $hidden)) {
                        $style = ' style="display:none;"';
                    }

                    $attributes = $class . $style;

                 
                   $name = BuyLockSmithDealsCustomizationAddon::blsd_get_userFullName($rec['ID']);
                    
                  $parent_id = wp_get_post_parent_id($rec['order_id'] );
                  
                    //Display the cell
                    switch ($column_name) {
                        case "ID":echo '<td ' . $attributes . '>' . stripslashes($rec['ID']) . '</td>';
                            break;
                        case "user_login":echo '<td ' . $attributes . '>' . stripslashes($rec['user_login']) . '</td>';
                            break;
                        case "display_name":echo '<td ' . $attributes . '>' . stripslashes($rec['display_name']) . '</td>';
                            break;
                        case "user_email":echo '<td ' . $attributes . '>' . stripslashes($rec['user_email']) . '</td>';
                            break;
                        case "total_deals":echo '<td ' . $attributes . '>' . stripslashes($rec['total_deals']) . '</td>';
                            break;
                        case "active_deals":echo '<td ' . $attributes . '>' . stripslashes($rec['active_deals']) . '</td>';
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
