<?php
include 'components/session_manager.php';
include 'components/connect.php';

// ตรวจสอบว่าล็อกอินอยู่แล้วหรือไม่
if(SessionManager::isLoggedIn()) {
    header('location:home.php');
    exit;
}

if(isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
    $select_user->execute([$email, $pass]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if($select_user->rowCount() > 0) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['login_time'] = time();
        
        session_regenerate_id(true);
        
        header('location:home.php');
        exit;
    }
    else {
        $warning_msg[] = 'incorrect email or password';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kab Shop - user login page</title>
    <link rel = "stylesheet" type = "text/css" href = "css/user_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <?php include 'components/user_header.php'; ?>
    <div class = "form-container">
        <form action="" method="post" enctype="multipart/form-data" class="login">
            <h3>login now</h3>
            <div class="flex">
                <div class="input-field">
                    <p>your email <span>*</span></p>
                    <input type = "email" name="email" placeholder="enter your email" maxlength="50" required class="box">
                </div>

                <div class="input-field">
                    <p>your password <span>*</span></p>
                    <input type = "password" name="pass" placeholder="enter your password" maxlength="50" required class="box">
                </div>
                
                <p class="link">do not have an acoount? <a href="register.php">register now</a></p>
                <input type="submit" name="submit" value="login now" class="btn">
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src = "js/user_script.js"></script>
    <?php include 'components/alert.php'; ?>
</body>
</html>