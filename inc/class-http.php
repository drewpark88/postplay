<?php

class PostPlayConnector {

    private $api_email, $api_key, $api_url;

    public function __construct() {
        $this->api_email = esc_attr(get_option('_postplay_api_email'));
        $this->api_key = esc_attr(get_option('_postplay_api_key'));
        $this->api_url = 'http://postplay.dev/api/v1';
    }

    public function checkIfApiDetailsAvailable() {
        if (empty($this->api_email) || empty($this->api_key))
            return FALSE;
        return TRUE;
    }

    public function checkApiStatus() {
        $response = $this->call('/verify_status', array(
            'api_email' => $this->api_email,
            'api_key' => $this->api_key
                )
        );

        if ($response['status'] == 'success') {
            return $response['body'];
        }
        return FALSE;
    }

    public function postJob($post_id, $title, $content) {        
        $response = $this->call('/publish', array(
            'api_email' => $this->api_email,
            'api_key' => $this->api_key,
            'pp_title' => $title,
            'pp_content' => $content,
            'pp_url' => get_permalink($post_id),
            'pp_data' => array('site_title' => get_bloginfo('name'), 'site_url' => site_url(), 'post_id' => $post_id)
        ));

        return $response['body'];
    }

    private function call($path, $body) {
        $response = wp_remote_post($this->api_url . $path, array(
            'method' => 'POST',
            'timeout' => 20,
            'httpversion' => '1.0',
            'body' => $body
                )
        );

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return array('status' => 'error', 'message' => $error_message);
        }

        return array('status' => 'success', 'body' => json_decode(wp_remote_retrieve_body($response)), true);
    }

}
