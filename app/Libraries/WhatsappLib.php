<?php

namespace App\Libraries;

use Config\OSPOS;

class WhatsappLib
{
    private array $config;

    public function __construct()
    {
        $this->config = config(OSPOS::class)->settings;
    }

    /**
     * Send a WhatsApp message via Twilio
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function send(string $phone, string $message, ?string $mediaUrl = null): bool
    {
        $sid = $this->config['twilio_account_sid'] ?? '';
        $token = $this->config['twilio_auth_token'] ?? '';
        $from_number = $this->config['twilio_whatsapp_number'] ?? '';

        if (empty($sid) || empty($token) || empty($from_number)) {
            log_message('error', 'Twilio WhatsApp configuration is missing.');
            return false;
        }

        // Clean phone number (remove spaces, dashes, parentheses)
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Auto-append +91 if country code is missing (no + prefix)
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+91' . ltrim($phone, '0');
        }

        $twilio_url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $post_data = [
            'From' => 'whatsapp:' . $from_number,
            'To'   => 'whatsapp:' . $phone,
            'Body' => $message
        ];

        if ($mediaUrl !== null) {
            $post_data['MediaUrl'] = $mediaUrl;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $twilio_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            return true;
        } else {
            log_message('error', 'Twilio WhatsApp API Error: ' . $response);
            return false;
        }
    }
}
