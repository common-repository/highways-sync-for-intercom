<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin {
    
    /**
     * Extension constructor.
     */
       
    public $webhooks;
    
    public function __construct()
    {
        
        self::includes();
        
        /* Admin Pages */
        
        add_action( 'admin_menu', [$this, 'intercom_wp_settings_page'] );
        
        add_action( 'admin_init', [$this, 'intercom_wp_settings_init'] );
        
    }
    
    public function includes()
    {
        /* WordPress */
        
        require_once INTERCOM_WP_PATH . '/includes/wordpress.php';
        
        $wp = new \Intercom_WP\WordPress;
        
        /* WooCommerce */
        
        if ( $this->iwp_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

            require_once INTERCOM_WP_PATH . '/includes/woocommerce.php';
            
            $wc = new \Intercom_WP\WooCommerce;
            
        }
        
        /* WooCommerce Subscriptions */
        if ( $this->iwp_is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {

            require_once INTERCOM_WP_PATH . '/includes/woocommerce-subscriptions.php';
            
            $wcs = new \Intercom_WP\WooCommerce_Subscriptions;
            
        }
        
        /* WooCommerce Deposits */
        if ( $this->iwp_is_plugin_active( 'woocommerce-deposits/woocommerce-deposits.php' ) ) {

            require_once INTERCOM_WP_PATH . '/includes/woocommerce-deposits.php';
            
            $wcd = new \Intercom_WP\WooCommerce_Deposits;
            
        }
        
        /* WooCommerce Bookings */
        
        if ( $this->iwp_is_plugin_active( 'woocommerce-bookings/woocommerce-bookings.php' ) ) {

            require_once INTERCOM_WP_PATH . '/includes/woocommerce-bookings.php';
            
            $wcb = new \Intercom_WP\WooCommerce_Bookings;
            
        }
        
        /* WooCommerce Shipments */
        
        if ( $this->iwp_is_plugin_active( 'woocommerce-shipment-tracking/woocommerce-shipment-tracking.php' ) ) {

            require_once INTERCOM_WP_PATH . '/includes/woocommerce-shipment-tracking.php';
        
            $wst = new \Intercom_WP\WooCommerce_Shipment_Tracking;
        }
        
        /* Intercom SDK */

        require_once INTERCOM_WP_PATH . '/includes/intercom.php';
        
        $intercom = new \Intercom_WP\Intercom;
        
        /* Webhooks */

        require_once INTERCOM_WP_PATH . '/includes/webhooks.php';
        
        $this->webhooks = new \Intercom_WP\Webhooks;
        
        /* Chat Bubble */

        require_once INTERCOM_WP_PATH . '/includes/chat-bubble.php';
        
        $chat = new \Intercom_WP\Chat_Bubble;
        
        /* Action Scheduler */
        //Removed in 1.1.1
    }
    
    /* Admin Pages */
    
    public function intercom_wp_settings_page() {
        add_options_page( 'Intercom Sync', 'Intercom Sync', 'manage_options', 'intercom-wp', [$this, 'intercom_wp_options_page'] );
    }
    
    public function intercom_wp_settings_init(  ) {
        
        register_setting( 'intercomWP', 'intercom_wp_settings' );
        
        add_settings_section(
        'intercomWP_section',
        __( 'Intercom WP Settings', INTERCOM_WP_TEXT_DOMAIN ),
        [$this, 'intercom_wp_settings_section_callback'],
        'intercomWP'
        );
        
        add_settings_field(
        'intercom_wp_connect_btn',
        __( 'Connect With Intercom', INTERCOM_WP_TEXT_DOMAIN ),
        [$this->webhooks, 'connect_url'],
        'intercomWP',
        'intercomWP_section'
        );
        
        add_settings_field(
        'intercom_wp_intercom_api_key',
        __( 'Intercom API Key', INTERCOM_WP_TEXT_DOMAIN ),
        [$this, 'intercom_wp_render_field'],
        'intercomWP',
        'intercomWP_section',
        array('intercom_wp_intercom_api_key', 'XXXXOjA4ZmJiODkwXzJmMzRfNGYXXXXiM2Q0X2QzMDk4MGJmOTBkMToXXXX=')
        );  
        
        /* Check for official Intercom Plugin options */
        
        $app_id = '';
        $secret = '';
        
        $intercom_options = get_option('intercom');
        
        if(is_array($intercom_options)) {
            
            $options = get_option( 'intercom_wp_settings' );
            
            if(!$options || !is_array($options)) {
                    
                $options = [];
            }
            
            $app_id = isset($intercom_options['app_id']) ? $intercom_options['app_id'] : false;
            
            if($app_id) {
                
                $options['intercom_wp_intercom_app_id'] = $app_id;
            }
            
            $secret = isset($intercom_options['secret']) ? $intercom_options['secret'] : false;
            
            if($secret) {
                
                $options['intercom_wp_intercom_secret'] = $secret;
            }
            
            update_option('intercom_wp_settings', $options);
        }
        
        add_settings_field(
        'intercom_wp_intercom_app_id',
        __( 'Intercom App ID', INTERCOM_WP_TEXT_DOMAIN ),
        [$this, 'intercom_wp_render_field'],
        'intercomWP',
        'intercomWP_section',
        array('intercom_wp_intercom_app_id', 'abcd123')
        );
        
        add_settings_field(
        'intercom_wp_intercom_secret',
        __( 'Intercom Verification Secret', INTERCOM_WP_TEXT_DOMAIN ),
        [$this, 'intercom_wp_render_field'],
        'intercomWP',
        'intercomWP_section',
        array('intercom_wp_intercom_secret', 'xxxxBMbJEcSstXXXXfZdmLoSgMeXXXXyJuXXXXX')
        );
        
        /* End official Intercom Plugin options */
        
        add_settings_field('enable_chat_bubble', 'Enable Chat Bubble', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'enable_chat_bubble']);
        
        add_settings_field('enable_web_verification', 'Enable User Verification (Recommended)', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'enable_web_verification']);
        
        add_settings_field('enable_sync_calls', 'Enable Synchronous Calls', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'enable_sync_calls']);

        add_settings_field('enable_logging_calls', 'Enable Intercom Logging', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'enable_logging_calls']);
        
        add_settings_field('enable_post_page_tracking', 'Enable Post & Page Tracking', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'enable_post_page_tracking']);
        
        add_settings_field('disable_user_creation', 'Disable User Creation', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'disable_user_creation']);
        
        add_settings_field('disable_lead_creation', 'Disable Lead Creation', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'disable_lead_creation']);
        
        add_settings_field('disable_intercom_updates', 'Disable User/Lead Updates', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'disable_intercom_updates']);
        
        add_settings_field('disable_events', 'Disable Events', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'disable_events']);
        
        add_settings_field('disable_tags', 'Disable Tags', [$this, 'intercom_wp_checkbox'], 'intercomWP', 'intercomWP_section', ['field' => 'disable_tags']);
    }
    
    public function intercom_wp_settings_section_callback(  ) {
        
        echo __( 'You can connect via Intercom (Recommended for most users) or create your own Intercom Application (Recommended for Developers Only). <strong>You do not need to enter an API key if you connect via Intercom, and vice-versa.</strong>', INTERCOM_WP_TEXT_DOMAIN );
    }
    
    public function intercom_wp_render_field($args) {
          
        $field = $args[0];
            
        $placeholder = isset($args[1]) ? $args[1] : '';
   
        $options = get_option( 'intercom_wp_settings' );
            
        $value = isset($options[$field]) ? $options[$field] : '';
        
        ?>
    
        <input type='text' size='50' placeholder='<?php echo $placeholder; ?>' name='intercom_wp_settings[<?php echo $field; ?>]' value='<?php echo $value; ?>'>
        
        <?php
        
    }
    
    public function intercom_wp_options_page(  ) {
    ?>
    <form action='options.php' method='post'>

        <h2>Settings</h2>

        <?php
            
        settings_fields( 'intercomWP' );
        
        do_settings_sections( 'intercomWP' );
        
        submit_button();
        
        ?>

    </form>
    <?php
    }
    
    public function intercom_wp_checkbox($args) {
        
        $option = isset($args['field']) ? $args['field'] : false;
	   
        $options = get_option('intercom_wp_settings');
        
        $checked = '';
	   
        if(isset($options[$option])) { 
            $checked = ' checked="checked" '; 
        }
	   
        echo "<input ".$checked." id='".$option."' name='intercom_wp_settings[".$option."]' type='checkbox' />";
    }
    
    public function iwp_is_plugin_active( $plugin ) {
        
        return in_array( $plugin, (array) get_option( 'active_plugins', array() 
         ) 
      );

    }
    
   
    
    
}