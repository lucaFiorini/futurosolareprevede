<?php
require("Utilities/functions.php");
$c = new COORD(0,0);
updateOpenMeteoData();
exit();
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
$OSRMdata = loadOSRMdata($current_location,$first_point["coords"]);

$duration = $OSRMdata[0]["time_to_next"];

echo json_encode($out);
?>