<!-- forgot_password.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    require_once 'database.php';

    // Check if the email exists in the database
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a password reset token
        $token = bin2hex(random_bytes(50)); // 50 bytes for the token
        $expiration = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expires in 1 hour

        // Store the token and expiration in the database
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];

        $sql_insert_token = "INSERT INTO password_resets (user_id, token, expiration) VALUES (?, ?, ?)";
        $stmt_insert_token = $conn->prepare($sql_insert_token);
        $stmt_insert_token->bind_param("iss", $user_id, $token, $expiration);
        $stmt_insert_token->execute();

        // Send email with reset link
        $reset_link = "http://yourwebsite.com/reset_password.php?token=" . $token;

        $subject = "Password Reset Request";
        $message = "To reset your password, click the link below:\n\n" . $reset_link;
        $headers = "From: no-reply@yourwebsite.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "An email has been sent with instructions to reset your password.";
        } else {
            echo "Error sending email.";
        }
    } else {
        echo "No account found with that email.";
    }
}
?>

<form method="POST" action="forgot_password.php">
    <label for="email">Enter your email to receive a password reset link:</label>
    <input type="email" name="email" required>
    <button type="submit">Submit</button>
</form>
<!-- #region -->