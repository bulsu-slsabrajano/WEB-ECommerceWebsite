<?php
// db/action/fetch_reports.php

/* ── Revenue summary (total, this month, today) ── */
function getRevenueSummary($conn) {
    $row = $conn->query("
        SELECT
            COALESCE(SUM(total_amount), 0)                                              AS total_revenue,
            COALESCE(SUM(CASE WHEN MONTH(order_date)=MONTH(NOW())
                              AND YEAR(order_date)=YEAR(NOW())
                         THEN total_amount END), 0)                                     AS month_revenue,
            COALESCE(SUM(CASE WHEN DATE(order_date)=CURDATE()
                         THEN total_amount END), 0)                                     AS today_revenue,
            COUNT(*)                                                                     AS total_orders,
            COUNT(CASE WHEN order_status='completed' THEN 1 END)                        AS completed_orders,
            COUNT(CASE WHEN order_status='pending'   THEN 1 END)                        AS pending_orders,
            COUNT(CASE WHEN order_status='cancelled' THEN 1 END)                        AS cancelled_orders,
            COALESCE(AVG(CASE WHEN order_status='completed' THEN total_amount END), 0)  AS avg_order_value
        FROM orders
    ")->fetch(PDO::FETCH_ASSOC);
    return $row;
}

/* ── Monthly revenue for the last 12 months ── */
function getMonthlyRevenue($conn) {
    return $conn->query("
        SELECT DATE_FORMAT(order_date, '%b %Y') AS label,
               DATE_FORMAT(order_date, '%Y-%m') AS sort_key,
               COALESCE(SUM(total_amount), 0)   AS revenue,
               COUNT(*)                          AS orders
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY sort_key, label
        ORDER BY sort_key ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Daily revenue for last 30 days ── */
function getDailyRevenue($conn) {
    return $conn->query("
        SELECT DATE_FORMAT(order_date, '%b %d') AS label,
               DATE(order_date)                 AS sort_key,
               COALESCE(SUM(total_amount), 0)   AS revenue,
               COUNT(*)                          AS orders
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY sort_key, label
        ORDER BY sort_key ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Weekly revenue for last 12 weeks ── */
function getWeeklyRevenue($conn) {
    return $conn->query("
        SELECT CONCAT('Wk ', WEEK(order_date)) AS label,
               YEARWEEK(order_date)             AS sort_key,
               COALESCE(SUM(total_amount), 0)   AS revenue,
               COUNT(*)                          AS orders
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
        GROUP BY sort_key, label
        ORDER BY sort_key ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Order status breakdown ── */
function getOrderStatusBreakdown($conn) {
    return $conn->query("
        SELECT order_status                             AS status,
               COUNT(*)                                 AS count,
               COALESCE(SUM(total_amount), 0)           AS revenue
        FROM orders
        GROUP BY order_status
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Top 10 products by revenue ── */
function getTopProductsByRevenue($conn) {
    return $conn->query("
        SELECT p.name,
               SUM(oi.quantity)                AS units_sold,
               SUM(oi.subtotal)                AS revenue
        FROM order_items oi
        JOIN products p ON p.product_id = oi.product_id
        JOIN orders o   ON o.order_id   = oi.order_id
        WHERE o.order_status != 'cancelled'
        GROUP BY p.product_id, p.name
        ORDER BY revenue DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Top categories by revenue ── */
function getTopCategoriesByRevenue($conn) {
    return $conn->query("
        SELECT c.category_name,
               SUM(oi.quantity)  AS units_sold,
               SUM(oi.subtotal)  AS revenue
        FROM order_items oi
        JOIN products p   ON p.product_id   = oi.product_id
        JOIN categories c ON c.category_id  = p.category_id
        JOIN orders o     ON o.order_id     = oi.order_id
        WHERE o.order_status != 'cancelled'
        GROUP BY c.category_id, c.category_name
        ORDER BY revenue DESC
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Customer growth (new registrations per month, last 12 months) ── */
function getCustomerGrowth($conn) {
    return $conn->query("
        SELECT DATE_FORMAT(date_created, '%b %Y') AS label,
               DATE_FORMAT(date_created, '%Y-%m') AS sort_key,
               COUNT(*)                            AS new_customers
        FROM users
        WHERE role = 'customer'
          AND date_created >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY sort_key, label
        ORDER BY sort_key ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Recent transactions (last 10 orders) ── */
function getRecentTransactions($conn) {
    return $conn->query("
        SELECT o.order_id,
               o.order_date,
               o.order_status,
               o.total_amount,
               CONCAT(u.first_name, ' ', u.last_name) AS customer_name
        FROM orders o
        JOIN users u ON u.user_id = o.user_id
        ORDER BY o.order_date DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
}