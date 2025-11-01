<?php
	include '../components/connect.php';

	if (isset($_COOKIE['seller_id'])) {
		$seller_id = $_COOKIE['seller_id'];
	} else {
		$seller_id = '';
		header('Location: login.php');
		exit(); // Added exit after redirect
	}

	// Update order from database
	if (isset($_POST['update_order'])) { // Fixed typo here
		$order_id = $_POST['order_id'];
		$order_id = filter_var($order_id, FILTER_SANITIZE_STRING);
  
		$update_payment = $_POST['update_payment'];
		$update_payment = filter_var($update_payment, FILTER_SANITIZE_STRING);
  
		$update_pay = $conn->prepare("UPDATE `orderss` SET payment_status = ? WHERE id = ?");
		$update_pay->execute([$update_payment, $order_id]);
		$success_msg[] = 'Order payment status updated';

    // Check if the selected payment status is 'order delivered'
    if ($update_payment == 'order delivered') {
        $update_status = 'delivered'; // Set the status to delivered
    } else {
        $update_status = 'pending'; // Default to pending or any other relevant status
    }
  
    // Update the payment status and order status in the database
    $update_pay = $conn->prepare("UPDATE `orderss` SET payment_status = ?, status = ? WHERE id = ?");
    $update_pay->execute([$update_payment, $update_status, $order_id]);
    $success_msg[] = 'Order payment status updated';
	}
 
	// Delete order
	if (isset($_POST['delete_order'])) {
		$delete_id = $_POST['order_id'];
		$delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
  
		$verify_delete = $conn->prepare("SELECT * FROM `orderss` WHERE id = ?");
		$verify_delete->execute([$delete_id]); // Fixed variable name typo

		if ($verify_delete->rowCount() > 0) {
			$delete_order = $conn->prepare("DELETE FROM `orderss` WHERE id = ?");
			$delete_order->execute([$delete_id]);
			$success_msg[] = 'Order deleted';
		} else {
			$warning_msg[] = 'Order already deleted'; // Fixed syntax error
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - Seller Register</title>
	<link rel="stylesheet" type="text/css" href="../css/admin_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<div class="main-container">
		<?php include '../components/admin_header.php'; ?>
		<section class="order-container">
			<div class="heading">
				<h1>Total Orders Placed</h1>
			</div>
			<div class="box-container">
				<?php
					$select_order = $conn->prepare("SELECT * FROM `orderss` WHERE seller_id = ?");
					$select_order->execute([$seller_id]);
     
					if ($select_order->rowCount() > 0) {
						while ($fetch_order = $select_order->fetch(PDO::FETCH_ASSOC)) {
				?>
				<div class="box">
					<div class="status" style="color: <?php echo ($fetch_order['status'] == 'in progress') ? 'limegreen' : 'red'; ?>"><?= $fetch_order['status']; ?></div>
					<div class="details">
						<p>User Name: <span><?= $fetch_order['name']; ?></span></p>
						<p>User ID: <span><?= $fetch_order['user_id']; ?></span></p>
						<p>Placed On: <span><?= $fetch_order['date']; ?></span></p>
						<p>User Number: <span><?= $fetch_order['number']; ?></span></p>
						<p>User Email: <span><?= $fetch_order['email']; ?></span></p>
						<p>Total Price: <span><?= $fetch_order['price']; ?></span></p>
						<p>Payment Method: <span><?= $fetch_order['method']; ?></span></p>
						<p>User Address: <span><?= $fetch_order['address']; ?></span></p>
					</div>
					<form action="" method="post">
						<input type="hidden" name="order_id" value="<?= $fetch_order['id']; ?>">
						<select name="update_payment" class="box" style="width: 90%;">
							<option disabled selected><?= $fetch_order['payment_status']; ?></option>
							<option value="pending">Pending</option>
							<option value="order delivered">Order Delivered</option> <!-- Fixed spelling -->
						</select>
						<div class="flew-btn">
							<input type="submit" name="update_order" value="Update Payment" class="btn">
							<input type="submit" name="delete_order" value="Delete Order" class="btn" onclick="return confirm('Delete this order?');">
						</div>
					</form>
				</div>
				<?php
						}
					} else {
						echo '<div class="empty"><p>No orders placed yet!</p></div>';
					}
				?>
			</div>
		</section>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
	<script src="../js/admin_script.js"></script>

	<?php include '../components/alert.php'; ?>
</body>
</html>
