<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $customer_id = $_POST['customer_id'];
    $quantity = $_POST['quantity'];

    try {
        $stmt = $pdo->prepare("CALL Process_Order(?, ?, ?)");
        $stmt->execute([$customer_id, $book_id, $quantity]);
        $message = "Order placed successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Order Result</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">BookStore</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="select_book.php">Place Order</a></li>
            <li><a href="recent_orders.php" class="active">View Recent Orders</a></li>
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Order Result</h1>
        <div class="notification <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>