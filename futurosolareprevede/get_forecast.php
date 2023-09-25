<?php
require("Utilities/functions.php");

if($_POST == NULL) 
  die("Cannot GET get_forecast.php");

$req = $_POST;
$current_location = new COORD($req["lat"],$req["lon"]);
$maxSpeed = $req['max_speed'];

$points = loadPoints();

$closest = array("coords" => new COORD(0,0), "distance" => INF, "id" => NULL);
$second_closest = array("coords" => new COORD(0,0), "distance" => INF, "id" => NULL);

foreach($points as $id => $point){
  $cur_distance = $current_location->getDistanceFrom($point["coord"]);
  if($cur_distance < $closest["distance"]){
    $second_closest = $closest;
    $closest = array("coords" => $current_location, "distance" => $cur_distance,"id" => $id);
  } else if($cur_distance < $second_closest["distance"]) {
    $second_closest = array("coords" => $current_location, "distance" => $cur_distance,"id" => $id);
  }
}

$first_point = ($closest["id"] > $second_closest["id"]) ? $closest : $second_closest;
$next_waypoints = array_slice($points,$first_point["id"],30,false);

$forecast = loadOSRMdata(...array_column($next_waypoints,"coord"));
$is_updated = ($forecast === false) ? false : true; 

header("Content-Type: application/json");
$out = array(
  "flag"=>$is_updated,
  "data"=>$next_waypoints
);
echo json_encode($out);
?>