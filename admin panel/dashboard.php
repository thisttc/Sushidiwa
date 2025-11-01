<?php
	include '../components/connect.php';

	if(isset($_COOKIE['seller_id'])) {
		$seller_id = $_COOKIE['seller_id'];
	}
	else {
		$seller_id = '';
		header('location:login.php');
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset = 'utf-8'>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - seller register</title>
	<link rel = "stylesheet" type = "text/css" href = "../css/admin_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<div class ="main-container">
        
		<?php include '../components/admin_header.php'; ?>

        <section class="dashboard">
            <div class="heading">
                <h1>dashboard</h1>
            </div>
            <div class="box-container">
                <div class="box">
				     <h3>welcome !</h3>
				     <p><?= $fetch_profile['name'];?></p>
				     <a href="update.php" class="btn">update profile</a>
			    </div>
                
                <div class="box">
	               <?php
	                   $select_products = $conn->prepare("SELECT * FROM `products` WHERE seller_id=?");
	                   $select_products->execute([$seller_id]);
	                   $number_of_products = $select_products->rowCount();
	               ?>
	               <h3><?= $number_of_products; ?></h3>
	               <p>products added</p>
	               <a href="add_products.php" class="btn">add product</a>
	            </div>

				<div class="box">
	                <?php 
	                	$select_products = $conn->prepare("SELECT * FROM `products` WHERE seller_id = ? AND status = ?");
	                	$select_products->execute([$seller_id, 'active']);
	                	$number_of_products = $select_products->rowCount();
	                ?>
	                <h3><?= $number_of_products; ?></h3>
	                <p>total products</p>
	                <a href="view_product.php" class="btn">view product</a>
	            </div>

                <div class="box">
	               <?php
	                   $select_users = $conn->prepare("SELECT * FROM `users`");
	                   $select_users->execute();
	                   $number_of_users = $select_users->rowCount();
	               ?>
	               <h3><?= $number_of_users; ?></h3>
	               <p>users account</p>
	               <a href="user_accounts.php" class="btn">see users</a>
	            </div>

			    <div class="box">
			    	<?php
			            $select_orders = $conn->prepare("SELECT * FROM `orderss` WHERE seller_id = ?");
			            $select_orders->execute([$seller_id]);
			            $number_of_orders = $select_orders->rowCount();
			        ?>
			        <h3><?= $number_of_orders; ?></h3>
			        <p>total orders placed</p>
			        <a href="admin_order.php" class="btn">total orders</a>
			    </div>
                
                <!-- New Monthly Sales Report Box -->
                <div class="box">
                    <?php
                        // Calculate monthly sales
                        $current_month = date('m');
                        $current_year = date('Y');
                        $select_monthly_sales = $conn->prepare("SELECT SUM(price * qty) as monthly_sales FROM `orderss` WHERE seller_id = ? AND MONTH(date) = ? AND YEAR(date) = ? AND status = 'delivered'");
                        $select_monthly_sales->execute([$seller_id, $current_month, $current_year]);
                        $monthly_sales = $select_monthly_sales->fetch(PDO::FETCH_ASSOC);
                        $monthly_sales_amount = $monthly_sales['monthly_sales'] ? $monthly_sales['monthly_sales'] : 0;
                    ?>
                    <h3><?= $monthly_sales_amount; ?>฿</h3>
                    <p>Monthly Sales</p>
                    <a href="report.php" class="btn">view report</a>
                </div>
            </div>
        </section>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
	<script src = "../js/admin_script.js"></script>

	<?php include '../components/alert.php'; ?>

</body>
</html>