<?php 
function getDbConnection(){
  $conn = mysqli_connect("localhost","root","","futurosolareprevede");	
  $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, TRUE);
  if (!$conn) die("Connessione fallita: " . mysqli_connect_error());
  else return $conn;
}

class COORD{
  public float $lat;
  public float $lon;
  function __construct(float $lat,float $lon){
    $this->lat = $lat;
    $this->lon = $lon;
  }

  //source : https://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
  static function getDistanceBetween(COORD $a,COORD $b, float  $earthRadius = 6371000) : float{
    // convert from degrees to radians
    $latFrom = deg2rad($a->lat);
    $lonFrom = deg2rad($a->lon);
    $latTo = deg2rad($b->lat);
    $lonTo = deg2rad($b->lon);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    return $angle * $earthRadius;
  }

  function getDistanceFrom(COORD $other) : float {
    return COORD::getDistanceBetween($this,$other);
  }

}

/*
* @return COORD[] 
*/
function loadPoints() : array {
  $conn = getDbConnection();
  $res = $conn->query("SELECT * FROM points ORDER BY point_ID");
  $out = [];
  while($row = $res->fetch_assoc()){
    $row["coord"] = new COORD($row["latitude"],$row["longitude"]);
    unset($row["latitude"],$row["longitude"]);
    $out[] = $row;
  }
  return $out; 
}

function getRoute(COORD $first, COORD $second,COORD ...$more){

  $waypoints = [$second,...$more];
  $concatenated_waypoints = strval($first->lon).','.strval($first->lat);
  
  foreach($waypoints as $waypoint){
    $concatenated_waypoints .= ';'.strval($waypoint->lon).','.strval($waypoint->lat);
  }

  $requestRoute = 'http://router.project-osrm.org/route/v1/driving/%s?overview=false';
  $req = sprintf(
      $requestRoute,
      $concatenated_waypoints
  );

  $res = file_get_contents($req);
  if($res === false){
    return false;
  }
  return json_decode($res);
}

function getForecastOnRoute(COORD $first, COORD $second,COORD ...$more){
  //TODO: 1)get suncalc data 2)sort out weather API
}
?>