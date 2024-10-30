<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Deposits {
    
    public function __construct()
    {
        /* WooCommerce Deposits */
         
        add_action('woocommerce_new_order', [$this, 'iwp_woocommerce_new_order'], 99, 1);
        
        /* Event Filtering */
        
        add_filter('iwp_permitted_events', [$this, 'iwp_wcd_permitted_events'], 10, 1);
    }
    
    public function iwp_woocommerce_new_order($order_id) {
        
        $order = wc_get_order($order_id);
        
        if(!$order) return;
        
        $user = $order->get_user();
        
        if(!$user) return false;
        
        $has_deposit = WC_Deposits_Order_Manager::has_deposit($order);
        
        $has_future_payments = WC_Deposits_Order_Manager::order_has_future_deposit_payment($order);
        
        $intercom = new Intercom($user->ID);
        
        $metadata = [
            'Order ID' => $order_id,
         ];
        
        if($has_deposit) {
            $intercom->create_event('wc_deposit_made', $metadata);
        }
        
        if($has_future_payments) {
            $intercom->create_event('wc_deposit_payment_plan', $metadata);
        }
        
        $tags = apply_filters('iwp_woocommerce_deposit_order', [], $user, $order_id, $has_deposit, $has_future_payments);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_wcd_permitted_events($events) {
        
        $iwp_events = array(
            'wc_deposit_made',
            'wc_deposit_payment_plan'
        );
        
        return array_merge($iwp_events, $events);
    }
    
}

?>