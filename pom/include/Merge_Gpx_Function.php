<?php

//Συνάρτηση που ενσωματώνει δεδομένα σε ένα αρχείο. Τα νέα δεδομένα θα αρχίσουν από την θέση position
function injectData($file, $data, $position) 
{
    $temp = tmpfile();
    $fd = fopen($file, "r+");
    

    fseek($fd, $position);
    stream_copy_to_stream($fd, $temp); // αντιγράφει το τέλος του αρχείου στο προσωρινό

    fseek($fd, $position); // seek back - γυρίζει πίσω στην θέση που θα μπουν τα νέα δεδομένα
    fwrite($fd, $data); // γράφει τα δεδομένα στο αρχικό αρχείο

    rewind($temp);//γυρίζει τη θέση του προσωρινού αρχείου στην αρχή
    stream_copy_to_stream($temp, $fd); // ξαναγράφει το τέλος

    fclose($temp);
    fclose($fd);
}


/*
* Συνάρτηση που ενσωματώνει κάθε νέο gpx αρχείο (newfilename) στο mergeFileName αρχείο. Αν το δεύτερο δεν υπάρχει το δημιουργεί και απλά
* αντιγράφει σε αυτό το νέο gpx αρχείο
*/
function mergeGpxFiles($mergeFileName, $newfilename){

    
    //Αν το merge αρχείο δεν υπάρχει είναι η πρώτη φορά που ανέβηκε ένα gpx άρα αντίγραψε το σε ένα αρχείο με όνομα mergePaths.gpx
    if(!file_exists($mergeFileName)){

        copy($newfilename,$mergeFileName);

    }
    else{//Αλλιώς ενσωμάτωσε το νέο αρχείο στο mergePaths.gpx

        $stringMaintanedFromNewFileForWayPoints = NULL;
        $positionForWpts = NULL;
        
        //Για την ενσωμάτωση των trksegments
        $allNewFilestring = file_get_contents($newfilename); //Ολόκληρο το νέο αρχείο σε string

        $searchForStartOfTrkSeg = '<trkseg>';//Από αυτό το tag και κάτω θέλουμε να κρατήσουμε από το νέο αρχείο
        $startPosOfTrkSeg = strpos($allNewFilestring, $searchForStartOfTrkSeg);//Η θέση από την οποία και κάτω θέλουμε να κρατήσουμε
        $searchForEndOfTrkSeg = '</trk>';//Μέχρι αυτό το tag θέλουμε να κρατήσουμε
        $endPosOfTrkSeg = strpos($allNewFilestring, $searchForEndOfTrkSeg);//Η θέση μέχρι την οποία θέλουμε να κρατήσουμε
        $lengthOfStringOfTrkSeg = $endPosOfTrkSeg - $startPosOfTrkSeg;//Το μήκος του string που θέλουμε να κρατήσουμε
        $stringMaintanedFromNewFileForTrkSeg1 = substr($allNewFilestring, $startPosOfTrkSeg,$lengthOfStringOfTrkSeg );//To string που θέλουμε να διατηρήσουμε από το νέο αρχείο

        $stringMaintanedFromNewFileForTrkSeg = "<name>Tracking by PoM</name>".$stringMaintanedFromNewFileForTrkSeg1;//Το name θέλουμε να το προσθέσουμε πριν το trseg

        //Για την ενσωμάτωση των waypoints 
        $searchWaypoints = '<wpt';//Αν υπάρχουν waypoints θα υπάρχει αυτό το tag
        $startPosOfWaypoints = strpos($allNewFilestring, $searchWaypoints);//Η θέση που αρχίζουν τα waypoints αν υπάρχουν
        $wayPointsExistsInNewFile = false; //Στην αρχή θεωρούμε ότι δεν υπάρχουν wayPoints
        if ($startPosOfWaypoints){ //Αν υπάρχουν waypoints θα βρούμε το string που τα περιέχει
            $searchEndOfWayPoints = '<trk>';//Όταν αρχίζει το tag: trk τελειώνουν τα waypoints
            $endPosOfWaypoints = strpos($allNewFilestring, $searchEndOfWayPoints);//Η θέση που τελειώνουν τα waypoints
            $lengthOfWaypoints = $endPosOfWaypoints - $startPosOfWaypoints;//Το μήκος του τμήματος των waypoints
            
            $stringMaintanedFromNewFileForWayPoints = substr($allNewFilestring,$startPosOfWaypoints,$lengthOfWaypoints);//Το string που διατηρούμε

            $wayPointsExistsInNewFile = true;//Υπάρχουν waypoints στο νέο αρχείο
        }

        unset($allNewFilestring);//Για να ελευθερώσουμε χώρο

        $searchforEndOfTrk = '</trk>';//Πριν από αυτό το tag θα μπει το καινούργιο trkseg
        $mergeString = file_get_contents($mergeFileName);//Φορτώνει το merge αρχείο στην μεταβλητή $mergeString
        $positionForTrkSeg = strpos($mergeString, $searchforEndOfTrk); //Η θέση στην οποία θα ενσωματωθεί το καινούργιο trkseg

        if($wayPointsExistsInNewFile){//Η θέση που θα μπουν τα waypoints στο merge αρχείο μας απασχολεί μόνο αν υπάρχουν wpt στο νέο 
            $searchforEndOfWpts = '<trk>';//Πριν από αυτό το tag θα μπουν τα καινούργια wpts
            $positionForWpts = strpos($mergeString,$searchforEndOfWpts);
        }

        unset($mergeString);//Για ελευθέρωση μνήμης

        if($positionForTrkSeg) //αν βρέθηκε η θέση τότε ενσωμάτωσε το string που θέλουμε
        {
            injectData($mergeFileName, $stringMaintanedFromNewFileForTrkSeg, $positionForTrkSeg); //Η ενσωμάτωση του trkseg στο merge αρχείο
        }

        if($wayPointsExistsInNewFile && $positionForWpts!=NULL)//Αν υπάρχουν waypoints τα βάζει στο νέο αρχείο
        {
            injectData($mergeFileName, $stringMaintanedFromNewFileForWayPoints, $positionForWpts); //Η ενσωμάτωση των waypoints στο merge αρχείο
        }
    }
}


/*
* Συνάρτηση που ενσωματώνει κάθε νέο gpx αρχείο (newfilename) στο mergeFileName αρχείο. Αν το δεύτερο δεν υπάρχει το δημιουργεί και απλά
* αντιγράφει σε αυτό το νέο gpx αρχείο
*/
function mergeGpxFilesRadius($mergeFileName, $newfilename, $pathId, $first_write){

    //Αν το merge αρχείο δεν υπάρχει είναι η πρώτη φορά που ανέβηκε ένα gpx άρα αντίγραψε το σε ένα αρχείο με όνομα mergePaths.gpx
    if($first_write){//!file_exists($mergeFileName)){

        //Για την ενσωμάτωση των trksegments
        $allNewFilestring = file_get_contents($newfilename); //Ολόκληρο το νέο αρχείο σε string

        $searchForStartOfTrkSeg = '<trkseg>';//Από αυτό το tag και κάτω θέλουμε να κρατήσουμε από το νέο αρχείο
        $startPosOfTrkSeg = strpos($allNewFilestring, $searchForStartOfTrkSeg);//Η θέση κάτω από την οποία θέλουμε να κρατήσουμε
        $searchForEndOfTrkSeg = '</trkseg>';//Από αυτό το tag και πάνω θέλουμε να κρατήσουμε από το νέο αρχείο
        $endPosOfTrkSeg = strpos($allNewFilestring, $searchForEndOfTrkSeg);//Η θέση πάνω από την οποία θέλουμε να κρατήσουμε
        $lengthOfStringOfTrkSeg = $endPosOfTrkSeg - $startPosOfTrkSeg;//Το μήκος του string που θέλουμε να κρατήσουμε
        $stringMaintanedFromNewFileForTrkSeg1 = substr($allNewFilestring, $startPosOfTrkSeg, $lengthOfStringOfTrkSeg);//To string που θέλουμε να διατηρήσουμε από το νέο αρχείο
        $stringMaintanedFromNewFileForTrkSeg = $stringMaintanedFromNewFileForTrkSeg1."<name>Tracking by PoM</name><pathid>".$pathId."</pathid></trkseg></trk></gpx>";//Το name και το pathid θέλουμε να το προσθέσουμε πριν το trseg

        //Για την ενσωμάτωση των waypoints
        $searchWaypoints = '<wpt';//Αν υπάρχουν waypoints θα υπάρχει αυτό το tag
        $startPosOfWaypoints = strpos($allNewFilestring, $searchWaypoints);//Η θέση που αρχίζουν τα waypoints αν υπάρχουν
        if ($startPosOfWaypoints) { //Αν υπάρχουν waypoints θα βρούμε το string που τα περιέχει
            $searchEndOfWayPoints = '<trk>';//Όταν αρχίζει το tag: trk τελειώνουν τα waypoints
            $endPosOfWaypoints = strpos($allNewFilestring, $searchEndOfWayPoints);//Η θέση που τελειώνουν τα waypoints
            $lengthOfWaypoints = $endPosOfWaypoints - $startPosOfWaypoints;//Το μήκος του τμήματος των waypoints
            $stringMaintanedFromNewFileForWayPoints = substr($allNewFilestring, $startPosOfWaypoints, $lengthOfWaypoints);//Το string που διατηρούμε
            $stringMaintanedFromNewFileStart1 = substr($allNewFilestring, 0, $startPosOfWaypoints);//Το string που διατηρούμε
            $stringMaintanedFromNewFileStart = $stringMaintanedFromNewFileStart1.$stringMaintanedFromNewFileForWayPoints."<trk><name>Tracking by PoM</name>";
        }else{
            $searchTrk = '<trkseg>';//Όταν αρχίζει το tag
            $startPosOfTrk = strpos($allNewFilestring, $searchTrk);//Η θέση που αρχίζει το trk
            $stringMaintanedFromNewFileStart = substr($allNewFilestring, 0, $startPosOfTrk);//Το string που διατηρούμε
        }

        unset($allNewFilestring);//Για να ελευθερώσουμε χώρο

        $fd = fopen($mergeFileName, "w");//"wb+");
        fwrite($fd, $stringMaintanedFromNewFileStart); // γράφει τα δεδομένα στο αρχικό αρχείο
        fwrite($fd, $stringMaintanedFromNewFileForTrkSeg); // γράφει τα δεδομένα στο αρχικό αρχείο
        fclose($fd);

    }
    else{//Αλλιώς ενσωμάτωσε το νέο αρχείο στο mergePaths.gpx

        $stringMaintanedFromNewFileForWayPoints = NULL;
        $positionForWpts = NULL;

        //Για την ενσωμάτωση των trksegments
        $allNewFilestring = file_get_contents($newfilename); //Ολόκληρο το νέο αρχείο σε string

        $searchForStartOfTrkSeg = '<trkseg>';//Από αυτό το tag και κάτω θέλουμε να κρατήσουμε από το νέο αρχείο
        $startPosOfTrkSeg = strpos($allNewFilestring, $searchForStartOfTrkSeg);//Η θέση από την οποία και κάτω θέλουμε να κρατήσουμε
        $searchForEndOfTrkSeg = '</trkseg>';//Μέχρι αυτό το tag θέλουμε να κρατήσουμε
        $endPosOfTrkSeg = strpos($allNewFilestring, $searchForEndOfTrkSeg);//Η θέση μέχρι την οποία θέλουμε να κρατήσουμε
        $lengthOfStringOfTrkSeg = $endPosOfTrkSeg - $startPosOfTrkSeg;//Το μήκος του string που θέλουμε να κρατήσουμε
        $stringMaintanedFromNewFileForTrkSeg1 = substr($allNewFilestring, $startPosOfTrkSeg,$lengthOfStringOfTrkSeg);//To string που θέλουμε να διατηρήσουμε από το νέο αρχείο

        $stringMaintanedFromNewFileForTrkSeg = $stringMaintanedFromNewFileForTrkSeg1."<name>Tracking by PoM</name><pathid>".$pathId."</pathid></trkseg>";//Το name θέλουμε να το προσθέσουμε πριν το trseg


        //Για την ενσωμάτωση των waypoints
        $searchWaypoints = '<wpt';//Αν υπάρχουν waypoints θα υπάρχει αυτό το tag
        $startPosOfWaypoints = strpos($allNewFilestring, $searchWaypoints);//Η θέση που αρχίζουν τα waypoints αν υπάρχουν
        $wayPointsExistsInNewFile = false; //Στην αρχή θεωρούμε ότι δεν υπάρχουν wayPoints
        if ($startPosOfWaypoints){ //Αν υπάρχουν waypoints θα βρούμε το string που τα περιέχει
            $searchEndOfWayPoints = '<trk>';//Όταν αρχίζει το tag: trk τελειώνουν τα waypoints
            $endPosOfWaypoints = strpos($allNewFilestring, $searchEndOfWayPoints);//Η θέση που τελειώνουν τα waypoints
            $lengthOfWaypoints = $endPosOfWaypoints - $startPosOfWaypoints;//Το μήκος του τμήματος των waypoints
            $stringMaintanedFromNewFileForWayPoints = substr($allNewFilestring,$startPosOfWaypoints,$lengthOfWaypoints);//Το string που διατηρούμε
            $wayPointsExistsInNewFile = true;//Υπάρχουν waypoints στο νέο αρχείο
        }

        unset($allNewFilestring);//Για να ελευθερώσουμε χώρο

        $searchforEndOfTrk = '</trk>';//Πριν από αυτό το tag θα μπει το καινούργιο trkseg
        $mergeString = file_get_contents($mergeFileName);//Φορτώνει το merge αρχείο στην μεταβλητή $mergeString
        $positionForTrkSeg = strpos($mergeString, $searchforEndOfTrk); //Η θέση στην οποία θα ενσωματωθεί το καινούργιο trkseg

        if($wayPointsExistsInNewFile){//Η θέση που θα μπουν τα waypoints στο merge αρχείο μας απασχολεί μόνο αν υπάρχουν wpt στο νέο
            $searchforEndOfWpts = '<trk>';//Πριν από αυτό το tag θα μπουν τα καινούργια wpts
            $positionForWpts = strpos($mergeString,$searchforEndOfWpts);
        }

        unset($mergeString);//Για ελευθέρωση μνήμης

        if($positionForTrkSeg) //αν βρέθηκε η θέση τότε ενσωμάτωσε το string που θέλουμε
        {
            injectData($mergeFileName, $stringMaintanedFromNewFileForTrkSeg, $positionForTrkSeg); //Η ενσωμάτωση του trkseg στο merge αρχείο
        }

        if($wayPointsExistsInNewFile && $positionForWpts!=NULL)//Αν υπάρχουν waypoints τα βάζει στο νέο αρχείο
        {
            injectData($mergeFileName, $stringMaintanedFromNewFileForWayPoints, $positionForWpts); //Η ενσωμάτωση των waypoints στο merge αρχείο
        }
    }
}


function mergeSugGpxFile($mergeFileName, $pathId, $startLat, $startLong, $endLat, $endLong, $pending, $createDate){

    //Για την ενσωμάτωση των trksegments
    $mergeString = file_get_contents($mergeFileName);               //Ολόκληρο το νέο αρχείο σε string
    $searchForEndOfTrk = '</trk>';                                  //Πριν από αυτό το tag θα μπει το καινούργιο trkseg
    $positionForTrkSeg = strpos($mergeString, $searchForEndOfTrk);  //Η θέση στην οποία θα ενσωματωθεί το καινούργιο trkseg
    if($positionForTrkSeg) //αν βρέθηκε η θέση τότε ενσωμάτωσε το string που θέλουμε
    {
        //<trkseg><trkpt lat="37.9799156" lon="23.6601709"><time>2017-01-23T22:04:40+0200</time><hdop>14.521</hdop></trkpt><trkpt lat="37.9799272" lon="23.6601857"><time>2017-01-23T22:05:22+0200</time><hdop>10.0</hdop></trkpt><name>Tracking by PoM</name><pathid>63</pathid></trkseg>
        //$newData = '<trkseg><trkpt lat="'.$startLat.'" lon="'.$startLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><trkpt lat="'.$endLat.'" lon="'.$endLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><name>Sketch Path</name><pathid>'.$pathId.'</pathid></trkseg>';
        //$data = '<trkseg><trkpt lat="'.$startLat.'" lon="'.$startLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><trkpt lat="'.$endLat.'" lon="'.$endLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt>';
        if($pending==1) $newData = '<trkseg><trkpt lat="'.$startLat.'" lon="'.$startLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><trkpt lat="'.$endLat.'" lon="'.$endLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><name>Pending Path</name><pathid>'.$pathId.'</pathid></trkseg>';
        else if($pending==2) $newData = '<trkseg><trkpt lat="'.$startLat.'" lon="'.$startLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><trkpt lat="'.$endLat.'" lon="'.$endLong.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt><name>Sketch Path</name><pathid>'.$pathId.'</pathid></trkseg>';
        else return;

        injectData($mergeFileName, $newData, $positionForTrkSeg);       //Η ενσωμάτωση του trkseg στο merge αρχείο
    }

}



/*
 * Συνάρτηση που ενσωματώνει κάθε νέο pending μονοπατι στο mergeFileName αρχείο.
 */
function mergeGpxPendFiles($mergeFileName, $s_lat, $s_long, $e_lat, $e_long, $time, $hdop, $pathId){

    //Αν το merge αρχείο δεν υπάρχει
    if(!file_exists($mergeFileName)){

        $startOfFile = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'.'<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" creator="PoM" version="1.1" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd"><trk><name>Tracking by PoM</name><trkseg>';
        $trksegStart = '<trkpt lat="'.$s_lat.'" lon="'.$s_long.'"><time>'.$time.'</time><hdop>'.$hdop.'</hdop></trkpt>';
        $trksegEnd = '<trkpt lat="'.$e_lat.'" lon="'.$e_long.'"><time>'.$time.'</time><hdop>'.$hdop.'</hdop></trkpt>';
        $endOfFile = '<name>Tracking by PoM</name><pathid>'.$pathId.'</pathid></trkseg></trk></gpx>';
        $fileData =  $startOfFile.$trksegStart.$trksegEnd.$endOfFile;

        $fd = fopen($mergeFileName, "w");//"wb+");
        fwrite($fd, $fileData); // γράφει τα δεδομένα στο αρχείο
        fclose($fd);

    }else{  //Αν το merge αρχείο υπάρχει

        //Για την ενσωμάτωση των trk segments
        $allMergeFileString = file_get_contents($mergeFileName); //Ολόκληρο το αρχείο σε string

        $searchOfNewData = '</trk>';//Μέχρι αυτό το tag θέλουμε να κρατήσουμε
        $startPosOfNewData = strpos($allMergeFileString, $searchOfNewData);//Η θέση μέχρι την οποία θέλουμε να κρατήσουμε
        //$stringMaintanedFromOldFile = substr($allMergeFileString, 0, $startPosOfNewData);//To string που θέλουμε να διατηρήσουμε από το νέο αρχείο

        unset($allMergeFileString);//Για να ελευθερώσουμε χώρο

        $trksegStart = '<trkseg><trkpt lat="'.$s_lat.'" lon="'.$s_long.'"><time>'.$time.'</time><hdop>'.$hdop.'</hdop></trkpt>';
        $trksegEnd = '<trkpt lat="'.$e_lat.'" lon="'.$e_long.'"><time>'.$time.'</time><hdop>'.$hdop.'</hdop></trkpt>';
        $endOfFile = '<name>Tracking by PoM</name><pathid>'.$pathId.'</pathid></trkseg>';
        $fileData = $trksegStart.$trksegEnd.$endOfFile;

        if($startPosOfNewData) {
            injectData($mergeFileName, $fileData, $startPosOfNewData); //Η ενσωμάτωση του trkseg στο merge αρχείο
        }
    }
}


/*
 * Admin get sketch paths
 */
function mergeAdminSketchGpxFile($mergeFileName, $pathId, $s_lat, $s_long, $e_lat, $e_long, $pending, $createDate, $first_write){

    //Αν το merge αρχείο δεν υπάρχει
    if($first_write){//!file_exists($mergeFileName)){

        $startOfFile = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'.'<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" creator="PoM" version="1.1" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd"><trk><name>Tracking by PoM</name><trkseg>';
        $trksegStart = '<trkpt lat="'.$s_lat.'" lon="'.$s_long.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt>';
        $trksegEnd = '<trkpt lat="'.$e_lat.'" lon="'.$e_long.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt>';
        if($pending==1)     $endOfFile = '<name>Pending Path</name><pathid>'.$pathId.'</pathid></trkseg></trk></gpx>';
        else if($pending==2)$endOfFile = '<name>Sketch Path</name><pathid>'.$pathId.'</pathid></trkseg></trk></gpx>';
        else                $endOfFile = '<name>Rejected Path</name><pathid>'.$pathId.'</pathid></trkseg></trk></gpx>';
        $fileData =  $startOfFile.$trksegStart.$trksegEnd.$endOfFile;

        $fd = fopen($mergeFileName, "w");//"wb+");
        fwrite($fd, $fileData); // γράφει τα δεδομένα στο αρχείο
        fclose($fd);

    }else{  //Αν το merge αρχείο υπάρχει

        //Για την ενσωμάτωση των trk segments
        $allMergeFileString = file_get_contents($mergeFileName); //Ολόκληρο το αρχείο σε string

        $searchOfNewData = '</trk>';//Μέχρι αυτό το tag θέλουμε να κρατήσουμε
        $startPosOfNewData = strpos($allMergeFileString, $searchOfNewData);//Η θέση μέχρι την οποία θέλουμε να κρατήσουμε
        //$stringMaintanedFromOldFile = substr($allMergeFileString, 0, $startPosOfNewData);//To string που θέλουμε να διατηρήσουμε από το νέο αρχείο

        unset($allMergeFileString);//Για να ελευθερώσουμε χώρο

        $trksegStart = '<trkseg><trkpt lat="'.$s_lat.'" lon="'.$s_long.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt>';
        $trksegEnd = '<trkpt lat="'.$e_lat.'" lon="'.$e_long.'"><time>'.$createDate.'</time><hdop>14.00</hdop></trkpt>';
        if($pending==1)     $endOfFile = '<name>Pending Path</name><pathid>'.$pathId.'</pathid></trkseg>';
        else if($pending==2)$endOfFile = '<name>Sketch Path</name><pathid>'.$pathId.'</pathid></trkseg>';
        else                $endOfFile = '<name>Rejected Path</name><pathid>'.$pathId.'</pathid></trkseg>';
        $fileData = $trksegStart.$trksegEnd.$endOfFile;

        if($startPosOfNewData) {
            injectData($mergeFileName, $fileData, $startPosOfNewData); //Η ενσωμάτωση του trkseg στο merge αρχείο
        }
    }
}



/*
 * Admin get new paths
 */
function mergeAdminNewGpxFile($mergeFileName, $newfilename, $first_write){

    //Αν το merge αρχείο δεν υπάρχει
    if($first_write){

        copy($newfilename,$mergeFileName);

    }else{  //Αν το merge αρχείο υπάρχει

        //Για την ενσωμάτωση των trksegments
        $allNewFilestring = file_get_contents($newfilename); //Ολόκληρο το νέο αρχείο σε string

        $searchForStartOfTrkSeg = '<trkseg>';//Από αυτό το tag και κάτω θέλουμε να κρατήσουμε από το νέο αρχείο
        $startPosOfTrkSeg = strpos($allNewFilestring, $searchForStartOfTrkSeg);//Η θέση από την οποία και κάτω θέλουμε να κρατήσουμε
        $searchForEndOfTrkSeg = '</trk>';//Μέχρι αυτό το tag θέλουμε να κρατήσουμε
        $endPosOfTrkSeg = strpos($allNewFilestring, $searchForEndOfTrkSeg);//Η θέση μέχρι την οποία θέλουμε να κρατήσουμε
        $lengthOfStringOfTrkSeg = $endPosOfTrkSeg - $startPosOfTrkSeg;//Το μήκος του string που θέλουμε να κρατήσουμε
        $stringMaintanedFromNewFileForTrkSeg1 = substr($allNewFilestring, $startPosOfTrkSeg,$lengthOfStringOfTrkSeg );//To string που θέλουμε να διατηρήσουμε από το νέο αρχείο

        $stringMaintanedFromNewFileForTrkSeg = "<name>Tracking by PoM</name>".$stringMaintanedFromNewFileForTrkSeg1;//Το name θέλουμε να το προσθέσουμε πριν το trseg

        //Για την ενσωμάτωση των waypoints
        $searchWaypoints = '<wpt';//Αν υπάρχουν waypoints θα υπάρχει αυτό το tag
        $startPosOfWaypoints = strpos($allNewFilestring, $searchWaypoints);//Η θέση που αρχίζουν τα waypoints αν υπάρχουν
        $wayPointsExistsInNewFile = false; //Στην αρχή θεωρούμε ότι δεν υπάρχουν wayPoints
        if ($startPosOfWaypoints){ //Αν υπάρχουν waypoints θα βρούμε το string που τα περιέχει
            $searchEndOfWayPoints = '<trk>';//Όταν αρχίζει το tag: trk τελειώνουν τα waypoints
            $endPosOfWaypoints = strpos($allNewFilestring, $searchEndOfWayPoints);//Η θέση που τελειώνουν τα waypoints
            $lengthOfWaypoints = $endPosOfWaypoints - $startPosOfWaypoints;//Το μήκος του τμήματος των waypoints
            $stringMaintanedFromNewFileForWayPoints = substr($allNewFilestring,$startPosOfWaypoints,$lengthOfWaypoints);//Το string που διατηρούμε
            $wayPointsExistsInNewFile = true;//Υπάρχουν waypoints στο νέο αρχείο
        }

        unset($allNewFilestring);//Για να ελευθερώσουμε χώρο

        $searchforEndOfTrk = '</trk>';//Πριν από αυτό το tag θα μπει το καινούργιο trkseg
        $mergeString = file_get_contents($mergeFileName);//Φορτώνει το merge αρχείο στην μεταβλητή $mergeString
        $positionForTrkSeg = strpos($mergeString, $searchforEndOfTrk); //Η θέση στην οποία θα ενσωματωθεί το καινούργιο trkseg

        if($wayPointsExistsInNewFile){//Η θέση που θα μπουν τα waypoints στο merge αρχείο μας απασχολεί μόνο αν υπάρχουν wpt στο νέο
            $searchforEndOfWpts = '<trk>';//Πριν από αυτό το tag θα μπουν τα καινούργια wpts
            $positionForWpts = strpos($mergeString,$searchforEndOfWpts);
        }

        unset($mergeString);//Για ελευθέρωση μνήμης

        if($positionForTrkSeg) //αν βρέθηκε η θέση τότε ενσωμάτωσε το string που θέλουμε
        {
            injectData($mergeFileName, $stringMaintanedFromNewFileForTrkSeg, $positionForTrkSeg); //Η ενσωμάτωση του trkseg στο merge αρχείο
        }

        if($wayPointsExistsInNewFile && $positionForWpts)//Αν υπάρχουν waypoints τα βάζει στο νέο αρχείο
        {
            injectData($mergeFileName, $stringMaintanedFromNewFileForWayPoints, $positionForWpts); //Η ενσωμάτωση των waypoints στο merge αρχείο
        }
    }
}


?>