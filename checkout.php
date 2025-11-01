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

	if (isset($_POST['place_order'])) {
		$name =  $_POST['name'];
		$name = filter_var($name, FILTER_SANITIZE_STRING);

		$number =  $_POST['number'];
		$number = filter_var($number, FILTER_SANITIZE_STRING);

		$email =  $_POST['email'];
		$email = filter_var($email, FILTER_SANITIZE_STRING);

		$address =  $_POST['flat'].', '.$_POST['street'].', '.$_POST['city'].', '.$_POST['country'].', '.$_POST['pin'];
		$address = filter_var($address, FILTER_SANITIZE_STRING);
		$address_type = $_POST['address_type'];
		$address_type = filter_var($address_type, FILTER_SANITIZE_STRING);

		$method = $_POST['method'];
		$method = filter_var($method, FILTER_SANITIZE_STRING);

		$verify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
		$verify_cart->execute([$user_id]);


		if (isset($_GET['get_id'])) {
			$get_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
			$get_product->execute([$_GET['get_id']]);

			//เพิ่ม
			$error_msg = array();

			if($get_product->rowCount() > 0) {
				while($fetch_p = $get_product->fetch(PDO::FETCH_ASSOC)) {

					// ตรวจสอบสถานะสินค้าก่อนสั่งซื้อ (ส่วนที่เพิ่ม)
			        if ($fetch_p['status'] != 'active') {
			            $error_msg[] = 'product ' . $fetch_p['name'] . ' is currently not for sale.';
			            continue;
			        }
			        
			        // ตรวจสอบสต็อกสินค้า (ส่วนที่เพิ่ม)
			        if ($fetch_p['stock'] < 1) {
			            $error_msg[] = 'product ' . $fetch_p['name'] . ' is out of stock';
			            continue;
			        }

					$seller_id = $fetch_p['seller_id'];

					$insert_order = $conn->prepare("INSERT INTO `orderss` (id, user_id, seller_id, name, number, email, address, address_type, method, product_id, price, qty) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$insert_order->execute([uniqid(), $user_id, $seller_id, $name, $number, $email, $address, $address_type, $method, $fetch_p['id'], $fetch_p['price'], 1]);

					$update_stock = $conn->prepare("UPDATE `products` SET stock = stock - 1 WHERE id = ?");
        			$update_stock->execute([$fetch_p['id']]);

        			$update_sales = $conn->prepare("UPDATE `products` SET sales = sales + 1 WHERE id = ?");
                	$update_sales->execute([$fetch_p['id']]);


					header('location:order.php');
				}
			} else {
				$warning_msg[] = 'something went wrong';
			}
		} elseif($verify_cart->rowCount() > 0) {
			while($f_cart = $verify_cart->fetch(PDO::FETCH_ASSOC)) {

				$s_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
				$s_products->execute([$f_cart['product_id']]);
				$f_product = $s_products->fetch(PDO::FETCH_ASSOC);

				$seller_id = $f_product['seller_id'];

				// ตรวจสอบสถานะสินค้าก่อนสั่งซื้อ (ส่วนที่เพิ่ม)
		        if ($f_product['status'] != 'active') {
		            $error_msg[] = 'product ' . $f_product['name'] . ' is currently not for sale.';
		            continue;
		        }
		        
		        // ตรวจสอบสต็อกสินค้า (ส่วนที่เพิ่ม)
		        if ($f_product['stock'] < $f_cart['qty']) {
		            $error_msg[] = 'product ' . $f_product['name'] . ' is out of stock';
		            continue;
		        }

				$insert_order = $conn->prepare("INSERT INTO `orderss` (id, user_id, seller_id, name, number, email, address, address_type, method, product_id, price, qty) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$insert_order->execute([uniqid(), $user_id, $seller_id, $name, $number, $email, $address, $address_type, $method, $f_cart['product_id'], $f_cart['price'], $f_cart['qty']]);

				$update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
            	$update_stock->execute([$f_cart['qty'], $f_cart['product_id']]);

            	$update_sales = $conn->prepare("UPDATE `products` SET sales = sales + ? WHERE id = ?");
                $update_sales->execute([$f_cart['qty'], $f_cart['product_id']]);
			}

			if (!empty($error_msg)) {
		        $warning_msg = $error_msg;
		    }
			else if($insert_order) {
				$delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
				$delete_cart->execute([$user_id]);
				header('location:order.php');
			}
		}
		else {
			$warning_msg[] = 'something went wrong';
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - checkout page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<?php include 'components/user_header.php'; ?>
	
	<div class="checkout">
		<div class="heading">
			<h1>checkout summary</h1>
			<!-- <img src="image/line.png"> -->
		</div>
		<div class="row">
			<form id="fefr"action="" method="post" class="register">
				<input type="hidden" name="p_id" value="<?= $get_id; ?>">
				<h3>billing details</h3>
				<div class="flex">
					<div class="box">
						<div class="input-field">
							<p>your name <span>*</span></p>
							<input type="text" name="name" required maxlength="50" placeholder="Enter your name" class="input">
						</div>
						<div class="input-field">
							<p>your number <span>*</span></p>
							<input type="number" name="number" required maxlength="10" min="0" placeholder="Enter your number" class="input">
						</div>
						<div class="input-field">
							<p>your email <span>*</span></p>
							<input type="email" name="email" required maxlength="50" placeholder="Enter your email" class="input">
						</div>
						<div class="input-field">
							<p>payment method <span>*</span></p>
							<select name="method" class="input">
								<option value="cash on delivery">cash on delivery</option>
								<option value="cash on delivery">credit or debit card</option>
								<option value="cash on delivery">net banking</option>
							</select>
						</div>
						<!--<div class="input-field">
							<p>address type <span>*</span></p>
							<select name="address_type" class="input">
								<option value="home">Home</option>
								<option value="office">Office</option>
							</select>
						</div>-->

					
					<!-- Built-In Form Validation  -->
						<div class="input-field">
							<p>address<span>*</span></p>
							<input type="text" name="flat" required maxlength="50" placeholder="flat or building name" class="input">
						</div>
					</div>
					<div class="box">
						<div class="input-field">
							<p>sub-district<span>*</span></p>
							<input type="text" name="street" required maxlength="50" placeholder="e.g street name" class="input">
						</div>
						<div class="input-field">
							<p>district<span>*</span></p>
							<input type="text" name="city" required maxlength="50" placeholder="city name" class="input">
						</div>
						<div class="input-field">
							<p>province name<span>*</span></p>
							<input type="text" name="country" required maxlength="50" placeholder="e.g. country name" class="input">
						</div>
						<div class="input-field">
							<p>country name<span>*</span></p>
							<input type="text" name="country" required maxlength="50" placeholder="e.g. country name" class="input">
						</div>
						<div class="input-field">
							<p>pincode<span>*</span></p>
							<input type="number" name="pin" required maxlength="6" min="0" placeholder="e.g. 110011" class="input">
						</div>
					</div>
				</div>
				<button type="submit" name="place_order" class="btn">place order</button>
			</form>
			<div class="summary">
				<h3>my bag</h3>
				<div class="box-container">
					<?php 
						$grand_total = 0;
						if(isset($_GET['det_id'])) {
							$select_get = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
							$select_get->execute([$_GET['get_id']]);

							while($fetch_get = $select_get->fetch(PDO::FETCH_ASSOC)) {
								$sub_total = $fetch_get['price'];
								$grand_total+=$sub_total;
					?>
					<div class="flex">
						<img src="uploaded_files/<?= $fetch_get['image']; ?>" class="image">
						<div>
							<h3 class="name"><?= $fetch_get['name']; ?></h3>
							<p class="price">$<?= $fetch_get['price']; ?>/-</p>
						</div>
					</div>
					<?php
							}
						} else {
							$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
							$select_cart->execute([$user_id]);

							if ($select_cart->rowCount() > 0) {
								while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
									$select_products = $conn->prepare("SELECT * FROM products WHERE id = ?");
									$select_products->execute([$fetch_cart['product_id']]);
									$fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
									$sub_total = ($fetch_cart['qty'] * $fetch_products['price']);
									$grand_total += $sub_total;
					?>
					<div class="flex">
						<img src="uploaded_files/<?= $fetch_products['image']; ?>" class="image">
						<div>
							<h3 class="name"><?= $fetch_products['name']; ?></h3>
							<p class="price">$<?= $fetch_products['price']; ?> X <?= $fetch_cart['qty']; ?></p>
						</div>
					</div>
					<?php 
								}
							} else {
								echo '<p class="empty">your cart is empty</p>';
							}
						}
					?>
				</div>
				<div class="grand-total">
					<span>total amount payable: </span>
					<p>$<?= $grand_total; ?>/-</p>
				</div>
			</div>
		</div>
	</div>


	<script>

	// Front-End Validation //			

document.getElementById("fefr").addEventListener("submit", function(e) {
  const email = document.querySelector('input[name="email"]').value.trim();
  const emailRegex = /^[^\s@]+@(gmail\.com|email\.com)$/i;
  if (!emailRegex.test(email)) {
    alert("Emailต้องลงท้ายด้วย @gmail.com หรือ @email.com");
    e.preventDefault();
    return;
  }

	let number = document.querySelector('input[name="number"]').value;
	number = number.toString();

	const firstdigit = number.slice(0, 2);

  if (!["02","06","08","09"].includes(firstdigit)) {
  alert("เบอร์โทรศัพท์ต้องขึ้นต้นด้วย 06, 08 หรือ 09");
  e.preventDefault();
}


  let pin = document.querySelector('input[name="pin"]').value;
  pin = pin.toString();
  if (pin.length < 5 ) {
    alert("เลขไปรษณีย์ต้องมี 5 หลัก");
    e.preventDefault();
    return;
  }
  if (pin[0] === "0") {
    alert("เลขไปรษณีย์ต้องไม่ขึ้นต้นด้วย 0");
    e.preventDefault();
    return;
  }
});
</script>



	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

	<script src = "js/user_script.js"></script>

	<?php include 'components/alert.php'; ?>

</body>
</html>