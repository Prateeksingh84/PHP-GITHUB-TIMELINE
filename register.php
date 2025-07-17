<?php

function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationCodeEmail($email, $code, $type = 'register') {
    $subject = ($type === 'register') ? "Your Registration Verification Code" : "Your Unsubscribe Verification Code";
    $message = "Your verification code is: $code";
    $headers = "From: no-reply@example.com\r\n" .
               "Reply-To: no-reply@example.com\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Use mail() or your preferred mailer configured to work with your SMTP server
    return mail($email, $subject, $message, $headers);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    // Make sure the folder is writable and file exists or create it
    if (!file_exists($file)) {
        file_put_contents($file, ""); // create empty file
    }
    // Append email only if not already registered
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($email, $emails)) {
        // Already registered
        return true;
    }
    // Append new email
    $result = file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
    return $result !== false;
}
