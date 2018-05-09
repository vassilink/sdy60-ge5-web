<?php 
//ob_start();
header('Cache-Control: no-store, no-cache, must-revalidate');

$error = array(); // store error messages
$data  = array(); // store validated/sanitized data

//exit(var_dump($_POST));

/*
* ACTION: Database Connect 
*******************************************************************************/
require_once 'DB_Connect.php';
$db = connect();

$data['command']    = (isset($_POST['command']) ? $_POST['command'] : '');

$data['player']     = (isset($_POST['player']) ? $_POST['player'] : 0);
$data['tags']       = (isset($_POST['tags']) ? $_POST['tags'] : 0);
$data['meters']     = (isset($_POST['meters']) ? (int) $_POST['meters'] : 0);
$data['pathpoints'] = (isset($_POST['mapdata']) ? mysqli_real_escape_string($db, $_POST['mapdata']) : '');

$data['path']       = (isset($_POST['path']) ? $_POST['path'] : 0);
$data['previous']   = (isset($_POST['previous']) ? $_POST['previous'] : 0);

$data['stars'] = 0;

switch ($data['command']) {
	case 'one':
		$data['stars'] = 1;
		break;
	case 'two':
		$data['stars'] = 2;
		break;
	case 'three':
		$data['stars'] = 3;
		break;
	case 'four':
		$data['stars'] = 4;
		break;
	case 'five':
		$data['stars'] = 5;
		break;
	default:
		$data['stars'] = 0;
		break;
}

if ($data['command'] === 'save') {
	$sql_save_path = 
		'INSERT INTO web_paths ('.
			'player_id,'.
			'tags,'.
			'meters,'.
			'pathpoints,'.
			'created_at'.
		') VALUES ('.
			$data['player'].','.
			$data['tags'].','.
			$data['meters'].','.
			'"'.$data['pathpoints'].'",'.
			'UTC_TIMESTAMP()'.
		')';
		//exit($sql_save_path);
	$req_save_path = mysqli_query($db, $sql_save_path) OR exit('Αποτυχία Συστήματος: η αποθήκευση απέτυχε');
	exit('bien');
}

if ($data['command'] === 'one' || $data['command'] === 'two' || $data['command'] === 'three' || $data['command'] === 'four' || $data['command'] === 'five') {
	if ((int) $data['previous'] === 0) {
		$sql_save_rating = 
			'INSERT INTO web_reviews ('.
				'player_id,'.
				'path_id,'.
				'rated,'.
				'created_at'.
			') VALUES ('.
				$data['player'].','.
				$data['path'].','.
				$data['stars'].','.
				'UTC_TIMESTAMP()'.
			')';
			//exit($sql_save_path);
		$req_save_rating = mysqli_query($db, $sql_save_rating) OR exit('Αποτυχία Συστήματος: η εισαγωγή Βαθμολόγησης απέτυχε');
	} else {
		$sql_update_rating = 
			'UPDATE web_reviews SET '.
			'rated = '.$data['stars'].','.
			'updated_at = UTC_TIMESTAMP() '.
			'WHERE player_id = '.$data['player'].' '.
			'AND path_id = '.$data['path'];
		$req_update_rating = mysqli_query($db, $sql_update_rating) OR exit('Αποτυχία Συστήματος: η ενημέρωση Βαθμολόγησης απέτυχε');
	}
	
	exit('bien');
}


/*
    if($_REQUEST['command']=='fetch')
    {
        $query = "select value from mapdir";
        if(!($res = mysql_query($query)))die(mysql_error());        
        $rs = mysql_fetch_array($res,1);
        die($rs['value']);      
    }
*/
