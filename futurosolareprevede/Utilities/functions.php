<?php 
require_once(__DIR__.'/vendor/autoload.php');
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

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
 * @return POINT[]|COORD[]|false 
 */
function loadOSRMdata(POINT|COORD $first, POINT|COORD $second,POINT|COORD ...$more): array|false { 
  
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

  return $data;
}

//TODO: load data from https://open-meteo.com/en/docs#start_date=2023-09-25&end_date=2023-09-25
function loadOpenMeteoData(POINT $c, $timestamp) : array{

  $conn = getDbConnection();
  
  //1: load data for hour before and after $timestamp from database
  //2: weigh data depending on current minute 
  //   Weigh output so that the two hourly readings around the exact time are taken into consideration.
  //   The output should be determined by 3/4 by the 15:00 reading and by 1/4 by the 16:00 reading.
  //4: update database with latest info
  //3: return output in standards associative array to be passed down to js.
  return array();
}

function updateOpenMeteoData(){
  
  if(!isConnected()) return false;

  $conn = getDbConnection();
  $client = new GuzzleHttp\Client();

  $points = loadPoints();

  $conn->query("DELETE FROM forecast");
  $promises = [];
  foreach($points as $point){;
    $reqURL = "https://api.open-meteo.com/v1/forecast?latitude=%f&longitude=%f&hourly=temperature_2m,direct_radiation&timeformat=unixtime&forecast_days=1";
    $reqURL = sprintf($reqURL,
      $point->lat,
      $point->lon
    );
    //$reqURL = "http://localhost/futurosolareprevede/futurosolareprevede";
    $promises[] = new Request('GET',$reqURL);
  }

  $pool = new POOL($client,$promises,[
    'concurrency' => 50,
    'fulfilled' => function (Response $response, $index) use ($conn) {
      $data = json_decode((string)$response->getBody(),true);
      for($i = 0; $i < sizeof($data["time"]); $i++){

        $query = "INSERT INTO forecast(point_id,referenced_time,direct_radiation,temperature_2m) VALUES (%d,%d,%f,%f)";
        $query = sprintf($query,
          $index,
          $data["time"][$i],
          $data["direct_radiation"][$i],
          $data["temperature_2m"][$i]
        );
        $conn->query($query);

      }
    },
    'rejected' => function (RequestException $reason, $index) {
      echo "WTF m8";
    },
  ]);

  $promise = $pool->promise();
  $promise->wait();
  return true;
}

/*
    $promise->then(function(ResponseInterface $res) use ($point,$conn) {
      echo"wow";
      $data = json_decode($res->getBody())["hourly"];
      for($i = 0; $i < sizeof($data["time"]); $i++){

        $query = "INSERT INTO forecast(point_id,referenced_time,direct_radiation,temperature_2m) VALUES (%d,%d,%f,%f)";
        $query = sprintf($query,
          $point->point_ID,
          $data["time"][$i],
          $data["direct_radiation"][$i],
          $data["temperature_2m"][$i]
        );
        $conn->query($query);

      }
    });
     */
?>