<?php
/**
 * Vendor dashboard Rentals->Quote -> Quote single page content
 *
 * Used by WCMp_AFM_Quote_Details_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-endpoint-quote-details.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/rental
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

global $wp, $WCMp;
if ( empty( $quote_post ) || empty( $product_id ) ) {
    ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <?php esc_html_e( 'Invalid quote', WCMp_AFM_TEXT_DOMAIN ); ?>
        </div>
    </div>
    <?php
    return;
}

$vendor = get_current_vendor();
$quote_id = $quote_post->ID;

$product_title = get_the_title( $product_id );
$product_url = get_the_permalink( $product_id );

$order_quote_meta = json_decode( get_post_meta( $quote_id, 'order_quote_meta', true ), true );
// Save data on form submit
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( ! isset( $_POST['vendor_quote_request_nonce'] ) || ! wp_verify_nonce( $_POST['vendor_quote_request_nonce'], 'dc-vendor-quote-request-details' ) ) {
        ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <?php _e( 'Invalid quote', WCMp_AFM_TEXT_DOMAIN ); ?>
            </div>
        </div>
        <?php
        return;
    } else {

        $from_name = $vendor->user_data->user_nicename;
        $from_email = $vendor->user_data->user_email;

        $to_author_id = get_post_field( 'post_author', $quote_id );
        $to_email = get_the_author_meta( 'user_email', $to_author_id );

        if ( apply_filters( 'is_vendor_can_take_quote_actions', true, $vendor->id ) ) {
            if ( isset( $_POST['post_status'] ) && $_POST['post_status'] !== $quote_post->post_status ) {
                $update_quote = array(
                    'ID'          => $quote_id,
                    'post_status' => $_POST['post_status'],
                );
                wp_update_post( $update_quote );
                // send email
                // $form_data = $order_quote_meta;

                $subject = ( $quote_post->post_status === 'quote-accepted' ) ? "Congratulations! Your quote request has been accepted" : "Your quote request status has been updated";
                $data_object = array(
                    'quote_id' => $quote_id,
                );

                // Send the mail to the customer
                $email = new RnB_Email();
                $email->quote_accepted_notify_customer( $to_email, $subject, $from_email, $from_name, $data_object );
            }

            if ( isset( $_POST['quote_price'] ) ) {
                update_post_meta( $quote_id, '_quote_price', $_POST['quote_price'] );
            }
            $quote_post = get_post( $quote_id );
        }

        if ( apply_filters( 'is_vendor_can_add_quote_message', true, $vendor->id ) && ! empty( $_POST['add-quote-message'] ) ) {
            global $current_user;

            $time = current_time( 'mysql' );

            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $ip = apply_filters( 'redq_rental_get_ip', $ip );

            $data = array(
                'comment_post_ID'      => $quote_id,
                'comment_author'       => $vendor->user_data->user_nicename,
                'comment_author_email' => $vendor->user_data->user_email,
                'comment_author_url'   => $vendor->user_data->user_url,
                'comment_content'      => $_POST['add-quote-message'],
                'comment_type'         => 'quote_message',
                'comment_parent'       => 0,
                'user_id'              => $current_user->ID,
                'comment_author_IP'    => $ip,
                'comment_agent'        => $_SERVER['HTTP_USER_AGENT'],
                'comment_date'         => $time,
                'comment_approved'     => 1,
            );

            $comment_id = wp_insert_comment( $data );

            if ( $comment_id ) {
                $subject = "New reply for your quote request";
                $reply_message = $_POST['add-quote-message'];
                $data_object = array(
                    'reply_message' => $reply_message,
                    'quote_id'      => $quote_id,
                );

                // Send the mail to the customer
                $email = new RnB_Email();
                $email->owner_reply_message( $to_email, $subject, $from_email, $from_name, $data_object );
            }
        }
    }
}

// Remove the comments_clauses where query here.
remove_filter( 'comments_clauses', 'exclude_request_quote_comments_clauses' );
$args = array(
    'post_id' => $quote_id,
    'orderby' => 'comment_ID',
    'order'   => 'DESC',
    'approve' => 'approve',
    'type'    => 'quote_message'
);
$comments = get_comments( $args );
do_action( 'before_wcmp_vendor_dashboard_quote_details' );
?>
<div class="col-md-12">
    <form method="post" name="quote-request-details">
        <div class="icon-header">
            <span><i class="wcmp-font ico-order-details-icon"></i></span>
            <h2><?php esc_html_e( 'Quote request #', WCMp_AFM_TEXT_DOMAIN ); ?><?php esc_html_e( $quote_id ); ?></h2>
            <h3><?php _e( sprintf( 'was placed on %s and is currently <span class="%s">%s</span>', date_i18n( get_option( 'date_format' ), strtotime( $quote_post->post_date ) ), $quote_post->post_status, ucwords( str_replace( '-', ' ', str_replace( 'quote-', '', $quote_post->post_status ) ) ) ) ); ?></h3>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default pannel-outer-heading mt-0">
                    <div class="panel-heading"><h3><?php esc_html_e( 'Quote message', WCMp_AFM_TEXT_DOMAIN ); ?></h3></div>
                    <div class="panel-body panel-content-padding">
                        <?php if ( apply_filters( 'is_vendor_can_add_quote_message', true, $vendor->id ) ) : ?>
                            <div class="panel-form-content">
                                <div class="form-group">
                                    <textarea name="add-quote-message" id="add-quote-message" class="form-control"></textarea>
                                </div>
                                <input type="submit" class="btn btn-default add-quote-reply" value="<?php esc_html_e( 'Add Message', WCMp_AFM_TEXT_DOMAIN ); ?>"/>
                            </div>
                        <?php endif; ?>
                        <ul class="rental-quote-message">
                            <?php foreach ( $comments as $comment ) : ?>
                                <?php
                                $list_class = 'message-list';
                                $content_class = 'quote-message-content';
                                if ( $comment->user_id === get_post_field( 'post_author', $quote_id ) ) {
                                    $list_class .= ' customer';
                                    $content_class .= ' customer';
                                }
                                ?>
                                <li class="<?php echo $list_class ?>">
                                    <div class="<?php echo $content_class ?>">
                                        <?php echo wpautop( wptexturize( wp_kses_post( $comment->comment_content ) ) ); ?>
                                    </div>
                                    <p class="meta"><?php printf( __( 'added on %1$s at %2$s', WCMp_AFM_TEXT_DOMAIN ), date_i18n( wc_date_format(), strtotime( $comment->comment_date ) ), date_i18n( wc_time_format(), strtotime( $comment->comment_date ) ) ); ?>
                                        <?php printf( ' ' . __( 'by %s', WCMp_AFM_TEXT_DOMAIN ), $comment->comment_author ); ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="panel panel-default pannel-outer-heading">
                    <div class="panel-heading">
                        <h3><?php esc_html_e( 'Quote management', WCMp_AFM_TEXT_DOMAIN ); ?></h3>
                    </div>
                    <div class="panel-body panel-content-padding qoute-managment-wrap">
                        <div class="col-md-6">
                            <h2><?php esc_html_e( sprintf( __( 'Quote #%s Details', 'redq-rental' ), $quote_id ) ); ?></h2>
                            <p class="quote_number">
                                <?php
                                $get_labels = reddq_rental_get_settings( $product_id, 'labels', array( 'pickup_location', 'return_location', 'pickup_date', 'return_date', 'resources', 'categories', 'person', 'deposites' ) );
                                $labels = $get_labels['labels'];
                                ?>
                                <?php esc_html_e( 'Request for:', 'redq-rental' ) ?> <a href="<?php echo esc_url( $product_url ) ?>" target="_blank"><?php esc_html_e( $product_title ); ?></a>
                            </p>
                            <?php
                            $customer_infos = '';
                            foreach ( $order_quote_meta as $meta ) {
                                ?>
                                <?php
                                if ( isset( $meta['name'] ) ) {

                                    switch ( $meta['name'] ) {
                                        case 'add-to-cart':
                                            # code...
                                            break;

                                        case 'currency-symbol':
                                            # code...
                                            break;

                                        case 'pickup_location':
                                            if ( ! empty( $meta['value'] ) ):
                                                $pickup_location_title = $labels['pickup_location'];
                                                $dval = explode( '|', $meta['value'] );
                                                $pickup_value = $dval[0] . ' ( ' . wc_price( $dval[2] ) . ' )';
                                                ?>
                                                <p><?php echo esc_attr( $pickup_location_title ) ?>: <?php echo $pickup_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'dropoff_location':
                                            if ( ! empty( $meta['value'] ) ):
                                                $return_location_title = $labels['return_location'];
                                                $dval = explode( '|', $meta['value'] );
                                                $return_value = $dval[0] . ' ( ' . wc_price( $dval[2] ) . ' )';
                                                ?>
                                                <p><?php echo esc_attr( $return_location_title ) ?>: <?php echo $return_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'pickup_date':
                                            if ( ! empty( $meta['value'] ) ):
                                                $pickup_date_title = $labels['pickup_date'];
                                                $pickup_date_value = $meta['value'];
                                                ?>
                                                <p><?php echo esc_attr( $pickup_date_title ) ?>: <?php echo $pickup_date_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'pickup_time':
                                            if ( ! empty( $meta['value'] ) ):
                                                $pickup_time_title = $labels['pickup_time'];
                                                $pickup_time_value = $meta['value'] ? $meta['value'] : '';
                                                ?>
                                                <p><?php echo esc_attr( $pickup_time_title ) ?>: <?php echo $pickup_time_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'dropoff_date':
                                            if ( ! empty( $meta['value'] ) ):
                                                $return_date_title = $labels['return_date'];
                                                $return_date_value = $meta['value'] ? $meta['value'] : '';
                                                ?>
                                                <p><?php echo esc_attr( $return_date_title ) ?>: <?php echo $return_date_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'dropoff_time':
                                            if ( ! empty( $meta['value'] ) ):
                                                $return_time_title = $labels['return_time'];
                                                $return_time_value = $meta['value'] ? $meta['value'] : '';
                                                ?>
                                                <p><?php echo esc_attr( $return_time_title ) ?>: <?php echo $return_time_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'additional_adults_info':
                                            if ( ! empty( $meta['value'] ) ):
                                                $person_title = $labels['adults'];
                                                $dval = explode( '|', $meta['value'] );
                                                $person_value = $dval[0] . ' ( ' . wc_price( $dval[1] ) . ' - ' . $dval[2] . ' )';
                                                ?>
                                                <p><?php echo esc_attr( $person_title ) ?>: <?php echo $person_value; ?></p>
                                                <?php
                                            endif;
                                            break;

                                        case 'extras':
                                            ?>
                                            <?php
                                            $resources_title = $labels['resource'];
                                            $resource_name = '';
                                            $payable_resource = array();
                                            foreach ( $meta['value'] as $key => $value ) {
                                                $extras = explode( '|', $value );
                                                $payable_resource[$key]['resource_name'] = $extras[0];
                                                $payable_resource[$key]['resource_cost'] = $extras[1];
                                                $payable_resource[$key]['cost_multiply'] = $extras[2];
                                                $payable_resource[$key]['resource_hourly_cost'] = $extras[3];
                                            }
                                            foreach ( $payable_resource as $key => $value ) {
                                                if ( $value['cost_multiply'] === 'per_day' ) {
                                                    $resource_name .= $value['resource_name'] . ' ( ' . wc_price( $value['resource_cost'] ) . ' - ' . __( 'Per Day', WCMp_AFM_TEXT_DOMAIN ) . ' )' . ' , <br> ';
                                                } else {
                                                    $resource_name .= $value['resource_name'] . ' ( ' . wc_price( $value['resource_cost'] ) . ' - ' . __( 'One Time', WCMp_AFM_TEXT_DOMAIN ) . ' )' . ' , <br> ';
                                                }
                                            }
                                            ?>
                                            <p><?php echo esc_attr( $resources_title ); ?>: <?php echo $resource_name; ?></p>
                                            <?php
                                            break;
                                        case 'security_deposites':
                                            ?>
                                            <?php
                                            $deposits_title = $labels['deposite'];
                                            $deposite_name = '';
                                            $payable_deposits = array();
                                            foreach ( $meta['value'] as $key => $value ) {
                                                $extras = explode( '|', $value );
                                                $payable_deposits[$key]['deposite_name'] = $extras[0];
                                                $payable_deposits[$key]['deposite_cost'] = $extras[1];
                                                $payable_deposits[$key]['cost_multiply'] = $extras[2];
                                                $payable_deposits[$key]['deposite_hourly_cost'] = $extras[3];
                                            }
                                            foreach ( $payable_deposits as $key => $value ) {
                                                if ( $value['cost_multiply'] === 'per_day' ) {
                                                    $deposite_name .= $value['deposite_name'] . ' ( ' . wc_price( $value['deposite_cost'] ) . ' - ' . __( 'Per Day', WCMp_AFM_TEXT_DOMAIN ) . ' )' . ' , <br> ';
                                                } else {
                                                    $deposite_name .= $value['deposite_name'] . ' ( ' . wc_price( $value['deposite_cost'] ) . ' - ' . __( 'One Time', WCMp_AFM_TEXT_DOMAIN ) . ' )' . ' , <br> ';
                                                }
                                            }
                                            ?>
                                            <p><?php echo esc_attr( $deposits_title ); ?>: <?php echo trim( $deposite_name, ' , <br> ' ); ?></p>
                                            <?php
                                            break;

                                        default:
                                            ?>
                                            <p><?php echo esc_attr( $meta['name'] ) ?>: <?php echo esc_attr( $meta['value'] ) ?></p>
                                            <?php
                                            break;
                                    }
                                }
                                ?>

                                <?php
                                if ( isset( $meta['forms'] ) ) {
                                    $contacts = $meta['forms'];
                                    ob_start();
                                    ?>
                                    <div class="col-md-6">
                                        <h2><?php esc_html_e( 'Customer information', WCMp_AFM_TEXT_DOMAIN ); ?></h2>
                                        <?php foreach ( $contacts as $key => $value ) { ?>
                                            <?php if ( ! ( $key === 'quote_message' || $key === 'quote_username' || $key === 'quote_password' ) ) : ?>
                                                <p><?php echo ucfirst( substr( $key, 6 ) ) ?> : <?php echo $value ?></p>
                                            <?php endif ?>
                                        <?php } ?>
                                    </div>
                                    <?php
                                    $customer_infos .= ob_get_contents();
                                    ob_end_clean();
                                    ?>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <?php echo $customer_infos; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <h3><?php esc_html_e( 'Quote actions :', WCMp_AFM_TEXT_DOMAIN ); ?></h3>
                <ul class="quote_actions submitbox list-group">
                    <li class="list-group-item list-group-item-action flex-column align-items-start">
                        <?php
                        $quote_statuses = apply_filters( 'redq_get_request_quote_post_statuses', array(
                            'quote-pending'    => _x( 'Pending', 'Quote status', 'redq-rental' ),
                            'quote-processing' => _x( 'Processing', 'Quote status', 'redq-rental' ),
                            'quote-on-hold'    => _x( 'On Hold', 'Quote status', 'redq-rental' ),
                            'quote-accepted'   => _x( 'Accepted', 'Quote status', 'redq-rental' ),
                            'quote-completed'  => _x( 'Completed', 'Quote status', 'redq-rental' ),
                            'quote-cancelled'  => _x( 'Cancelled', 'Quote status', 'redq-rental' ),
                            )
                        );
                        ?>
                        <?php if ( apply_filters( 'is_vendor_can_take_quote_actions', true, $vendor->id ) ) { ?>
                            <div>
                                <p class="form-group">
                                    <label><?php esc_html_e( 'Quote Status', WCMp_AFM_TEXT_DOMAIN ) ?></label>
                                    <select name="post_status" class="form-control">
                                        <?php foreach ( $quote_statuses as $key => $value ) : ?>
                                            <option value="<?php echo $key ?>" <?php echo ( $quote_post->post_status === $key) ? 'selected="selected"' : '' ?>><?php echo $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <p class="form-group">
                                    <label><?php esc_html_e( 'Price', WCMp_AFM_TEXT_DOMAIN ) ?> (<?php echo esc_attr( get_post_meta( $quote_id, 'currency-symbol', true ) ) ?>)</label>
                                    <?php
                                    $price = floatval( get_post_meta( $quote_id, '_quote_price', true ) );
                                    ?>
                                    <input type="text" class="redq_input_price form-control" name="quote_price" value="<?php echo $price ?>">
                                </p>
                            </div>
                            <input class="btn btn-default button-primary wcmp-save-quote" type="submit" name="save" value="<?php esc_html_e( 'Update Quote', WCMp_AFM_TEXT_DOMAIN ); ?>" />
                        <?php } else { ?>
                            <p><?php esc_html_e( 'Quote Status', WCMp_AFM_TEXT_DOMAIN ) ?> <strong><?php esc_html_e( $quote_statuses[$quote_post->post_status] ); ?></strong></p>
                            <p><?php esc_html_e( 'Price', WCMp_AFM_TEXT_DOMAIN ) ?> <strong><?php esc_html_e( get_post_meta( $quote_id, '_quote_price', true ) ); ?><?php esc_html_e( get_post_meta( $quote_id, 'currency-symbol', true ) ) ?></strong></p>
                        <?php } ?>
                    </li>
                </ul>
            </div>
        </div>
    </form>
</div>
<?php
do_action( 'after_wcmp_vendor_dashboard_quote_details' );
