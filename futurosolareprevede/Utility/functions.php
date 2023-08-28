<?php 


//$A and $B are COORDINATE OBJECTS (float attributes lat,lon)
/* EXAMPLE CALL
$A['lon'] = 130.86899597672;
$A['lat'] = -12.42540233038;

$B['lon'] = 130.834324;
$B['lat'] = -12.459402;

getRoute($B,$A)
*/
function getRoute($A, $B){

  $requestRoute = 'http://router.project-osrm.org/route/v1/driving/%s;%s?overview=false';

  $req = sprintf(
      $requestRoute,
      strval($A['lon']).','.strval($A['lat']),
      strval($B['lon']).','.strval($B['lat'])
  );

  $ch = curl_init($req);
  header("content-type: application/json");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
  $res = curl_exec($ch);
  return json_decode($res);
}

?>