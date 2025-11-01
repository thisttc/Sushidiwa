<?php
	include 'connect.php';
	 
	setcookie('seller_id', '', time() - 1, '/');
	header('Location: ../admin panel/login.php');
?>
