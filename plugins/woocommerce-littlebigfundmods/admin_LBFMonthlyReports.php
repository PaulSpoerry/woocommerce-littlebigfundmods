<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
      

        My stuff<br />
        -------------------------------<br />
<?php 
//function woocommerce_get_order_item_meta( $item_id, $key, $single = true ) {
//return get_metadata( 'order_item', $item_id, $key, $single );
//}

global $woocommerce;
$order_id = 670;
$order = new WC_Order( $order_id );
$formattedDescription = '';
$bShowTotal = false;

if (sizeof($order->get_items())>0) {
        foreach($order->get_items() as $item) {
            $_product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
            //echo '<br />productid: ' . $item['product_id'];
            //echo '<br />filterid:  ' . apply_filters( 'woocommerce_order_table_product_title', '<a href="' . get_permalink( $item['product_id'] ) . '">' . $item['name'] . '</a>', $item ) . ' ';
            $formattedDescription = $item['name'] . ' (Order ' . $order->get_order_number() . ')';
            //echo '<br />filterquantity : ' . apply_filters( 'woocommerce_order_table_item_quantity', '<strong class="product-quantity">&times; ' . $item['qty'] . '</strong>', $item );

            $item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
            $LBFProducts = array('Daily Contribution', 'Daily Donation to LBF Operating Costs', 'Donation', 'Donation to LBF Operating Costs', 'Monthly Contribution');
            
            foreach ( $item_meta->meta as $meta_key => $meta_values ) {
                if (in_array($meta_key, $LBFProducts)) {
                    //echo '<br />key: ' . $meta_key . ' and its val: ' . $meta_values[0];
                     $formattedDescription = $formattedDescription . ' - ' . $meta_key . ':' . $meta_values[0];
                }
            }
            
            //echo '<br />product-total: ' . $order->get_formatted_line_subtotal( $item );
            if ($bShowTotal)
                $formattedDescription = $formattedDescription . ' - Total:' . $order->get_formatted_line_subtotal( $item );
        }
    }

echo '$formattedDescription = ' . $formattedDescription;

echo '<br/>lets break here';
echo '<br />';

echo gatewayStripeDescriptionFormatter(988);

?>

<div class="clear"></div>






        
        
        
       <br /><br /><br />
       regular query:
       -------------------------------<br /> 
        
        
        
        
        
        <?php
        global $woocommerce, $wpdb;
        $monthForQuery = '6';
        $yearForQuery = '2013';
        
        $order_items = $wpdb->get_results( "
            SELECT 
                    order_items.order_id, order_items.order_item_name, ProdKeepRunning.meta_key, ProdKeepRunning.meta_value
            FROM wp_posts AS posts_orders 
            JOIN wp_woocommerce_order_items AS order_items
                    ON posts_orders.ID = order_items.order_id
            JOIN wp_woocommerce_order_itemmeta AS ProdKeepRunning -- keep us running orders
                    ON order_items.Order_Item_ID = ProdKeepRunning.Order_Item_ID
                             AND order_items.order_item_name = 'Help Keep Us Running'
                             AND ProdKeepRunning.meta_key = 'Donation to LBF Operating Costs'
            LEFT JOIN wp_term_relationships AS rel 
                    ON posts_orders.ID = rel.object_ID
            LEFT JOIN wp_term_taxonomy AS taxonomy USING( term_taxonomy_id )
            LEFT JOIN wp_terms AS term USING( term_id )
            WHERE 
                    -- info about the post/order
                    posts_orders.post_type = 'shop_order'
                    AND ( ( MONTH(posts_orders.post_date) = " . $monthForQuery . " OR " . $monthForQuery . " IS NULL ) AND ( YEAR(posts_orders.post_date) = " . $yearForQuery . " OR " . $yearForQuery . " IS NULL ) )
                    AND 	posts_orders.post_status = 'publish'
                    AND 	taxonomy.taxonomy = 'shop_order_status' AND term.slug = 'completed'  
        ");
        
        if ( $order_items ) {
        ?>
        <!-- 
        <table border="1" cellpadding="2" cellspacing="2">
            <tr>
                <th colspan ="4">Keep Us Running</th>
            </tr>
            <tr>
                <th>Order ID</th>
                <th>Order Item Name</th>
                <th>meta_key</th>
                <th>meta_value</th>
            </tr>
        
        <?php
            foreach ( $order_items as $order_item ) {
                echo '<tr>';
                    echo '<td>' . $order_item->order_id . '</td>';
                    echo '<td>' . $order_item->order_item_name . '</td>';
                    echo '<td>' . $order_item->meta_key . '</td>';
                    echo '<td>' . $order_item->meta_value . '</td>';
                echo '</tr>';
            }
         ?>
            <tr>
                <td colspan="3">Total</td>
                <td><?php echo $KeepUsRunningTotal ?></td>
            </tr>
            </table>
        -->
         <?php
	}
        ?>
    </body>
</html>
