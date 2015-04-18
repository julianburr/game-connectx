<?php

/**
 * Function module to call class methods via ajax
 *
 * Needs the following URL parameters:
 * 		class		= name of the class that should be called
 *		class_id	= optional ID parameter that will be passed to the class
 *		method		= name of the method that should be called
 *
 * Returns a json ovject with the methods return
 * If the method returns an array, this will be converted into the response json object
 **/

session_start();

//Include all needed class files
include_once( __DIR__ . "/includes.php" );

//Check if class name is given and if class exists
if(!isset($_REQUEST['class']) || !class_exists($_REQUEST['class'])){
	echo "{\n\t\"error\" : \"InvalidClassName\"\n}";
	exit;
}

//Check optional id parameter
if(!isset($_REQUEST['classID'])){
	$_REQUEST['classID'] = null;
}

//Catch possible Exceptions
try {
	
	//Create instance of requestes class
	$instance = new $_REQUEST['class']($_REQUEST['classID']);
	
	//Check method parameter and if method exists in called class
	if(!isset($_REQUEST['method']) || !method_exists($instance, $_REQUEST['method'])){
		echo "{\n\t\"error\" : \"InvalidMethodName\"\n}";
		exit;	
	}

	//Call method and save response
	$response = $instance->$_REQUEST['method']();
	
} catch(Exception $e){
	
	//Return caught Exception message as error
	echo "{\n\t\"error\" : \"CaughtExeption\"\n\t\"message\" : \"{$e->getMessage()}\"\n}";
	exit;
	
}

//If response is normal value, write it into array for JSON conversion
if(!is_array($response)){
	$response = array("response" => $response);
}

//Write JSON from array
echo "{\n";
foreach($response as $key => $value){
	echo "\t\"{$key}\" : \"{$value}\"\n";
}
echo "}";