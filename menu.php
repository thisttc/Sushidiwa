<?php
// เริ่มต้น session และเชื่อมต่อ database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'components/connect.php';

// ตรวจสอบการล็อกอินด้วย session และ cookie
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif(isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

require_once 'components/add_wishlist.php';
require_once 'components/add_cart.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - our shop page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<?php include 'components/user_header.php'; ?>
 	<div class="products">
 		<div class="heading">
 			<h1>our <span>products</span></h1>
 		</div>
 		<div class="box-container" id="products-container">
 			<div class="loading">กำลังโหลดสินค้า...</div>
 		</div>
 	</div>

	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
	<script src = "js/user_script.js"></script>
	
	<!-- สคริปต์สำหรับจัดการสินค้าแบบ real-time -->
	<script>
	// ฟังก์ชันโหลดสินค้าผ่าน XMLHttpRequest
	function loadProducts() {
		const xhr = new XMLHttpRequest();
		xhr.open('GET', 'api/get_products.php', true);
		
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4) {
				const container = document.getElementById('products-container');
				
				if (xhr.status === 200) {
					try {
						const response = JSON.parse(xhr.responseText);
						
						if (response.success) {
							displayProducts(response.products);
						} else {
							container.innerHTML = '<div class="empty"><p>เกิดข้อผิดพลาด: ' + response.message + '</p></div>';
						}
					} catch (e) {
						container.innerHTML = '<div class="empty"><p>เกิดข้อผิดพลาดในการประมวลผลข้อมูล</p></div>';
					}
				} else {
					container.innerHTML = '<div class="empty"><p>เกิดข้อผิดพลาดในการเชื่อมต่อ</p></div>';
				}
			}
		};
		
		xhr.send();
	}
	
	// ฟังก์ชันแสดงสินค้าในหน้า
	function displayProducts(products) {
		const container = document.getElementById('products-container');
		
		if (products.length === 0) {
			container.innerHTML = '<div class="empty"><p>no products added yet!</p></div>';
			return;
		}
		
		let html = '';
		
		products.forEach(product => {
			const isDisabled = product.stock == 0;
			const disabledClass = isDisabled ? 'disabled' : '';
			
			// กำหนดคลาสสีตามจำนวนสต็อก
			let stockClass = '';
			let stockText = '';
			
			if (product.stock > 9) {
				stockClass = 'stock-green';
				stockText = 'In stock';
			} else if (product.stock == 0) {
				stockClass = 'stock-red';
				stockText = 'out of stock';
			} else {
				stockClass = 'stock-red';
				stockText = `Hurry, only ${product.stock}`;
			}
			
			html += `
			<form action="" method="post" class="box ${disabledClass}" data-product-id="${product.id}">
				<img src="uploaded_files/${product.image}" class="image">
				<span class="stock ${stockClass}" id="product-stock-${product.id}" data-stock="${product.stock}">
					${stockText}
				</span>
				<div class="content">
					<img src="image/81.png" alt="" class="shap">
					<div class="button">
						<div><h3 class="name">${product.name}</h3></div>
						<div>
							<button type="submit" name="add_to_cart"><i class="fa-solid fa-cart-shopping"></i></button>
							<button type="submit" name="add_to_wishlist"><i class="fa-regular fa-heart"></i></button>
							<a href="view_page.php?pid=${product.id}" class="fa-regular fa-eye"></a>
						</div>
					</div>
					<p class="price">price $${product.price}</p>
					<input type="hidden" name="product_id" value="${product.id}">
					<div class="flex-btn">
						<input type="number" name="qty" required min="1" value="1" max="${product.stock}" maxlength="2" class="qty box" id="qty-${product.id}">
					</div>
				</div>
			</form>
			`;
		});
		
		container.innerHTML = html;
	}
	
	// ฟังก์ชันอัพเดตสต็อกสินค้า
	function updateProductStock(productId, newStock) {
		const xhr = new XMLHttpRequest();
		xhr.open('POST', 'api/update_stock.php', true);
		xhr.setRequestHeader('Content-Type', 'application/json');
		
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					try {
						const response = JSON.parse(xhr.responseText);
						
						if (response.success) {
							updateStockUI(productId, newStock);
						} else {
							console.error('Failed to update stock:', response.message);
						}
					} catch (e) {
						console.error('Error parsing response:', e);
					}
				} else {
					console.error('Request failed with status:', xhr.status);
				}
			}
		};
		
		const data = JSON.stringify({
			product_id: productId,
			new_stock: newStock
		});
		
		xhr.send(data);
	}
	
	// ฟังก์ชันอัพเดต UI หลังจากเปลี่ยนสต็อก
	function updateStockUI(productId, newStock) {
		const stockElement = document.getElementById(`product-stock-${productId}`);
		const qtyInput = document.getElementById(`qty-${productId}`);
		const productForm = document.querySelector(`form[data-product-id="${productId}"]`);
		
		if (!stockElement) return;
		
		// อัพเดตข้อมูล attribute
		stockElement.setAttribute('data-stock', newStock);
		
		// อัพเดตข้อความสต็อกและคลาส
		let stockClass = '';
		let stockText = '';
		
		if (newStock > 9) {
			stockClass = 'stock-green';
			stockText = 'In stock';
		} else if (newStock == 0) {
			stockClass = 'stock-red';
			stockText = 'out of stock';
		} else {
			stockClass = 'stock-red';
			stockText = `Hurry, only ${newStock}`;
		}
		
		// อัพเดตคลาสและข้อความ
		stockElement.className = 'stock ' + stockClass;
		stockElement.textContent = stockText;
		
		// อัพเดตจำนวนสูงสุดที่สั่งได้
		if (qtyInput) {
			qtyInput.max = newStock;
			
			// ถ้าจำนวนที่เลือกมากกว่าสต็อกใหม่ ให้ลดลง
			if (parseInt(qtyInput.value) > newStock) {
				qtyInput.value = newStock;
			}
		}
		
		// เพิ่ม/ลบคลาส disabled
		if (newStock == 0) {
			productForm.classList.add('disabled');
		} else {
			productForm.classList.remove('disabled');
		}
	}
	
	// โหลดสินค้าเมื่อหน้าเว็บโหลดเสร็จ
	document.addEventListener('DOMContentLoaded', function() {
		loadProducts();
		
		// โหลดสินค้าใหม่ทุก 30 วินาที
		setInterval(loadProducts, 30000);
	});
	</script>

	<?php include 'components/alert.php'; ?>

</body>
</html>