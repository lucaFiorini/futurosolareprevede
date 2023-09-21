<?php 
function getDbConnection() : mysqli{
  static $conn = null;
  if($conn === null){
    $conn = mysqli_connect("localhost","root","","futurosolareprevede");
    $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, TRUE);
    if (!$conn) die("Connessione fallita: " . mysqli_connect_error());
  }
  return $conn;
}

function isConnected(){
  $connected = fopen("http://www.google.com:80/","r");
  if($connected) return true;
  else return false;
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

class POINT extends COORD{
  public int $point_ID;
  public int $update_time;
  public array $forecast_data;

  function __construct(COORD $c, int $point_ID,array $forecast_data = null, int $update_time = -1){
    parent::__construct($c->lat,$c->lon);
    $this->$point_ID = $point_ID;
    $this->$forecast_data = $forecast_data;
    $this->$update_time = $update_time;
  }

}

/**
 * @return POINT[]
 */
function loadPoints(COORD $startLocation = null,int $N = 50) : array {

  $conn = getDbConnection();
  $res = $conn->query("SELECT * FROM points ORDER BY point_ID");
  $out = [];
  while($row = $res->fetch_assoc()){
    $out[] = new POINT(new COORD($row["latitude"],$row["longitude"]),$row["point_ID"]);
  }

  if($startLocation != null) {
    
  }
  return $out; 
}


//TODO: KILL IT WAS A BAD IDEA JOIN IT WITH loadPoints()
/**
 * @return COORD[]|false 
 */
function getForecast(POINT $first, POINT $second,POINT ...$more): array|false { 
  
  $arrays = array_chunk(array($first,$second,...$more),40,false);

  $responses = [];
  for($i = 0; $i < sizeof($arrays); $i++){
    if(isset($arrays[$i+1])){
      $arrays[$i][] = $arrays[$i+1][0];
    }

    $concatenated_waypoints = '';
    foreach($arrays[$i] as $waypoint){
      $concatenated_waypoints .= strval($waypoint->lon).','.strval($waypoint->lat).';';
    }
    $concatenated_waypoints = substr($concatenated_waypoints,0,-1);
    $requestRoute = 'http://router.project-osrm.org/route/v1/driving/%s?overview=false';
    $req = sprintf(
      $requestRoute,
      $concatenated_waypoints
    );
    
    $responses[] = json_decode(file_get_contents($req));
  }

  return array_column(array_column(array_column($responses,"routes"),0),"legs"); //idk merge these somehow

}

?>