<?php
/**
 * Spam protection utilities:
 * - Honeypot field validation
 * - Google reCAPTCHA v2 verification
 * - Rate limiting (per IP)
 */

require_once(__DIR__ . '/config.php');

/**
 * Check the honeypot field. Returns true if the submission looks legitimate.
 *
 * @param string $fieldName The name of the honeypot field in POST data
 * @return bool
 */
function check_honeypot($fieldName = 'form_botcheck') {
    if (!isset($_POST[$fieldName])) {
        return true;
    }
    return $_POST[$fieldName] === '';
}

/**
 * Verify Google reCAPTCHA v2 response.
 *
 * @return array ['success' => bool, 'error' => string|null]
 */
function verify_recaptcha() {
    $secretKey = RECAPTCHA_SECRET_KEY;

    // If reCAPTCHA is not configured, skip verification
    if (empty($secretKey)) {
        return ['success' => true, 'error' => null];
    }

    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

    if (empty($recaptchaResponse)) {
        return ['success' => false, 'error' => 'Please complete the reCAPTCHA verification.'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret'   => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        // On connection error, allow the submission (fail open)
        return ['success' => true, 'error' => null];
    }

    $result = json_decode($response, true);

    if (isset($result['success']) && $result['success'] === true) {
        return ['success' => true, 'error' => null];
    }

    return ['success' => false, 'error' => 'reCAPTCHA verification failed. Please try again.'];
}

/**
 * Simple file-based rate limiting per IP address.
 *
 * @param int $maxRequests  Maximum requests allowed in the time window
 * @param int $windowSeconds  Time window in seconds
 * @return array ['allowed' => bool, 'error' => string|null]
 */
function check_rate_limit($maxRequests = 5, $windowSeconds = 300) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rateLimitDir = sys_get_temp_dir() . '/epsom_rate_limits';

    if (!is_dir($rateLimitDir)) {
        mkdir($rateLimitDir, 0700, true);
    }

    $file = $rateLimitDir . '/' . md5($ip) . '.json';
    $now = time();

    $data = ['timestamps' => []];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['timestamps'])) {
            $data = ['timestamps' => []];
        }
    }

    // Remove expired timestamps
    $data['timestamps'] = array_filter($data['timestamps'], function ($ts) use ($now, $windowSeconds) {
        return ($now - $ts) < $windowSeconds;
    });
    $data['timestamps'] = array_values($data['timestamps']);

    if (count($data['timestamps']) >= $maxRequests) {
        return ['allowed' => false, 'error' => 'Too many submissions. Please wait a few minutes before trying again.'];
    }

    // Record this request
    $data['timestamps'][] = $now;
    file_put_contents($file, json_encode($data), LOCK_EX);

    return ['allowed' => true, 'error' => null];
}

/**
 * Run all spam protection checks (honeypot + reCAPTCHA + rate limiting).
 *
 * @param string $honeypotField The name of the honeypot POST field
 * @return array ['passed' => bool, 'error' => string|null]
 */
function check_spam_protection($honeypotField = 'form_botcheck') {
    // 1. Honeypot check
    if (!check_honeypot($honeypotField)) {
        return ['passed' => false, 'error' => 'Bot detected.'];
    }

    // 2. Rate limiting
    $rateCheck = check_rate_limit();
    if (!$rateCheck['allowed']) {
        return ['passed' => false, 'error' => $rateCheck['error']];
    }

    // 3. reCAPTCHA verification
    $recaptchaCheck = verify_recaptcha();
    if (!$recaptchaCheck['success']) {
        return ['passed' => false, 'error' => $recaptchaCheck['error']];
    }

    return ['passed' => true, 'error' => null];
}
