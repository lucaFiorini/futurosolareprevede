<?php
require("Utilities/functions.php");
if(!isset($_GET)) die("No get request specified");
$req = $_GET;

$current_location = new COORD($req["lat"],$req["lon"]);

$points = loadPoints();

$closest = array("coords" => new COORD(0,0), "distance" => INF, "id" => NULL);
$second_closest = array("coords" => new COORD(0,0), "distance" => INF, "id" => NULL);

foreach($points as $id => $point){
    $cur_distance = $current_location->getDistanceFrom($point);
    if($cur_distance < $closest["distance"]){
        $second_closest = $closest;
        $closest = array("coords" => $current_location, "distance" => $cur_distance,"id" => $id);
    } else if($distance < $second_closest["distance"]) {
        $second_closest = array("coords" => $current_location, "distance" => $cur_distance,"id" => $id);
    }
}

$first_point = ($closest["id"] > $second_closest["id"]) ? $closest : $second_closest;

for($i = $first_point["id"]; isset($points[$i]); $i++){
    //Give info and predictions for next N points TODO: determine N
}
?>