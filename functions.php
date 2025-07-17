<?php
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationCodeEmail($toEmail, $code, $type = 'register') {
    $subject = $type === 'register' ? "Your Verification Code" : "Unsubscribe Verification Code";
    $message = "Your verification code is: $code";
    $headers = "From: noreply@example.com\r\n";
    return mail($toEmail, $subject, $message, $headers);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';

    if (!file_exists($file)) {
        if (file_put_contents($file, "") === false) {
            error_log("Failed to create $file");
            return false;
        }
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (in_array($email, $emails)) {
        return true;
    }

    $result = file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);

    if ($result === false) {
        error_log("Failed to write email to $file");
        return false;
    }

    return true;
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';

    if (!file_exists($file)) {
        return false;
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $newEmails = array_filter($emails, function($e) use ($email) {
        return strtolower(trim($e)) !== strtolower(trim($email));
    });

    $result = file_put_contents($file, implode(PHP_EOL, $newEmails) . PHP_EOL, LOCK_EX);

    return $result !== false;
}
