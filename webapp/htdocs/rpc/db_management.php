<?php
die();
require_once("../../vendor/autoload.php");

$host = "localhost";
$port = "8086";

$client = new InfluxDB\Client($host, $port);
$database = $client->selectDB('energy');

//deleteOlderThan();

function deleteOlderThan()
{
	global $database;
	$query = "delete from \"Meter values\" where time < now() - 500d";
	$result = $database->query($query);
	var_dump($result);

}
