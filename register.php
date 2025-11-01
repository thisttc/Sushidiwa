<?php
include 'components/session_manager.php';
include 'components/connect.php';

// ตรวจสอบว่าล็อกอินอยู่แล้วหรือไม่
if(SessionManager::isLoggedIn()) {
    header('location:home.php');
    exit;
}

if(isset($_POST['submit'])) {
    $id = unique_id();
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $address = $_POST['address'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);

    $phone = $_POST['phone'];
    $phone = filter_var($phone, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
	$image = filter_var($image, FILTER_SANITIZE_STRING);
	$ext = pathinfo($image, PATHINFO_EXTENSION);
	$rename = unique_id().'.'.$ext;
	$image_size = $_FILES['image']['size'];
	$image_tmp_name = $_FILES['image']['tmp_name'];
	$image_folder = 'uploaded_files/'.$rename;

    $select_seller = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select_seller->execute([$email]);

    if($select_seller->rowCount() > 0) {
        $warning_msg[] = 'email already exist!';
    }
    else {
        if($pass != $cpass) {
            $warning_msg[] = 'confirm password not matched';
        }
        else {
            
            $insert_seller = $conn->prepare("INSERT INTO `users`(id, name, email, password, address, phone, image) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $insert_seller->execute([$id, $name, $email, $pass, $address, $phone, $rename]);

            if($insert_seller) {
                // สร้าง session ทันทีหลังลงทะเบียนสำเร็จ
                $_SESSION['user_id'] = $id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $name;
                $_SESSION['login_time'] = time();

                move_uploaded_file($image_tmp_name, $image_folder);
                
                $success_msg[] = 'new user registered! redirecting...';
                header('refresh:2;url=home.php');
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
    <title>Kab Shop - home page</title>
    <link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <?php include 'components/user_header.php'; ?>
    <div class = "form-container">
        <form id="fefr" action="" method="post" enctype="multipart/form-data" class="register">
            <h3>register now</h3>
            <div class = "flex">
                <div class = "col">
                    <div class="input-field">
                        <p>your name <span>*</span></p>
                        <input type = "text" name="name" placeholder="enter your name" maxlength="50" required class="box">
                    </div>
                    <div class="input-field">
                        <p>your email <span>*</span></p>
                        <input type = "email" name="email" placeholder="enter your email" maxlength="50" required class="box">
                    </div>
                    <div class="input-field">
                        <p>your password <span>*</span></p>
                        <input type = "password" name="pass" placeholder="enter your password" maxlength="50" required class="box">
                    </div>
                </div>
                <div class = "col">
                    <div class="input-field">
                        <p>your address <span>*</span></p>
                        <input type="text" name="address" placeholder="enter your address" maxlength="100" required class="box">
                    </div>
                    <div class="input-field">
                        <p>your phone number <span>*</span></p>
                        <input type="text" name="phone" placeholder="enter your phone number" maxlength="15" required class="box">
                    </div>
                    <div class="input-field">
                        <p>confirm password <span>*</span></p>
                        <input type = "password" name="cpass" placeholder="confirm your password" maxlength="50" required class="box">
                    </div>
                </div>
            </div>
            <div class="input-field">
				<p>your profile <span>*</span></p>
				<input type = "file" name="image" accept="image/*" required class="box">
			</div>
            <p class="link">already have an acoount? <a href="login.php">login now</a></p>
            <input type="submit" name="submit" value="register now" class="btn">
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src = "js/user_script.js"></script>

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

        let phone = document.querySelector('input[name="phone"]').value;
        phone = phone.toString();

        const firstdigit = phone.slice(0, 2);

        if (!["06","08","09"].includes(firstdigit)) {
            alert("เบอร์โทรศัพท์ต้องขึ้นต้นด้วย 06, 08 หรือ 09");
            e.preventDefault();
        }

        let pin = document.querySelector('input[name="pin"]').value;
        if (pin) {
            pin = pin.toString();
            if (pin.length < 6 ) {
                alert("เลขไปรษณีย์ต้องมี 6 หลัก");
                e.preventDefault();
                return;
            }
            if (pin[0] === "0") {
                alert("เลขไปรษณีย์ต้องไม่ขึ้นต้นด้วย 0");
                e.preventDefault();
                return;
            }
        }
    });
    </script>

    <?php include 'components/alert.php'; ?>
</body>
</html>