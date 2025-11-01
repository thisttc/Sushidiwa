<?php
session_start();
require_once '../components/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = $input['product_id'] ?? null;
$new_stock = $input['new_stock'] ?? null;

if (!$product_id || $new_stock === null) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $update_stmt = $conn->prepare("UPDATE `products` SET stock = ? WHERE id = ?");
    $update_stmt->execute([$new_stock, $product_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>