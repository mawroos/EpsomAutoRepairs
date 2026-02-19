<?php
/**
 * Lightweight SendGrid v3 API wrapper using cURL.
 * No external libraries required.
 */

require_once(__DIR__ . '/config.php');

/**
 * Send an email using the SendGrid v3 API.
 *
 * @param string $toEmail    Recipient email address
 * @param string $toName     Recipient name
 * @param string $fromEmail  Sender email address
 * @param string $fromName   Sender name
 * @param string $subject    Email subject
 * @param string $htmlBody   Email body (HTML)
 * @param string|null $replyToEmail  Reply-to email address (optional)
 * @param string|null $replyToName   Reply-to name (optional)
 * @param array  $attachments Array of attachments [['content' => base64, 'filename' => name, 'type' => mime]] (optional)
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendgrid_send($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $replyToEmail = null, $replyToName = null, $attachments = []) {
    $apiKey = SENDGRID_API_KEY;

    if (empty($apiKey)) {
        return ['success' => false, 'error' => 'SendGrid API key is not configured.'];
    }

    $data = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $toEmail, 'name' => $toName]
                ],
                'subject' => $subject
            ]
        ],
        'from' => [
            'email' => $fromEmail,
            'name'  => $fromName
        ],
        'content' => [
            [
                'type'  => 'text/html',
                'value' => $htmlBody
            ]
        ]
    ];

    if ($replyToEmail) {
        $data['reply_to'] = [
            'email' => $replyToEmail,
            'name'  => $replyToName ?: $replyToEmail
        ];
    }

    if (!empty($attachments)) {
        $data['attachments'] = $attachments;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }

    // SendGrid returns 202 on success
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'error' => null];
    }

    $decoded = json_decode($response, true);
    $errorMsg = isset($decoded['errors'][0]['message']) ? $decoded['errors'][0]['message'] : 'Unknown error (HTTP ' . $httpCode . ')';
    return ['success' => false, 'error' => $errorMsg];
}
