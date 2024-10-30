<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if ( ! defined( 'ABSPATH' ) ) exit;

class WordPress {
    
    public function __construct()
    {
        
        /* Standard WordPress Actions */
        
        //do_action( 'register_new_user', $user_id );
        add_action('register_new_user', [$this, 'iwp_register_new_user'], 99, 1);

        //do_action( 'user_register', $user_id );
        add_action('user_register', [$this, 'iwp_register_new_user'], 99, 1);
        
        //do_action( 'wp_login', $user->user_login, $user );
        add_action('wp_login', [$this, 'iwp_user_login'], 99, 2);

        //do_action( 'profile_update', $user_id, $old_user_data );
        add_action('profile_update', [$this, 'iwp_profile_update'], 99, 2);
        
        //do_action( 'after_password_reset', $user, $new_pass );
        add_action('after_password_reset', [$this, 'iwp_password_reset'], 99, 2);
        
        //do_action( 'delete_user', $id, $reassign );
        add_action('delete_user', [$this, 'iwp_delete_user'], 99, 2);
        
        //do_action( 'set_user_role', int $user_id, string $role, string[] $old_roles )
        add_action('set_user_role', [$this, 'iwp_set_user_role'], 99, 3);
        
        //do_action( 'clear_auth_cookie' );
        add_action('clear_auth_cookie', [$this, 'iwp_clear_auth_cookie'], 10);
        
        /* Post & Page Tracking */
        add_filter('the_content', [$this, 'iwp_the_content'], 99);
        
        /* Event Filtering */
        add_filter('iwp_permitted_events', [$this, 'iwp_permitted_events'], 10, 1);
    }
    
     /* Plugin Actions */
    
    public function iwp_register_new_user($user_id) {
        
        $user = get_userdata( $user_id );

        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $create_lead = apply_filters('iwp_intercom_user_type', false, $user);
        
        if($create_lead) {
            
            $intercom->create_lead();
        }
        
        else $intercom->create_user();
        
        $intercom->create_event('register_new_user');
        
        $tags = apply_filters('iwp_user_register_tags', [], $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_delete_user($id, $reassign) {
        
        $user = get_userdata( $id );

        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $intercom->create_event('delete_user');
        
        $tags = apply_filters('iwp_delete_user_tags', [], $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_user_login($user_name, $wp_user) {
        
        $intercom = new Intercom($wp_user->ID);
        
        $intercom->create_event('user_login');
        
        $tags = apply_filters('iwp_user_login_tags', [], $wp_user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_clear_auth_cookie() {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $intercom->create_event('user_logout');
        
        $tags = apply_filters('iwp_user_logout_tags', [], $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
    }
    
    public function iwp_profile_update($user_id, $old_user_data) {
        
        $intercom = new Intercom($user_id);
        
        $intercom->create_event('profile_update');
        
    }
    
    public function iwp_password_reset($wp_user, $new_pass) {
        
        $intercom = new Intercom($wp_user->ID);
        
        $intercom->create_event('password_reset');
    }
    
    public function iwp_set_user_role($user_id, $role, $old_roles) {
        
        $user = get_userdata( $user_id );

        if ( ! $user->exists() ) {
           return false;
        }
        
        $intercom = new Intercom($user->ID);
        
        $intercom->create_event('set_user_role');
        
        $tags = apply_filters('iwp_set_user_role_tags', $role, $old_roles, $user);
        
        if(!empty($tags)) {
            
            $intercom->add_tag($tags);
        }
        
    }
    
    public function iwp_the_content($content) {
        
        if(is_admin()) return $content;
        
        $options = get_option('intercom_wp_settings');
        
        $enabled = isset($options['enable_post_page_tracking']) ? $options['enable_post_page_tracking'] : false; 
        
        if(!$enabled) return $content; 
            
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
           return $content;
        }
        
        $intercom = new Intercom($user->ID);
        
        $metadata = ['url' => get_page_link()];
        
        /* Viewed Post */
        if (is_single()) {
         
            $event = apply_filters('iwp_the_content_post', 'user_viewed_post', $user, get_the_ID());
                
            $intercom->create_event($event, $metadata);
        }
        
        /* Viewed Page */
        if (is_page()) {
            
            $event = apply_filters('iwp_the_content_page', 'user_viewed_page', $user, get_the_ID());
            
            $intercom->create_event($event, $metadata);
            
        }
        
        return $content;
    }

    public function iwp_permitted_events($events) {
        
        $iwp_events = array(
            'user_login', 
            'user_register', 
            'user_deleted',
            'profile_update', 
            'password_reset',
            'set_user_role',
            'user_logout',
            'user_viewed_page',
            'user_viewed_post'
        );
        
        return array_merge($iwp_events, $events);
    }
    
}
?>