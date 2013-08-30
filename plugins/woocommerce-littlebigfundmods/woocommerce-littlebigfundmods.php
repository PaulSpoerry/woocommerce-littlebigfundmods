<?php

/*
Plugin Name: WooCommerce LittleBigFundMods
Plugin URI: http://www.PaulSpoerry.com
Description:  WooCommerce LittleBigFundMods
Version: 1.0
Author: Paul Spoerry
Author URI: http://www.PaulSpoerry.com
*/

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    /**
     * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
     */
    add_action( 'wp_enqueue_scripts', 'ps_WC_LittleBigFundMods_CSS' );
    /**
     * Enqueue plugin style-file
     */
    function ps_WC_LittleBigFundMods_CSS() {
        // Respects SSL, Style.css is relative to the current file
        wp_register_style( 'prefix-style', plugins_url('css/ps_wc_littlebigfundmods.css', __FILE__) );
        wp_enqueue_style( 'prefix-style' );
    }

     /**
     * Remove images from single products as we don't want them to display. WC_removeimages.css contains styles requires for help
     */
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

	add_filter( 'woocommerce_product_thumbnails_columns', 'hide_product_images', 10, 2 );
	add_filter( 'woocommerce_single_product_image_thumbnail_html', 'hide_product_images', 10, 2 );
	add_filter( 'woocommerce_single_product_image_html', 'hide_product_images', 10, 2 );

    /* Hide the product images */
	function hide_product_images( $price, $product ) {
	  return '';
	}

    /**
     *  Set WooCommerce Virtual Order Status to Complete After Payment.
     */
	add_filter( 'woocommerce_payment_complete_order_status', 'virtual_order_payment_complete_order_status', 10, 2 );
	function virtual_order_payment_complete_order_status( $order_status, $order_id ) {
	  $order = new WC_Order( $order_id );
	  if ( 'processing' == $order_status &&
		   ( 'on-hold' == $order->status || 'pending' == $order->status || 'failed' == $order->status ) ) {
		$virtual_order = null;

		if ( count( $order->get_items() ) > 0 ) {
		  foreach( $order->get_items() as $item ) {
			if ( 'line_item' == $item['type'] ) {
			  $_product = $order->get_product_from_item( $item );
			  if ( ! $_product->is_virtual() ) {
				// once we've found one non-virtual product we know we're done, break out of the loop
				$virtual_order = false;
				break;
			  } else {
				$virtual_order = true;
			  }
			}
		  }
		}

		// virtual order, mark as completed
		if ( $virtual_order ) {
		  return 'completed';
		}
	  }
	  // non-virtual order, return original status
	  return $order_status;
	}

        // Make phone NOT required
        add_filter( 'woocommerce_billing_fields', 'wc_npr_filter_phone', 10, 1 );
        function wc_npr_filter_phone( $address_fields ) {
            $address_fields['billing_phone']['required'] = false;
            return $address_fields;
        }

        
        add_action('admin_menu', 'register_LBFWooCommerceReports_submenu_page');

        function lbfmonthly_reports_page() {
            include('admin_LBFMonthlyReports.php');  
        }
        function register_LBFWooCommerceReports_submenu_page() {
                $reports_page = add_submenu_page( 'woocommerce', __( 'LBF Monthly Reports', 'woocommerce' ),  __( 'LBF Monthly Reports', 'woocommerce' ) , 'view_lbfmonthly_reports', 'lbfmonthly_reports', 'lbfmonthly_reports_page' );
        
        add_action( 'load-' . $reports_page, 'woocommerce_admin_help_tab' );
        }
        
        

        function my_custom_submenu_page_callback() {
                echo '<h3>My Custom Submenu Page</h3>';

        }
        
        function gatewayStripeDescriptionFormatter($order_id) {
            global $woocommerce;
            $order_id = str_replace("#", "", $order_id);
            $order = new WC_Order( $order_id );
            $formattedDescription = '';
            $bShowTotal = false;

            if (sizeof($order->get_items())>0) {
                    foreach($order->get_items() as $item) {
                        $_product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
                        $formattedDescription = $item['name'] . ' (Order ' . $order->get_order_number() . ')';

                        $item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
                        $LBFProducts = array('Daily Contribution', 'Daily Donation to LBF Operating Costs', 'Donation', 'Donation to LBF Operating Costs', 'Monthly Contribution');

                        foreach ( $item_meta->meta as $meta_key => $meta_values ) {
                            if (in_array($meta_key, $LBFProducts)) {
                                //echo '<br />key: ' . $meta_key . ' and its val: ' . $meta_values[0];
                                 $formattedDescription = $formattedDescription . ' - ' . $meta_key . ':' . $meta_values[0];
                            }
                        }
                        if ($bShowTotal)
                            $formattedDescription = $formattedDescription . ' - Total:' . $order->get_formatted_line_subtotal( $item );
                    }
                }
            return $formattedDescription;
        }
}
?>