<?php

namespace App\Validation;

class RecaptchaRules
{
    /**
     * Verifies the Google reCAPTCHA response.
     *
     * @param string|null $response The reCAPTCHA response from the form.
     * @return boolean
     */
    public function verify_recaptcha(?string $response): bool
    {
        if (empty($response)) {
            return false;
        }

        $secretKey = getenv('recaptcha.secretKey');

        // Jika secret key tidak ada, anggap gagal untuk keamanan.
        if (empty($secretKey)) {
            log_message('error', 'Kunci rahasia reCAPTCHA tidak disetel di file .env.');
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret'   => $secretKey,
            'response' => $response,
            'remoteip' => service('request')->getIPAddress(),
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context  = stream_context_create($options);
        $verify = @file_get_contents($url, false, $context);

        if (!$verify) {
            log_message('error', 'Gagal menghubungi server verifikasi reCAPTCHA.');
            return false;
        }

        $captcha_success = json_decode($verify);

        return $captcha_success->success ?? false;
    }
}
