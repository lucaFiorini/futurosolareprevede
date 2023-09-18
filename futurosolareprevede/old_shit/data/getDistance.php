<?php 

$requestRoute = 'http://router.project-osrm.org/route/v1/driving/%s;%s?overview=false';
$A;
$B;


$A['long'] = 130.8444;
$A['lat'] = -12.4637;


$B['long']  =  138.6007;
$B['lat'] = -34.9285;

$req = sprintf(
	$requestRoute,
    strval($A['long']).','.strval($A['lat']),
    strval($B['long']).','.strval($B['lat'])
);

$ch = curl_init($req);
header("content-type: application/json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
$res = curl_exec($ch);
echo($res);

?>
