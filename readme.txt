=== Highways Sync for Intercom ===
Contributors: highways
Tags: intercom, crm, sync, automation, email marketing
Donate link: https://www.highways.io
Requires at least: 5.1
Tested up to: 5.7
Requires PHP: 7.1
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.md

A complete WordPress to Intercom Sync Plugin. Fully compatible with all major WooCommerce extensions.

== Description ==

Welcome to the easiest way to keep your WordPress and Intercom instances in perfect sync.

**Zero set up** meaning you only need to install and connect the plugin to Intercom, the rest is taken care of automatically.

With Intercom Sync by Highways you can ensure that as users, leads and customers are created in your WordPress site they are transported to Intercom with all relevant data fields.

Intercom Sync by Highways was designed to be developer friendly meaning we've included a host of actions and filters for you to work with. Missing a key piece of functionality? Contact us and we'll add it in our next release!

### HIGHWAYS SYNC FOR INTERCOM CAN:

* Create Users & Leads from WordPress Users
* Trigger Events from WordPress & WooCommerce Actions
* Sync WooCommerce Events like Orders, Subscriptions, Payments & Coupons
* Transfer WooCommerce Subscription, Deposit and Booking events to Intercom
* And so much more...

### HIGHWAYS SYNC INCLUDES THE FOLLOWING EVENTS & TRIGGERS:

* **New User Registration** — Create & Tag WordPress Users in Intercom as they are created in WordPress
* **User Login** — Track Login Events by WordPress users in Intercom
* **Profile Update** — Track WordPress Profile update events in Intercom
* **Password Reset** — Track Password Reset  events in Intercom
* **User Role Change** — Tag & Track User Roles in Intercom

### HIGHWAYS SYNC IS WOOCOMMERCE COMPATIBLE:

* **Cart Item Removed or Emptied** — Trigger events for cart items being removed or the WooCommerce cart being emptied
* **Payment Complete** — Trigger events for completed payments
* **Checkout Reached** — Track users as they reach the checkout to reduce abandonment
* **Coupon Applied** — Trigger events when coupons are applied
* **Subscription Created** — Track users as they create or update WooCommerce Subscriptions
* **Subscription Trials** — View user events for WooCommerce Subscription Trials
* **Deposits & Payment Plans** — Track users that have paid by deposit or started payment plans in WooCommerce Deposits.
* **Bookings** — Track users that have made, cancelled or completed.

### ABOUT US

We built our Intercom Sync plugin as a complimentary and free plugin for the users of our Highways suite of apps. Highways creates and crafts the best interconnectors between Intercom and world leading platforms such as Pipedrive, SalesLoft and HubSpot.

== Screenshots ==
1. Easy to use settings and full control over what is synced to Intercom
2. Simple installation via secure Intercom OAuth or direct API Key entry
3. Clear and concise meta data for each event including WooCommerce Cart & Order data

== Installation ==
= Plugin Repo =

* Install
* Activate
* Connect via Intercom
* Syncing starts automatically

= FTP =

* Upload the zip archive
* unzip
* activate through the plugins manager
* Connect via Intercom
* Syncing starts automatically

== Frequently Asked Questions ==

= Do I need an Intercom or Highways account? =
You will require an Intercom account to install and use our software. You do not require a Highways account if you would prefer to install and use our sofware in developer mode.

= Are changed in Intercom pushed to WordPress? =
At present, this plugin only syncs in one direction from WordPress to Intercom. We recommend you contact us if you have a requirement to sync in the other direction, that is from Intercom to WordPress.

= Is there a fee for using this software? =
No, this plugin is completely free to use.

= Does this connect to Woocommerce or other plugins? =
Yes, this plugin is fully compatible with WooCommerce, WooCommerce Subscriptions, WooCommerce Deposits and WooCommerce Bookings.

= Does Intercom Sync work with my theme? =
Intercom Sync is theme agnostic, meaning it should work with every and any theme. 

= Is Intercom Sync secure? =
We believe in security first and adhere to all best practises. Our plugin is also open source and we respond to any feedback promptly.

== Changelog ==

= 1.1.6 =
* FIX: Refactored WC Subscriptions

= 1.1.5 =
* TWEAK: Tested to WordPress 5.7 and WooCommerce 5.0
* FIX: Added JSON error messages for Intercom OAuth / Webhooks

= 1.1.4 =
* TWEAK: Tested to WordPress 5.6 and WooCommerce 4.9.1

= 1.1.3 =
* TWEAK: Minor refactoring

= 1.1.2 =
* FIX: Minor bug fixes
* FIX: Added PHP requirement and testing versions

= 1.1.1 =
* FIX: Removed Action Schedule Library
* FIX: Added REST API call back for permission_callback requirement in WP 5.5.0

= 1.1.0 =
* FIX: Added Intercom API Version

= 1.0.9 =
* TWEAK: Lower priority for WP filters to avoid potential conflicts with other plugins.

= 1.0.8 =
* FIX: Added new filter for user registration during WooCommerce Checkout
* REMOVED: Geo Location no longer supported by WooCommerce or Intercom

= 1.0.7 =
* FIX: Settings removed/overwritten in intercom.php
* Added: Ability to log all Intercom calls for verbose debugging

= 1.0.6 =
* Added: Updated Action Scheduler to 3.1.6
* Added: Logging to WooCommerce Logs (If installed and available)

= 1.0.5 =
* FIX: Bug Fix for Chat Bubble (Logged Out Users)

= 1.0.4 =
* FIX: Bug Fixes for WP_User in WordPress Class

= 1.0.3 =
* FIX: Various Bug Fixes
* Added initial WC Shipment Tracking Support for Intercom Messenger

= 1.0.2 =
* FIX: Return $content for the_content when no WP User found 
* FIX: Updated WooCommerce Cart counting
* CREDIT: Thank you James W for feedback and suggestions

= 1.0.1 =
* Added Intercom chat bubble functionality
* Added User Verification for web chat

= 1.0 =
* First Commit

== Upgrade Notice ==
* None
