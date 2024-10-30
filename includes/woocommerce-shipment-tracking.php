<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Shipment_Tracking {
    
    public function __construct()
    {
        
        $enabled = apply_filters('iwp_track_ship_enabled', true);
        
        if($enabled) {
            
            /* Add Rest Routes */
            add_action('rest_api_init', [$this, 'iwp_woocommerce_shipment_rest'], 10, 2);
            
        }
          
    }
    
    public function iwp_woocommerce_shipment_rest() {
        
        /* Initialize */
        register_rest_route( 'iwp/shipment-tracking/v1', '/initialize', array(
            'methods' => 'POST',
            'callback' => [$this, 'iwp_initialize']
        ) );
        
        /* Submit */
        register_rest_route( 'iwp/shipment-tracking/v1', '/submit', array(
            'methods' => 'POST',
            'callback' => [$this, 'iwp_submit']
        ) );
         
    }
    
    private function iwp_canvas_payload($components) {
        
        $canvas = ['canvas' => ['content' => ['components' => $components]]];
        
        return apply_filters('iwp_canvas_payload', $canvas);
    }
    
    public function iwp_initialize() {
        
        $components = [];
        
        $components[] = ['type' => 'button', 'id' => 'track_wc_order_btn', 'style' => 'primary', 'label' => __( 'Track My Order', 'intercom-wp' ), 'action' => ['type' => 'submit']];
        
        $components = apply_filters('iwp_initialize_components', $components);
        
        wp_send_json($this->iwp_canvas_payload($components));    
    }
    
    public function iwp_submit() {
        
        try {
                
            $payload = json_decode(file_get_contents('php://input'));
            
            if(isset($payload->component_id)) {
                
                $component_id = $payload->component_id;
                
                if($component_id == 'track_wc_order_btn') {
                    
                    //Show Tracking Info Form
                    
                    wp_send_json($this->iwp_track_order_form(), 200);
                    
                }
                
                if($component_id == 'track_wc_order_btn_execute') {
                    
                    //Get Tracking Info
                    
                    wp_send_json($this->iwp_track_order($payload), 200);
                    
                }
                
            }
        }
        
        catch(\Exception $e) {
            
            error_log($e->getMessage());
        }
        
        $this->iwp_initialize();
        
    }
    
    public function iwp_track_order_form($errors = false) {
        
        $components = [];
        
        $save_state = 'unsaved';
        
        if($errors) {
            
            $components[] = ['type' => 'text', 'text' => $errors, 'style' => 'paragraph', 'id' => 'tracking_form_errors'];
            
            $save_state = 'failed';
        }
        
        $components[] = ['type' => 'input', 'id' => 'track_wc_order_no', 'label' => __( 'Order Number.', 'intercom-wp' ), 'placeholder' => __( 'Order Number.', 'intercom-wp' ), 'save_state' => $save_state];
        
        $components[] = ['type' => 'input', 'id' => 'track_wc_track_no', 'label' => __( 'Tracking Number.', 'intercom-wp' ), 'placeholder' => __( 'Tracking Number.', 'intercom-wp' ), 'save_state' => $save_state];
        
        $components[] = ['type' => 'button', 'id' => 'track_wc_order_btn_execute', 'style' => 'primary', 'label' => __( 'Track', 'intercom-wp' ), 'action' => ['type' => 'submit']];
        
        $components[] = ['type' => 'button', 'id' => 'track_wc_order_btn_back', 'style' => 'secondary', 'label' => __( 'Back', 'intercom-wp' ), 'action' => ['type' => 'submit']];
        
        $components = apply_filters('iwp_track_order_form_components', $components);
        
        return $this->iwp_canvas_payload($components);
        
    }
    
    public function iwp_track_order($payload) {
        
        $inputValues = $payload->input_values;
        
        $orderNumber = isset($inputValues->track_wc_order_no) ? $inputValues->track_wc_order_no : false;
        
        $trackingNumber = isset($inputValues->track_wc_track_no) ? $inputValues->track_wc_track_no : false;
        
        if(!$orderNumber || !$trackingNumber) {
            
            return $this->iwp_track_order_form(__( 'Please enter a valid order and tracking number to track this shipment.', 'intercom-wp' ));
        }
        
        $orderNumber = intval($orderNumber);
            
        $st             = \WC_Shipment_Tracking_Actions::get_instance();
		
        $items = $st->get_tracking_items( $orderNumber, true );
        
        if(!is_array($items)) {
            
            return $this->iwp_track_order_form(__( 'No item was found for this Order. Please contact us for further assistance.', 'intercom-wp' ));
        }
        
        $items = (object) reset($items);
        
        $trackingNumber = strtolower(trim($trackingNumber));
        
        if(strtolower($items->tracking_number) != $trackingNumber) {
            
            return $this->iwp_track_order_form(__( 'The tracking number you provided is invalid.', 'intercom-wp' ));
        }
        
        /* Shipping Provider */
        $trackingProvider = isset($items->formatted_tracking_provider) ? $items->formatted_tracking_provider : false;
        
        $trackingProvider = apply_filters('iwp_track_ship_provider', $trackingProvider);
        
        /* Tracking Number */
        $tracking_number = isset($items->tracking_number) ? $items->tracking_number : false;
        
        $tracking_number = apply_filters('iwp_track_ship_number', $tracking_number);
        
        /* Ship Date */
        $shipDate = false;
        
        if(isset($items->date_shipped)) {
            
            $format = apply_filters('iwp_track_ship_date_format', 'jS F, Y');
            
            $shipDate = date($format, $items->date_shipped);
        }
        
        $shipDate = apply_filters('iwp_track_ship_date', $trackingLink);
        
        /* Tracking Link */
        $trackingLink = false;
        
        if(isset($items->formatted_tracking_link)) {
            
            if(filter_var($items->formatted_tracking_link, FILTER_VALIDATE_URL)) {
                
                $trackingLink = $items->formatted_tracking_link;
                
            }
        }
        
        $trackingLink = apply_filters('iwp_track_ship_link', $trackingLink);
        
        $components = [];
        
        if($trackingProvider) $components[] = ['type' => 'text', 'text' => __( 'Shipped By: ', 'intercom-wp' ).$trackingProvider, 'style' => 'paragraph', 'id' => 'track_wc_tracking_provider'];
        
        if($tracking_number) $components[] = ['type' => 'text', 'text' =>__( 'Tracking Number: ', 'intercom-wp' ).$tracking_number, 'style' => 'paragraph', 'id' => 'track_wc_tracking_number'];
        
        if($shipDate) $components[] = ['type' => 'text', 'text' => __( 'Shipped On: ', 'intercom-wp' ).$shipDate, 'style' => 'paragraph', 'id' => 'track_wc_ship_date'];
        
        if($trackingLink) $components[] = ['type' => 'button', 'id' => 'track_wc_order_link', 'style' => 'primary', 'label' => __( 'Track Online', 'intercom-wp' ), 'action' => ['type' => 'url', 'url' => $trackingLink]];
        
        $components[] = ['type' => 'button', 'id' => 'track_wc_order_btn_back', 'style' => 'secondary', 'label' => __( 'Back', 'intercom-wp' ), 'action' => ['type' => 'submit']];
        
        $components = apply_filters('iwp_track_order_components', $components);
        
        return $this->iwp_canvas_payload($components);
        
    }
    
}

?>