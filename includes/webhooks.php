<?php

namespace Intercom_WP;

use Intercom_WP\Intercom;

if (!defined('ABSPATH')) exit;

class Webhooks
{

    /**
     * Extension constructor.
     */

    const HIGHWAYS_WP_URL = 'https://app.highways.io/intercom/connect?app=wordpress';

    public function __construct()
    {

        add_action('rest_api_init', [$this, 'intercom_wp_webhook']);

    }

    public function intercom_wp_webhook()
    {

        register_rest_route('intercom-wp/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => [$this, 'intercom_wp_webhook_process'],
            'permission_callback' => '__return_true'
        ));

    }

    public function intercom_wp_webhook_process()
    {

        try {

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {

                wp_send_json(['message' => "Payload is empty"], 400);

            }

            if (isset($data['nouce']) && isset($data['intercom_api_key'])) {

                if ($data['nouce'] == get_transient('intercom_wp_verification')) {

                    delete_transient('intercom_wp_verification');

                    $options = get_option('intercom_wp_settings');

                    if (!$options || !is_array($options)) {

                        $options = [];
                    }

                    $options['intercom_wp_intercom_api_key'] = $data['intercom_api_key'];

                    $options['intercom_wp_intercom_app_id'] = isset($data['intercom_workspace_id']) ? $data['intercom_workspace_id'] : false;

                    update_option('intercom_wp_settings', $options);

                    wp_send_json(['message' => "Payload is valid and saved!"]);

                } else {

                    wp_send_json(['message' => "Payload is present but invalid!"], 401);

                }

            }

        } catch (\Exception $e) {

            error_log($e->getMessage());
        }

        //Assume a failure

        status_header(400);

        die();

    }

    public function connect_url()
    {

        $url = self::HIGHWAYS_WP_URL;

        $nonce = time() . sha1(site_url());

        set_transient('intercom_wp_verification', $nonce, HOUR_IN_SECONDS);

        $data = ['site' => site_url('/wp-json/intercom-wp/v1/webhook'), 'nouce' => $nonce];

        $url = $url . "&" . http_build_query(['hyconfig' => $data]);

        $options = get_option('intercom_wp_settings');

        if (isset($options['intercom_wp_intercom_api_key'])) {
            $connect = 'Reconnect Intercom';
            $class = 'button-secondary';
        } else {
            $connect = 'Connect Intercom';
            $class = 'button-primary';
        }

        ?>
        <a href="<?php echo $url ?>"
           class="<?php echo $class; ?>"><?php echo $connect; ?></a>
        <?php
    }


}

?>
