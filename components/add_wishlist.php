<?php 
// add_wishlist.php
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

//add products in wishlist
if(isset($_POST['add_to_wishlist'])) {
    $response = array('success' => false, 'message' => '');
    
    if($user_id != '') {
        $id = unique_id();
        $product_id = $_POST['product_id'];

        $verify_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND product_id = ?");
        $verify_wishlist->execute([$user_id, $product_id]);

        $cart_num = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $cart_num->execute([$user_id, $product_id]);

        if ($verify_wishlist->rowCount() > 0) {
            $response['message'] = 'product already exist in your wishlist';
        }
        else if ($cart_num->rowCount() > 0) {
            $response['message'] = 'product already exist in your cart';
        }
        else if ($user_id != '') {
            $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (id, user_id, product_id, price) VALUES(?,?,?,?) ");
            if($insert_wishlist->execute([$id, $user_id, $product_id, $fetch_price['price']])) {
                $response['success'] = true;
                $response['message'] = 'product added to your wishlist successfully';
            } else {
                $response['message'] = 'failed to add product to wishlist';
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