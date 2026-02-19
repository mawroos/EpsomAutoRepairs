<?php

require_once(__DIR__ . '/sendgrid-mailer.php');
require_once(__DIR__ . '/spam-protection.php');

$message = "";
$status = "false";

if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if( $_POST['form_email'] != '' AND $_POST['form_message'] != '' ) {

        $name = 'Quick Contact';
        $email = filter_var($_POST['form_email'], FILTER_SANITIZE_EMAIL);
        $form_message = htmlspecialchars($_POST['form_message'], ENT_QUOTES, 'UTF-8');

        $subject = 'New Message | Quick Contact Form';

        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $message = 'Please provide a valid email address.';
            $status = "false";
        } else {
            // Run spam protection checks (honeypot + rate limiting + reCAPTCHA)
            $spamCheck = check_spam_protection('form_botcheck');

            if( $spamCheck['passed'] ) {

                $email_line = !empty($email) ? "Email: $email<br><br>" : '';
                $message_line = !empty($form_message) ? "Message: $form_message<br><br>" : '';

                $referrer = isset($_SERVER['HTTP_REFERER']) ? '<br><br><br>This Form was submitted from: ' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

                $body = "$email_line $message_line $referrer";

                $result = sendgrid_send(
                    CONTACT_EMAIL, CONTACT_NAME,
                    CONTACT_EMAIL, CONTACT_NAME,
                    $subject,
                    $body,
                    $email, $name
                );

                if( $result['success'] ):
                    $message = 'We have <strong>successfully</strong> received your Message and will get Back to you as soon as possible.';
                    $status = "true";
                else:
                    $message = 'Email <strong>could not</strong> be sent due to some Unexpected Error. Please Try Again later.';
                    $status = "false";
                endif;
            } else {
                $message = htmlspecialchars($spamCheck['error'], ENT_QUOTES, 'UTF-8');
                $status = "false";
            }
        }
    } else {
        $message = 'Please <strong>Fill up</strong> all the Fields and Try Again.';
        $status = "false";
    }
} else {
    $message = 'An <strong>unexpected error</strong> occured. Please Try Again later.';
    $status = "false";
}

$status_array = array( 'message' => $message, 'status' => $status);
echo json_encode($status_array);
?>