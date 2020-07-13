<?php
/**
 * My Orders - Deprecated
 *
 * @deprecated 2.6.0 this template file is no longer used. My Account shortcode uses orders.php.
 * @package WooCommerce/Templates
 */
defined('ABSPATH') || exit;

global  $wpdb;

$my_orders_columns =  array(
    'id' => esc_html__('Dispute ID', 'woocommerce'),
    'order-id' => esc_html__('Order ID', 'woocommerce'),
    'sub-order-id' => esc_html__('Sub Order ID', 'woocommerce'),
    'title' => esc_html__('Title', 'woocommerce'),
    'status' => esc_html__('Status', 'woocommerce'),
    'order-actions' => '&nbsp;',
      
);

$customer_orders = get_posts(
        apply_filters(
                'woocommerce_my_account_my_orders_query', array(
    'numberposts' => $order_count,
    'meta_key' => '_customer_user',
    'meta_value' => get_current_user_id(),
    'post_type' => wc_get_order_types('view-orders'),
    'post_status' => array_keys(wc_get_order_statuses()),
                )
        )
);


$table_name_status = BuyLockSmithDealsCustomizationAddon::blsd_status_table_name();
$table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
$table_name_message_table = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
$user_id = get_current_user_id();
$query = "SELECT $table_name.*,$table_name_status.name as status_name "
        . ", (select title from  $table_name_message_table where $table_name_message_table.dispute_id=$table_name.id limit 1) as title FROM $table_name"
        . " inner join $table_name_status on $table_name_status.id=$table_name.status "
        . " WHERE (user_id=$user_id or who_opose_user_id=$user_id) ";
$results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);



if ($customer_orders) :
    ?>



    <table class="shop_table shop_table_responsive my_account_orders">

        <thead>
            <tr>
    <?php foreach ($my_orders_columns as $column_id => $column_name) : ?>
                    <th class="<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
    <?php
    foreach ($results as $result) : ?>
                <tr class="order">
              
                        <td class="" data-title="">
                      <?php echo $result['id'];?>
                        </td>
                        <td class="" data-title="">
                      <?php $order_id =  $result['order_id'];
                      $post_data = get_post($order_id);
                      echo wp_get_post_parent_id($post_data);
                      
                      ?>
                        </td>
                        <td class="" data-title="">
                      <?php echo $result['order_id'];?>
                        </td>
                        <td class="" data-title="">
                      <?php echo $result['title'];?>
                        </td>
                        <td class="" data-title="">
                      <?php echo $result['status_name'];?>
                        </td>
                        <td class="" data-title="">
                      <a href="<?php echo home_url();?>/my-account/dispute-view?view=<?php echo $result['id'];?>" class="woocommerce-button button view">View</a>
                        </td>
              
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
