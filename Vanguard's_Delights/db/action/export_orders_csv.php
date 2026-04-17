<?php
require_once '../../db/action/config.php';
require_once '../../db/connection.php';

session_start();

$stmt = $conn->query("
    SELECT 
        o.order_id,
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
        u.email,
        o.order_date,
        o.order_status,
        o.total_amount,
        p.payment_method,
        p.payment_status
    FROM orders o
    LEFT JOIN users u   ON u.user_id  = o.user_id
    LEFT JOIN payment p ON p.order_id = o.order_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'orders_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM para ma-open ng Excel ng maayos ang UTF-8
fwrite($output, "\xEF\xBB\xBF");

// Header row
fputcsv($output, ['Order ID', 'Customer', 'Email', 'Date', 'Order Status', 'Total (PHP)', 'Payment Method', 'Payment Status']);

foreach ($orders as $o) {
    fputcsv($output, [
        '#ORD-' . str_pad($o['order_id'], 3, '0', STR_PAD_LEFT),
        $o['customer_name'] ?? '—',
        $o['email'] ?? '—',
        !empty($o['order_date']) ? date('M d, Y', strtotime($o['order_date'])) : '—',
        ucfirst($o['order_status'] ?? ''),
        number_format($o['total_amount'], 2),
        $o['payment_method'] ?? '—',
        ucfirst($o['payment_status'] ?? '—'),
    ]);
}

fclose($output);
exit;