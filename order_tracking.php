<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Get the order_id from the URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Query to include shipping_address and tracking_number
$sql = "SELECT o.order_id, o.total_price, o.order_date, o.status, 
               o.shipping_address, o.tracking_number, o.receiver_name, o.contact_number, 
               oi.product_id, p.product_name, p.image 
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.order_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {
    echo "<p>Order not found.</p>";
    exit();
}

$order_details = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | LALABUY</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6f7dff, #e0e0e0);
        }

        .container {
            max-width: 700px;
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            text-align: center;
        }

        .navbar {
            background-color: #e3f2fd;
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .navbar-brand {
            color: #007bff;
            font-weight: bold;
        }

        .progress-bar {
            background-color: #28a745;
            font-weight: bold;
            color: white;
        }

        img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-xl">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">LALABUY</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLight" aria-controls="navbarLight" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarLight">
                    <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link active" href="cart.php">My Cart</a></li>
                        <li class="nav-item"><a class="nav-link active" href="me.php">Me</a></li>
                    </ul>
                    <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <h2>Order Tracking</h2>
        <h3>Order ID: #<?php echo htmlspecialchars($order_details['order_id']); ?></h3>
        <h4>Total Price: $<?php echo number_format($order_details['total_price'], 2); ?></h4>
        <p>Order Date: <?php echo date("F j, Y, g:i a", strtotime($order_details['order_date'])); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['receiver_name']); ?></p>
        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order_details['contact_number']); ?></p>
        </p>
        <h4 class="mt-4">Shipping Address</h4>
        <p class="text-center" style="font-size: 16px; font-weight: bold;">
            <?php echo nl2br(htmlspecialchars($order_details['shipping_address'])); ?>
        </p>

        <!-- Display Tracking Number if available -->
        <?php if (!empty($order_details['tracking_number'])) { ?>
            <h4 class="mt-4">Tracking Number</h4>
            <p class="text-center" style="font-size: 16px; font-weight: bold;">
                <?php echo htmlspecialchars($order_details['tracking_number']); ?>
            </p>
        <?php } else { ?>
            <h4 class="mt-4">Tracking Number</h4>
            <p class="text-center" style="font-size: 16px; font-weight: bold; color: red;">
                Not Available
            </p>
        <?php } ?>

        <div class="progress mt-4">
            <div class="progress-bar" role="progressbar"
                style="width: <?php echo getOrderProgress($order_details['status']); ?>%;"
                aria-valuenow="<?php echo getOrderProgress($order_details['status']); ?>"
                aria-valuemin="0" aria-valuemax="100">
                <?php echo getOrderStatusText($order_details['status']); ?>
            </div>
        </div>

        <h4 class="mt-4">Products</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                    echo "<td><img src='uploads/" . htmlspecialchars($row['image']) . "' alt='Product Image'></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    function getOrderProgress($status)
    {
        switch ($status) {
            case 'Shipped':
                return 100;
            case 'Processing':
                return 50;
            case 'Delivered':
                return 100;
            default:
                return 0;
        }
    }

    function getOrderStatusText($status)
    {
        switch ($status) {
            case 'Shipped':
                return 'Shipped';
            case 'Processing':
                return 'Processing';
            case 'Delivered':
                return 'Delivered';
            default:
                return 'Pending';
        }
    }
    ?>
</body>

</html>