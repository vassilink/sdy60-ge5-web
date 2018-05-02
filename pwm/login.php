<?php 
session_name(sha1('sdy60ge5'));
session_start();

$error = array(); // store error messages
$data  = array(); // store validated/sanitized data

/*
* ACTION: Database Connect 
*******************************************************************************/
require_once 'DB_Connect.php';
$db = connect();

/*
* CHECK: User Logged In? 
*******************************************************************************/
if (session_status() === PHP_SESSION_ACTIVE) {
	if (isset($_SESSION['user'])) {
		$user = $_SESSION['user'];
		
		if (ctype_digit($user) && (int) $user >= 0) {
			header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/path_draw.php') OR exit('Cannot redirect');
			exit();
		}
	}
}

/*
* ACTION: Submit Login 
*******************************************************************************/
if (isset($_POST['login']) && $_POST['login'] === 'Login') {
	/*
	* CHECK: Input values 
	*******************************************************************************/
	if (isset($_POST['email']) && strlen(trim($_POST['email'])) > 0) {
		$data['email'] = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL, array('options' => array('default' => '')));
	} else {
		$data['email']  = '';
		$error['email'] = 'Email is missing';
	} // endif - Submit email
	
	/*
	* CHECK: Account 
	*******************************************************************************/
	if (empty($error)) {
		$sql_player = 
			'SELECT uid, email '.
			'FROM players '.
			'WHERE email = "'.$data['email'].'"';
		$req_player = mysqli_query($db, $sql_player) or exit('Cannot request Player');
		$cnt_player = mysqli_affected_rows($db);
		$ftc_player = mysqli_fetch_array($req_player);
		
		if (!$cnt_player) {
			// Login Error - Invalid Account
			$error['account'] = 'Invalid Email';
		} else {
			// Login Successfull
			if (session_status() === PHP_SESSION_ACTIVE) {
				session_regenerate_id(TRUE);
				
				// Login User
				$_SESSION['user'] = $ftc_player['uid'];
				
				header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/path_draw.php') OR exit('Cannot redirect');
				exit();
			} else {
				exit('Error: #IND0141');
			} // endif - Session Active
		}
	} // endif - Error not Found
} // endif - Request Login
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pedestrian Web Mapping</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="../vendors/animate.css/animate.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
    <div>
      <a class="hiddenanchor" id="signup"></a>
      <a class="hiddenanchor" id="signin"></a>

      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" name="login" method="post">
              <h1>Login</h1>
              <div>
                <input class="form-control" id="email" name="email" type="email" required="" placeholder="Email">
              </div>
			  <div>
                <button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" name="login" type="submit" value="Login">Είσοδος</button>
              </div>

              <div class="clearfix"></div>

              
            </form>
          </section>
        </div>

        
      </div>
    </div>
  </body>
</html>
