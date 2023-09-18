<?php 
function getSqlConnection(){
  $conn = mysqli_connect('localhost', 'futurosolareprevede', '', 'my_futurosolareprevede');	
  if (!$conn) die("Connessione fallita: " . mysqli_connect_error());
  else return $conn;
}

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

function loadWaypoints() : array {
  $out = [];
  $conn = getSqlConnection();
  $res = $conn->query("SELECT * FROM points");
  while($row = $res->fetch_row()){
    $out[] = $row;
  }
  return $out; 
}


?>