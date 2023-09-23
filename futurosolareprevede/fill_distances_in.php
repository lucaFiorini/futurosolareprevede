<?php
include_once("Utilities/functions.php");
echo "<pre>";
$data = loadPoints();
$forecast = getForecast(...$data);
echo("<pre>");
var_dump($forecast);
?>