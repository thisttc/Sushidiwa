<?php
// เริ่มต้น session และเชื่อมต่อ database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'components/connect.php';

// ตรวจสอบการล็อกอินด้วย session
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // ดึงข้อมูลโปรไฟล์ผู้ใช้
    $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
    $select_profile->execute([$user_id]);
    
    if($select_profile->rowCount() > 0) {
        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    } else {
        header('location:login.php');
        exit;
    }
} else {
    header('location:login.php');
    exit;
}

$select_orders = $conn->prepare("SELECT * FROM `orderss` WHERE user_id = ?");
$select_orders->execute([$user_id]);
$total_orders = $select_orders->rowCount();

//$select_message = $conn->prepare("SELECT * FROM `message` WHERE user_id = ?");
//$select_message->execute([$user_id]);
//$total_message = $select_message->rowCount();
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - user profile page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<?php include 'components/user_header.php'; ?>
	
	<section class="profile">
		<div class="heading">
			<h1>profile <span>detail</span></h1>
			<!-- <img src="image/line.png"> -->
		</div>
		<div class="details">
			<div class="user">
				<img src="uploaded_files/<?= $fetch_profile['image']; ?>">
				<h3><?= $fetch_profile['name']; ?></h3>
				<p>user</p>
				<a href="update.php" class="btn">update profile</a>
			</div>
			<div class="box-container">
				<div class="box">
					<div class="flex">
						<i class="fa-solid fa-folder-minus"></i>
						<h3><?= $total_orders; ?></h3>
					</div>
					<a href="order.php" class="btn">view orders</a>
				</div>
				<!-- <div class="box">
					<div class="flex">
						<i class="fa-solid fa-message"></i>
						<h3><?= $total_message; ?></h3>
					</div>
					<a href="message.php" class="btn">view message</a>
				</div> -->
			</div>
		</div>
	</section>


	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

	<script src = "js/user_script.js"></script>

	<?php include 'components/alert.php'; ?>

</body>
</html>