<?php

/**
 * Συναρτήσεις για την επικοινωνία του server με την βάση δεδομέων
 */

    //Χρησιμοποιείται για την σύνδεση στην βάση
    require_once 'DB_Connect.php';


    /*
     * Λαμβάνει τους τύπους των μονοπατιών οι οποίοι
     * μπορεί να αλλάξουν ανάλογα τη χρήση της εφαρμογής
     */
    function getPathTypes($path_set) {
        //σύνδεση με την βάση δεδομένων
        $mysqli = connect();

        $result = mysqli_query($mysqli,"SELECT * FROM pathstypes WHERE path_set = '$path_set'") or die(mysqli_error());

        // ελέγχει για αποτέλεσμα
        $no_of_rows = mysqli_num_rows($result);

        if ($no_of_rows > 0) {
			// looping through all results - paths types node
    		$response["pathstypes"] = array();
			
			 while ($row = mysqli_fetch_array($result)) {
       			 // temp path sets array
                 $pathsTypes = array();
                 $pathsTypes["path_set"] = $row["path_set"];
                 $pathsTypes["name"] = $row["name"];
                 $pathsTypes["set_order"] = $row["set_order"];

        		// push single path sets into final response array
        		array_push($response["pathstypes"], $pathsTypes);
    		}
			// success - return path sets
    		$response["success"] = 1;
        } else {
			//error -  not found path sets
    		$response["error"] = 1;
    		$response["error_msg"] = "No path set found";
        }
        //mysqli_close($mysqli);
        return $response;
    }


    /*
    * Καταχωρεί το balance πόντων
    * για ένα παίκτη (με βάση το id του path)
    * type: 0 - pedestrian
    *       1 - cycle
    */
    function updatePlayerBalanceByPath($type, $path_id, $balance, $new_path){
        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        //Find the Player id (Player who has upload the path) to set balance
        if($type==0) {
            $result_pl = mysqli_query($mysqli, "SELECT player_id, updated_at FROM paths WHERE uid = '$path_id'");
        }else{
            $result_pl = mysqli_query($mysqli, "SELECT player_id, updated_at FROM cyclepaths WHERE uid = '$path_id'");
        }
        $no_of_rows = mysqli_num_rows($result_pl);
        if ($no_of_rows > 0) {
            $res = mysqli_fetch_array($result_pl);
            $player_path_id = $res['player_id'];


            if($new_path>0){
                $updated_at = $res['updated_at'];
                if($updated_at==NULL) { //allow only one set of new/old path (we trust players)
                    if($type==0) {
                        mysqli_query($mysqli, "UPDATE paths SET new_path = '$new_path', updated_at = NOW() WHERE uid = '$path_id'");
                    }else{
                        mysqli_query($mysqli, "UPDATE cyclepaths SET new_path = '$new_path', updated_at = NOW() WHERE uid = '$path_id'");
                    }
                }
            }


            //Keep and Delete previous balance if exist
            $res_bal = mysqli_query($mysqli,"SELECT uid, balance FROM playerbalance WHERE player_id = '$player_path_id'");
            $no_of_rows = mysqli_num_rows($res_bal);
            $old_balance = 0;
            if($no_of_rows>0){
                //Already Exist in balance table
                $res = mysqli_fetch_array($res_bal);
                $uid = $res['uid'];
                $old_balance = $res['balance'];

                //Delete Row from balance table
                mysqli_query($mysqli,"DELETE  FROM playerbalance WHERE uid = '$uid'");
            }
            //Does not exist in balance table

            //Insert new balance
            $new_balance = $old_balance + $balance;
            $result = mysqli_query($mysqli,"INSERT INTO playerbalance (player_id, balance) VALUES ( '$player_path_id', '$new_balance')");
            if ($result) {
                //mysqli_close($mysqli);
                return true;
            } else {
                //mysqli_close($mysqli);
                return false;
            }
        }else{
            //mysqli_close($mysqli);
            return false;
        }
    }

    /*
    * Καταχωρεί το balance πόντων
    * για ένα παίκτη (με βάση το id του path)
    * type: 0 - pedestrian
    *       1 - cycle
    */
    function updatePlayerBalanceSugByPath($type, $path_id, $balance){
        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        //Find the Player id (Player who has upload the path) to set balance
        if($type==0) {
            $result_pl = mysqli_query($mysqli, "SELECT player_id FROM suggestedpaths WHERE uid = '$path_id'");
        }else{
            $result_pl = mysqli_query($mysqli, "SELECT player_id FROM sugcyclepaths WHERE uid = '$path_id'");
        }
        $no_of_rows = mysqli_num_rows($result_pl);
        if ($no_of_rows > 0) {
            $res = mysqli_fetch_array($result_pl);
            $player_path_id = $res['player_id'];

            //Keep and Delete previous balance if exist
            $res_bal = mysqli_query($mysqli,"SELECT uid, balance FROM playerbalance WHERE player_id = '$player_path_id'");
            $no_of_rows = mysqli_num_rows($res_bal);
            $old_balance = 0;
            if($no_of_rows>0){
                //Already Exist in balance table
                $res = mysqli_fetch_array($res_bal);
                $uid = $res['uid'];
                $old_balance = $res['balance'];

                //Delete Row from balance table
                mysqli_query($mysqli,"DELETE  FROM playerbalance WHERE uid = '$uid'");
            }
            //Does not exist in balance table

            //Insert new balance
            $new_balance = $old_balance + $balance;
            $result = mysqli_query($mysqli,"INSERT INTO playerbalance (player_id, balance) VALUES ( '$player_path_id', '$new_balance')");
            if ($result) {
                //mysqli_close($mysqli);
                return true;
            } else {
                //mysqli_close($mysqli);
                return false;
            }
        }else{
            //mysqli_close($mysqli);
            return false;
        }
    }


    /*
     * Επιστρέφει το balance πόντων για ένα παίκτη
     */
    function getPlayerBalance($player_id){
        $mysqli = connect();//σύνδεση με την βάση δεδομένων
        $result = mysqli_query($mysqli,"SELECT uid, balance FROM playerbalance WHERE player_id = '$player_id'");
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            $balance = $result['balance'];

            //Player get balance: Delete row
            $uid = $result['uid'];
            mysqli_query($mysqli,"DELETE  FROM playerbalance WHERE uid = '$uid'");

            //mysqli_close($mysqli);
            return $balance;
        }else{
            //mysqli_close($mysqli);
            return false;
        }
    }


    /*
     * Αποθηκεύει έναν νέο παίκτη
     * επιστρέφει τα στοιχεία του παίκτη
     */
    function storePlayer($name, $email, $password) {
		
		$mysqli = connect();//σύνδεση με την βάση δεδομένων
		//Γυρίζει ένα μοναδικό id βασιζόμενο στο microtime (τρέχουσα ώρα σε μικροδευτερόλεπτα)
       // $uuid = uniqid('', true);//Η πρώτη παράμετρος βάζει πρόθεμα (εδώ τίποτα) ενώ η δεύτερη κάνει το αποτέλεσμα πιο μαναδικό (23 χαρακτήρων)
        $hash = hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // κρυπτογραφιμένο password
        $salt = $hash["salt"]; // salt
        $pathSet = 1;
        $result = mysqli_query($mysqli,"INSERT INTO players( name, email, encrypted_password, salt, created_at, path_set) VALUES( '$name', '$email', '$encrypted_password', '$salt', NOW(), '$pathSet')");//Η NOW () επιστρέφει την τρέχουσα ημερομηνία και ώρα.
        // ελέγχει για επιτυχή εγγραφή
        if ($result) {
            // παίρνει τα στοιχεία του παίκτη 
            $uid = mysqli_insert_id($mysqli); // τελευταίο εισαγόμενο id
            $result = mysqli_query($mysqli,"SELECT * FROM players WHERE uid = $uid");
            // επιστρέφει τα στοιχεία του παίκτη
			$result = mysqli_fetch_array($result);
			//mysqli_close($mysqli);
            return $result;
        } else {
			//mysqli_close($mysqli);
            return false;
        }
    }

    /*
     * Λαμβάνει τον παίκτη μέσω του email και του password
     */
    function getPlayerByEmailAndPassword($email, $password) {
		$mysqli = connect();//σύνδεση με την βάση δεδομένων
		
        $result = mysqli_query($mysqli,"SELECT * FROM players WHERE email = '$email'") or die(mysqli_error());
        // ελέγχει για αποτέλεσμα 
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            $salt = $result['salt'];
            $encrypted_password = $result['encrypted_password'];
            $hash = checkhashSSHA($salt, $password);
			//mysqli_close($mysqli);
            // ελέγχει αν ο κωδικός είναι ο ίδιος
            //if ($encrypted_password == $hash) {
                // τα στοιχεία ταυτότητας του παίκτη είναι σωστά
            //    return $result;
            //}
            return $result;//?????
        } else {
            //ο παίκτης δεν βρέθηκε
			//mysqli_close($mysqli);
            return false;
        }
    }

    /*
     * Ελέγχει αν ο παίκτης υπάρχει ή όχι
     */
   function isPlayerExisted($email) {
	   
	   	$mysqli = connect(); // σύνδεση με την βάση δεδομένων
	   
        $result = mysqli_query($mysqli,"SELECT email from players WHERE email = '$email'");
        $no_of_rows = mysqli_num_rows($result);
		//mysqli_close($mysqli);
        if ($no_of_rows > 0) {
            // ο παίκτης υπάρχει 
            return true;
        } else {
            //ο παίκτης δεν υπάρχει
            return false;
        }
    }

    /*
     * Κρυπτογράφηση κωδικού πρόσβασης
     * @param password
     * γυρίζει salt και κρυπτογραφημένο password
     */
     function hashSSHA($password) {

		//Η συνάρτηση SHA1() υπολογίζει τον κατακερματισμό (hash) SHA-1 μιας συμβολοσειράς (string).Εδώ του ακέραιου που γυρίζει η rand()
        $salt = sha1(rand());//Η συνάρτηση rand () δημιουργεί ένα τυχαίο ακέραιο
        $salt = substr($salt, 0, 10);//Γυρίζει τους 10 πρώτους χαρακτήρες.
		
		//Κωδικοποιεί τα δεδομένα με base64. Επειδή στην sha1 βάλαμε true θα γυρίσει 20 ακατέργαστους (raw) χαρακτήρες σε δυαδική μορφή 
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);//πίνακας που στο salt περιέχει το salt και στο encryped τον κρυπτογραφημένο κωδ.
        return $hash;//Γυρίζει τον πίνακα
    }

    /*
     * Αποκρυπτογράφηση κωδικού πρόσβασης
     * @param salt, password
     * γυρίζει hash string
     */
    function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }

	/*
     * Αποθηκεύει έναν νέο μονοπάτι
     * επιστρέφει τα στοιχεία του μονοπατιού
     * type: 0 - pedestrian
     *       1 - cycle
     */
    function storePath($type, $player_id, $path_raw_gpx_google, $path_smooth_gpx, $tags, $meters, $new_path) {
		
		$mysqli = connect();//σύνδεση με την βάση δεδομένων
        if($type==0) {
            $result = mysqli_query($mysqli, "INSERT INTO paths( player_id, path_raw_google_gpx, path_smooth_google_gpx,tags,meters, new_path, created_at) VALUES(	'$player_id', '$path_raw_gpx_google', '$path_smooth_gpx', '$tags', '$meters', '$new_path', NOW())");//Η NOW () επιστρέφει την τρέχουσα ημερομηνία και ώρα.
        }else{
            $result = mysqli_query($mysqli, "INSERT INTO cyclepaths( player_id, path_raw_gpx, path_smooth_gpx, tags, meters, new_path, created_at) VALUES(	'$player_id', '$path_raw_gpx_google', '$path_smooth_gpx', '$tags', '$meters', '$new_path', NOW())");//Η NOW () επιστρέφει την τρέχουσα ημερομηνία και ώρα.
        }

        // ελέγχει για επιτυχή εγγραφή
        if ($result) {
			//mysqli_close($mysqli);
            return $result;
        } else {
			//mysqli_close($mysqli);
            return false;
        }
    }


    /*
     * Γυρίζει το uid του τελευταίου μονοπατιού που ανέβηκε
     * type: 0 - pedestrian
     *       1 - cycle
     */
    function getLastPathId($type, $player_id, $path_smooth_google_gpx){

        $mysqli = connect();//σύνδεση με την βάση δεδομένων
        if($type==0) {
            $result = mysqli_query($mysqli, "SELECT uid FROM paths WHERE player_id='$player_id' AND path_smooth_google_gpx ='$path_smooth_google_gpx'");
        }else{
            $result = mysqli_query($mysqli, "SELECT uid FROM cyclepaths WHERE player_id='$player_id' AND path_smooth_gpx ='$path_smooth_google_gpx'");
        }
        if ($result) {
            $data= mysqli_fetch_assoc($result);
            $pathId = $data['uid'];
            //mysqli_close($mysqli);
            return $pathId;
        } else {
            //mysqli_close($mysqli);
            return false;
        }
    }

    /*
     * Αποθηκεύει έναν νέο μονοπάτι
     * επιστρέφει τα στοιχεία του μονοπατιού
     * type: 0 - pedestrian
     *       1 - cycle
     */
    function storePathPoints($type, $path_id, $s_lat, $s_long, $m_lat, $m_long, $e_lat, $e_long) {
        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        if($type==0) {
            $result = mysqli_query($mysqli, "INSERT 
INTO pathpoints( path_id, start_lat, start_long, middle_lat, middle_long, end_lat, end_long) 
VALUES(	'$path_id', '$s_lat', '$s_long', '$m_lat', '$m_long', '$e_lat', '$e_long')");
        }else{
            $result = mysqli_query($mysqli, "INSERT 
INTO cyclepathpoints( path_id, start_lat, start_long, middle_lat, middle_long, end_lat, end_long) 
VALUES(	'$path_id', '$s_lat', '$s_long', '$m_lat', '$m_long', '$e_lat', '$e_long')");
        }
        // ελέγχει για επιτυχή εγγραφή
        if ($result) {
            //mysqli_close($mysqli);
            return $result;
        } else {
            //mysqli_close($mysqli);
            return false;
        }
    }

    /*
     * Αποθηκεύει έναν νέο προτεινόμενο από το χρήστη μονοπάτι και
     * επιστρέφει τα στοιχεία του μονοπατιού
     * type: 0 - pedestrian
     *       1 - cycle
     */
    function storeSuggestedPath($type, $path_id, $player_id, $s_lat, $s_long, $e_lat, $e_long, $time, $pending, $rate){

        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        //First Sketch (for a walk path) flag
        $first_sketch = false;

        if($type==0) {
            //Pedestrian

            //Check if players has sketch this path again
            $result_sketch = mysqli_query($mysqli,"SELECT uid FROM suggestedpaths WHERE path_id = '$path_id'");
            if ($result_sketch) {
                $row = mysqli_num_rows($result_sketch);
                if ($row > 0) {
                    $first_sketch = false;
                }else{
                    $first_sketch = true;
                }
            }else{
                $first_sketch = true;
            }

            $result = mysqli_query($mysqli, "INSERT 
INTO suggestedpaths( path_id, player_id, start_lat, start_long, end_lat, end_long, pending, rate, created_at) 
VALUES(	'$path_id', '$player_id', '$s_lat', '$s_long', '$e_lat', '$e_long', '$pending', '$rate', '$time')");

        }else{
            //Cycle

            //Check if players has sketch this path again
            $result_sketch = mysqli_query($mysqli,"SELECT uid FROM suggestedpaths WHERE path_id = '$path_id'");
            if ($result_sketch) {
                $row = mysqli_num_rows($result_sketch);
                if ($row > 0) {
                    $first_sketch = false;
                }else{
                    $first_sketch = true;
                }
            }else{
                $first_sketch = true;
            }
            
            $result = mysqli_query($mysqli, "INSERT 
INTO sugcyclepaths( path_id, player_id, start_lat, start_long, end_lat, end_long, pending, rate, created_at) 
VALUES(	'$path_id', '$player_id', '$s_lat', '$s_long', '$e_lat', '$e_long', '$pending', '$rate', '$time')");

        }
        // ελέγχει για επιτυχή εγγραφή
        //mysqli_close($mysqli);
        if ($result) {
            if($first_sketch) return 1;
            else return 2;
        } else {
            return 0;
        }
    }

    /*
     * Γυρίζει πόσα μονοπάτια έχουν αποθηκευτεί μέχρι τώρα (τελικά δεν χρησιμοποιήθηκε)
     * type: 0 - pedestrian
     *       1 - cycle
     */
   function numberOfPaths($type) {
	   
	   $mysqli = connect();//σύνδεση με την βάση δεδομένων
       if($type==0) {
           $result = mysqli_query($mysqli, "SELECT COUNT(uid) AS total FROM paths");
       }else{
           $result = mysqli_query($mysqli, "SELECT COUNT(uid) AS total FROM cyclepaths");
       }
		
		if ($result) {
			$data= mysqli_fetch_assoc($result);
			$no_of_rows = $data['total'];
			//mysqli_close($mysqli);
            return $no_of_rows;;
        } else {
			//mysqli_close($mysqli);
            return false;
        }
    }

    /*
    * Γυρίζει το uid του τελευταίου μονοπατιού που έχει αποθηκευτεί μέχρι τώρα (Θα χρησιμοποιηθεί για το όνομα του νέου μονοπατιού)
    * type: 0 - pedestrian
    *       1 - cycle
    */
    function uidOfLatsPath($type) {

        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        if($type==0) {
            $result = mysqli_query($mysqli, "SELECT uid AS lastUid FROM paths ORDER BY uid DESC LIMIT 1 ");
        }else{
            $result = mysqli_query($mysqli, "SELECT uid AS lastUid FROM cyclepaths ORDER BY uid DESC LIMIT 1 ");
        }

        if ($result) {
            $data= mysqli_fetch_assoc($result);
            $last_uid = $data['lastUid'];
            //mysqli_close($mysqli);
            return $last_uid;
        } else {
            //mysqli_close($mysqli);
            //return false;
			return 0; //VK
        }
    }


    /*
     * Γυρίζει όλες τις διαδρομές μονοπατιών που βρίσκονται εντός μιας
     * κυκλικής περιοχής σε σχέση με την τρέχουσα θέση του χρήστη
     * Διαδρομές που επιστρέφονται: Walk, Pending Sketch, Accepted Sketch
     */
    function radiusPaths($player_id, $player_lat, $player_lon, $radius) {

        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        $result = mysqli_query($mysqli, "SELECT paths.uid, paths.path_smooth_google_gpx FROM paths,
(SELECT pathpoints.path_id,
(  6371 * acos( cos( radians('$player_lat') ) * cos( radians( pathpoints.start_lat ) ) * cos( radians( pathpoints.start_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( pathpoints.start_lat ) ) ) ) AS distance_s,
(  6371 * acos( cos( radians('$player_lat') ) * cos( radians( pathpoints.middle_lat ) ) * cos( radians( pathpoints.middle_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( pathpoints.middle_lat ) ) ) ) AS distance_m,
(  6371 * acos( cos( radians('$player_lat') ) * cos( radians( pathpoints.end_lat ) ) * cos( radians( pathpoints.end_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( pathpoints.end_lat ) ) ) ) AS distance_e
FROM pathpoints 
HAVING distance_s < '$radius' OR distance_m < '$radius' OR distance_e < '$radius') nearpaths 
WHERE paths.uid = nearpaths.path_id");


        // ελέγχει για αποτέλεσμα
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {

            $first_w = true;

            //Delete old file for user $player_id
            $mergeFileName = "mergeFile/merge_"."$player_id"."_gpx.gpx"; //Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
            //if(file_exists($mergeFileName)) unlink($mergeFileName);
            
            // looping through all results
            while ($row = mysqli_fetch_array($result)) {

                $path_id        = $row["uid"];
                $path_user_gpx  = $row["path_smooth_google_gpx"];

                //εισάγει την συνάρτηση για merge snapWaypoints
                //require_once 'include/snapWpt.php';
                //snapWayPoints($path_user_gpx);

                // εισάγει την συνάρτηση για merge gpx
                require_once 'Merge_Gpx_Function.php';
                //mergeGpxFiles($mergeFileName, $path_user_gpx, $path_id);//Το καινούργιο smooth αρχείο θα μπει στο merge
                mergeGpxFilesRadius($mergeFileName, $path_user_gpx, $path_id, $first_w);//Το καινούργιο smooth αρχείο θα μπει στο merge
                $first_w = false;
            }

            $result_sug = mysqli_query($mysqli, "SELECT suggestedpaths.uid, suggestedpaths.path_id, suggestedpaths.start_lat, suggestedpaths.start_long, suggestedpaths.end_lat, suggestedpaths.end_long, suggestedpaths.pending, suggestedpaths.created_at, 
( 6371 * acos( cos( radians('$player_lat') ) * cos( radians( suggestedpaths.start_lat ) ) * cos( radians( suggestedpaths.start_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( suggestedpaths.start_lat ) ) ) ) AS distance_s,
( 6371 * acos( cos( radians('$player_lat') ) * cos( radians( suggestedpaths.end_lat ) ) * cos( radians( suggestedpaths.end_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( suggestedpaths.end_lat ) ) ) ) AS distance_e
FROM suggestedpaths
HAVING (distance_s < '$radius' OR distance_e < '$radius') AND (suggestedpaths.pending=1 OR suggestedpaths.pending=2)");


            // ελέγχει για αποτέλεσμα
            $sug_rows = mysqli_num_rows($result_sug);
            if ($sug_rows > 0) {
                // looping through all results
                while ($row_s = mysqli_fetch_array($result_sug)) {
                    $uid            = $row_s["uid"];
                    //$path_id        = $row_s["path_id"];
                    $start_lat      = $row_s["start_lat"];
                    $start_long     = $row_s["start_long"];
                    $end_lat        = $row_s["end_lat"];
                    $end_long       = $row_s["end_long"];
                    $pending        = $row_s["pending"];
                    $create_date    = $row_s["created_at"];
                    
                    // εισάγει την συνάρτηση για merge gpx
                    require_once 'Merge_Gpx_Function.php';
                    $mergeFileName = "mergeFile/merge_"."$player_id"."_gpx.gpx"; //Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
                    mergeSugGpxFile($mergeFileName, $uid, $start_lat, $start_long, $end_lat, $end_long, $pending, $create_date);
                }
            }
            // success - return paths
            $response["success"] = 1;
        } else {
            //error -  not found paths
            $response["error"] = 1;
            $response["error_msg"] = "No found paths";
        }
        //mysqli_close($mysqli);
        return $response;
    }


    /*
     * Γυρίζει όλες τις διαδρομές μονοπατιών που βρίσκονται εντός μιας
     * κυκλικής περιοχής σε σχέση με την τρέχουσα θέση του χρήστη
     * Διαδρομές που επιστρέφονται: Walk, Pending Sketch, Accepted Sketch
     */
    function radiusCyclePaths($player_id, $player_lat, $player_lon, $radius) {

        $mysqli = connect();//σύνδεση με την βάση δεδομένων
        $result = mysqli_query($mysqli, "SELECT cyclepaths.uid, cyclepaths.path_smooth_gpx FROM cyclepaths, 
(SELECT cyclepathpoints.path_id,
(  6371 * acos( cos( radians('$player_lat') ) * cos( radians( cyclepathpoints.start_lat ) ) * cos( radians( cyclepathpoints.start_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( cyclepathpoints.start_lat ) ) ) ) AS distance_s,
(  6371 * acos( cos( radians('$player_lat') ) * cos( radians( cyclepathpoints.middle_lat ) ) * cos( radians( cyclepathpoints.middle_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( cyclepathpoints.middle_lat ) ) ) ) AS distance_m,
(  6371 * acos( cos( radians('$player_lat') ) * cos( radians( cyclepathpoints.end_lat ) ) * cos( radians( cyclepathpoints.end_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( cyclepathpoints.end_lat ) ) ) ) AS distance_e
FROM cyclepathpoints 
HAVING distance_s < '$radius' OR distance_m < '$radius' OR distance_e < '$radius') nearpaths 
WHERE cyclepaths.uid = nearpaths.path_id");

        $result_sug = mysqli_query($mysqli, "SELECT sugcyclepaths.uid, sugcyclepaths.path_id, sugcyclepaths.start_lat, sugcyclepaths.start_long, sugcyclepaths.end_lat, sugcyclepaths.end_long, sugcyclepaths.pending, sugcyclepaths.created_at, 
( 6371 * acos( cos( radians('$player_lat') ) * cos( radians( sugcyclepaths.start_lat ) ) * cos( radians( sugcyclepaths.start_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( sugcyclepaths.start_lat ) ) ) ) AS distance_s,
( 6371 * acos( cos( radians('$player_lat') ) * cos( radians( sugcyclepaths.end_lat ) ) * cos( radians( sugcyclepaths.end_long ) - radians('$player_lon') ) + sin( radians('$player_lat') ) * sin( radians( sugcyclepaths.end_lat ) ) ) ) AS distance_e
FROM sugcyclepaths
HAVING (distance_s < '$radius' OR distance_e < '$radius') AND (sugcyclepaths.pending=1 OR sugcyclepaths.pending=2)");


        // ελέγχει για αποτέλεσμα
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {

            $first_w = true;

            //Delete old file for user $player_id
            $mergeFileName = "mergeFile/merge_"."$player_id"."_gpx.gpx"; //Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
            //if(file_exists($mergeFileName)) unlink($mergeFileName);

            // looping through all results
            while ($row = mysqli_fetch_array($result)) {

                $path_id = $row["uid"];
                $path_user_gpx = $row["path_smooth_gpx"];

                //εισάγει την συνάρτηση για merge snapWaypoints
                //require_once 'include/snapWpt.php';
                //snapWayPoints($path_user_gpx);

                // εισάγει την συνάρτηση για merge gpx
                require_once 'Merge_Gpx_Function.php';
                //mergeGpxFiles($mergeFileName, $path_user_gpx, $path_id);//Το καινούργιο smooth αρχείο θα μπει στο merge
                mergeGpxFilesRadius($mergeFileName, $path_user_gpx, $path_id, $first_w);//Το καινούργιο smooth αρχείο θα μπει στο merge
                $first_w = false;
            }

            // ελέγχει για αποτέλεσμα
            $sug_rows = mysqli_num_rows($result_sug);
            if ($sug_rows > 0) {
                // looping through all results
                while ($sug_rows = mysqli_fetch_array($result_sug)) {
                    $uid            = $sug_rows["uid"];
                    //$path_id        = $sug_rows["path_id"];
                    $start_lat      = $sug_rows["start_lat"];
                    $start_long     = $sug_rows["start_long"];
                    $end_lat        = $sug_rows["end_lat"];
                    $end_long       = $sug_rows["end_long"];
                    $pending        = $sug_rows["pending"];
                    $create_date    = $sug_rows["created_at"];

                    // εισάγει την συνάρτηση για merge gpx
                    require_once 'Merge_Gpx_Function.php';
                    mergeSugGpxFile($mergeFileName, $uid, $start_lat, $start_long, $end_lat, $end_long, $pending, $create_date);
                }
            }

            // success - return paths
            $response["success"] = 1;
        } else {
            //error -  not found paths
            $response["error"] = 1;
            $response["error_msg"] = "No found paths";
        }
        //mysqli_close($mysqli);
        return $response;
    }


	/*
     * Αποθηκεύει μία νέα κριτική
     * επιστρέφει τα στοιχεία της κριτικής
     * type: 0 - pedestrian
     *       1 - cycle
     */
    function storeReview($type, $player_id, $path_id,$rated,$rated_tags) {
		
		$mysqli = connect();//σύνδεση με την βάση δεδομένων

        //First Review flag
        $first_rev = false;
        
        if($type==0) {
            //Check if same player
            $result_player = mysqli_query($mysqli,"SELECT player_id FROM paths WHERE uid = '$path_id'");
            if ($result_player) {
                $player_row = mysqli_num_rows($result_player);
                if ($player_row > 0) {
                    //Only one player upload a path with this id
                    $result_player_id = mysqli_fetch_array($result_player);
                    $p_id = $result_player_id['player_id'];
                    if ($p_id == $player_id) {
                        //mysqli_close($mysqli);
                        return 2;
                    }
                }
            }

            //Check if player has review this path again
            $result_rev = mysqli_query($mysqli,"SELECT player_id FROM reviews WHERE path_id = '$path_id'");
            if ($result_rev) {
                $rev_row = mysqli_num_rows($result_rev);
                if ($rev_row > 0) {
                    /*
                    $result_rev = mysqli_fetch_array($result_rev);
                    $p_id = $result_rev['player_id'];
                    if ($p_id == $player_id) {
                        //mysqli_close($mysqli);
                        return 3;
                    }
                    */
                    while ($result_rev_pl_id = mysqli_fetch_array($result_rev)) {
                        $p_id = $result_rev_pl_id['player_id'];
                        if ($p_id == $player_id) {
                            //mysqli_close($mysqli);
                            return 3;
                        }
                    }
                }else{
                    $first_rev = true;
                }
            }else{
                $first_rev = true;
            }
            
            //Insert Review
            $result = mysqli_query($mysqli, "INSERT INTO reviews( player_id, path_id, rated,rated_tags, created_at) VALUES('$player_id', '$path_id', '$rated','$rated_tags', NOW())");//Η NOW () επιστρέφει την τρέχουσα ημερομηνία και ώρα.


        }else{
            //Check if same player
            $result_player = mysqli_query($mysqli,"SELECT player_id FROM cyclepaths WHERE uid = '$path_id'");
            if($result_player) {
                $player_row = mysqli_num_rows($result_player);
                if ($player_row > 0) {
                    //Only one player upload a path with this id
                    $result_player = mysqli_fetch_array($result_player);
                    $p_id = $result_player['player_id'];
                    if ($p_id == $player_id) {
                        //mysqli_close($mysqli);
                        return 2;
                    }
                }
            }

            //Check if player has review this path again
            $result_rev = mysqli_query($mysqli,"SELECT player_id FROM cyclereviews WHERE path_id = '$path_id'");
            if ($result_rev) {
                $rev_row = mysqli_num_rows($result_rev);
                if ($rev_row > 0) {
                    /*
                    $result_rev = mysqli_fetch_array($result_rev);
                    $p_id = $result_rev['player_id'];
                    if ($p_id == $player_id) {
                        //mysqli_close($mysqli);
                        return 3;
                    }
                    */
                    while ($result_rev_pl_id = mysqli_fetch_array($result_rev)) {
                        $p_id = $result_rev_pl_id['player_id'];
                        if ($p_id == $player_id) {
                            //mysqli_close($mysqli);
                            return 3;
                        }
                    }
                }else{
                    $first_rev = true;
                }
            }else{
                $first_rev = true;
            }
            
            $result = mysqli_query($mysqli, "INSERT INTO cyclereviews( player_id, path_id, rated,rated_tags, created_at) VALUES('$player_id', '$path_id', '$rated','$rated_tags', NOW())");//Η NOW () επιστρέφει την τρέχουσα ημερομηνία και ώρα.


        }
        // ελέγχει για επιτυχή εγγραφή
        if ($result) {
			//mysqli_close($mysqli);
            if($first_rev==true) return 0;
            else return 4;
        } else {
			//mysqli_close($mysqli);
            return 1;
        }
    }
    
    /*
     * Αποθηκεύει μία νέα κριτική για ένα pending sketch 
     * μονοπάτι και επιστρέφει τα στοιχεία της κριτικής
     * type: 0 - pedestrian
     *       1 - cycle 
     */
    function storeSugReview($type, $uid, $player_id, $rated, $rated_tags)
    {
        $mysqli = connect();//σύνδεση με την βάση δεδομένων

        //First Review flag
        $first_rev = false;
        
        if($type==0) {
            $result = mysqli_query($mysqli, "SELECT suggestedpaths.uid, suggestedpaths.player_id, suggestedpaths.rate, suggestedpaths.updated_at 
FROM suggestedpaths
WHERE suggestedpaths.uid = '$uid'");
        }else{
            $result = mysqli_query($mysqli, "SELECT sugcyclepaths.uid, sugcyclepaths.player_id, sugcyclepaths.rate, sugcyclepaths.updated_at 
FROM sugcyclepaths
WHERE sugcyclepaths.uid = '$uid'");
        }
        if ($result==false) {
            //mysqli_close($mysqli);
            return 1;
        }
        
        // ελέγχει για αποτέλεσμα
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            //Check if same player
            $res = mysqli_fetch_array($result);
            $p_id = $res['player_id'];
            if($p_id==$player_id){
                //mysqli_close($mysqli);
                return 2;
            }
            $rate = $res['rate'];
            switch($rated)
            {
                case 1: //not acceptable path
                    if($rate>1) $rate = $rate - 2;
                    break;
                case 2: //difficult to accept path
                    if($rate>0) $rate = $rate - 1;
                    break;
                case 3: //acceptable path
                    $rate = $rate + 1;
                    break;
                case 4: //good path
                    $rate = $rate + 2;
                    break;
                case 5: //excellent path
                    $rate = $rate + 3;
                    break;
            }
            if($rate==0)      $pending = 0; //Rejected
            else if($rate<=3) $pending = 1; //Pending
            else              $pending = 2; //Accepted

            //Check if already reviewed from another player
            $updated_at = $res['updated_at'];
            if($updated_at==NULL) $first_rev = true;

            if($type==0) {
                $result = mysqli_query($mysqli, "UPDATE suggestedpaths 
SET pending = '$pending', rate = '$rate', updated_at = NOW()
WHERE uid = '$uid'");
            }else{
                $result = mysqli_query($mysqli, "UPDATE sugcyclepaths 
SET pending = '$pending', rate = '$rate', updated_at = NOW()
WHERE uid = '$uid'");
            }
            //mysqli_close($mysqli);
            if ($result) {
                if($first_rev==true) return 0;
                else return 4;
            } else {
                return 1;
            }
        } else {
            //mysqli_close($mysqli);
            return 1;
        }
    }
