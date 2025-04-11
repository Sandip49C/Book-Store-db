<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Select Book to Buy</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">BookStore</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="select_book.php" class="active">Buy Books</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="recent_orders.php">Recent Orders</a></li>
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Select a Book to Buy</h1>
        <?php
        session_start();
        include 'db_connect.php';

        // Handle Add to Cart
        if (isset($_POST['add_to_cart'])) {
            $book_id = $_POST['book_id'];
            $quantity = $_POST['quantity'];
            $session_id = session_id();

            // Check if the book is already in the cart
            $stmt = $pdo->prepare("SELECT * FROM Cart WHERE session_id = ? AND book_id = ?");
            $stmt->execute([$session_id, $book_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                // Update quantity
                $new_quantity = $cart_item['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE Cart SET quantity = ? WHERE session_id = ? AND book_id = ?");
                $stmt->execute([$new_quantity, $session_id, $book_id]);
            } else {
                // Add new item to cart
                $stmt = $pdo->prepare("INSERT INTO Cart (session_id, book_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$session_id, $book_id, $quantity]);
            }
            echo "<div class='notification success'>Book added to cart! <a href='cart.php'>View Cart</a></div>";
        }

        // Display Books
        try {
            $stmt = $pdo->query("SELECT * FROM books");
            if ($stmt->rowCount() > 0) {
                echo '<table class="book-table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Title</th>';
                echo '<th>Author</th>';
                echo '<th>Price</th>';
                echo '<th>Stock</th>';
                echo '<th>Action</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                    echo "<td>\$" . number_format($row['price'], 2) . "</td>";
                    echo "<td>" . ($row['stock'] > 0 ? $row['stock'] : '<span class="out-of-stock">Out of Stock</span>') . "</td>";
                    echo "<td>";
                    if ($row['stock'] > 0) {
                        echo "<form method='POST' class='order-form'>";
                        echo "<input type='hidden' name='book_id' value='{$row['book_id']}'>";
                        echo "<div class='form-group'>";
                        echo "<label for='quantity_{$row['book_id']}'>Qty:</label>";
                        echo "<input type='number' name='quantity' id='quantity_{$row['book_id']}' min='1' max='{$row['stock']}' value='1' style='width: 60px;'>";
                        echo "</div>";
                        echo "<button type='submit' name='add_to_cart' class='btn btn-order'>Add to Cart</button>";
                        echo "</form>";
                    } else {
                        echo "<button class='btn btn-disabled' disabled>Add to Cart</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo "<div class='notification info'>No books available to buy.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='notification error'>Error fetching books: " . $e->getMessage() . "</div>";
        }
        ?>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>