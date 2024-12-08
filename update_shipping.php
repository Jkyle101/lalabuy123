<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if the required data is provided in the POST request
// Check if the required data is provided in the POST request
if (isset($_POST['order_id']) && isset($_POST['product_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $status = $_POST['status'];

    // Get the tracking number from the form submission (or use the existing one if not updated)
    $tracking_number = isset($_POST['tracking_number']) ? $_POST['tracking_number'] : '';  // Use the current tracking number if not updated

    // Query to update the order status and tracking number
    $sql_update_order = "UPDATE orders o
                         JOIN order_items oi ON o.order_id = oi.order_id
                         SET o.status = ?, o.tracking_number = ?
                         WHERE o.order_id = ? AND oi.product_id = ?";
    $stmt_update_order = $conn->prepare($sql_update_order);
    
    if ($stmt_update_order === false) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }

    $stmt_update_order->bind_param("ssii", $status, $tracking_number, $order_id, $product_id);

    if ($stmt_update_order->execute()) {
        if ($stmt_update_order->affected_rows > 0) {
            // Redirect to the seller page after successful update
            header("Location: sellerpage.php?product_id=" . $product_id);
            exit();
        } else {
            echo "No rows updated. Please check the order and product IDs.";
        }
    } else {
        echo "Error executing query: " . $stmt_update_order->error;
    }
} else {
    echo "Invalid request. Missing required fields.";
}

