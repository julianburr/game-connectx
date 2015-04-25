<?php

/**
 * ConnectX
 * Core Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Core Game Engine Class
 *		Handles requests, actions and objects to be called inside the output files
 **/

class ConnectX {
	
	public $session = null;
	public $game = null;
	
	public $request = null;
	public $get = null;
	public $post = null;
	
	public $htaccess = false;
	
	private $action_response = array();
	
	public function __construct(){
		/**
		 * Set request parameters and check htaccess file
		 **/
		$this->request = $_REQUEST;
		$this->get = $_GET;
		$this->post = $_POST;
		$this->checkHtaccess();
	}
	
	public function init($game_id=null){
		/**
		 * Inits session and game
		 **/
		$this->session = new Session();
		$this->game = new Game($game_id);
	}
	
	public function getCanonicalURL(){
		/**
		 * Returns canonical url for current page
		 **/
		$url = $this->getProtocol() . "://" . $this->getDomain() . "/";
		if($this->htaccess){
			if(isset($this->request['gameID'])){
				$url .= "game/{$this->request['gameID']}";
			} elseif(isset($this->get['page'])){
				$url .= $this->get['page'];
			}
		} else {
			$url .= $this->getRootDir() . "/core.php";
			$sep = "?";
			if(isset($this->get['gameID'])){
				$url .= $sep . "page=game&gameID=" . $this->request['gameID'];
				$sep = "&";
			} elseif(isset($this->get['page'])){
				$url .= $sep . "page=" . $this->get['page'];
			}
		}
		return $url;
	}
	
	public function checkHtaccess(){
		/**
		 * Checks if .htaccess is set up and if game is in root dir
		 * Sets $this->htaccess (boolean)
		 * Needed for canonical URL generator
		 **/
		$this->htaccess = false;
		if(realpath(dirname(__DIR__ . "/../core.php")) == realpath($_SERVER['DOCUMENT_ROOT']) && is_file(__DIR__ . "/../.htaccess")){
			$this->htaccess = true;
		}
	}
	
	public function getBaseURL(){
		/**
		 * Returns current URL base to be used e.g. for links or css refs
		 **/
		if($this->htaccess){
			return "/";
		}
		return "";
	}
	
	public function getRootDir(){
		/**
		 * Determines root dir of core file
		 **/
		return str_replace(realpath($_SERVER['DOCUMENT_ROOT']), "", realpath(dirname(__DIR__ . "/../core.php")));
	}
	
	public function getProtocol(){
		/**
		 * Returns protocol of current http request
		 **/
		return "http"; //TODO
	}
	
	public function getDomain(){
		/**
		 * Determines domain basis of current request
		 **/
		return $_SERVER['SERVER_NAME'];
	}
	
	public function setGame($id){
		/**
		 * Assigns game to core instance
		 **/
		$this->game = new Game($id);
	}
	
	public function loadGame($id){
		/**
		 * Initializes instance of game
		 *
		 * Parameters:
		 * id: id of the game to be loaded
		 *
		 * Returns:
		 * game: game Object
		 **/
		$this->game = new Game($id);
		return $this->game;
	}
	
	public function executeActions(){
		/**
		 * Execute actions
		 * Action names will be fetched from HTTP request parameter do
		 * Multiple actions can be called using an array: do[]
		 * TODO: check allowed actions
		 **/
		if(!isset($this->request['do'])) return;
		
		if(!is_array($this->request['do'])){
			$this->request['do'] = array($this->request['do']);
		}
		
		foreach($this->request['do'] as $action){
			$this->action_response = array();
			$method = "action_" . $action;
			if(method_exists($this, $method)){
				$this->action_response[$action] = $this->$method();
				$player_id = null;
				$game_id = null;
				if(is_object($this->session->me)) $player_id = $this->session->me->getID();
				if(is_object($this->game)) $game_id = $this->game->getID();
				$sql = new SqlManager();
				$new = array( "action_date" => date("Y-m-d H:i:s", time()), "action_name" => $action, "action_game" => $game_id, "action_player" => $player_id );
				$sql->insert("action", $new);
			}
		}
	}
	
	public function getActionResponse($action=null){
		/**
		 * Returns saved action response array
		 * If action is specified, return just specified actions response!
		 **/
		 if($action){
			 if(isset($this->action_response[$action])){
			 	return $this->action_response[$action];
			 }
			 return;
		 }
		 return $this->action_response;
	}
	
	public function isMe(Player $player){
		/**
		 * Check if player is equal to current sessions player
		 *
		 * Parameters
		 * player: player object
		 **/
		if($this->session->getPlayerID() == $player->getID()){
			return true;
		}
		return false;
	}
	
	public function getGames(){
		/**
		 * Return Game Objetcs of all games in the database
		 **/
		$sql = new SqlManager();
		$sql->setQuery("SELECT game_id FROM game WHERE game_status != 'finished'");
		$sql->execute();
		
		$games = array();
		while($row = $sql->fetch()){
			$games[] = new Game($row['game_id']);
		}
		
		return $games;
	}
	
	/*=======================================================================*/
	/* In the following come the actions that can be called via HTTP request */
	/*=======================================================================*/
	
	private function action_debug(){
		var_dump($this);
	}
	
	private function action_enterGame(){
		return $this->game->addPlayer($this->session->me);
	}
	
	private function action_leaveGame(){
		return $this->game->removePlayer($this->session->me);
	}
	
	private function action_startGame(){
		return $this->game->start();
	}
	
	private function action_stopGame(){
		return $this->game->stop();
	}
	
	private function action_setStone(){
		return $this->game->setField($this->request['row'], $this->session->me->getID());
	}
	
	private function action_playerLogin(){
		return $this->session->loginPlayer($this->post['username'], $this->post['password']);
	}
	
	private function action_killMe(){
		$this->action_playerLogout();
	}
	
	private function action_playerLogout(){
		return $this->session->logoutPlayer();
	}
	
	private function action_playerSignUp(){
		return $this->session->newPlayer($this->post['username'], $this->post['password'], $this->post['options']);
	}
	
	private function action_createGame(){
		return $this->game->create(array());
	}
}