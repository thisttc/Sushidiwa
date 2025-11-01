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
    $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
    $select_user->execute([$user_id]);
    
    if($select_user->rowCount() > 0) {
        $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
        $fetch_profile = $fetch_user; // ตั้งค่าให้ใช้กับฟอร์ม
    } else {
        header('location:login.php');
        exit;
    }
} else {
    header('location:login.php');
    exit;
}

if(isset($_POST['submit'])) {
  
    $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
    $select_user->execute([$user_id]);
    $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
  
    $prev_pass = $fetch_user['password'];
    $prev_image = $fetch_user['image'];
  
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    /*$line_id = filter_var($_POST['line_id'], FILTER_SANITIZE_STRING);*/
    
    //update name
    if (!empty($name)) {
        $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
        $update_name->execute([$name, $user_id]);
        
        // อัพเดท session name ด้วย
        $_SESSION['user_name'] = $name;
        
        $success_msg[] = 'username updated successfully';
    }

    //update email
    if (!empty($email)) {
        $select_email = $conn->prepare("SELECT * FROM `users` WHERE id = ? AND email = ?");
        $select_email->execute([$user_id, $email]);
    
        if($select_email->rowCount() > 0) {
            $warning_msg[] = 'email already exist';
        }
        else {
            $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id =?");
            $update_email->execute([$email, $user_id]);
            
            // อัพเดท session email ด้วย
            $_SESSION['user_email'] = $email;
            
            $success_msg[] = 'email updated successfully';
        }
    }

    // Update address
    if (!empty($address)) {
        $update_address = $conn->prepare("UPDATE `users` SET address = ? WHERE id = ?");
        $update_address->execute([$address, $user_id]);
        $success_msg[] = 'Address updated successfully';
    }

    // Update phone
    if (!empty($phone)) {
        $update_phone = $conn->prepare("UPDATE `users` SET phone = ? WHERE id = ?");
        $update_phone->execute([$phone, $user_id]);
        $success_msg[] = 'Phone number updated successfully';
    }

    // Update LINE ID
    /*if (!empty($line_id)) {
        $update_line_id = $conn->prepare("UPDATE `users` SET line_id = ? WHERE id = ?");
        $update_line_id->execute([$line_id, $user_id]);
        $success_msg[] = 'LINE ID updated successfully';
    }*/

    //update image
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = unique_id().'.'.$ext;
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_files/'.$rename;
   
    if(!empty($image)) {
        if ($image_size > 20000000) {
            $warning_msg[] = ' image size is too large';
        }
        else {
            $update_image = $conn->prepare("UPDATE `users` SET `image` = ? WHERE id = ?");
            $update_image->execute([$rename, $user_id]);
            move_uploaded_file($image_tmp_name, $image_folder);
     
            if ($prev_image != '' AND $prev_image != $rename) {
                unlink('uploaded_files/'.$prev_image);
            }
            $success_msg[] = 'image updated successfully';
        }
    }

    //update password
    $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   
    /*$old_pass = sha1($_POST['old_pass']);
    $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);*/
   
    $new_pass = sha1($_POST['new_pass']);
    $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
   
    if ($new_pass != $empty_pass) {
        /*if ($old_pass != $prev_pass) {
            $warning_msg[] = 'old password not matched';
        }*/
        if ($cpass == $empty_pass) {
            $warning_msg[] = 'Please re-enter your password to confirm';
        }
        else if($new_pass != $cpass) {
            $warning_msg[] = 'password not matched';
        }
        else {
            if ($new_pass != $empty_pass) {
                $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id =?");
                $update_pass->execute([$cpass, $user_id]);
                $success_msg[] = 'password updated successfully';
            }
            else {
                $warning_msg[] = 'please enter a new password';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kab Shop - update profile login page</title>
	<link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
	<?php include 'components/user_header.php'; ?>
	<section class="form-container">
			<div class="heading">
				<h1 id="update-title">update profile details</h1>
			</div>
			 <form action="" method="post" enctype="multipart/form-data" class="register">
    			<div class="img-box">
     				<img src="uploaded_files/<?= $fetch_profile['image']; ?>">
    			</div>
    			<h3>update profile</h3>
    			<div class="flex">
     				<div class="col">
      					<div class="input-field">
       						<p>your name</p>
       						<input type="text" name="name" placeholder="<?= $fetch_profile['name']; ?>" class="box">
      					</div>
      					<div class="input-field">
       						<p>your email</p>
       						<input type="email" name="email" placeholder="<?= $fetch_profile['email']; ?>" class="box">
      					</div>
      					<div class="input-field">
							<p>Your Address</p>
							<input type="text" name="address" placeholder="enter your new address" class="box">
						</div>
      					<!-- <div class="input-field">
       						<p>old password</p>
       						<input type="password" name="old_pass" placeholder="enter your old password" class="box">
      					</div> -->
     				</div>
     				<div class="col">
     					
						<div class="input-field">
							<p>Your Phone</p>
							<input type="text" name="phone" placeholder="enter your new phone" class="box">
						</div>
						<div class="input-field">
       						<p>new password</p>
       						<input type="password" name="new_pass" placeholder="enter your new password" class="box">
      					</div>
						<div class="input-field">
       						<p>confirm password</p>
       						<input type="password" name="cpass" placeholder="confirm your password" class="box">
      					</div>
						<!-- <div class="input-field">
							<p>Your LINE ID</p>
							<input type="text" name="line_id" placeholder="enter your new line ID" class="box">
						</div>-->
      					<div class="input-field">
       						<p>select pic</p>
       						<input type="file" name="image" accept="image/*" class="box">
      					</div>
     				</div>
    			</div>
    			<input type="submit" name="submit" value="update profile" class="btn" id="update-btn"> 
   			</form>
		</section>
	</div>




	<?php include 'components/footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

	<script src = "js/user_script.js"></script>

	<?php include 'components/alert.php'; ?>

</body>
</html>