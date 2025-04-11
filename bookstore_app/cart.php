<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Your Cart</title>
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
            <li><a href="cart.php" class="active">Cart</a></li>
            <li><a href="recent_orders.php">Recent Orders</a></li>
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Your Cart</h1>
        <?php
        session_start();
        include 'db_connect.php';

        // Handle Remove from Cart
        if (isset($_GET['remove'])) {
            $cart_id = $_GET['remove'];
            $stmt = $pdo->prepare("DELETE FROM Cart WHERE cart_id = ?");
            $stmt->execute([$cart_id]);
            // Redirect to cart.php to refresh the cart display
            header("Location: cart.php");
            exit();
        }

        // Display Cart
        $session_id = session_id();
        $stmt = $pdo->prepare("
            SELECT c.cart_id, c.book_id, c.quantity, b.title, b.author, b.price, b.stock
            FROM Cart c
            JOIN books b ON c.book_id = b.book_id
            WHERE c.session_id = ?
        ");
        $stmt->execute([$session_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($cart_items) {
            echo "<table class='book-table'>";
            echo "<thead><tr><th>Book</th><th>Author</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr></thead>";
            echo "<tbody>";
            $grand_total = 0;
            foreach ($cart_items as $item) {
                $total = $item['price'] * $item['quantity'];
                $grand_total += $total;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['title']) . "</td>";
                echo "<td>" . htmlspecialchars($item['author']) . "</td>";
                echo "<td>\$" . number_format($item['price'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($item['quantity']) . "</td>";
                echo "<td>\$" . number_format($total, 2) . "</td>";
                echo "<td><a href='cart.php?remove={$item['cart_id']}' class='btn btn-back' onclick='return confirm(\"Are you sure you want to remove this item?\");'>Remove</a></td>";
                echo "</tr>";
            }
            echo "<tr><td colspan='4'><strong>Grand Total</strong></td><td>\$" . number_format($grand_total, 2) . "</td><td></td></tr>";
            echo "</tbody></table>";

            // Customer Details Form
            ?>
            <h2>Checkout</h2>
            <form method="POST" class="order-form">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" name="phone_number" id="phone_number">
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea name="address" id="address"></textarea>
                </div>
                <button type="submit" name="buy" class="btn btn-submit">Buy</button>
            </form>

            <?php
            // Handle Buy Action
            if (isset($_POST['buy'])) {
                try {
                    $pdo->beginTransaction();

                    // Save Customer Details
                    $first_name = $_POST['first_name'];
                    $last_name = $_POST['last_name'];
                    $email = $_POST['email'];
                    $phone_number = $_POST['phone_number'] ?: null;
                    $address = $_POST['address'] ?: null;

                    // Check if customer already exists by email
                    $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE email = ?");
                    $stmt->execute([$email]);
                    $existing_customer = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing_customer) {
                        $customer_id = $existing_customer['customer_id'];
                    } else {
                        // Insert new customer
                        $stmt = $pdo->prepare("
                            INSERT INTO customers (first_name, last_name, email, phone_number, address, balance)
                            VALUES (?, ?, ?, ?, ?, 0.00)
                        ");
                        $stmt->execute([$first_name, $last_name, $email, $phone_number, $address]);
                        $customer_id = $pdo->lastInsertId();
                    }

                    // Process the Order
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

                            // Update stock
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

                        // Redirect to cart.php with a success message
                        header("Location: cart.php?success=Order placed successfully!");
                        exit();
                    } else {
                        throw new Exception("Cart is empty.");
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "<div class='notification error'>Error: " . $e->getMessage() . "</div>";
                }
            }
        } else {
            echo "<div class='notification info'>Your cart is empty.</div>";
        }

        // Display success message if redirected with a success parameter
        if (isset($_GET['success'])) {
            echo "<div class='notification success'>" . htmlspecialchars($_GET['success']) . " <a href='recent_orders.php'>View Recent Orders</a></div>";
        }
        ?>
        <p><a href="select_book.php" class="btn btn-order">Continue Shopping</a></p>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>