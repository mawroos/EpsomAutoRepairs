<?php

require_once(__DIR__ . '/sendgrid-mailer.php');
require_once(__DIR__ . '/spam-protection.php');

if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if( $_POST['contact-form-name'] != '' AND $_POST['contact-form-email'] != '' AND $_POST['contact-form-subject'] != '' ) {

        $name = htmlspecialchars($_POST['contact-form-name'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['contact-form-email'], FILTER_SANITIZE_EMAIL);
        $subject = htmlspecialchars($_POST['contact-form-subject'], ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($_POST['contact-form-phone'], ENT_QUOTES, 'UTF-8');
        $form_message = htmlspecialchars($_POST['contact-form-message'], ENT_QUOTES, 'UTF-8');

        $subject = !empty($subject) ? $subject : 'New Message From Contact Form';

        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            echo 'Please provide a valid email address.';
        } else {
            // Run spam protection checks (honeypot + rate limiting + reCAPTCHA)
            $spamCheck = check_spam_protection('contact-form-botcheck');

            if( $spamCheck['passed'] ) {

                $name_line = !empty($name) ? "Name: $name<br><br>" : '';
                $email_line = !empty($email) ? "Email: $email<br><br>" : '';
                $phone_line = !empty($phone) ? "Phone: $phone<br><br>" : '';
                $message_line = !empty($form_message) ? "Message: $form_message<br><br>" : '';

                $referrer = isset($_SERVER['HTTP_REFERER']) ? '<br><br><br>This Form was submitted from: ' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

                $body = "$name_line $email_line $phone_line $message_line $referrer";

                $result = sendgrid_send(
                    CONTACT_EMAIL, CONTACT_NAME,
                    CONTACT_EMAIL, CONTACT_NAME,
                    $subject,
                    $body,
                    $email, $name
                );

                if( $result['success'] ):
                    // Send autoresponder
                    $ar_body = "Thank you for contacting us. We will reply within 24 hours.<br><br>Regards,<br>" . CONTACT_NAME . ".";
                    sendgrid_send(
                        $email, $name,
                        CONTACT_EMAIL, CONTACT_NAME,
                        'We\'ve received your Email',
                        $ar_body
                    );
                    echo 'We have <strong>successfully</strong> received your Message and will get Back to you as soon as possible.';
                else:
                    echo 'Email <strong>could not</strong> be sent due to some Unexpected Error. Please Try Again later.';
                endif;
            } else {
                echo htmlspecialchars($spamCheck['error'], ENT_QUOTES, 'UTF-8');
            }
        }
    } else {
        echo 'Please <strong>Fill up</strong> all the Fields and Try Again.';
    }
} else {
    echo 'An <strong>unexpected error</strong> occured. Please Try Again later.';
}

?>