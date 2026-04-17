<?php
// Siguraduhin na walang space sa itaas nito
header('Content-Type: application/json');

// I-off muna natin ang error reporting para malinis ang JSON
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once '../../db/connection.php';

$response = ['success' => false, 'message' => 'Initial state'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Kunin ang values (Siguraduhin na may laman ang mga ito)
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $action   = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Status mapping
    $new_status = ($action === 'received') ? 'Completed' : 'Cancelled';

    if ($order_id > 0) {
        try {
            // Gamitin ang $conn base sa connection.php mo
            // Siguraduhin na ang table name ay 'orders' at column ay 'order_status'
            $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $execute = $stmt->execute([$new_status, $order_id]);

            if ($execute) {
                $response['success'] = true;
                $response['message'] = "Database updated to $new_status for ID $order_id";
            } else {
                $response['message'] = "Query failed to execute.";
            }
        } catch (PDOException $e) {
            $response['message'] = "DB Error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Invalid Order ID: $order_id";
    }
} else {
    $response['message'] = "Not a POST request";
}

echo json_encode($response);
exit;