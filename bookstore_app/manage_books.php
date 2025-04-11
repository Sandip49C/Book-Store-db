<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStore - Manage Books</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script>
        function toggleAddBookForm() {
            const form = document.getElementById('add-book-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
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
            <li><a href="customer.php">Customer Dashboard</a></li>
            <li><a href="manage_books.php" class="active">Manage Books</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Manage Books</h1>

        <!-- Button to Show/Hide Add New Book Form -->
        <button onclick="toggleAddBookForm()" class="btn btn-order">Add New Book</button>

        <!-- Add New Book Form (Hidden by Default) -->
        <div id="add-book-form" style="display: none;">
            <h2>Add New Book</h2>
            <form action="manage_books.php" method="POST" class="order-form">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="author">Author:</label>
                    <input type="text" name="author" id="author" required>
                </div>
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" name="stock" id="stock" min="0" required>
                </div>
                <div class="form-group">
                    <label for="publication_year">Publication Year:</label>
                    <input type="number" name="publication_year" id="publication_year" min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
                <button type="submit" name="add_book" class="btn btn-submit">Add Book</button>
            </form>
        </div>

        <!-- Display Books with Edit/Delete Options -->
        <h2>All Books</h2>
        <?php
        include 'db_connect.php';

        // Handle Add Book
        if (isset($_POST['add_book'])) {
            $title = $_POST['title'];
            $author = $_POST['author'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $publication_year = $_POST['publication_year'];

            try {
                $stmt = $pdo->prepare("CALL Add_Book(?, ?, ?, ?, ?)");
                $stmt->execute([$title, $author, $price, $stock, $publication_year]);
                echo "<div class='notification success'>Book added successfully!</div>";
            } catch (PDOException $e) {
                echo "<div class='notification error'>Error adding book: " . $e->getMessage() . "</div>";
            }
        }

        // Handle Update Book
        if (isset($_POST['update_book'])) {
            $book_id = $_POST['book_id'];
            $title = $_POST['title'];
            $author = $_POST['author'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $publication_year = $_POST['publication_year'];

            try {
                $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, price = ?, stock = ?, publication_year = ? WHERE book_id = ?");
                $stmt->execute([$title, $author, $price, $stock, $publication_year, $book_id]);
                echo "<div class='notification success'>Book updated successfully!</div>";
            } catch (PDOException $e) {
                echo "<div class='notification error'>Error updating book: " . $e->getMessage() . "</div>";
            }
        }

        // Handle Delete Book
        if (isset($_GET['delete'])) {
            $book_id = $_GET['delete'];
            try {
                $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
                $stmt->execute([$book_id]);
                echo "<div class='notification success'>Book deleted successfully!</div>";
            } catch (PDOException $e) {
                echo "<div class='notification error'>Error deleting book: " . $e->getMessage() . "</div>";
            }
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
                echo '<th>Publication Year</th>';
                echo '<th>Actions</th>';
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
                    echo "<a href='manage_books.php?edit={$row['book_id']}' class='btn btn-order'>Edit</a> ";
                    echo "<a href='manage_books.php?delete={$row['book_id']}' class='btn btn-back' onclick='return confirm(\"Are you sure you want to delete this book?\");'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo "<div class='notification info'>No books found in the database.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='notification error'>Error fetching books: " . $e->getMessage() . "</div>";
        }

        // Edit Book Form
        if (isset($_GET['edit'])) {
            $book_id = $_GET['edit'];
            $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
            $stmt->execute([$book_id]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($book) {
                ?>
                <h2>Edit Book</h2>
                <form action="manage_books.php" method="POST" class="order-form">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author:</label>
                        <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo $book['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" name="stock" id="stock" min="0" value="<?php echo $book['stock']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="publication_year">Publication Year:</label>
                        <input type="number" name="publication_year" id="publication_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $book['publication_year']; ?>" required>
                    </div>
                    <button type="submit" name="update_book" class="btn btn-submit">Update Book</button>
                </form>
                <?php
            }
        }
        ?>
        <p><a href="index.php" class="btn btn-back">Back to Home</a></p>
    </div>
</body>
</html>