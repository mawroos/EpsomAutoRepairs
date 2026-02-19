<?php

require_once(__DIR__ . '/sendgrid-mailer.php');
require_once(__DIR__ . '/spam-protection.php');

$message = "";
$status = "false";

if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if( $_POST['form_name'] != '' AND $_POST['form_email'] != '' AND $_POST['form_sex'] != '' AND $_POST['form_post'] != '' AND $_POST['form_message'] != '' ) {

        $name = htmlspecialchars($_POST['form_name'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['form_email'], FILTER_SANITIZE_EMAIL);
        $sex = htmlspecialchars($_POST['form_sex'], ENT_QUOTES, 'UTF-8');
        $job_post = htmlspecialchars($_POST['form_post'], ENT_QUOTES, 'UTF-8');
        $form_message = htmlspecialchars($_POST['form_message'], ENT_QUOTES, 'UTF-8');

        $subject = 'New Message | Job Apply Form';

        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $message = 'Please provide a valid email address.';
            $status = "false";
        } else {
            // Run spam protection checks (honeypot + rate limiting + reCAPTCHA)
            $spamCheck = check_spam_protection('form_botcheck');

            if( $spamCheck['passed'] ) {

                $name_line = !empty($name) ? "Name: $name<br><br>" : '';
                $email_line = !empty($email) ? "Email: $email<br><br>" : '';
                $sex_line = !empty($sex) ? "Sex: $sex<br><br>" : '';
                $job_post_line = !empty($job_post) ? "Job Post: $job_post<br><br>" : '';
                $message_line = !empty($form_message) ? "Message: $form_message<br><br>" : '';

                $referrer = isset($_SERVER['HTTP_REFERER']) ? '<br><br><br>This Form was submitted from: ' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

                $body = "$name_line $email_line $sex_line $job_post_line $message_line $referrer";

                // Handle file attachment
                $attachments = [];
                $attachmentError = false;
                if ( isset( $_FILES['form_attachment'] ) && $_FILES['form_attachment']['error'] == UPLOAD_ERR_OK ) {
                    $maxFileSize = 10 * 1024 * 1024; // 10 MB
                    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'text/plain'];
                    $fileMime = mime_content_type($_FILES['form_attachment']['tmp_name']);
                    if ($_FILES['form_attachment']['size'] > $maxFileSize) {
                        $message = 'File is too large. Maximum size is 10 MB.';
                        $status = "false";
                        $attachmentError = true;
                    } elseif (!in_array($fileMime, $allowedTypes)) {
                        $message = 'File type not allowed. Please upload a PDF, Word document, image, or text file.';
                        $status = "false";
                        $attachmentError = true;
                    } else {
                        $fileContent = base64_encode(file_get_contents($_FILES['form_attachment']['tmp_name']));
                        $fileName = htmlspecialchars($_FILES['form_attachment']['name'], ENT_QUOTES, 'UTF-8');
                        $attachments[] = [
                            'content'  => $fileContent,
                            'filename' => $fileName,
                            'type'     => $fileMime
                        ];
                    }
                }

                if (!$attachmentError) {
                    $result = sendgrid_send(
                        CONTACT_EMAIL, CONTACT_NAME,
                        CONTACT_EMAIL, CONTACT_NAME,
                        $subject,
                        $body,
                        $email, $name,
                        $attachments
                    );

                    if( $result['success'] ):
                        $message = 'We have <strong>successfully</strong> received your Message and will get Back to you as soon as possible.';
                        $status = "true";
                    else:
                        $message = 'Email <strong>could not</strong> be sent due to some Unexpected Error. Please Try Again later.';
                        $status = "false";
                    endif;
                }
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