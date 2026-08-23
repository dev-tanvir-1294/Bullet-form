<?php
if (!defined('ABSPATH')) exit;

class BFMSF_Payment {
    private $form_id;
    private $settings;

    public function __construct($form_id) {
        $this->form_id = $form_id;
        $this->settings = BFMSF_Settings::get_settings($form_id);
    }

    public function process_payment($payment_data) {
        $gateway = $this->settings['payment_gateway'] ?? 'stripe';
        if ($gateway === 'stripe') {
            return $this->process_stripe($payment_data);
        } elseif ($gateway === 'paypal') {
            return $this->process_paypal($payment_data);
        }
        return false;
    }

    private function process_stripe($data) {
        // Requires Stripe PHP library (stripe/stripe-php)
        // We'll assume the library is installed via composer or manually included.
        // For simplicity, we'll redirect to a Stripe Checkout session.
        $secret_key = $this->settings['stripe_secret_key'] ?? '';
        if (!$secret_key) return false;
        \Stripe\Stripe::setApiKey($secret_key);
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $this->settings['currency'] ?? 'usd',
                        'product_data' => ['name' => 'Form #' . $this->form_id],
                        'unit_amount' => intval($data['amount'] * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => add_query_arg('bfmsf_payment_success', $this->form_id, get_permalink()),
                'cancel_url' => add_query_arg('bfmsf_payment_cancel', $this->form_id, get_permalink()),
            ]);
            return array('redirect' => $session->url);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function process_paypal($data) {
        $business = $this->settings['paypal_business_email'] ?? '';
        if (!$business) return false;
        $paypal_url = ($this->settings['paypal_sandbox'] ?? false) 
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' 
            : 'https://www.paypal.com/cgi-bin/webscr';
        $params = array(
            'cmd' => '_xclick',
            'business' => $business,
            'item_name' => 'Form #' . $this->form_id,
            'amount' => number_format($data['amount'], 2, '.', ''),
            'currency_code' => $this->settings['currency'] ?? 'USD',
            'return' => add_query_arg('bfmsf_payment_success', $this->form_id, get_permalink()),
            'cancel_return' => add_query_arg('bfmsf_payment_cancel', $this->form_id, get_permalink()),
            'notify_url' => admin_url('admin-ajax.php?action=bfmsf_paypal_ipn'),
        );
        return array('redirect' => $paypal_url . '?' . http_build_query($params));
    }
}