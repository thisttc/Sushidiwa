<?php
	// เริ่มต้น session และเชื่อมต่อ database
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	require_once 'components/connect.php';

	// ตรวจสอบการล็อกอินด้วย session
	if(isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
	} else {
		header('location:login.php');
		exit;
	}

	if (isset($_GET['get_id'])) {
		$get_id = $_GET['get_id'];
		
		// ตรวจสอบว่าผู้ใช้เป็นเจ้าของคำสั่งซื้อนี้จริงๆ
		$verify_order = $conn->prepare("SELECT * FROM `orderss` WHERE id = ? AND user_id = ?");
		$verify_order->execute([$get_id, $user_id]);
		
		if($verify_order->rowCount() == 0) {
			header('location:order.php');
			exit;
		}
	} else {
		header('location:order.php');
		exit;
	}

	if (isset($_POST['cancel'])) {
		$update_order = $conn->prepare("UPDATE `orderss` SET status = ? WHERE id = ? AND user_id = ?");
		$update_order->execute(['canceled', $get_id, $user_id]);
		
		header('location:order.php');
		exit;
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - order detail page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<?php include 'components/user_header.php'; ?>
	
	<div class="order-detail">
		<div class="heading">
			<h1 id="order-main-title">my order detail</h1>
			
			<!-- <img src="image/p04.png"> -->
			
		</div>
		<div class="box-container">
	   		<?php
	    		$grand_total = 0;
	    		$select_order = $conn->prepare("SELECT * FROM `orderss` WHERE id = ? LIMIT 1");
	    		$select_order->execute([$get_id]);
	    
	    		if ($select_order->rowCount() > 0) {
	     			while ($fetch_order = $select_order->fetch(PDO::FETCH_ASSOC)) {
	      				$select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
	      				$select_product->execute([$fetch_order['product_id']]);
	      				if ($select_product->rowCount() > 0) {
	       					while ($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)) {
	        					$sub_total = ($fetch_order['price']* $fetch_order['qty']);
	        					$grand_total += $sub_total;
	       
	   		?>
	   		<div class="box">
	    		<div class="col">
	     			<p class="title"><i class="fa-regular fa-calendar-days"></i><?= $fetch_order['date']; ?></p>
	    			<img src="uploaded_files/<?= $fetch_product['image']; ?>" class="image">
	     			<p class="price">$<?= $fetch_product['price']; ?>/-</p>
	     			<h3 class="name"><?= $fetch_product['name']; ?></h3>
	     			<p class="grand-total">total amount payable : <span>$<?= $grand_total; ?>/-</span></p>
	    		</div>
	    		<div class="col">
	     			<p class="title">billing address</p>
				    <p class="user"><i class="fa-solid fa-user"></i><?= $fetch_order['name']; ?></p>
				    <p class="user"><i class="fa-solid fa-phone"></i><?= $fetch_order['number']; ?></p>
				    <p class="user"><i class="fa-solid fa-envelope"></i><?= $fetch_order['email']; ?></p>
				    <p class="user"><i class="fa-solid fa-location-dot"></i><?= $fetch_order['address']; ?></p>
				    <p class="status" style="color:<?php if($fetch_order['status'] == 'delivered'){echo "green";}elseif($fetch_order['status'] == 'canceled'){echo "red";}else{echo "orange";} ?>"><?= $fetch_order['status']; ?></p>
				    <?php if($fetch_order['status']=='canceled'){ ?>
				    	<a href="checkout.php?get_id=<?= $fetch_product['id']; ?>" class="btn" style="line-height: 3;">order again</a>
				    <?php } else { ?>
	      				<!--<form action="" method="post">
	       					<button type="submit" name="cancel" class="btn" onclick="return confirm('do you want to cancel this product');">cancel</button>
	      				</form>-->
	     			<?php } ?>
	    		</div>
	   		</div>
	   		<?php
	       					}
	      				}
	     			}
	    		} else {
	     			echo '<p class="empty">no order take placed yet</p>';
	    		}
	   		?>
	  	</div>
	</div>


	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

	<script src = "js/user_script.js"></script>

	<?php include 'components/alert.php'; ?>

</body>
</html>