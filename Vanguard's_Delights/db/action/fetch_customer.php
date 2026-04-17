<?php
function getAllCustomers($conn) {
    try {
        $sql = "SELECT 
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.email,
                    u.image_url,
                    u.user_status,
                    u.date_created,
                    (SELECT COUNT(*) 
                        FROM orders o 
                        WHERE o.user_id = u.user_id) AS order_count,
                    (SELECT COALESCE(SUM(o.total_amount), 0) 
                        FROM orders o 
                        WHERE o.user_id = u.user_id) AS total_spent
                FROM users u
                WHERE u.role = 'customer'
                ORDER BY u.date_created DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCustomerById($conn, $user_id) {
    try {
        $sql = "SELECT 
                    u.user_id,
                    u.first_name,
                    u.middle_name,
                    u.last_name,
                    u.username,
                    u.email,
                    u.image_url,
                    u.user_status,
                    u.date_created
                FROM users u
                WHERE u.user_id = ? AND u.role = 'customer'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function getCustomerContacts($conn, $user_id) {
    try {
        $sql = "SELECT contact_id, phone_number 
                FROM contact_numbers 
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCustomerAddresses($conn, $user_id) {
    try {
        $sql = "SELECT address_id, street, city, province, postal_code, country, is_default 
                FROM addresses 
                WHERE user_id = ? 
                ORDER BY is_default DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCustomerOrders($conn, $user_id) {
    try {
        $sql = "SELECT 
                    o.order_id,
                    o.order_date,
                    o.order_status,
                    o.total_amount
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.order_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>