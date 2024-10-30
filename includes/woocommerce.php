<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce {
    
    public function __construct()
    {
        /* WooCommerce Actions */
        
        /* Item Removed from Cart */ 
        add_action('woocommerce_cart_item_removed', [$this, 'iwp_woocommerce_cart_item_removed'], 99, 2);
        
        /* Item Added to Cart */
        add_action('woocommerce_add_to_cart', [$this, 'iwp_woocommerce_add_to_cart'], 99, 6);
        
        /* Cart Emptied */
        add_action('woocommerce_cart_emptied', [$this, 'iwp_woocommerce_cart_emptied'], 99);
        
        /* Reached Cart */
        add_action('woocommerce_before_cart', [$this, 'iwp_woocommerce_before_cart'], 99);
                
        /* Reached Checkout */
        add_action('woocommerce_before_checkout_form', [$this, 'iwp_woocommerce_before_checkout_form'], 99);
        
        /* Checkout Complete */
        add_action('woocommerce_thankyou', [$this, 'iwp_woocommerce_checkout_complete'], 99, 1);
        
        /* Order Status Changed */
        add_action('woocommerce_order_status_changed', [$this, 'iwp_woocommerce_order_status_changed'], 99, 4);
        
        /* Coupon Applied */
        add_action('woocommerce_applied_coupon', [$this, 'iwp_woocommerce_applied_coupon'], 99, 1);
                
        /* Event Filtering */
        
        add_filter('iwp_permitted_events', [$this, 'iwp_wc_permitted_events'], 10, 1);
    }
    
    public function iwp_woocommerce_cart_item_removed($cart_item_key,  $cart) {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $line_item = $cart->removed_cart_contents[ $cart_item_key ];
        
        $product_id = isset($line_item[ 'product_id' ]) ? $line_item[ 'product_id' ] : false;
        
        $product = wc_get_product($product_id);
        
        if($product) {
        
            $metadata = [
                'Product' => $product->get_name(),
                'Price' => $product->get_price(),
            ];
        }
        
        else $meta_data = [];
        
        $intercom->create_event('wc_cart_item_removed', $meta_data);
        
        $tags = apply_filters('iwp_woocommerce_cart_item_removed', [], $user, $cart_item_key, $instance);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_woocommerce_add_to_cart($cart_item_key,  $product_id,  $quantity,  $variation_id,  $variation,  $cart_item_data) {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $product = wc_get_product($product_id);
        
        $metadata = [
            'Product' => $product->get_name(),
            'Price' => $product->get_price(),
        ];
        
        $intercom->create_event('wc_cart_item_added', $metadata);
        
        $tags = apply_filters('iwp_woocommerce_add_to_cart', [], $user, $cart_item_key,  $product_id,  $quantity,  $variation_id,  $variation,  $cart_item_data);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_woocommerce_cart_emptied() {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $intercom->create_event('wc_cart_emptied');
        
        $tags = apply_filters('iwp_woocommerce_cart_emptied', [], $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_woocommerce_before_cart() {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $metadata = [
            'Total' => $this->getCartTotal(),
            'Line Item(s)' => count(WC()->cart->get_cart_contents())
        ];
        
        $intercom->create_event('wc_cart_viewed', $metadata);
        
        $tags = apply_filters('iwp_iwp_woocommerce_before_cart', [], $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_woocommerce_checkout_complete($order_id) {
        
        $order = wc_get_order( $order_id );
    
        if(!$order) return false;
    
        $user = $order->get_user();
        
        if(!$user) return false;
        
        $intercom = new Intercom($user->ID);
        
        $metadata = [
            'Order Total' => $order->get_total(),
            'Line Item(s)' => count($order->get_items()),
            'View Order (Admin)' => $order->get_view_order_url()
        ];
        
        $intercom->create_event('wc_checkout_complete', $metadata);
        
        $tags = apply_filters('iwp_woocommerce_checkout_complete', [], $user, $order_id, $order);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
        /* Update Total Orders */
        
        $args = array(
            'customer' => $user->user_email
        );
        
        $orders = wc_get_orders( $args );
        
        if(!empty($orders)) {
            
            $total_spend = 0;
            
            $total_refund = 0;
            
            foreach($orders as $order) {
                
                $total_spend += $order->get_total();
                
                $total_refund += $order->get_total_refunded();
                
            }
            
            $intercom->update_user(
                ['custom_attributes' => 
                    [
                        'wc_total_orders' => count($orders),
                        'wc_total_spend' => $total_spend,
                        'wc_total_refund' => $total_refund,
                    ]
                ]
            );
        }
    }
    
    public function iwp_woocommerce_order_status_changed($order_id, $old_status, $new_status, $order) {
        
        $order = wc_get_order( $order_id );
        
        if(!$order) return false;
        
        $user = $order->get_user();
        
        if(!$user) return false;
        
        $intercom = new Intercom($user->ID);
        
        $status = $this->format_order_status($new_status);
        
        $event = apply_filters('iwp_woocommerce_order_status_changed_event', 'wc_order_status_'.$status, $user, $order_id, $old_status, $new_status, $order);
        
        $metadata = [
            'New Order Status' => $new_status,
            'Previous Order Status' => $old_status,
            'Order ID' => $order_id,
            'Order Total' => $order->get_total(),
        ];
        
        $intercom->create_event($event, $metadata);
        
        $tags = apply_filters('iwp_woocommerce_order_status_changed_tags', [], $user, $order_id, $old_status, $new_status, $order);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_woocommerce_before_checkout_form() {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $metadata = [
            'Cart Total' => $this->getCartTotal(),
            'Line Item(s)' => count(WC()->cart->get_cart_contents())
        ];
        
        $intercom->create_event('wc_checkout_reached', $metadata);
        
        $tags = apply_filters('iwp_woocommerce_before_checkout_form', [], $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_woocommerce_applied_coupon($code) {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $event = apply_filters('iwp_woocommerce_applied_coupon_event', 'wc_coupon_applied', $user, $code );
        
        $metadata = ['Coupon Code' => $code];
        
        $intercom->create_event($event, $metadata);
        
        $tags = apply_filters('iwp_woocommerce_applied_coupon_tags', [], $user, $code);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_wc_permitted_events($events) {
        
        $iwp_events = array(
            'wc_cart_item_removed', 
            'wc_cart_item_added',
            'wc_cart_emptied',
            'wc_cart_viewed',
            'wc_checkout_complete',
            'wc_checkout_reached',
            'wc_coupon_applied'
        );
        
        /* Order Status */
               
        foreach ( wc_get_order_statuses() as $status ) {
            $iwp_events[] = 'wc_order_status_'.$this->format_order_status($status);
        }
        
        return array_merge($iwp_events, $events);
    }
    
    private function format_order_status($status) {
        
        return str_replace(' ', '-', strtolower($status));

    }
    
    private function getCartTotal() {
                
        if ( ! WC()->cart->prices_include_tax ) {
            $amount = WC()->cart->cart_contents_total;
        } else {
            $amount = WC()->cart->cart_contents_total + WC()->cart->tax_total;
        }
        
        if(is_numeric($amount)) return $amount;
        
        return 0;
    }
}

?>