<?php
include("../Utility/functions.php");

//var_dump($conn);
$conn = getSqlConnection();
$gpx = simplexml_load_file("../data/futurosolareprevedewaypoints.gpx");
$path = [];
$i = 0;

foreach ($gpx->wpt as $pt) {
  $path[$i]['lat'] = (float) $pt->attributes()['lat'];
  $path[$i]['lon'] = (float) $pt->attributes()['lon'];
  $path[$i]['distance_to_next'] = 5; ///table/v1/{profile}/{coordinates}?{sources}=[{elem}...];&{destinations}=[{elem}...]&annotations={duration|distance|duration,distance}
	$path[$i]['ele'] = -1;
  $i++;
}

echo "<pre>";
echo $i;
for($j= count($path) -1 ; $j >= 0; $j-- ){

  $sql = "INSERT INTO points (point_ID, latest_weather_data, distance_to_next, latitude, longitude, elevation)
    VALUES (". (count($path) - $j - 1) .", '{}', ".$path[$j]['distance_to_next'].",".$path[$j]['lat'].','.$path[$j]['lon'].', '.$path[$j]['ele'].')';

  if(false === mysqli_query($conn, $sql)) {
      exit("Errore: impossibile eseguire la query. " . mysqli_error($conn) . '\n' . $sql);
  }
}

  mysqli_close($conn);

?>