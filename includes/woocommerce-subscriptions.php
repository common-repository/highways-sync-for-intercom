<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if (!defined('ABSPATH')) exit;

class WooCommerce_Subscriptions
{

    public function __construct()
    {

        /* Subscription Cancelled or Pending Cancellation */
        add_action('woocommerce_subscription_status_updated', [$this, 'iwp_woocommerce_subscription_status_updated'], 99, 3);

        /* Subscription Paused */
        add_action('woocommerce_subscription_status_on-hold', [$this, 'iwp_woocommerce_subscription_status_paused'], 99, 1);

        /* Subscription Created via Checkout */
        add_action('woocommerce_checkout_subscription_created', [$this, 'iwp_woocommerce_checkout_subscription_created'], 99, 3);

        /* Subscription Renewed */
        add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'iwp_woocommerce_subscription_renewal_payment_complete'], 99, 2);

        /* Subscription Trial Ended */
        add_action('woocommerce_scheduled_subscription_trial_end', [$this, 'iwp_woocommerce_scheduled_subscription_trial_end'], 99, 1);

        /* Event Filtering */

        add_filter('iwp_permitted_events', [$this, 'iwp_wcs_permitted_events'], 10, 1);
    }


    public function iwp_woocommerce_subscription_status_updated($subscription, $new_status, $old_status)
    {

        $orders_ids = $subscription->get_related_orders();

        if (empty($orders_ids)) return;

        reset($orders_ids);

        $last_order_id = key($orders_ids);

        $order = wc_get_order($last_order_id);

        if (!$order || empty($order)) return;

        $user = $order->get_user();

        if (!$user) return false;

        $intercom = new Intercom($user->ID);

        if ($new_status == 'cancelled') {
            $status = 'cancelled';
        }

        if ($new_status == 'pending-cancel') {
            $status = 'pending_cancellation';
        }

        $status = apply_filters('iwp_woocommerce_subscription_status_updated', $status, $subscription, $new_status, $old_status);

        $intercom->create_event('wc_subscription_' . $status, ['Last Order ID' => $last_order_id]);

        $tags = apply_filters('iwp_woocommerce_subscription_cancelled', [], $user, $subscription, $new_status, $old_status);

        if (!empty($tags)) {

            $intercom->add_tag($tags);
        }

    }

    public function iwp_woocommerce_subscription_status_paused($subscription)
    {

        $orders_ids = $subscription->get_related_orders();

        if (empty($orders_ids)) return;

        reset($orders_ids);

        $last_order_id = key($orders_ids);

        $order = wc_get_order($last_order_id);

        if (!$order || empty($order)) return;

        $user = $order->get_user();

        if (!$user) return false;

        $intercom = new Intercom($user->ID);

        $intercom->create_event('wc_subscription_paused');

        $tags = apply_filters('iwp_woocommerce_subscription_paused', [], $user, $subscription);

        if (!empty($tags)) {

            $intercom->add_tag($tags);
        }

    }

    public function iwp_woocommerce_checkout_subscription_created($subscription, $order, $recurring_cart)
    {

        $user = $order->get_user();

        if (!$user) return false;

        $intercom = new Intercom($user->ID);

        $metadata = [
            'Order ID' => $order->get_id(),
            'Order Total' => $order->get_total(),
            'View Order (Admin)' => $order->get_view_order_url(),
            'Initial Payment' => $subscription->get_total_initial_payment(),
            'Ongoing Payment' => $subscription->get_total(),
            'Billing Interval' => $subscription->get_billing_interval(),
            'Billing Period' => $subscription->get_billing_period()
        ];

        $intercom->create_event('wc_subscription_purchased', $metadata);

        $tags = apply_filters('iwp_wc_subscription_purchased', [], $user, $subscription, $order, $recurring_cart);

        if (!empty($tags)) {

            $intercom->add_tag($tags);
        }

    }

    public function iwp_woocommerce_subscription_renewal_payment_complete($subscription, $last_order)
    {

        $user = $last_order->get_user();

        if (!$user) return false;

        $intercom = new Intercom($user->ID);

        $metadata = [
            'Last Order ID' => $last_order->get_id(),
            'Order Total' => $last_order->get_total(),
            'View Order (Admin)' => $last_order->get_view_order_url()
        ];

        $intercom->create_event('wc_subscription_renewed', $metadata);

        $tags = apply_filters('iwp_wc_subscription_renewed', [], $user, $subscription, $last_order);

        if (!empty($tags)) {

            $intercom->add_tag($tags);
        }

    }

    public function iwp_woocommerce_scheduled_subscription_trial_end($subscription_id)
    {

        $subscription = wcs_get_subscription($subscription_id);

        $orders_ids = $subscription->get_related_orders();

        if (empty($orders_ids)) return;

        reset($orders_ids);

        $last_order_id = key($orders_ids);

        $order = wc_get_order($last_order_id);

        if (!$order || empty($order)) return;

        $user = $order->get_user();

        if (!$user) return false;

        $intercom = new Intercom($user->ID);

        $intercom->create_event('wc_subscription_trial_ended');

        $tags = apply_filters('iwp_woocommerce_scheduled_subscription_trial_end', [], $user, $subscription_id);

        if (!empty($tags)) {

            $intercom->add_tag($tags);
        }
    }

    public function iwp_wcs_permitted_events($events)
    {

        $iwp_events = array(
            'wc_subscription_cancelled',
            'wc_subscription_pending_cancellation',
            'wc_subscription_paused',
            'wc_subscription_purchased',
            'wc_subscription_renewed',
            'wc_subscription_trial_ended'
        );

        return array_merge($iwp_events, $events);
    }

}

?>
