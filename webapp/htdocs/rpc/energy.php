<?php

header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);
require_once("../settings.php");
require_once("../../vendor/autoload.php");

//Session check
//..

//Input
$function = $_GET['function'];
$input = json_decode(file_get_contents('php://input'));
if(isset($function)) {
	$parameters = isset($input->parameters) ? $input->parameters : null;
	call_user_func($function, $parameters);
}
else {
	echo "Missing function";
}

function query($sql)
{
	global $db_host,$db_port;

	$client = new InfluxDB\Client($db_host, $db_port);
	$database = $client->selectDB('energy');

	$result = $database->query($sql);
	if($result)
	{
		return $result->getPoints();
	}
	return null;
}

function getNextTime($utc)
{
	$utc=intval($utc);

	$sql = "select time,* from \"Meter values\" where \"time\" > ".$utc."s limit 1" ;

	$points = query($sql);

	if(!empty($points)) {
		return $points[0]['time'];
	}
	return false;
}

function getPreviousTime($utc)
{
	global $database;

	$utc=intval($utc);

	$sql = "select time,* from \"Meter values\" where \"time\" < ".$utc."s order by desc limit 1" ;

	$points = query($sql);

	if(!empty($points)) {
		return $points[0]['time'];
	}
	return false;
}


function getState($parameters)
{
	$timestamp = isset($parameters->timestamp) ? $parameters->timestamp : null;
	$sql = null;
	$points = null;

	if($timestamp) {
		$time = getNextTime($timestamp);
		if($time) {
			$sql = "select * from \"Meter values\" where time = '".$time."' tz('Europe/Amsterdam')";
		}
	}
	else {
		//get last state
		$sql = "select last(\"Frequency\"),* from \"Meter values\" group by * tz('Europe/Amsterdam')";
	}

	if($sql) {
		$points = query($sql);
	}

	$output=array();
	if(!empty($points)) {
		foreach($points as $key=>$values) {
			$output[$key] = array();
			$output[$key]['id'] = $values['Meter id'];
			$output[$key]['time'] = $values['time'];
			$output[$key]['frequency'] = $values['Frequency'];
			$output[$key]['voltage'] = $values['V1'];
			$output[$key]['current'] = $values['I1'];
			$output[$key]['kwh'] = $values['TA'];
		}
	}
	echo "{ \"meters\" : ". json_encode($output). " }";
}

function getUsageByDatetimeRange($parameters)
{
	$meter_id = isset($parameters->meter_id) ? intval($parameters->meter_id) : null;
	$start_timestamp = isset($parameters->start_timestamp) ? intval($parameters->start_timestamp) : null;
	$end_timestamp = isset($parameters->end_timestamp) ? intval($parameters->end_timestamp) : null;

	$output=array();
	if(isset($meter_id) && isset($start_timestamp) && isset($end_timestamp)) {

		$start_time = getNextTime($start_timestamp);
		$end_time = getPreviousTime($end_timestamp);

		if($start_time && $end_time) {
			$sql = "select min(TA) as begin_ta,max(TA) as end_ta from \"Meter values\" WHERE \"Meter id\" = '".$meter_id."' AND time >= '".$start_time."' AND time <= '".$end_time."' group by time(1d) tz('Europe/Amsterdam')";
			$points = query($sql);
	
			if(!empty($points)) {
				foreach($points as $key=>$values) {
					$output[$key] = array();
					$output[$key]['time'] = $values['time'];
					$output[$key]['begin_kwh'] = $values['begin_ta'];
					$output[$key]['end_kwh'] = $values['end_ta'];
				}
			}
		}
	}
	echo "{ \"usage_per_day\" : ". json_encode($output). " }";
}


function getSettings($parameters)
{
	global $meter_count, $free_kwh_per_day, $price_per_kwh;


	echo "{ \"settings\" : { 
		\"meter_count\" : $meter_count,
		\"free_kwh_per_day\" : $free_kwh_per_day,
		\"price_per_kwh\" : $price_per_kwh
	} }";

}
