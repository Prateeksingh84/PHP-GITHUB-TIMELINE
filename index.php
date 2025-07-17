<?php
session_start();
require_once 'functions.php';  // Make sure functions.php has registerEmail(), unsubscribeEmail(), sendVerificationCodeEmail(), generateVerificationCode()

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $action = $_POST['action'] ?? ''; // 'register' or 'unsubscribe'

    if (!$email) {
        $error = "Please enter a valid email.";
    } elseif (!in_array($action, ['register', 'unsubscribe'])) {
        $error = "Please select a valid action.";
    } elseif (empty($_POST['verification_code'])) {
        // No code entered yet, so send code
        $code = generateVerificationCode();
        $_SESSION['verification_email'] = $email;
        $_SESSION['verification_code'] = $code;
        $_SESSION['verification_action'] = $action;

        if (sendVerificationCodeEmail($email, $code, $action)) {
            $message = "Verification code sent to $email. Please enter the code below.";
        } else {
            $error = "Failed to send verification code. Please try again.";
        }
    } else {
        // User submitted verification code
        $input_code = trim($_POST['verification_code']);
        if (isset($_SESSION['verification_code'], $_SESSION['verification_email'], $_SESSION['verification_action']) &&
            $input_code === $_SESSION['verification_code'] &&
            $email === $_SESSION['verification_email'] &&
            $action === $_SESSION['verification_action']) {

            if ($action === 'register') {
                if (registerEmail($email)) {
                    $message = "Successfully registered $email.";
                } else {
                    $error = "Failed to register email. Please try again.";
                }
            } else { // unsubscribe
                if (unsubscribeEmail($email)) {
                    $message = "Successfully unsubscribed $email.";
                } else {
                    $error = "Failed to unsubscribe email. Please try again.";
                }
            }

            // Clear session codes after success
            unset($_SESSION['verification_code'], $_SESSION['verification_email'], $_SESSION['verification_action']);
        } else {
            $error = "Invalid verification code or mismatch. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Register/Unsubscribe</title>
</head>
<body>
<h2>Email Register or Unsubscribe</h2>

<?php if ($message): ?>
    <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">
    <label>Email:</label><br>
    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"><br><br>

    <label>Action:</label><br>
    <input type="radio" name="action" value="register" <?php if(($_POST['action'] ?? '')==='register') echo 'checked'; ?>> Register<br>
    <input type="radio" name="action" value="unsubscribe" <?php if(($_POST['action'] ?? '')==='unsubscribe') echo 'checked'; ?>> Unsubscribe<br><br>

    <label>Verification Code:</label><br>
    <input type="text" name="verification_code" maxlength="6" pattern="\d{6}" placeholder="Enter code (if received)"><br><br>

    <button type="submit">Submit</button>
</form>
</body>
</html>
