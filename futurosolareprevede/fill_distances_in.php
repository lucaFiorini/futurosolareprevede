<?php
include_once("Utilities/functions.php");
$data = loadPoints();
$forecast = getForecast(...$data);
echo("<pre>");
var_dump($forecast);
?>