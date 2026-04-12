<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vanguards_delights_db";

try {
    // 1. Initial Connection
    $conn = new PDO("mysql:host=$servername;port=3306", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create Database
    $sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;
    $conn->exec($sql);
    $conn->exec("USE " . $dbname);
    
    echo "Database created or already exists.<br>";

    // 3. Define Tables in Order (to respect Foreign Key constraints)
    $tables = [
        "USERS" => "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100),
            last_name VARCHAR(100) NOT NULL,
            username VARCHAR(100) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role VARCHAR(50) DEFAULT 'customer',
            user_status VARCHAR(50),
            date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            image_url TEXT
        )",

        "SESSIONS" => "CREATE TABLE IF NOT EXISTS sessions (
            session_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            token TEXT,
            ip_address VARCHAR(45),
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )",

        "CONTACT_NUMBERS" => "CREATE TABLE IF NOT EXISTS contact_numbers (
            contact_id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(20) NOT NULL,
            user_id INT,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )",

        "ADDRESSES" => "CREATE TABLE IF NOT EXISTS addresses (
            address_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            street VARCHAR(255),
            city VARCHAR(100),
            province VARCHAR(100),
            postal_code VARCHAR(20),
            country VARCHAR(100),
            is_default BOOLEAN DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )",

        "CATEGORIES" => "CREATE TABLE IF NOT EXISTS categories (
            category_id INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            image_url TEXT
        )",

        "PRODUCTS" => "CREATE TABLE IF NOT EXISTS products (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            stock_quantity INT DEFAULT 0,
            sku VARCHAR(100) UNIQUE,
            category_id INT,
            image_url TEXT,
            is_active BOOLEAN DEFAULT 1,
            product_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(category_id)
        )",

        "CART" => "CREATE TABLE IF NOT EXISTS cart (
            cart_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            cart_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )",

        "CART_ITEMS" => "CREATE TABLE IF NOT EXISTS cart_items (
            cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
            cart_id INT,
            product_id INT,
            quantity INT NOT NULL,
            subtotal DECIMAL(10, 2),
            FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        )",

        "ORDERS" => "CREATE TABLE IF NOT EXISTS orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            address_id INT,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            order_status VARCHAR(50),
            total_amount DECIMAL(10, 2),
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (address_id) REFERENCES addresses(address_id)
        )",

        "ORDER_ITEMS" => "CREATE TABLE IF NOT EXISTS order_items (
            order_item_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity INT NOT NULL,
            subtotal DECIMAL(10, 2),
            FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        )",

        "PAYMENT" => "CREATE TABLE IF NOT EXISTS payment (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            payment_method VARCHAR(100),
            amount DECIMAL(10, 2),
            payment_status VARCHAR(50),
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(order_id)
        )",

        "REVIEWS" => "CREATE TABLE IF NOT EXISTS reviews (
            review_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            product_id INT,
            rating INT CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            rate_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        )"
    ];

    // Execute each table creation
    foreach ($tables as $name => $query) {
        $conn->exec($query);
    }

    echo "Tables for 'Vanguard's Delights' created successfully.<br>";

    // 4. Sample Admin Insert (Using the hashing method from your sample)
    $checkUser = $conn->query("SELECT count(*) FROM users")->fetchColumn();
    if ($checkUser == 0) {
        $data = ['Admin', 'System', 'Vanguard', 'admin', md5('admin123'), 'administrator'];
        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, username, password, role) VALUES (?,?,?,?,?,?)");
        $stmt->execute($data);
        echo "Default admin user created.";
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$conn = null;
?>