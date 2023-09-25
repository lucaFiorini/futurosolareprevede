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
  public int|null $update_time;
  public array|null $forecast_data;

  function __construct(COORD $c, int $point_ID,array $forecast_data = null, int $update_time = -1){
    parent::__construct($c->lat,$c->lon);
    $this->point_ID = $point_ID;
    $this->forecast_data = $forecast_data;
    $this->update_time = $update_time;
  }

}

function loadForecast(COORD $startLocation, int $N){

  $points = loadPoints();

  $closest = array("coords" => new COORD(0,0), "distance" => INF, "id" => NULL);
  $second_closest = array("coords" => new COORD(0,0), "distance" => INF, "id" => NULL);

  foreach($points as $id => $point){
    $cur_distance = $startLocation->getDistanceFrom($point["coord"]);
    if($cur_distance < $closest["distance"]){
      $second_closest = $closest;
      $closest = array("coords" => $startLocation, "distance" => $cur_distance,"id" => $id);
    } else if($cur_distance < $startLocation["distance"]) {
      $second_closest = array("coords" => $startLocation, "distance" => $cur_distance,"id" => $id);
    }
  }
  
  $first_point = ($closest["id"] > $second_closest["id"]) ? $closest : $second_closest;
  $waypoints = array_slice($points,$first_point["id"],30,false);
  
  $data = [];

  return $data;
}

/**
 * @return POINT[]
 */
function loadPoints() : array {
  $conn = getDbConnection();
  $res = $conn->query("SELECT * FROM points ORDER BY point_ID");
  $out = [];
  while($row = $res->fetch_assoc()){
    $out[] = new POINT(new COORD($row["latitude"],$row["longitude"]),$row["point_ID"]);
  }
  return $out;
}

/**
 * @return POINT[]|false 
 */
function loadOSRMdata(POINT $first, POINT $second,POINT ...$more): array|false { 
  
  $N = 40;
  $points = array($first,$second,...$more);
  $arrays = array_chunk($points,$N,false);

  $data = [];
  for($i = 0; $i < sizeof($arrays); $i++){
    $concatenated_waypoints = '';
    foreach($arrays[$i] as $waypoint){
      $concatenated_waypoints .= strval($waypoint->lon).','.strval($waypoint->lat).';';
    }
    if(isset($arrays[$i+1])){ //add extra point so route can be calculated
      $concatenated_waypoints .=strval($arrays[$i+1][0]->lon).','.strval($arrays[$i+1][0]->lat).';';
    }

    $concatenated_waypoints = substr($concatenated_waypoints,0,-1);

    $requestRoute = 'http://router.project-osrm.org/route/v1/driving/%s?overview=false';
    $req = sprintf(
      $requestRoute,
      $concatenated_waypoints
    );
    
    $res = json_decode(file_get_contents($req),true);
    $OSRMlegs = array_column($res["routes"],"legs")[0];
    $OSRMwaypoints = array_column($res["waypoints"],"location");
    for($j = 0; $j < sizeof($OSRMlegs); $j++){
      $data[] = array(
        "location" => $points[($i*40)+$j],
        "distance_to_next" => $OSRMlegs[$j]["distance"],
        "time_to_next" => $OSRMlegs[$j]["duration"]
      );
    }
  }

  $data[] = array(
    "location" => end($more),
    "distance_to_next" => NULL,
    "time_to_next" => NULL
  );

  return $data; //idk merge these somehow

}
?>