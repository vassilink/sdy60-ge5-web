<?php 
session_name(sha1('sdy60ge5'));
session_start();

$error = array(); // store error messages
$data  = array(); // store validated/sanitized data

/*
* CHECK: User Logged In? 
*******************************************************************************/
if (session_status() === PHP_SESSION_ACTIVE) {
	if (isset($_SESSION['user'])) {
		unset($_SESSION['user']);
	}
}

header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/login.php') OR exit('Cannot redirect');
exit();
