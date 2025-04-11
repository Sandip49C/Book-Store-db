<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Customer Dashboard</title>
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
            <li><a href="recent_orders.php">Recent Orders</a></li>
            <li><a href="customer.php" class="active">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Customer Dashboard</h1>
        <form method="GET" class="customer-form">
            <div class="form-group">
                <label for="search_query">Search Customer (ID, Name, Email, Phone, Address):</label>
                <input type="text" name="search_query" id="search_query" placeholder="Enter any customer detail" required>
            </div>
            <button type="submit" class="btn btn-submit">Search</button>
        </form>

        <?php
        include 'db_connect.php';

        // Search Customers
        if (isset($_GET['search_query'])) {
            $search_query = '%' . $_GET['search_query'] . '%';
            $stmt = $pdo->prepare("
                SELECT * FROM customers 
                WHERE customer_id LIKE ? 
                OR first_name LIKE ? 
                OR last_name LIKE ? 
                OR email LIKE ? 
                OR phone_number LIKE ? 
                OR address LIKE ?
            ");
            $stmt->execute([$search_query, $search_query, $search_query, $search_query, $search_query, $search_query]);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($customers) {
                echo "<h2>Search Results</h2>";
                echo "<table class='book-table'>";
                echo "<thead><tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Balance</th><th>Action</th></tr></thead>";
                echo "<tbody>";
                foreach ($customers as $customer) {
                    $customer_id = $customer['customer_id'];
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($customer['customer_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($customer['first_name'] . " " . $customer['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($customer['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($customer['phone_number'] ?: 'Not provided') . "</td>";
                    echo "<td>" . htmlspecialchars($customer['address'] ?: 'Not provided') . "</td>";
                    echo "<td>\$" . number_format($customer['balance'], 2) . "</td>";
                    echo "<td><a href='customer.php?view_details=$customer_id' class='btn btn-order'>View Details</a></td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<div class='notification error'>No customers found matching your search.</div>";
            }
        }

        // View Customer Details
        if (isset($_GET['view_details'])) {
            $customer_id = $_GET['view_details'];
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer) {
                echo "<h2>Customer: " . htmlspecialchars($customer['first_name']) . " " . htmlspecialchars($customer['last_name']) . "</h2>";
                echo "<div class='customer-details'>";
                echo "<p><strong>Customer ID:</strong> " . htmlspecialchars($customer['customer_id']) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($customer['email']) . "</p>";
                echo "<p><strong>Phone Number:</strong> " . htmlspecialchars($customer['phone_number'] ?: 'Not provided') . "</p>";
                echo "<p><strong>Address:</strong> " . htmlspecialchars($customer['address'] ?: 'Not provided') . "</p>";
                echo "<p><strong>Balance:</strong> \$" . number_format($customer['balance'], 2) . "</p>";
                echo "<p><strong>Total Orders:</strong> " . call_user_func(function($id) use ($pdo) {
                    $stmt = $pdo->prepare("SELECT Customer_Order_Count(?) AS order_count");
                    $stmt->execute([$id]);
                    return $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];
                }, $customer_id) . "</p>";
                echo "</div>";

                // Order History
                $stmt = $pdo->prepare("
                    SELECT o.order_id, o.order_date, b.title, od.quantity, od.unit_price
                    FROM orders o
                    JOIN order_details od ON o.order_id = od.order_id
                    JOIN books b ON od.book_id = b.book_id
                    WHERE o.customer_id = ?
                ");
                $stmt->execute([$customer_id]);
                echo "<h3>Order History</h3>";
                if ($stmt->rowCount() > 0) {
                    echo "<table class='book-table'>";
                    echo "<thead><tr><th>Order ID</th><th>Date</th><th>Book</th><th>Qty</th><th>Total</th></tr></thead>";
                    echo "<tbody>";
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['order_id']}</td>";
                        echo "<td>{$row['order_date']}</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>{$row['quantity']}</td>";
                        echo "<td>\$" . number_format($row['quantity'] * $row['unit_price'], 2) . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<div class='notification info'>No orders found for this customer.</div>";
                }
            } else {
                echo "<div class='notification error'>Customer not found.</div>";
            }
        }
        ?>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>