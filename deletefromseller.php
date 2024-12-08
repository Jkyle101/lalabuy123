<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if the delete button was pressed and an order_id is provided
if (isset($_POST['delete_order']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Log for debugging purposes
    error_log("Attempting to delete order with ID: $order_id");

    // Start a transaction to ensure both tables are updated correctly
    $conn->begin_transaction();

    try {
        // Step 1: Delete from the order_items table (This deletes the products linked to the order)
        $sql = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            error_log("Order items deleted successfully for order ID: $order_id");
        } else {
            error_log("Failed to delete order items for order ID: $order_id");
        }

        // Step 2: Delete from the orders table (This deletes the order itself)
        $sql = "DELETE FROM orders WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            error_log("Order deleted successfully with ID: $order_id");
        } else {
            error_log("Failed to delete order with ID: $order_id");
        }

        // Commit the transaction
        $conn->commit();

        // Redirect back to the seller page with a success message
        header("Location: sellerpage.php?message=Order deleted successfully");
        exit();
    } catch (Exception $e) {
        // If something goes wrong, rollback the transaction
        $conn->rollback();

        // Log the error message and show a generic error
        error_log("Error during deletion: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
} else {
    error_log("Invalid request: Missing order_id or delete_order action.");
    echo "Invalid request.";
}
?>
