<?php

require_once(__DIR__ . '/sendgrid-mailer.php');
require_once(__DIR__ . '/spam-protection.php');

$status = "false";

if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if( $_POST['reservation_name'] != '' AND $_POST['reservation_email'] != '' AND $_POST['reservation_phone'] != '' AND $_POST['car_service_select'] != '') {

        $name = htmlspecialchars($_POST['reservation_name'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['reservation_email'], FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars($_POST['reservation_phone'], ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($_POST['reservation_address'], ENT_QUOTES, 'UTF-8');

        $car_name = htmlspecialchars($_POST['car_name_select'], ENT_QUOTES, 'UTF-8');
        $car_type = htmlspecialchars($_POST['car_type_select'], ENT_QUOTES, 'UTF-8');
        $car_year = htmlspecialchars($_POST['car_year_select'], ENT_QUOTES, 'UTF-8');
        $car_number_of_wheels = htmlspecialchars($_POST['number_of_wheels'], ENT_QUOTES, 'UTF-8');
        $car_extra_services = $_POST['extra_services'];
        $car_model = htmlspecialchars($_POST['car_model'], ENT_QUOTES, 'UTF-8');
        $car_service = htmlspecialchars($_POST['car_service_select'], ENT_QUOTES, 'UTF-8');

        $subject = 'New Message | Reservation Form';
        $reservation_date = isset($_POST['reservation_date']) ? htmlspecialchars($_POST['reservation_date'], ENT_QUOTES, 'UTF-8') : '';
        $form_message = htmlspecialchars($_POST['form_message'], ENT_QUOTES, 'UTF-8');

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
                $address_line = !empty($address) ? "Address: $address<br><br>" : '';

                $car_name_line = !empty($car_name) ? "Car Name: $car_name<br><br>" : '';
                $car_type_line = !empty($car_type) ? "Car Type: $car_type<br><br>" : '';
                $car_year_line = !empty($car_year) ? "Car Year: $car_year<br><br>" : '';
                $car_wheels_line = !empty($car_number_of_wheels) ? "Number Of Wheels: $car_number_of_wheels<br><br>" : '';

                $extra_services_str = is_array($car_extra_services) ? htmlspecialchars(implode(", ", $car_extra_services), ENT_QUOTES, 'UTF-8') : htmlspecialchars($car_extra_services, ENT_QUOTES, 'UTF-8');
                $extra_services_line = !empty($extra_services_str) ? "Extra Services: $extra_services_str<br><br>" : '';

                $car_model_line = !empty($car_model) ? "Car Model: $car_model<br><br>" : '';
                $car_service_line = !empty($car_service) ? "Car Service: $car_service<br><br>" : '';
                $date_line = !empty($reservation_date) ? "Reservation: $reservation_date<br><br>" : '';
                $message_line = !empty($form_message) ? "Message: $form_message<br><br>" : '';

                $referrer = isset($_SERVER['HTTP_REFERER']) ? '<br><br><br>This Form was submitted from: ' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

                $body = "$name_line $email_line $phone_line $address_line $car_name_line $car_type_line $car_year_line $car_wheels_line $extra_services_line $car_model_line $car_service_line $date_line $message_line $referrer";

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