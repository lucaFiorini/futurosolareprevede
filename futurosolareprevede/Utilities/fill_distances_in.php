<?php
include_once("Utilities/functions.php");
$points = loadPoints();
$forecast = loadOSRMdata(...$points);
foreach($forecast as $data){
  $query = 'UPDATE points 
  SET distance_to_next ='.strval($data["distance_to_next"]).', time_to_next = '.strval(intval($data["time_to_next"])).'
  WHERE point_id = '.strval($data["location"]->point_ID);
  echo $query;
  $res = getDbConnection()->query($query);
}
?>