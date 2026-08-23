<?php
if (!defined('ABSPATH')) exit;

/**
 * Spam / CAPTCHA verification helper.
 *
 * Supports hCaptcha, Google reCAPTCHA v2/v3, and Cloudflare Turnstile.
 * Each provider is routed to its own API endpoint.
 * If no secret key is configured for a provider the check is skipped (fail-open)
 * so admins who have not set up keys are not unexpectedly blocked.
 */
class BFMSF_Spam {

    /**
     * Verify a CAPTCHA token for the given provider type.
     *
     * @param string $type    One of: 'hcaptcha', 'recaptcha', 'turnstile'.
     * @param string $token   The client-side response token.
     * @param int    $form_id The form ID (used to retrieve per-form keys).
     * @return bool True when the token is valid (or no key is configured), false otherwise.
     */
    public static function verify_captcha($type, $token, $form_id) {
        $settings   = BFMSF_Settings::get_settings($form_id);
        $secret_key = '';
        $api_url    = '';

        switch ($type) {
            case 'hcaptcha':
                $secret_key = $settings['hcaptcha_secret'] ?? '';
                $api_url    = 'https://api.hcaptcha.com/siteverify';
                break;

            case 'recaptcha':
                $secret_key = $settings['recaptcha_secret'] ?? '';
                $api_url    = 'https://www.google.com/recaptcha/api/siteverify';
                break;

            case 'turnstile':
                $secret_key = $settings['turnstile_secret'] ?? '';
                $api_url    = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
                break;
        }

        // If no secret key is configured, skip verification (fail-open).
        if (empty($secret_key) || empty($token)) {
            return true;
        }

        $response = wp_remote_post($api_url, array(
            'timeout' => 10,
            'body'    => array(
                'secret'   => $secret_key,
                'response' => $token,
                'remoteip' => self::get_client_ip(),
            ),
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($body['success']);
    }

    /**
     * Best-effort retrieval of the visitor's IP address.
     *
     * @return string
     */
    private static function get_client_ip() {
        $ip = '0.0.0.0';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip    = trim($parts[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}