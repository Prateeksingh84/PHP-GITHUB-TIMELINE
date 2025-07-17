<?php
session_start();
require_once __DIR__ . '/functions.php';

$message = '';
$error = '';

// Step 1: User enters email to get unsubscribe code
if (isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = "Invalid email address.";
    } else {
        $_SESSION['unsubscribe_email'] = $email;
        $_SESSION['unsubscribe_code'] = generateVerificationCode();

        if (sendVerificationCodeEmail($email, $_SESSION['unsubscribe_code'], 'unsubscribe')) {
            $message = "Verification code sent to $email";
        } else {
            $error = "Failed to send verification code email.";
        }
    }
}

// Step 2: User enters the verification code to confirm unsubscribe
if (isset($_POST['verify_code'])) {
    $code = trim($_POST['verify_code']);

    // Check if session vars exist and codes match
    if (isset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']) 
        && $code === $_SESSION['unsubscribe_code']) {

        if (unsubscribeEmail($_SESSION['unsubscribe_email'])) {
            $message = "You have been unsubscribed successfully.";
        } else {
            $error = "Failed to unsubscribe. Please try again.";
        }

        // Clear session vars after successful unsubscribe
        unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']);
    } else {
        $error = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Unsubscribe</title></head>
<body>
<h2>Unsubscribe from GitHub Updates</h2>

<?php if ($message): ?><p style="color:green;"><?=htmlspecialchars($message)?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?=htmlspecialchars($error)?></p><?php endif; ?>

<form method="POST">
    <label>Enter your email to get unsubscribe code:</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Send Unsubscribe Code</button>
</form>

<form method="POST" style="margin-top:20px;">
    <label>Enter verification code to unsubscribe:</label><br>
    <input type="text" name="verify_code" maxlength="6" pattern="\d{6}" required><br><br>
    <button type="submit">Confirm Unsubscribe</button>
</form>
</body>
</html>
