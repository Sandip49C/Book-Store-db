<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Place Order</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="js/script.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">BookStore</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="select_book.php" class="active">Place Order</a></li>
            <li><a href="recent_orders.php">View Recent Orders</a></li>
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Place Your Order</h1>
        <?php
        include 'db_connect.php';
        $book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            if ($book['stock'] > 0) {
                ?>
                <div class="book-details">
                    <p><strong>Book:</strong> <?php echo htmlspecialchars($book['title']); ?> by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p><strong>Price:</strong> $<?php echo number_format($book['price'], 2); ?></p>
                </div>

                <!-- Customer Lookup Form -->
                <form method="POST" class="order-form" id="customer-lookup-form">
                    <div class="form-group">
                        <label for="customer_id">Customer ID:</label>
                        <input type="number" name="customer_id" id="customer_id" required>
                        <button type="submit" name="lookup_customer" class="btn btn-submit">Lookup Customer</button>
                    </div>
                </form>

                <?php
                if (isset($_POST['lookup_customer'])) {
                    $customer_id = $_POST['customer_id'];
                    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
                    $stmt->execute([$customer_id]);
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($customer) {
                        ?>
                        <div class="customer-details">
                            <h3>Customer Details</h3>
                            <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($customer['customer_id']); ?></p>
                            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($customer['phone_number'] ?: 'Not provided'); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['address'] ?: 'Not provided'); ?></p>
                        </div>

                        <!-- Order Form -->
                        <form action="process_order.php" method="POST" class="order-form">
                            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                            <div class="form-group">
                                <label for="quantity">Quantity:</label>
                                <input type="number" name="quantity" id="quantity" min="1" max="<?php echo $book['stock']; ?>" value="1" required>
                            </div>
                            <button type="submit" class="btn btn-submit">Place Order</button>
                        </form>
                        <?php
                    } else {
                        echo "<div class='notification error'>Customer not found.</div>";
                    }
                }
            } else {
                echo "<div class='notification error'>Sorry, this book is out of stock.</div>";
            }
        } else {
            echo "<div class='notification error'>Book not found.</div>";
        }
        ?>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>