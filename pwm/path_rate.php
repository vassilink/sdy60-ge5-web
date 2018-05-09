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
	'SELECT uid, player_id, meters, pathpoints '.
	'FROM web_paths '.
	'WHERE meters > 0';
$req_list = mysqli_query($db, $sql_list) or exit('Cannot request Paths List');
$cnt_list = mysqli_affected_rows($db);

$data['stars_5'] = mysqli_fetch_row(mysqli_query($db, 'SELECT COUNT(1) FROM web_reviews WHERE rated = 5'))[0];
$data['stars_4'] = mysqli_fetch_row(mysqli_query($db, 'SELECT COUNT(1) FROM web_reviews WHERE rated = 4'))[0];
$data['stars_3'] = mysqli_fetch_row(mysqli_query($db, 'SELECT COUNT(1) FROM web_reviews WHERE rated = 3'))[0];
$data['stars_2'] = mysqli_fetch_row(mysqli_query($db, 'SELECT COUNT(1) FROM web_reviews WHERE rated = 2'))[0];
$data['stars_1'] = mysqli_fetch_row(mysqli_query($db, 'SELECT COUNT(1) FROM web_reviews WHERE rated = 1'))[0];
$data['stars_0'] = mysqli_fetch_row(mysqli_query($db, 'SELECT COUNT(1) FROM web_paths WHERE uid NOT IN (SELECT path_id FROM web_reviews) AND meters > 0;'))[0];
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
                <h3>Αξιολόγηση μονοπατιού</h3>
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
                    <h2>Επέλεξε και αξιολόγησε ένα μονοπάτι</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li id="distance_display">&nbsp;</li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<div id="map" style="height: 500px;"></div>
				  </div>
				  
				  <div class="x_content">
					<div class="row">
						&nbsp;
					</div>
				  </div>
				  
				  <div class="x_content">
					<!-- Path Categories -->
					<div class="x_panel tile overflow_hidden">
						<div class="x_title">
							<h4>Κατηγορίες μονοπατιών</h4>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<table class="" style="width:100%">
								<tr>
									<th style="width:63%;">
										<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
											<p class="">Αξιολόγηση</p>
										</div>
										<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
											<p class="">Πλήθος</p>
										</div>
									</th>
									<th>
										<p>&nbsp;</p>
									</th>
								</tr>
								<tr>
									<td>
										<table class="tile_info">
											<tr>
												<td><p><i class="fa fa-square star_5"></i>5 αστέρια </p></td>
												<td><?= $data['stars_5']; ?></td>
											</tr>
											<tr>
												<td><p><i class="fa fa-square star_4"></i>4 αστέρια </p></td>
												<td><?= $data['stars_4']; ?></td>
											</tr>
											<tr>
												<td><p><i class="fa fa-square star_3"></i>3 αστέρια </p></td>
												<td><?= $data['stars_3']; ?></td>
											</tr>
											<tr>
												<td><p><i class="fa fa-square star_2"></i>2 αστέρια </p></td>
												<td><?= $data['stars_2']; ?></td>
											</tr>
											<tr>
												<td><p><i class="fa fa-square star_1"></i>1 αστέρι </p></td>
												<td><?= $data['stars_1']; ?></td>
											</tr>
											<tr>
												<td><p><i class="fa fa-square star_0"></i>Χωρίς αξιολόγηση </p></td>
												<td><?= $data['stars_0']; ?></td>
											</tr>
										</table>
									</td>
									<td>
										<canvas class="canvasDoughnut" height="180" width="180" style="margin: 15px 10px 10px 0"></canvas>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<!-- /Path Categories -->
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
	<!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.js"></script>
	
	<script>
		// Library API Token
		L.mapbox.accessToken = 'pk.eyJ1IjoidmFzc2lsaXMxMCIsImEiOiJjamdpdWU0bzgwMzIzMzJwMnc1cG43eThlIn0.3KU68s781sAsckDflYw6AA';
		
		// Show Initial Map
		var map = L.mapbox.map('map', 'mapbox.streets-satellite').setView([37.983810, 23.727539], 13);
		
		<?php while ($cnt_list > 0 && $ftc_list = mysqli_fetch_array($req_list)) { ?>
			<?php 
			$path_id = $ftc_list['uid'];
			
			$sql_rating = 
				'SELECT IFNULL(ROUND(SUM(rated)), 0) AS stars '.
				'FROM web_reviews '.
				'WHERE path_id = '.$ftc_list['uid'];
			$req_rating = mysqli_query($db, $sql_rating) or exit('Cannot request Path Rating');
			$cnt_rating = mysqli_affected_rows($db);
			$ftc_rating = mysqli_fetch_array($req_rating);
			
			$sql_user_rated = 
				'SELECT IFNULL(SUM(rated), 0) AS is_rated '.
				'FROM web_reviews '.
				'WHERE player_id = '.$ftc_player['uid'].' '.
				'AND path_id = '.$ftc_list['uid'];
			$req_user_rated = mysqli_query($db, $sql_user_rated) or exit('Cannot request Path User Rated');
			$cnt_user_rated = mysqli_affected_rows($db);
			$ftc_user_rated = mysqli_fetch_array($req_user_rated);
			
			$line_color = '#8b4513';
			$stars      = '-';
			
			switch ((int) $ftc_rating['stars']) {
				case 1: 
					$line_color = '#ff0000';
					$stars      = '<i class=\"fa fa-star aero\"></i>';
					break;
				case 2: 
					$line_color = '#ffa500';
					$stars      = '<i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i>';
					break;
				case 3: 
					$line_color = '#ffff00';
					$stars      = '<i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i>';
					break;
				case 4: 
					$line_color = '#00ff00';
					$stars      = '<i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i>';
					break;
				case 5: 
					$line_color = '#0000ff';
					$stars      = '<i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i>';
					break;
				default: 
					$line_color = '#8b4513';
					$stars      = '-';
					break;
			}
			
			$one_star    = ((int) $ftc_user_rated['is_rated'] === 1 ? '<a href=\"#\" id=\"one'.$path_id.'\"><i class=\"fa fa-star aero\"></i></a>' : '<a href=\"#\" id=\"one'.$path_id.'\"><i class=\"fa fa-star-o\"></i></a>');
			$two_stars   = ((int) $ftc_user_rated['is_rated'] === 2 ? '<a href=\"#\" id=\"two'.$path_id.'\"><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i></a>' : '<a href=\"#\" id=\"two'.$path_id.'\"><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i></a>');
			$three_stars = ((int) $ftc_user_rated['is_rated'] === 3 ? '<a href=\"#\" id=\"three'.$path_id.'\"><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i></a>' : '<a href=\"#\" id=\"three'.$path_id.'\"><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i>');
			$four_stars  = ((int) $ftc_user_rated['is_rated'] === 4 ? '<a href=\"#\" id=\"four'.$path_id.'\"><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i></a>' : '<a href=\"#\" id=\"four'.$path_id.'\"><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i></a>');
			$five_stars  = ((int) $ftc_user_rated['is_rated'] === 5 ? '<a href=\"#\" id=\"five'.$path_id.'\"><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i><i class=\"fa fa-star aero\"></i></a>' : '<a href=\"#\" id=\"five'.$path_id.'\"><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i><i class=\"fa fa-star-o\"></i></a>');
			
			$popup = (
				$ftc_list['player_id'] === $ftc_player['uid'] 
				? 'Δεν επιτρέπεται αξιολόγηση δικού σου μονοπατιού' 
				: 'Η δική σου βαθμολογία <br> '.$one_star.' <br> '.$two_stars.' <br> '.$three_stars.' <br> '.$four_stars.' <br> '.$five_stars);
			?>
			
			// Get GeoJSON Data
			var geojsonFeature<?= $ftc_list['uid'] ?> = <?= $ftc_list['pathpoints']; ?>;
			
			// Add GeoJSON Layer to Map
			//L.geoJSON(geojsonFeature<?= $ftc_list['uid'] ?>).addTo(map);
			
			// Style Path Line and Bind Path Popup 
			L.geoJson(geojsonFeature<?= $ftc_list['uid'] ?>, {
				style: function(feature) {
					return {
						color: "<?= $line_color; ?>"
					};
				},
				onEachFeature: function(feature, layer) {
					layer.bindPopup("Βαθμολογία μονοπατιού <br> <?= $stars; ?> <br> <?= $popup; ?>");
				}
			}).addTo(map);
			
			$('#map').on('click', '#one<?= $path_id; ?>', function() {
				//alert('One Clicked!');
				
				// Get Path ID
				var pathId  = <?= $path_id; ?>;
				
				// Insert Data to DB
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				
				jax.open('POST','process.php');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=one&player=<?= $user; ?>&path=' + pathId + '&previous=<?= $ftc_user_rated['is_rated']; ?>')
				jax.onreadystatechange = function() {
					if (jax.readyState == 4) {
						if (jax.responseText.indexOf('bien') + 1) alert('Η βαθμολογία αποθηκεύτηκε');
						else alert(jax.responseText)
					}
				}
			});
			
			$('#map').on('click', '#two<?= $path_id; ?>', function() {
				//alert('One Clicked!');
				
				// Get Path ID
				var pathId  = <?= $path_id; ?>;
				
				// Insert Data to DB
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				
				jax.open('POST','process.php');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=two&player=<?= $user; ?>&path=' + pathId + '&previous=<?= $ftc_user_rated['is_rated']; ?>')
				jax.onreadystatechange = function() {
					if (jax.readyState == 4) {
						if (jax.responseText.indexOf('bien') + 1) alert('Η βαθμολογία αποθηκεύτηκε');
						else alert(jax.responseText)
					}
				}
			});
			
			$('#map').on('click', '#three<?= $path_id; ?>', function() {
				//alert('One Clicked!');
				
				// Get Path ID
				var pathId  = <?= $path_id; ?>;
				
				// Insert Data to DB
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				
				jax.open('POST','process.php');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=three&player=<?= $user; ?>&path=' + pathId + '&previous=<?= $ftc_user_rated['is_rated']; ?>')
				jax.onreadystatechange = function() {
					if (jax.readyState == 4) {
						if (jax.responseText.indexOf('bien') + 1) alert('Η βαθμολογία αποθηκεύτηκε');
						else alert(jax.responseText)
					}
				}
			});
			
			$('#map').on('click', '#four<?= $path_id; ?>', function() {
				//alert('One Clicked!');
				
				// Get Path ID
				var pathId  = <?= $path_id; ?>;
				
				// Insert Data to DB
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				
				jax.open('POST','process.php');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=four&player=<?= $user; ?>&path=' + pathId + '&previous=<?= $ftc_user_rated['is_rated']; ?>')
				jax.onreadystatechange = function() {
					if (jax.readyState == 4) {
						if (jax.responseText.indexOf('bien') + 1) alert('Η βαθμολογία αποθηκεύτηκε');
						else alert(jax.responseText)
					}
				}
			});
			
			$('#map').on('click', '#five<?= $path_id; ?>', function() {
				//alert('One Clicked!');
				
				// Get Path ID
				var pathId  = <?= $path_id; ?>;
				
				// Insert Data to DB
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				
				jax.open('POST','process.php');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=five&player=<?= $user; ?>&path=' + pathId + '&previous=<?= $ftc_user_rated['is_rated']; ?>')
				jax.onreadystatechange = function() {
					if (jax.readyState == 4) {
						if (jax.responseText.indexOf('bien') + 1) alert('Η βαθμολογία αποθηκεύτηκε');
						else alert(jax.responseText)
					}
				}
			});
		<?php } // endwhile ?>
		
		// Chart Doughnut
		function init_chart_doughnut() {
			if (typeof(Chart) === 'undefined') {return;}
			
			console.log('init_chart_doughnut');
			
			if ($('.canvasDoughnut').length) {
				var chart_doughnut_settings = {
					type: 'doughnut',
					tooltipFillColor: "rgba(51, 51, 51, 0.55)",
					data: {
						labels: [
							"5 Αστέρια",
							"4 Αστέρια",
							"3 Αστέρια",
							"2 Αστέρια",
							"1 Αστέρι",
							"Χωρίς",
						],
					datasets: [{
						data: [
							<?= $data['stars_5']; ?>, 
							<?= $data['stars_4']; ?>, 
							<?= $data['stars_3']; ?>, 
							<?= $data['stars_2']; ?>, 
							<?= $data['stars_1']; ?>, 
							<?= $data['stars_0']; ?>
						],
						backgroundColor: [
							"#0000ff",
							"#00ff00",
							"#ffff00",
							"#ffa500",
							"#ff0000",
							"#8b4513"
						],
						hoverBackgroundColor: [
							"#3b3bc4",
							"#3bc43b",
							"#c4c43b",
							"#c4943b",
							"#c43b3b",
							"#794825"
						]
					}]
					},
					options: {
						legend: false, 
						responsive: false 
					}
				}
				
				$('.canvasDoughnut').each(function() {
					var chart_element = $(this);
					var chart_doughnut = new Chart(chart_element, chart_doughnut_settings);
				});
			}
		}
	</script>
  </body>
</html>
