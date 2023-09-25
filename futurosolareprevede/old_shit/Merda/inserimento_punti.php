<?php
include("../../Utilities/functions.php");

//var_dump($conn);
$conn = getDbConnection();
$gpx = simplexml_load_file("../data/futurosolareprevedewaypoints.gpx");
$path = [];
$i = 0;

foreach ($gpx->wpt as $pt) {
  $path[$i]['lat'] = (float) $pt->attributes()['lat'];
  $path[$i]['lon'] = (float) $pt->attributes()['lon'];
  $path[$i]['distance_to_next'] = 5; 
	$path[$i]['ele'] = -1;
  $i++;
}

echo "<pre>";
echo $i;
for($j=0 ; $j < sizeof($path); $j++ ){

  $sql = "INSERT INTO points (point_ID, latitude, longitude, elevation)
    VALUES (".strval($j).",".$path[$j]['lat'].','.$path[$j]['lon'].', '.$path[$j]['ele'].')';

  if(false === mysqli_query($conn, $sql)) {
      exit("Errore: impossibile eseguire la query. " . mysqli_error($conn) . '\n' . $sql);
  }
}

  mysqli_close($conn);

?>