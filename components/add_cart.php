<?php
// add_cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'components/connect.php';

// ตรวจสอบการล็อกอินด้วย session
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// ตรวจสอบว่าเป็น request แบบ AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

//add product to cart
if(isset($_POST['add_to_cart'])) {
    $response = array('success' => false, 'message' => '');
    
    if($user_id != '') {
        $id = unique_id();
        $product_id = $_POST['product_id'];

        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_STRING);

        $verify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $verify_cart->execute([$user_id, $product_id]);

        $max_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $max_cart_items->execute([$user_id]);

        if($verify_cart->rowCount() > 0) {
            $response['message'] = 'product already exist in your cart';
        }
        else if($max_cart_items->rowCount() > 20) {
            $response['message'] = 'your cart is full';
        }
        else {
            $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

            $insert_cart = $conn->prepare("INSERT INTO `cart` (id, user_id, product_id, price, qty) VALUES(?, ?, ?, ?, ?)");
            if($insert_cart->execute([$id, $user_id, $product_id, $fetch_price['price'], $qty])) {
                $response['success'] = true;
                $response['message'] = 'product added to your cart successfully';
            } else {
                $response['message'] = 'failed to add product to cart';
            }
        }
    }
    else {
        $response['message'] = 'please login first';
    }
    
    // ถ้าเป็น AJAX request ให้ส่ง JSON response
    if($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // ถ้าไม่ใช่ AJAX ใช้ระบบ alert เดิม
        if($response['success']) {
            $success_msg[] = $response['message'];
        } else {
            $warning_msg[] = $response['message'];
        }
    }
}
?>