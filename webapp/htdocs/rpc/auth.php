<?php

header("Access-Control-Allow-Origin: *");

require_once("../settings.php");

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

function login($parameters)
{
	global $username,$password;
	if($parameters->username == $username && $parameters->password == $password) {
		session_start();
		$_SESSION["username"] = $parameters->username;
		echo "{ \"authentication\" : \"success\" }";
	}
	else {
		echo "{ \"authentication\" : \"error\" }";
	}
}

function logout()
{
	session_start();
	unset($_SESSION["username"]);
	session_destroy();
	echo "{ \"authentication\" : \"terminated\" }";
}
