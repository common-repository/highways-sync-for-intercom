<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Bookings {
    
    public function __construct()
    {
        
        /* Booking Created */
        add_action('save_post_shop_order', [$this, 'iwp_woocommerce_booking_created'], 99, 3);
        
        /* Booking Cancelled */
        add_action('woocommerce_booking_cancelled', [$this, 'iwp_woocommerce_booking_cancelled'], 99, 1);
        
        /* Booking Complete */
        add_action('complete_wc_booking', [$this, 'iwp_woocommerce_booking_complete'], 99, 2);
        
        add_action('wc-booking-complete', [$this, 'iwp_woocommerce_booking_complete'], 99, 1);
        
        /* Event Filtering */
        
        add_filter('iwp_permitted_events', [$this, 'iwp_wcb_permitted_events'], 10, 1);
    }
    
    public function iwp_woocommerce_booking_created($order_id, $order, $update) {
        if($update) return false;
        
        $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_id( $order_id );
        
        if(empty($booking_ids)) return false;
        
        $user = $order->get_user();
        
        if(!$user) return false;
        
        $metadata = [
            'Order ID' => $order_id,
            'Booking IDs' => implode(', ', $booking_ids),
            'Order Total' => $order->get_total(),
            'Line Item(s)' => count($order->get_items()),
            'View Order (Admin)' => $order->get_view_order_url()
        ];
        
        $intercom = new Intercom($user->ID);
        
        $intercom->create_event('wc_booking_created', $metadata);
        
        $tags = apply_filters('iwp_woocommerce_booking_created', [], $user, $order_id, $order, $booking_ids);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_woocommerce_booking_cancelled($booking_id) {
        
        $booking = new WC_Booking($booking_id);
            
        $order = $booking->get_order();
        
        if(empty($order)) return false;
        
        $user = $order->get_user();
        
        if(!$user) return false;
        
        $intercom = new Intercom($user->ID);
        
        $metadata = [
            'Booking ID' => $booking_id
        ];
        
        $intercom->create_event('wc_booking_cancelled', $metadata);
        
        $tags = apply_filters('iwp_woocommerce_booking_cancelled', [], $user, $order, $booking, $booking_id);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_woocommerce_booking_complete($booking_id, $booking = false) {
        
        if(!$booking) $booking = new WC_Booking($booking_id);
        
        $order = $booking->get_order();
        
        if(empty($order)) return false;
        
        $user = $order->get_user();
        
        if(!$user) return false;
        
        $intercom = new Intercom($user->ID);
        
        $metadata = [
            'Booking ID' => $booking_id
        ];
        
        $intercom->create_event('wc_booking_complete', $metadata);
        
        $tags = apply_filters('iwp_woocommerce_booking_complete', [], $user, $order, $booking, $booking_id);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_wcb_permitted_events($events) {
        
        $iwp_events = array(
            'wc_booking_created',
            'wc_booking_cancelled',
            'wc_booking_complete'
        );
        
        return array_merge($iwp_events, $events);
    }
    
}
               
?>