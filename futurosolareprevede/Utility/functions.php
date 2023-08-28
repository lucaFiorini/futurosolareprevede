<?php 
class COORD{
  public float $lat;
  public float $lon;
  function __construct(float $lat,float $lon){
    $this::$lat = $lat;
    $this::$lon = $lon;
  }

  //source : https://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
  static function getDistanceBetween(COORD $a,COORD $b, float  $earthRadius = 6371000) : float{
    // convert from degrees to radians
    $latFrom = deg2rad($a::$lat);
    $lonFrom = deg2rad($a::$lon);
    $latTo = deg2rad($b::$lat);
    $lonTo = deg2rad($b::$lon);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    return $angle * $earthRadius;
  }

  function getDistanceFrom(COORD $other) : float {
    return COORD::getDistanceBetween($this,$other);
  }

}

//$A and $B are COORDINATE OBJECTS (float attributes lat,lon)
/* EXAMPLE CALL
$A['lon'] = 130.86899597672;
$A['lat'] = -12.42540233038;

$B['lon'] = 130.834324;
$B['lat'] = -12.459402;

getRoute($B,$A)
*/

function getRoute(COORD $A,COORD $B){

  $requestRoute = 'http://router.project-osrm.org/route/v1/driving/%s;%s?overview=false';

  $req = sprintf(
      $requestRoute,
      strval($A::$lon).','.strval($A::$lat),
      strval($B::$lon).','.strval($B::$lat),
  );

  $ch = curl_init($req);
  header("content-type: application/json");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
  $res = curl_exec($ch);
  return json_decode($res);
}

/*
* @return COORD[] 
*/
function loadPoints() : array {
  $out = [];
  return $out; 
}


?>