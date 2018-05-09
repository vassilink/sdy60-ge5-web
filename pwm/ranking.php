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
			$sql_player = 
				'SELECT uid, email '.
				'FROM players '.
				'WHERE uid = "'.$user.'"';
			$req_player = mysqli_query($db, $sql_player) or exit('Cannot request Player');
			$cnt_player = mysqli_affected_rows($db);
			$ftc_player = mysqli_fetch_array($req_player);
			
			if (!$cnt_player) {
				// Login Error - Invalid Account
				unset($_SESSION['user']);
				header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/login.php') OR exit('Cannot redirect');
				exit();
			} else {
				$player = explode('@', $ftc_player['email']);
			}
		} else {
			// Login Error - Invalid Account
			unset($_SESSION['user']);
			header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/login.php') OR exit('Cannot redirect');
			exit();
		}
	} else {
		header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/login.php') OR exit('Cannot redirect');
		exit();
	}
} else {
	header('Location: https://webeasy.gr/projects/eap/sdy60/ge5/pwm/login.php') OR exit('Cannot redirect');
	exit();
}

/*
* GET: Page Data 
*******************************************************************************/
$sql_list = 
	'SELECT uid, email, '.
	'ROUND((pts_paths / 10)) AS paths_total, '.
	'pts_meters, '.
	'ROUND((pts_reviews / 10)) AS reviews_total, '.
	'(pts_paths + pts_10_paths + pts_rated_path + pts_1000_meters + pts_5000_meters + pts_reviews + pts_10_reviews) AS pts_total '.
	'FROM v_points_2 '.
	'ORDER BY pts_total desc';
$req_list = mysqli_query($db, $sql_list) or exit('Cannot request Players Points List');
$cnt_list = mysqli_affected_rows($db);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

    <title>Pedestrian Web Mapping</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
	
	<!-- Mapbox -->
	<!--script src='https://api.mapbox.com/mapbox-gl-js/v0.44.2/mapbox-gl.js'></script>
	<link href='https://api.mapbox.com/mapbox-gl-js/v0.44.2/mapbox-gl.css' rel='stylesheet' /-->
	
	<!-- Mapbox JS/CSS -->
	<script src='https://api.mapbox.com/mapbox.js/v3.1.1/mapbox.js'></script>
	<link href='https://api.mapbox.com/mapbox.js/v3.1.1/mapbox.css' rel='stylesheet' />
	
	<!-- Leaflet JS/CSS -->
	<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-draw/v0.4.10/leaflet.draw.css' rel='stylesheet' />
	<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-draw/v0.4.10/leaflet.draw.js'></script>

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.css" rel="stylesheet">
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="#" class="site_title"><i class="fa fa-globe"></i> <span>PWM</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
				<img src="images/0.jpg" alt="..." class="img-circle profile_img">
              </div>
              <div class="profile_info">
				<h2><?= $player[0]; ?></h2>
              </div>
              <div class="clearfix"></div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>ΕΠΙΛΟΓΕΣ</h3>
                <ul class="nav side-menu">
                  <li><a href="path_draw.php"><i class="fa fa-edit"></i> Σχεδίαση μονοπατιού </a></li>
				  <li><a href="path_rate.php"><i class="fa fa-clone"></i> Αξιολόγηση μονοπατιού </a></li>
				  <li><a href="ranking.php"><i class="fa fa-table"></i> Κατάταξη</a></li>
				  <li><a href="achievements.php"><i class="fa fa-trophy"></i> Επιτεύγματα</a></li>
                  <li><a href="stats.php"><i class="fa fa-bar-chart-o"></i> Στατιστικά </a></li>
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->
		  </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="images/0.jpg" alt=""><?= $player[0]; ?>
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <!--li><a href=""><i class="fa fa-user pull-right"></i> Προφίλ</a></li-->
                    <li><a href="logout.php"><i class="fa fa-sign-out pull-right"></i> Αποσύνδεση</a></li>
                  </ul>
                </li>

                <li role="presentation" class="dropdown">
                  <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                    <li>
                      <a>
                        <span class="image"><img src="images/0.jpg" alt="Profile Image" /></span>
                        <span>
                          <span><?= $player[0]; ?></span>
                          <span class="time">&nbsp;</span>
                        </span>
                        <span class="message">
                          &nbsp;
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="text-center">
                        <a>
                          <strong>&nbsp;</strong>
                          <i class="fa fa-angle-right"></i>
                        </a>
                      </div>
                    </li>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Κατάταξη παικτών</h3>
              </div>

              <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                  &nbsp;
                </div>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Κατάταξη με βάση τους κερδισμένους πόντους</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li id="distance_display">&nbsp;</li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<!-- Ranking -->
                    <table class="table table-striped projects">
                      <thead>
                        <tr>
                          <th style="width: 5%">α/α</th>
                          <th style="width: 20%">Παίκτης</th>
                          <th><i class="fa fa-map-o"></i> Μονοπάτια</th>
                          <th><i class="fa fa-arrows-h"></i> Μέτρα</th>
                          <th><i class="fa fa-star-half-o"></i> Αξιολογήσεις</th>
                          <th style="width: 20%"><i class="fa fa-calculator"></i> Πόντοι</th>
                        </tr>
                      </thead>
                      <tbody>
						<?php $aa = 1; ?>
						<?php while ($cnt_list > 0 && $ftc_list = mysqli_fetch_array($req_list)) { ?>
							<?php $ranked_player = explode('@', $ftc_list['email']); ?>
							<tr>
								<td><?= $aa; ?></td>
								<td><?= $ranked_player[0]; ?></td>
								<td><?= $ftc_list['paths_total']; ?></td>
								<td><?= $ftc_list['pts_meters']; ?></td>
								<td><?= $ftc_list['reviews_total']; ?></td>
								<td><?= $ftc_list['pts_total']; ?></td>
							</tr>
							<?php $aa++; ?>
						<?php } // endwhile ?>
					</tbody>
                   </table>
                   <!-- /Ranking -->
				  </div>
				  
				  <div class="x_content">
					<div class="row">
						&nbsp;
					</div>
				  </div>
				  
				  <div class="x_content">
					<!-- Ranking Rules -->
					<div class="x_panel tile">
						<div class="x_title">
							<h4>Κριτήρια απόδοσης πόντων</h4>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<div class="dashboard-widget-content">
								<ul class="quick-list">
									<li><i class="fa fa-map-o"></i> Σχεδίαση ενός (1) μονοπατιού: <span class="badge bg-green">10</span></li>
									<li><i class="fa fa-map-o"></i> Σχεδίαση δέκα (10) μονοπατιών: <span class="badge bg-green">100</span></li>
									<li><i class="fa fa-arrows-h"></i> Κάθε ένα (1) μέτρο σχεδιασμένου μονοπατιού: <span class="badge bg-green">1</span></li>
									<li><i class="fa fa-arrows-h"></i> Κάθε χίλια (1000) μέτρα σχεδιασμένου μονοπατιού: <span class="badge bg-green">100</span></li>
									<li><i class="fa fa-arrows-h"></i> Κάθε πέντε χιλιάδες (5000) μέτρα σχεδιασμένου μονοπατιού: <span class="badge bg-green">500</span></li>
									<li><i class="fa fa-star-half-o"></i> Αξιολόγηση ενός (1) μονοπατιού: <span class="badge bg-green">10</span></li>
									<li><i class="fa fa-star-half-o"></i> Αξιολόγηση δέκα (10) μονοπατιών: <span class="badge bg-green">100</span></li>
									<li><i class="fa fa-thumbs-down"></i> Μείωση πόντων μονοπατιού με αξιολόγηση 1 αστεριού: <span class="badge bg-red">-20%</span></li>
									<li><i class="fa fa-thumbs-down"></i> Μείωση πόντων μονοπατιού με αξιολόγηση 2 αστεριών: <span class="badge bg-red">-10%</span></li>
									<li><i class="fa fa-thumbs-up"></i> Αύξηση πόντων μονοπατιού με αξιολόγηση 4 αστεριών: <span class="badge bg-green">+10%</span></li>
									<li><i class="fa fa-thumbs-up"></i> Αύξηση πόντων μονοπατιού με αξιολόγηση 5 αστεριών: <span class="badge bg-green">+20%</span></li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Ranking Rules -->
				  </div>
				</div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            &nbsp;
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>
  </body>
</html>
