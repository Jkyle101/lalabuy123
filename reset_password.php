<!-- reset_password.php -->
<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    require_once 'database.php';

    // Check if the token exists and is still valid
    $sql = "SELECT * FROM password_resets WHERE token = ? AND expiration > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, show the password reset form
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Update the user's password
                $user = $result->fetch_assoc();
                $user_id = $user['user_id'];

                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the user's password
                $sql_update = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $hashed_password, $user_id);
                $stmt_update->execute();

                // Delete the token after successful password reset
                $sql_delete_token = "DELETE FROM password_resets WHERE token = ?";
                $stmt_delete_token = $conn->prepare($sql_delete_token);
                $stmt_delete_token->bind_param("s", $token);
                $stmt_delete_token->execute();

                echo "Your password has been reset successfully. You can now <a href='login.php'>login</a>.";
            } else {
                echo "Passwords do not match.";
            }
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>

<form method="POST" action="reset_password.php?token=<?php echo $_GET['token']; ?>">
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" required>
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" required>
    <button type="submit">Reset Password</button>
</form>
