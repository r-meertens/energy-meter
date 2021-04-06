<?php

header("Access-Control-Allow-Origin: *");

session_start();
if(!isset($_SESSION["username"])) {
   	header('HTTP/1.0 401 Unauthorized');
	die();
}

error_reporting(E_ALL);
require_once("../settings.php");
require_once("../../vendor/autoload.php");

//Raspberry PI sysinfo
$result = array();
$result['app_version'] = "0.1";
$result['os_version'] = shell_exec("uname -a | awk '{print $1 \" \" $3}'"); 
//$result['uptime'] = shell_exec("cat /proc/uptime | awk '{print $1}'");
$result['uptime'] = shell_exec("uptime -p");
$result['mem_free'] = shell_exec("free|grep Mem: | awk '{ print $7 }'");
$result['system_load'] = shell_exec("cat /proc/loadavg | awk '{ print $1 \" \" $2 \" \" $3}'");
$result['disk_free'] = shell_exec("df | grep /dev/root | awk '{ print $4}'");
$result['temp'] = round(intval(shell_exec("cat /sys/class/thermal/thermal_zone0/temp"))/1000,1);
$result['time'] = shell_exec("date +%F\ %T");

echo json_encode($result);

