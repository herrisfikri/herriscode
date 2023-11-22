<?php
// $_SESSION['res'] = 'test';
ob_start();
if (isset($_POST['email'])) {

	require 'connectDB.php';

	// $parents_email = base64_decode($_POST['email']);
	$parents_email = $_POST['email'];
	$parents_pass = $_POST['pwd'];
	$result = mysqli_query($con, "select status,parents_email from parents where parents_email='$parents_email'");
	if (empty($parents_email) || empty($parents_pass)) {
		header("location: login_users.php?error=emptyfields");
		exit();
	} else if (!filter_var($parents_email, FILTER_VALIDATE_EMAIL)) {
		header("location: login_users.php?error=invalidEmail");
		exit();
	} else {
		$sql = "SELECT * FROM parents WHERE parents_email=?";
		$result = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($result, $sql)) {
			header("location: login_users.php?error=sqlerror");
			exit();
		} else {
			mysqli_stmt_bind_param($result, "s", $parents_email);
			mysqli_stmt_execute($result);
			$resultl = mysqli_stmt_get_result($result);
			
			if ($row = mysqli_fetch_assoc($resultl)) {
				$pwdCheck = password_verify($_POST['pwd'],$row['password']);
				if ($pwdCheck == false) {
					header("location: login_users.php?error=wrongpassword");
					exit();
				} else if ($pwdCheck == true) {
					session_start();
					$_SESSION['Parents-name'] = $row['parents_name'];
					$_SESSION['Parents-email'] = $row['parents_email'];
					header("location: index_users.php?login=success");
					exit();
				}
			} else {
				header("location: login_users.php?error=nouser");
				exit();
			}
		}
	}
	mysqli_stmt_close($result);
	mysqli_close($conn);
} else {
	header("location: login_users.php");
	exit();
}
