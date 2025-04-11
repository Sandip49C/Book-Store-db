<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">BookStore</div>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="select_book.php">Buy Books</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="recent_orders.php">Recent Orders</a></li>
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Welcome to BookStore</h1>
        <h2>Available Books</h2>
        <?php
        include 'db_connect.php';
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
                echo '<th>Publication Year</th>';
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
                    echo "<td>" . htmlspecialchars($row['publication_year']) . "</td>";
                    echo "<td>";
                    if ($row['stock'] > 0) {
                        echo "<a href='select_book.php' class='btn btn-order'>Buy Now</a>";
                    } else {
                        echo "<button class='btn btn-disabled' disabled>Out of Stock</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo "<div class='notification info'>No books available.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='notification error'>Error fetching books: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
</body>
</html>