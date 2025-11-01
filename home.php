<?php
	session_start();
	include 'components/connect.php';
	if(isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
		if(isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
			session_destroy();
			header('location:login.php');
			exit;
		}
		$_SESSION['login_time'] = time();
	} else {
		$user_id = '';
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - home page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

	<?php include 'components/user_header.php'; ?>

	<!-- slider section start -->
	<div class="slider-container">
    <div class="slider">
        <div class="slideBox active">
            <div class="textBox">
                <h1>Best Sushi<br>in KMUTNB</h1>
                <a href="menu.php" class="btn">shop now</a>
            </div>
            <div class="imgBox">
                <img src="image/58.jpg">
            </div>
        	</div>
    	</div>
		</div>


	<!-- slider section end -->

	<!--<div class="service">
		<div class="box-container">
			<div class="box">
				<div class="icon">
					<div class="icon-box">
						<img src="image/delivery_1.png" class="img1">
						<img src="image/delivery_2.png" class="img2">
					</div>
				</div>
				<div class="detail">
					<h4>delivery</h4>
					<span>100% secure</span>
				</div>
			</div>
			<div class="box">
				<div class="icon">
					<div class="icon-box">
						<img src="image/payment_1.png" class="img1">
						<img src="image/payment_2.png" class="img2">
					</div>
				</div>
				<div class="detail">
					<h4>payment</h4>
					<span>100% secure</span>
				</div>
			</div>
			<div class="box">
				<div class="icon">
					<div class="icon-box">
						<img src="image/support_1.png" class="img1">
						<img src="image/support_2.png" class="img2">
					</div>
				</div>
				<div class="detail">
					<h4>support</h4>
					<span>24*7 hours</span>
				</div>
			</div>
			<div class="box">
				<div class="icon">
					<div class="icon-box">
						<img src="image/gift_1.png" class="img1">
						<img src="image/gift_2.png" class="img2">
					</div>
				</div>
				<div class="detail">
					<h4>gift service</h4>
					<span>support gift service</span>
				</div>
			</div>
			<div class="box">
				<div class="icon">
					<div class="icon-box">
						<img src="image/return_1.png" class="img1">
						<img src="image/return_2.png" class="img2">
					</div>
				</div>
				<div class="detail">
					<h4>returns</h4>
					<span>24*7 free return</span>
				</div>
			</div>
			<div class="box">
				<div class="icon">
					<div class="icon-box">
						<img src="image/save_1.png" class="img1">
						<img src="image/save_2.png" class="img2">
					</div>
				</div>
				<div class="detail">
					<h4>save money</h4>
					<span>hot promotion</span>
				</div>
			</div>
		</div>
	</div>-->

	<!-- service section end -->

	<div class="categories">
		<div class="heading">
			<h1>categories features</h1>
			<!-- <img src="image/p04.png"> -->
		</div>
		<div class="box-container">
			<div class="box">
				<img src="image/71.jpg">
				<a href="menu.php" class="btn">Nigiri sushi</a>
			</div>
			<div class="box">
				<img src="image/72.jpg">
				<a href="menu.php" class="btn">Makizushi</a>
			</div>
			<div class="box">
				<img src="image/73.jpg">
				<a href="menu.php" class="btn">Gunkan Maki</a>
			</div>
		</div>
	</div>

	<!-- categories section end -->

	<!--<img src="image/22.jpg" class="menu-banner">-->
	<!--<div class="taste">
		<div class="heading">-->
			<!-- <span>Kab Shop</span> -->
			<!--<h1>Your Inspiration Starts Here</h1>-->
			<!-- <img src="image/line.png"> -->
		<!--</div>
		<div class="box-container">
			<div class="box">
				<img src="image/46.png">
				<div class="detail">
					<h2>Kawaii & Cute</h2>
					<h1>Notebooks</h1>
				</div>
			</div>
			<div class="box">
				<img src="image/47.png">
				<div class="detail">
					<h2>Various types</h2>
					<h1>of stickers</h1>
				</div>
			</div>
			<div class="box">
				<img src="image/48.png">
				<div class="detail">
					<h2>colorful</h2>
					<h1>colored pencils</h1>
				</div>
			</div>
		</div>
	</div>-->

	<!-- taste section end -->

	<!--<div class="ice-container">
		<div class="overlay"></div>
		<div class="detail">
			<h1>Unleash Your Imagination at<br>Kab Shop!</h1>
			<p>Dive into a world of creativity with our extensive range of stationery! From vibrant notebooks and colorful pens to unique planners and artistic supplies, we have everything you need to bring your ideas to life. Whether you're a student, an artist, or just love to organize, our carefully curated selection will inspire you every day.<br>Visit us today and discover the magic of writing and creating!</p>
			<a href="menu.php" class="btn">shop now</a>
		</div>
	</div>-->

	<!-- container section end -->

	<!--<div class="taste2">
		<div class="t-banner">
			<div class="overlay"></div>
			<div class="detail">
				<h1>find your taste of desserts</h1>
				<p>vjdizfghbidhgbiodfnjihseiodlfknbhoudhfnbhiozdnbi'zhnpdl</p>
				<a href="menu.php" class="btn">shop now</a>
			</div>
		</div>
		<div class="box-container">
			<div class="box">
				<div class="box-overlay"></div>
				<img src="image/21.jpg">
				<div class="box-details fadeIn-bottom">
					<h1>strawberry</h1>
					<p>find your taste of desserts</p>
					<a href="menu.php" class="btn">explore more</a>
				</div>
			</div>
			<div class="box">
				<div class="box-overlay"></div>
				<img src="image/21.jpg">
				<div class="box-details fadeIn-bottom">
					<h1>strawberry</h1>
					<p>find your taste of desserts</p>
					<a href="menu.php" class="btn">explore more</a>
				</div>
			</div>
			<div class="box">
				<div class="box-overlay"></div>
				<img src="image/21.jpg">
				<div class="box-details fadeIn-bottom">
					<h1>strawberry</h1>
					<p>find your taste of desserts</p>
					<a href="menu.php" class="btn">explore more</a>
				</div>
			</div>
			<div class="box">
				<div class="box-overlay"></div>
				<img src="image/21.jpg">
				<div class="box-details fadeIn-bottom">
					<h1>strawberry</h1>
					<p>find your taste of desserts</p>
					<a href="menu.php" class="btn">explore more</a>
				</div>
			</div>
			<div class="box">
				<div class="box-overlay"></div>
				<img src="image/21.jpg">
				<div class="box-details fadeIn-bottom">
					<h1>strawberry</h1>
					<p>find your taste of desserts</p>
					<a href="menu.php" class="btn">explore more</a>
				</div>
			</div>
		</div>
	</div>-->

	<!-- taste2 section end -->

	<!--<div class="flavour">
		<div class="box-container">
			<img src="image/21.jpg">
			<div class="detail">
				<h1>Hot Dael! Sale Uo To <span>20% off</span></h1>
				<p>expired</p>
				<a href="menu.php" class="btn">shop now</a>
			</div>
		</div>
	</div>-->

	<!-- flavour section end -->

	<!--<div class="usage">
		<div class="heading">
			<h1>how it works</h1>
			<img src="image/p04.png">
		</div>
		<div class="row">
			<div class="box-container">
				<div class="box">
				 	<img src="image/29.jpg">
				 	<div class="detail">
				 		<h3>scoop ice-cream</h3>
				 		<p>nvbiorfjbnjdfnbnbozfjlnjkdfnbjkzdnfbhjozednbjdfnbzjedbnjednbjzednbjndzdnbjednbkznezjdbnze</p>
				 	</div>
				</div>
				<div class="box">
				 	<img src="image/29.jpg">
				 	<div class="detail">
				 		<h3>scoop ice-cream</h3>
				 		<p>nvbiorfjbnjdfnbnbozfjlnjkdfnbjkzdnfbhjozednbjdfnbzjedbnjednbjzednbjndzdnbjednbkznezjdbnze</p>
				 	</div>
				</div>
				<div class="box">
				 	<img src="image/29.jpg">
				 	<div class="detail">
				 		<h3>scoop ice-cream</h3>
				 		<p>nvbiorfjbnjdfnbnbozfjlnjkdfnbjkzdnfbhjozednbjdfnbzjedbnjednbjzednbjndzdnbjednbkznezjdbnze</p>
				 	</div>
				</div>
			</div>
			<img src="image/divider.png" class="divider">
			<div class="box-container">
				<div class="box">
				 	<img src="image/29.jpg">
				 	<div class="detail">
				 		<h3>scoop ice-cream</h3>
				 		<p>nvbiorfjbnjdfnbnbozfjlnjkdfnbjkzdnfbhjozednbjdfnbzjedbnjednbjzednbjndzdnbjednbkznezjdbnze</p>
				 	</div>
				</div>
				<div class="box">
				 	<img src="image/29.jpg">
				 	<div class="detail">
				 		<h3>scoop ice-cream</h3>
				 		<p>nvbiorfjbnjdfnbnbozfjlnjkdfnbjkzdnfbhjozednbjdfnbzjedbnjednbjzednbjndzdnbjednbkznezjdbnze</p>
				 	</div>
				</div>
				<div class="box">
				 	<img src="image/29.jpg">
				 	<div class="detail">
				 		<h3>scoop ice-cream</h3>
				 		<p>nvbiorfjbnjdfnbnbozfjlnjkdfnbjkzdnfbhjozednbjdfnbzjedbnjednbjzednbjndzdnbjednbkznezjdbnze</p>
				 	</div>
				</div>
			</div>
		</div>
	</div>-->

	<!-- usage section end -->

	<!--<div class="pride">
		<div class="detail">
			<h1>We pride ourselves on <br> exceptional flavors.</h1>
			<p>enjfcoivdsnSDNJkSVNKSNDVWENldsvnojwsnbvg<br>oWJEVNEWJDSnvIKPWSVGWNSVJDNGJfnvaiegvnikaenb</p>
			<a href="menu.php" class="btn">shop now</a>
		</div>
	</div>-->

	<!-- pride section end -->


	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
	
	<script src = "js/user_script.js"></script>

	<?php include 'components/alert.php'; ?>

</body>
</html>