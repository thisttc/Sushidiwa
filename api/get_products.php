<?php
session_start();
require_once '../components/connect.php';

header('Content-Type: application/json');

try {
    $select_products = $conn->prepare("SELECT id, name, price, image, stock, status FROM `products` WHERE status = ?");
    $select_products->execute(['active']);
    $products = $select_products->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>