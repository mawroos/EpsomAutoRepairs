<?php

require_once(__DIR__ . '/sendgrid-mailer.php');
require_once(__DIR__ . '/spam-protection.php');

$status = "false";

if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if( $_POST['reservation_email'] != '' AND $_POST['reservation_phone'] != '' AND $_POST['car_select'] != '') {

        $email = filter_var($_POST['reservation_email'], FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars($_POST['reservation_phone'], ENT_QUOTES, 'UTF-8');
        $car = htmlspecialchars($_POST['car_select'], ENT_QUOTES, 'UTF-8');

        $subject = 'New Message | Reservation Form';
        $name = isset($_POST['reservation_name']) ? htmlspecialchars($_POST['reservation_name'], ENT_QUOTES, 'UTF-8') : '';
        $reservation_date = isset($_POST['reservation_date']) ? htmlspecialchars($_POST['reservation_date'], ENT_QUOTES, 'UTF-8') : '';

        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $message = 'Please provide a valid email address.';
            $status = "false";
        } else {
            // Run spam protection checks (honeypot + rate limiting + reCAPTCHA)
            $spamCheck = check_spam_protection('form_botcheck');

            if( $spamCheck['passed'] ) {

                $name_line = !empty($name) ? "Name: $name<br><br>" : '';
                $email_line = !empty($email) ? "Email: $email<br><br>" : '';
                $phone_line = !empty($phone) ? "Phone: $phone<br><br>" : '';
                $car_line = !empty($car) ? "Car: $car<br><br>" : '';
                $date_line = !empty($reservation_date) ? "Reservation Date: $reservation_date<br><br>" : '';

                $referrer = isset($_SERVER['HTTP_REFERER']) ? '<br><br><br>This Form was submitted from: ' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

                $body = "$name_line $email_line $phone_line $car_line $date_line $referrer";

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