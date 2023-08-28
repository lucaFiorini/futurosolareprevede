<?php
die(); // avoids unvolontary execution
include "Utility/database_connection.php";

//var_dump($conn);

$gpx = simplexml_load_file("data/futurosolareprevedewaypoints.gpx");
$path = [];
echo "<pre>";

var_dump((float) $gpx->wpt[3]->attributes()->lat);

echo "STOP\n";
$i = 0;

foreach ($gpx->wpt as $pt) {
    $path[$i]['lat'] = (float) $pt->attributes()['lat'];
    $path[$i]['lon'] = (float) $pt->attributes()['lon'];
    $path[$i]['timeToNext'] = 5; ///table/v1/{profile}/{coordinates}?{sources}=[{elem}...];&{destinations}=[{elem}...]&annotations={duration|distance|duration,distance}
	$path[$i]['ele'] = -1;
    $i++;
}

echo "<pre>";
$gpx = simplexml_load_file("data/pathData.gpx");

for($j= count($path) -1 ; $j >= 0; $j-- ){

  $sql = "INSERT INTO points (IDpunto, LatestWeatherData, TimeToNext, Latitudine, Longitudine, Elevazione) VALUES (". (count($path) - $j - 1) .", '{}', ".$path[$j]['timeToNext'].",".$path[$j]['lat'].','.$path[$j]['lon'].', '.$path[$j]['ele'].')';

  if(false === mysqli_query($conn, $sql)) {
      exit("Errore: impossibile eseguire la query. " . mysqli_error($conn) . '\n' . $sql);
  }
}

  mysqli_close($conn);

?>