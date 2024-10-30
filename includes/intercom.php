<?php

namespace Intercom_WP;

use Intercom\IntercomClient;

if (!defined('ABSPATH')) exit;

class Intercom
{
    const API_VERSION = 1.4;

    const API_KEY_OPTION = 'intercom_wp_intercom_api_key';

    public $user_id, $wp_user, $client, $options;

    public function __construct($user_id = false)
    {
        $this->includes();

        $this->user_id = $user_id;

        if ($this->user_id) {

            $this->wp_user = get_user_by('id', $this->user_id);

        }

        $this->options = [];

        $this->client = self::intercom_client();

        /* Intercom Hook */
        add_action('iwp_send_to_intercom', [$this, 'iwp_send_to_intercom']);
    }

    public function includes()
    {
        /* Intercom PHP SDK */

        require INTERCOM_WP_PATH . '/includes/lib/intercom-php/vendor/autoload.php';

    }

    public function logMe($message)
    {
        if(!is_string($message)) $message = print_r($message);

        if (function_exists('wc_get_logger')) {

            $logger = wc_get_logger();

            $logger->info($message, array('source' => 'highways-sync-for-intercom'));
        } else error_log($message);
    }

    private function intercom_client()
    {

        try {

            $this->options = get_option('intercom_wp_settings');

            if (empty($this->options)) {

                $this->options = [];

            }

            if (isset($this->options[self::API_KEY_OPTION])) {

                $api_key = $this->options[self::API_KEY_OPTION];
            } else $api_key = false;

            if (empty($api_key)) throw new \Exception('Intercom API key is missing. Please reconnect Intercom or check your API Key is entered correctly.');

            return new IntercomClient($api_key, null, ['Intercom-Version' => apply_filters('iwp_api_version', 1.4)]);

        } catch (\Exception $e) {

            $this->logMe($e->getMessage());
        }

    }

    public function iwp_send_to_intercom($key)
    {

        $payload = get_transient($key);

        if (is_array($payload) && $this->client) {

            delete_transient($key);

            $intercom = new Intercom;

            $type = $payload['type'];

            unset($payload['type']);

            $method = $payload['method'];

            unset($payload['method']);

            try {

                $response = $this->client->{$type}->{$method}($payload);

            } catch (\Http\Client\Exception $e) {

                $this->logMe('Intercom Response Exception:: ' . $e->getMessage());
            } catch (\Exception $e) {

                $this->logMe('Intercom Response Exception:: ' . $e->getMessage());
            }

        }
    }

    public function schedule_intercom_call($payload)
    {

        $logging = isset($this->options['enable_logging_calls']) ? $this->options['enable_logging_calls'] : false;

        if($logging) $this->logMe('Intercom Payload:: ' . print_r($payload, true));

        $key = self::create_transient_id($payload);

        $sync = isset($this->options['enable_sync_calls']) ? $this->options['enable_sync_calls'] : false;

        if (set_transient($key, $payload, DAY_IN_SECONDS)) {

            if($logging) $this->logMe('Transient Key:: ' . $key);

            if ($sync) {

                if($logging) $this->logMe('Intercom called Synchronously');

                $this->iwp_send_to_intercom($key);
            }

            else {

                if (function_exists('as_schedule_single_action')) {

                    if($logging) $this->logMe('Intercom via Action Scheduler');

                    as_schedule_single_action(time() + 5, 'iwp_send_to_intercom', array($key));

                } else {

                    if($logging) $this->logMe('Intercom via WP Scheduler');

                    wp_schedule_single_event(time() + 5, 'iwp_send_to_intercom', array($key));
                }


            }
        }

    }

    public function create_user()
    {

        $disabled = isset($this->options['disable_user_creation']) ? $this->options['disable_user_creation'] : false;

        if ($disabled) return;

        if (self::API_VERSION > 1.4) {

            $this->create_contact();

            return;
        }

        $payload = array(
            'method' => 'update',
            'type' => 'users',
            'name' => $this->wp_user->user_firstname . ' ' . $this->wp_user->user_lastname,
            'last_request_at' => time()
        );

        $payload = apply_filters('iwp_create_user', $payload, $this->user_id);

        $payload = self::add_user_data($payload);

        $this->schedule_intercom_call($payload);

    }

    public function create_lead()
    {

        $disabled = isset($this->options['disable_lead_creation']) ? $this->options['disable_lead_creation'] : false;

        if ($disabled) return;

        if (self::API_VERSION > 1.4) {

            $this->create_contact();

            return;
        }

        $payload = array(
            'method' => 'update',
            'type' => 'leads',
            'name' => $this->wp_user->user_firstname . ' ' . $this->wp_user->user_lastname,
            'last_request_at' => time()
        );

        $payload = apply_filters('iwp_create_lead', $payload, $this->user_id);

        $payload = self::add_user_data($payload);

        $this->schedule_intercom_call($payload);

    }

    public function create_contact()
    {

        $payload = array(
            'method' => 'create',
            'type' => 'contacts',
            'name' => $this->wp_user->user_firstname . ' ' . $this->wp_user->user_lastname,
            'role' => apply_filters('iwp_create_contact_role', 'user', $this->user_id),
            'last_request_at' => time()
        );

        $payload = apply_filters('iwp_create_contact', $payload, $this->user_id);

        $payload = self::add_user_data($payload);

        $this->schedule_intercom_call($payload);
    }

    public function update_user($update = [])
    {

        $disabled = isset($this->options['disable_intercom_updates']) ? $this->options['disable_intercom_updates'] : false;

        if ($disabled) return;

        $type = apply_filters('iwp_update_type', 'users', $this->user_id);

        $payload = array(
            'method' => 'update',
            'type' => $type,
            'last_request_at' => time()
        );

        $payload = self::add_user_data($payload);

        $payload = array_merge($payload, $update);

        $this->schedule_intercom_call($payload);

    }

    public function create_event($event, $metadata = [])
    {

        $disabled = isset($this->options['disable_events']) ? $this->options['disable_events'] : false;

        if ($disabled) return;

        if (self::is_event_permitted($event)) {

            $prefix = apply_filters('iwp_event_prefix', 'wp_');

            $event = $prefix . $event;

            $payload = array(
                'event_name' => $event,
                'created_at' => time(),
                'method' => 'create',
                'type' => 'events',
            );

            if (!empty($metadata)) {

                $payload['metadata'] = $metadata;
            }

            $payload = self::add_user_data($payload);

            $this->schedule_intercom_call($payload);
        }

    }

    public function add_tag($tags)
    {

        $disabled = isset($this->options['disable_tags']) ? $this->options['disable_tags'] : false;

        if ($disabled) return;

        $tags = apply_filters('iwp_add_tag', $tags, $this->user_id);

        if (is_array($tags)) {

            foreach ($tags as $tag) {

                $payload = [];
                $payload['name'] = $tag;
                $payload['method'] = 'tag';
                $payload['type'] = 'tags';

                $payload['user'][] = self::add_user_data([]);

                $this->schedule_intercom_call($payload);

            }
        } else {

            $payload = [];
            $payload['name'] = $tags;
            $payload['method'] = 'tag';
            $payload['type'] = 'tags';
            $payload['user'][] = self::add_user_data([]);

            $this->schedule_intercom_call($payload);

        }

    }

    private function create_transient_id($payload)
    {

        return 'iwp_transient_' . sha1(json_encode($payload));
    }

    //  Add the User ID or Email to Intercom Payload
    //  Can assign by custom User ID or Email
    //  Default is ['email' => 'john.smith@example.com']

    private function add_user_data($payload)
    {

        $key = apply_filters('iwp_user_identifier_key', 'email', $this->user_id);

        $value = apply_filters('iwp_user_identifier_value', $this->wp_user->user_email, $this->user_id);

        $payload["" . $key . ""] = $value;

        $payload = apply_filters('iwp_add_user_data_payload', $payload);

        return $payload;

    }

    //Allow users to fine tune event tracking

    private function is_event_permitted($event)
    {

        if (in_array($event, apply_filters('iwp_permitted_events', []))) {

            return true;
        }

        error_log("$event is not permitted!");

        return false;
    }

}