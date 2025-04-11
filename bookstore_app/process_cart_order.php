<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $session_id = session_id();

    try {
        // Start a transaction for the entire order
        $pdo->beginTransaction();

        // Get cart items
        $stmt = $pdo->prepare("
            SELECT c.book_id, c.quantity, b.price
            FROM Cart c
            JOIN books b ON c.book_id = b.book_id
            WHERE c.session_id = ?
        ");
        $stmt->execute([$session_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($cart_items) {
            // Calculate total amount
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Insert into Orders
            $stmt = $pdo->prepare("INSERT INTO Orders (customer_id, order_date, total_amount) VALUES (?, CURDATE(), ?)");
            $stmt->execute([$customer_id, $total_amount]);
            $order_id = $pdo->lastInsertId();

            // Process each cart item
            foreach ($cart_items as $item) {
                $book_id = $item['book_id'];
                $quantity = $item['quantity'];
                $unit_price = $item['price'];

                // Insert into Order_Details
                $stmt = $pdo->prepare("INSERT INTO Order_Details (order_id, book_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $book_id, $quantity, $unit_price]);

                // Update stock (trigger will also handle this, but we include it for consistency)
                $stmt = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE book_id = ?");
                $stmt->execute([$quantity, $book_id]);

                // Check stock
                $stmt = $pdo->prepare("SELECT stock FROM books WHERE book_id = ?");
                $stmt->execute([$book_id]);
                $stock = $stmt->fetchColumn();
                if ($stock < 0) {
                    throw new Exception("Insufficient stock for book ID $book_id");
                }
            }

            // Clear the cart
            $stmt = $pdo->prepare("DELETE FROM Cart WHERE session_id = ?");
            $stmt->execute([$session_id]);

            $pdo->commit();
            $message = "Order placed successfully!";
            $message_type = "success";
        } else {
            throw new Exception("Cart is empty.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
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
            <li><a href="cart.php">View Cart</a></li>
            <li><a href="recent_orders.php">View Recent Orders</a></li>
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