BookStore Web Application

Overview

BookStore is a full-stack online bookstore application that allows users to browse, purchase, and manage books. It includes features for customers to buy books, track orders, and for admins to manage the book inventory. The project demonstrates web development concepts like database integration, session management, and transaction handling.

Features

Browse and Buy Books: View available books and add them to a cart with quantity selection.
Cart Management: View cart, remove items, and checkout by providing customer details.
Order Tracking: View recent orders with details like customer, books, quantities, and totals.
Customer Dashboard: Search for customers and view their details and order history.
Admin Features: Add, edit, or delete books from the inventory.

Technologies Used

Frontend: HTML, CSS, JavaScript (minimal)
Backend: PHP (with PDO for database interactions)
Database: MySQL (with stored procedures and functions)
Environment: Local server (e.g., XAMPP)

Project Structure

index.php: Home page to browse books.
select_book.php: Page to add books to the cart.
cart.php: Cart management and checkout.
recent_orders.php: Displays recent orders.
customer.php: Customer dashboard to search and view customer details.
manage_books.php: Admin page to manage book inventory.
db_connect.php: Database connection setup.
css/style.css: Styles for the application.
bookstore.sql: SQL file for database schema and initial data.

Key Highlights

Uses PHP sessions to manage the cart across pages.
Implements database transactions in cart.php to ensure atomicity during order processing.
Secures the app with PDO prepared statements and input escaping to prevent SQL injection and XSS attacks.
Fixes include correct order counting, grouped order display, and cart clearing after purchase.

Future Improvements

Add user authentication for secure access.
Integrate a payment gateway for real transactions.
Enhance the UI with a JavaScript framework like React.

![image](https://github.com/user-attachments/assets/10f57293-98f6-4096-98f4-e83e3fe51a1b)
![image](https://github.com/user-attachments/assets/237eb146-23ca-4948-95bf-37c369fd4c5a)
![image](https://github.com/user-attachments/assets/5823805c-a010-42d7-bada-faa9fac05d4c)
![image](https://github.com/user-attachments/assets/f3ccaff0-cf14-42cb-bcaf-feebf5bd4c1f)
![image](https://github.com/user-attachments/assets/8277e17b-2910-4295-839b-2744b6cb2e30)

