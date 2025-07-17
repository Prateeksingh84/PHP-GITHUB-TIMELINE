<?php
ini_set('SMTP', 'localhost');
ini_set('smtp_port', 25);

$to = 'your_email@example.com';
$subject = 'Test mail';
$message = 'This is a test email.';
$headers = 'From: no-reply@example.com' . "\r\n";

if(mail($to, $subject, $message, $headers)){
    echo "Mail sent successfully";
} else {
    echo "Failed to send mail";
}
