<?php
// ตรวจสอบว่า session ยังไม่เริ่มต้นก่อนจึงเรียก session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบ session user_id
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// เชื่อมต่อ database
include 'connect.php'; // เปลี่ยนจากเดิมที่อาจจะ include แค่ครั้งเดียว
?>

<header class="header">
    <section class="flex">
        
        <nav class="navbar">
            <a href="home.php">Home</a>
            <!--<a href="about-us.php">about us</a>-->
            <a href="menu.php">Shop</a>
            <a href="order.php">Order</a>
            <!--<a href="contact.php">contact us</a>-->
        </nav>
        <a href="home.php" class="logo"><img src="image/lego.png" width="130px"></a>
        <form action="search_product.php" method="post" class="search-form">
            <input type="text" name="search_product" placeholder="search product..." required maxlength="100">
            <button type="submit" class="fa-solid fa-magnifying-glass" id="search_product_btn"></button>
        </form>
        <div class="icons">
            <div class="fa-solid fa-bars" id="menu-btn"></div>
            <div class="fa-solid fa-magnifying-glass" id="search-btn"></div>

            <?php 
                $count_wishlist_item = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
                $count_wishlist_item->execute([$user_id]);
                $total_wishlist_items = $count_wishlist_item->rowCount();
            ?> 

            <a href="wishlist.php"><i class="fa-regular fa-heart"></i><sup><?= $total_wishlist_items; ?></sup></a>

            <?php 
                $count_cart_item = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_item->execute([$user_id]);
                $total_cart_items = $count_cart_item->rowCount();
            ?> 

            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i><sup><?= $total_cart_items; ?></sup></a>
            <div class="fa-regular fa-user" id="user-btn"></div>
        </div>
        <div class="profile-detail">
            <?php 
                if(isset($_SESSION['user_id'])) {
                    $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                    $select_profile->execute([$user_id]);
                    if($select_profile->rowCount() > 0 ) {
                        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            ?>
            <img src="uploaded_files/<?= $fetch_profile['image']; ?>">
            <h3 style="margin-bottom: 1rem;"><?= $fetch_profile['name']; ?></h3>
            <div class="flex-btn">
                <a href="profile.php" class="btn">view profile</a>
                <a href="components/user_logout.php" onclick="return confirm('logout from this website');" class="btn">log out</a>
            </div>
            <?php } 
                } else { ?>
                <h3 style="margin-bottom: 1rem;">please login or register</h3>
                <div class="flex-btn">
                    <a href="login.php" class="btn">login</a>
                    <a href="register.php" class="btn">register</a>
                </div>
            <?php } ?>
        </div>
    </section>
</header>