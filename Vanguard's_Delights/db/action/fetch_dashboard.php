<?php
// db/action/fetch_dashboard.php

function getDashboardSummary($conn) {
    $stats = [];
    $stats['total_orders'] = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
    $sales = $conn->query("SELECT SUM(total_amount) FROM orders WHERE order_status = 'Completed'")->fetchColumn();
    $stats['total_sales'] = $sales ?: 0;
    $stats['total_customers'] = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() ?: 0;
    return $stats;
}

// DAILY: Date format (e.g., "Apr 15")
function getDailySales($conn) {
    $sql = "SELECT DATE_FORMAT(order_date, '%b %d') as sales_date, SUM(total_amount) as revenue 
            FROM orders WHERE order_status = 'Completed'
            GROUP BY DATE(order_date) ORDER BY order_date ASC LIMIT 7";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// WEEKLY: Date Range format (e.g., "Apr 01 - Apr 07")
function getWeeklySales($conn) {
    $sql = "SELECT CONCAT(DATE_FORMAT(DATE_SUB(order_date, INTERVAL WEEKDAY(order_date) DAY), '%b %d'), ' - ', 
                   DATE_FORMAT(DATE_ADD(order_date, INTERVAL (6 - WEEKDAY(order_date)) DAY), '%b %d')) as sales_date, 
            SUM(total_amount) as revenue 
            FROM orders WHERE order_status = 'Completed'
            GROUP BY WEEK(order_date, 1) ORDER BY order_date ASC LIMIT 5";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// MONTHLY: Month Name (e.g., "January")
function getMonthlySales($conn) {
    $sql = "SELECT DATE_FORMAT(order_date, '%M') as sales_date, SUM(total_amount) as revenue 
            FROM orders WHERE order_status = 'Completed'
            GROUP BY MONTH(order_date) ORDER BY order_date ASC";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getTopSellingProducts($conn) {
    $sql = "SELECT p.name, SUM(oi.quantity) as sold_count 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.product_id 
            GROUP BY p.product_id 
            ORDER BY sold_count DESC LIMIT 5";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// NEW: Top Selling Categories
function getTopSellingCategories($conn) {
    $sql = "SELECT c.category_name, SUM(oi.quantity) as sold_count 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.product_id 
            JOIN categories c ON p.category_id = c.category_id
            GROUP BY c.category_id 
            ORDER BY sold_count DESC LIMIT 5";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}