<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if the required data is provided in the POST request
if (isset($_POST['order_id']) && isset($_POST['product_id']) && isset($_POST['tracking_number'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $tracking_number = $_POST['tracking_number'];

    // Query to update the tracking number in the database
    $sql_update_tracking = "UPDATE orders o
                            JOIN order_items oi ON o.order_id = oi.order_id
                            SET o.tracking_number = ?
                            WHERE o.order_id = ? AND oi.product_id = ?";
    $stmt_update_tracking = $conn->prepare($sql_update_tracking);
    $stmt_update_tracking->bind_param("sii", $tracking_number, $order_id, $product_id);
    
    if ($stmt_update_tracking->execute()) {
        // Redirect back to the seller page with updated tracking number
        header("Location: sellerpage.php?product_id=" . $product_id);
        exit();
    } else {
        echo "Error updating the tracking number.";
    }
} else {
    echo "Invalid request.";
}
?>
