<?php

session_start();

//Include libraries
include_once( __DIR__ . "/functions/includes.php" );

//Create core instance
$core = new ConnectX();
	
//Try to display content
//Catch possible exceptions and show exception page
try {
	//Initialize core
	$core->init();
	
	//Initialize game by passing gameID parameter
	if(isset($core->get['gameID'])){
		$core->setGame($core->get['gameID']);
	}
	
	//Run actions
	$core->executeActions();
	
	//Check if player is logged in
	if($core->request['page'] != 'signup' && (!is_object($core->session->me) || is_null($core->session->me->getID()))){
		$core->request['page'] = 'login';
	}
	
	//Check if request page is specified
	if(!isset($_REQUEST['page'])){
		$core->request['page'] = 'game';
	}
	
	//Check if requested page exists
	if(!is_file( __DIR__ . '/pages/' . $core->request['page'] . '.php' )){
		$core->request['page'] = '404';
	}

	//Include requested page
	include( __DIR__ . '/pages/' . $core->request['page'] . '.php');
	
} catch(Exception $e){
	
	//Include exception page
	include( __DIR__ . '/pages/exception.php' );
	
}