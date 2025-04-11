<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Recent Orders</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">BookStore</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="select_book.php">Buy Books</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="recent_orders.php" class="active">Recent Orders</a></li>
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Recent Orders</h1>
        <?php
        include 'db_connect.php';
        try {
            // Fetch orders with their items grouped by order_id
            $stmt = $pdo->query("
                SELECT o.order_id, o.order_date, c.first_name, c.last_name, c.customer_id
                FROM orders o
                JOIN customers c ON o.customer_id = c.customer_id
                ORDER BY o.order_date DESC
                LIMIT 10
            ");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($orders) {
                echo "<table class='book-table'>";
                echo "<thead><tr><th>Order ID</th><th>Date</th><th>Customer</th><th>Items</th><th>Total</th></tr></thead>";
                echo "<tbody>";

                foreach ($orders as $order) {
                    $order_id = $order['order_id'];
                    $customer_id = $order['customer_id'];

                    // Fetch items for this order
                    $items_stmt = $pdo->prepare("
                        SELECT b.title, od.quantity, od.unit_price
                        FROM order_details od
                        JOIN books b ON od.book_id = b.book_id
                        WHERE od.order_id = ?
                    ");
                    $items_stmt->execute([$order_id]);
                    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Calculate total for this order
                    $total = 0;
                    $items_display = [];
                    foreach ($items as $item) {
                        $item_total = $item['quantity'] * $item['unit_price'];
                        $total += $item_total;
                        $items_display[] = htmlspecialchars($item['title']) . " (Qty: {$item['quantity']}, Total: \$" . number_format($item_total, 2) . ")";
                    }

                    echo "<tr>";
                    echo "<td>{$order['order_id']}</td>";
                    echo "<td>{$order['order_date']}</td>";
                    echo "<td><a href='customer.php?view_details={$customer_id}'>" . htmlspecialchars($order['first_name'] . " " . $order['last_name']) . "</a></td>";
                    echo "<td>" . implode("<br>", $items_display) . "</td>";
                    echo "<td>\$" . number_format($total, 2) . "</td>";
                    echo "</tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<div class='notification info'>No recent orders found.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='notification error'>Error fetching orders: " . $e->getMessage() . "</div>";
        }
        ?>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>