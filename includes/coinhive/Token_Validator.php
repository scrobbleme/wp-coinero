<?php

class Coinero_Token_Validator
{
    function __construct()
    {
        add_filter('wp-coinero-validate-token', [$this, 'validate_token'], 10, 4);
    }

    function validate_token($value, $token, $hashes, $sitekey)
    {
        $api_url = $sitekey == COINERO_DEFAULT_SITEKEY ? COINERO_PROXY_BASE_URL : 'https://api.coinhive.com/token/verify';
        $response = wp_remote_post($api_url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => [],
                'body' => array(
                    'token' => $token,
                    'hashes' => $hashes,
                    'secret' => get_option('wp-coinero-general-private-key', false)
                ),
                'cookies' => []
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $api_response = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($api_response) && $api_response['success'];
    }
}

return new Coinero_Token_Validator();