<?php
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

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - product detail page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<?php include 'components/user_header.php'; ?>
	<section class="view_page">
    	<div class="heading">
        	<h1 id="product-detail-title">product detail</h1>
        	<!-- <img src="image/p04.png"> -->
        	<!-- <img src="image/line.png"> -->
    	</div>
    	<?php
        	if (isset($_GET['pid'])) {
            	$pid = $_GET['pid'];
            	$select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            	$select_products->execute([$pid]);

            	if ($select_products->rowCount() > 0) {
                	while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
    	?>
    	<form action="" method="post" class="box">
        	<div class="img-box">
            	<img src="uploaded_files/<?= $fetch_products['image']; ?>">
        	</div>
        	<div class="detail">
            	<?php if($fetch_products['stock'] > 9){ ?>
                	<span class="stock" style="color: green;">In Stock</span>
            	<?php }elseif($fetch_products['stock'] == 0){ ?>
                	<span class="stock" style="color: red;">Out of Stock</span>
            	<?php }else{ ?>
                	<span class="stock" style="color:red;">Hurry only <?= $fetch_products['stock']; ?> left</span>
           		<?php } ?>
            	<p class="price">$<?= $fetch_products['price']; ?>/-</p>
            	<div class="name"><?= $fetch_products['name']; ?></div>
            	<p class="product-detail"><?= $fetch_products['product_detail']; ?></p>
            	<input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
            	<!--<div class="button">
                	<button type="submit" name="add_to_wishlist" class="btn" id="wishlist-btn">add to wishlist <i class="fa-regular fa-heart"></i></button>
                	<input type="hidden" name="qty" value="1" min="0" class="quantity">
                	<button type="submit" name="add_to_cart" class="btn" id="cart-btn">add to cart<i class="fa-solid fa-cart-shopping"></i></button>
        		</div>-->
        	</div>
    	</form>
    	<?php
                	}
            	}
        	}        
    	?>
	</section>
	<!--<div class="products">
    	<div class="heading">
        	<h1>similar products</h1>
        	<p>Explore these related items</p>
        	<img src="image/line.png">
    	</div>
    	 <?php include 'components/shop.php'; ?>
    </div>-->


	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

	<script src = "js/user_script.js"></script>

	<?php include 'components/alert.php'; ?>

</body>
</html>