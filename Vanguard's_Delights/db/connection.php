<?php

require 'action/config.php';  // config.php is one folder deeper, inside action/

try {
    // Step 1: Connect without selecting a DB first
    $conn = new PDO("mysql:host=$servername;port=3306", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

    // Step 2: Create the database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $conn->exec("USE `$dbname`");

    // Step 3: Define all tables in dependency order
    $tables = [

        "USERS" => "CREATE TABLE IF NOT EXISTS users (
            user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100),
            last_name VARCHAR(100) NOT NULL,
            username VARCHAR(100) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role VARCHAR(50) DEFAULT 'customer',
            user_status VARCHAR(50),
            date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            image_url TEXT
        ) ENGINE=InnoDB",

        "SESSIONS" => "CREATE TABLE IF NOT EXISTS sessions (
            session_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            token TEXT,
            ip_address VARCHAR(45),
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB",

        "CONTACT_NUMBERS" => "CREATE TABLE IF NOT EXISTS contact_numbers (
            contact_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(20) NOT NULL,
            user_id INT UNSIGNED,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB",

        "ADDRESSES" => "CREATE TABLE IF NOT EXISTS addresses (
            address_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            street VARCHAR(255),
            city VARCHAR(100),
            province VARCHAR(100),
            postal_code VARCHAR(20),
            country VARCHAR(100),
            is_default BOOLEAN DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB",

        "CATEGORIES" => "CREATE TABLE IF NOT EXISTS categories (
            category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            image_url TEXT
        ) ENGINE=InnoDB",

        "PRODUCTS" => "CREATE TABLE IF NOT EXISTS products (
            product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            stock_quantity INT DEFAULT 0,
            sku VARCHAR(100) UNIQUE,
            category_id INT UNSIGNED,
            image_url TEXT,
            is_active BOOLEAN DEFAULT 1,
            product_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ) ENGINE=InnoDB",

        "CART" => "CREATE TABLE IF NOT EXISTS cart (
            cart_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            cart_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        ) ENGINE=InnoDB",

        "CART_ITEMS" => "CREATE TABLE IF NOT EXISTS cart_items (
            cart_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cart_id INT UNSIGNED,
            product_id INT UNSIGNED,
            quantity INT NOT NULL,
            subtotal DECIMAL(10, 2),
            FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        ) ENGINE=InnoDB",

        "ORDERS" => "CREATE TABLE IF NOT EXISTS orders (
            order_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            address_id INT UNSIGNED,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            order_status VARCHAR(50),
            total_amount DECIMAL(10, 2),
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (address_id) REFERENCES addresses(address_id)
        ) ENGINE=InnoDB",

        "ORDER_ITEMS" => "CREATE TABLE IF NOT EXISTS order_items (
            order_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED,
            product_id INT UNSIGNED,
            quantity INT NOT NULL,
            price DECIMAL(10, 2),
            subtotal DECIMAL(10, 2),
            FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        ) ENGINE=InnoDB",

        "PAYMENT" => "CREATE TABLE IF NOT EXISTS payment (
            payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED,
            payment_method VARCHAR(100),
            amount DECIMAL(10, 2),
            payment_status VARCHAR(50),
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ) ENGINE=InnoDB",

        "REVIEWS" => "CREATE TABLE IF NOT EXISTS reviews (
            review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            product_id INT UNSIGNED,
            rating INT CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            rate_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        ) ENGINE=InnoDB"

    ];

    // Step 4: Loop and create each table
    foreach ($tables as $tableName => $query) {
        try {
            $conn->exec($query);
            echo "Table <strong>$tableName</strong> created successfully.<br>";
        } catch (PDOException $e) {
            echo "Error creating table <strong>$tableName</strong>: " . $e->getMessage() . "<br>";
        }
    }

    // Step 5: Seed a default admin user if users table is empty
    $check = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

    if ($check == 0) {
        $defaultPassword = password_hash("admin123", PASSWORD_BCRYPT);

        $seed = $conn->prepare("INSERT INTO users (
            first_name, last_name, username, password, role, user_status
        ) VALUES (?, ?, ?, ?, ?, ?)");

        try {
            $conn->beginTransaction();
            $seed->execute(['Admin', 'User', 'admin', $defaultPassword, 'admin', 'active']);
            $conn->commit();
            echo "<br>Default admin user seeded successfully.<br>";
            echo "Username: <strong>admin</strong> | Password: <strong>admin123</strong><br>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error seeding admin user: " . $e->getMessage();
        }
    } else {
        echo "<br>Users table already has data. Skipping seed.<br>";
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>