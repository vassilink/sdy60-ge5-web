<?php

/**
 * Συνάρτηση που δέχεται ένα gpx αρχείο και εξάγει το πρώτο, το μεσσαίο και το τελευταίο σημείο
 */

function getLastPathPoints($gpxfile){

    $gpx = simplexml_load_file($gpxfile);
    $sizeOfTrkpts = sizeof($gpx->trk->trkseg->trkpt);

    if($sizeOfTrkpts==0) {
        $points_array = null;
    }
    else if ($sizeOfTrkpts==1){
        $point1 = $gpx->trk->trkseg->trkpt[0];
        $attr1 = $point1->attributes();
        $lat1 = (float)$attr1["lat"];
        $lon1 = (float)$attr1["lon"];

        $points_array= array($lat1, $lon1, 0.000000, 0.000000, 0.000000, 0.000000);
    }
    else if($sizeOfTrkpts==2){
        $point1 = $gpx->trk->trkseg->trkpt[0];
        $attr1 = $point1->attributes();
        $lat1 = (float)$attr1["lat"];
        $lon1 = (float)$attr1["lon"];

        $point3 = $gpx->trk->trkseg->trkpt[1];
        $attr3 = $point3->attributes();
        $lat3 = (float)$attr3["lat"];
        $lon3 = (float)$attr3["lon"];

        $points_array= array($lat1, $lon1, 0.000000, 0.000000, $lat3, $lon3);
    }
    else if($sizeOfTrkpts==3){
        $point1 = $gpx->trk->trkseg->trkpt[0];
        $attr1 = $point1->attributes();
        $lat1 = (float)$attr1["lat"];
        $lon1 = (float)$attr1["lon"];

        $point2 = $gpx->trk->trkseg->trkpt[1];
        $attr2 = $point2->attributes();
        $lat2 = (float)$attr2["lat"];
        $lon2 = (float)$attr2["lon"];

        $point3 = $gpx->trk->trkseg->trkpt[2];
        $attr3 = $point3->attributes();
        $lat3 = (float)$attr3["lat"];
        $lon3 = (float)$attr3["lon"];

        $points_array= array($lat1, $lon1, $lat2, $lon2, $lat3, $lon3);
    }
    else{
        $point1 = $gpx->trk->trkseg->trkpt[0];
        $attr1 = $point1->attributes();
        $lat1 = (float)$attr1["lat"];
        $lon1 = (float)$attr1["lon"];

        $middle = (int)round($sizeOfTrkpts/2);
        $point2 = $gpx->trk->trkseg->trkpt[$middle - 1];
        $attr2 = $point2->attributes();
        $lat2 = (float)$attr2["lat"];
        $lon2 = (float)$attr2["lon"];

        $point3 = $gpx->trk->trkseg->trkpt[$sizeOfTrkpts - 1];
        $attr3 = $point3->attributes();
        $lat3 = (float)$attr3["lat"];
        $lon3 = (float)$attr3["lon"];

        $points_array= array($lat1, $lon1, $lat2, $lon2, $lat3, $lon3);
    }
    return $points_array;
}
?>
