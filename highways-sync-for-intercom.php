<?php
/*
 * Plugin Name: Highways Sync for Intercom
 * Plugin URI:  https://www.highways.io/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Description: A complete WordPress to Intercom Sync Plugin
 * Version: 1.1.6
 * Author: Highways.io
 * Author URI: https://www.highways.io/?utm_source=wp-plugins&utm_campaign=intercom-wp&utm_medium=wp-dash
 * Text Domain: intercom-wp
 * Requires at least: 5.1
 * Tested up to: 5.7
 * WC requires at least: 4.0
 * WC tested up to: 5.0
 * Requires PHP: 7.1
 *
 * WordPress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * WordPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'INTERCOM_WP_VERSION', '1.1.6' );
define( 'INTERCOM_WP_PREVIOUS_STABLE_VERSION', '1.1.5' );
define( 'INTERCOM_WP_NAME', 'Highways Sync for Intercom' ); 
define( 'INTERCOM_WP__FILE__', __FILE__ );
define( 'INTERCOM_WP_PLUGIN_BASE', plugin_basename( INTERCOM_WP__FILE__ ) );
define( 'INTERCOM_WP_PATH', plugin_dir_path( INTERCOM_WP__FILE__ ) );

define( 'INTERCOM_WP_URL', plugins_url( '/', INTERCOM_WP__FILE__ ) );

define( 'INTERCOM_WP_ASSETS_PATH', INTERCOM_WP_PATH . 'assets/' );
define( 'INTERCOM_WP_ASSETS_URL', INTERCOM_WP_URL . 'assets/' );



add_action( 'plugins_loaded', function (){
    load_plugin_textdomain( INTERCOM_WP_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );
} );

define( 'INTERCOM_WP_TEXT_DOMAIN', 'intercom-wp' );

if ( ! version_compare( PHP_VERSION, '7.1', '>=' ) ) {
    add_action( 'admin_notices', function(){
        $message = sprintf( esc_html__( '%s requires PHP version %s+, plugin is currently NOT RUNNING.', INTERCOM_WP_TEXT_DOMAIN ), INTERCOM_WP_NAME, '7.1' );
        $html_message = sprintf( '<div class="notice notice-error">%s</div>', wpautop( $message ) );
        echo wp_kses_post( $html_message );
    } );
} 

elseif ( ! version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) {
    add_action( 'admin_notices', function (){
        $message = sprintf( esc_html__( '%s requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', INTERCOM_WP_TEXT_DOMAIN ), INTERCOM_WP_NAME, '4.9' );
        $html_message = sprintf( '<div class="notice notice-error">%s</div>', wpautop( $message ) );
        echo wp_kses_post( $html_message );
    } );
        
} 
else {

    //We are ready to rock
    include_once( INTERCOM_WP_PATH . '/includes/plugin.php' );
     
    $iwp = new \Intercom_WP\Plugin;
    
}
