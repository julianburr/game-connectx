<?php

/**
 * ConnectX
 * Session Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Handles game sessions
 *		Uses DB and PHP Session Vars (e.g. session_id())
 *		session_start is therefore essential and necessary!
 **/

class Session {
	
	private $id = null;
	private $key = null;
	
	public $me = null;
	
	public $player = null;
	public $meta = array();
	
	private $salt = "";
	
	public function __construct(){
		/**
		 * Get session id and load session
		 **/
		$this->key = session_id();
		$this->loadSession();
	}
	
	public function saveSession(){
		/**
		 * Saves current session to database
		 **/
		$sql = new SqlManager();
		$sql->update("session", array( "session_id" => $this->id, "session_key" => $this->key, "session_player" => $this->player ));
	}
	
	public function setPlayerID($id){
		/**
		 * Assign player to current session
		 * Creates player instance for $this->me
		 **/
		$this->player = $id;
		$this->me = new Player( $this->player );
		$this->saveSession();
	}
	
	public function getPlayerID(){
		/**
		 * Get id of assigned player
		 **/
		return $this->player;
	}
	
	public function loadSession(){
		/**
		 * Loads session from database
		 **/
		$sql = new SqlManager();
		$load = $sql->get("session", "session_key", $this->key);
		if(!$load['session_id']){
			$this->newSession();
		} else {
			$this->id = $load['session_id'];
			$this->key = $load['session_key'];
		}
		if($load['session_player'] > 0){
			$this->setPlayerID( $load['session_player'] );
			$this->saveSession();
		}
		$this->loadMeta();
	}
	
	public function newSession(){
		/**
		 * Creates new Session and saves it in database
		 **/
		$sql = new SqlManager();
		$sql->insert("session", array( 
			"session_key" => $this->key, 
			"session_player" => $this->player 
		));
		$this->id = $sql->getLastInsertID();
		$sql->insert("meta", array( 
			"meta_table" => "session", 
			"meta_table_id" => $this->id, 
			"meta_name" => "start", 
			"meta_value" => date("Y-m-d H:i:s")
		));
	}
	
	public function loadMeta(){
		/**
		 * Load session meta information
		 **/
		$this->meta = Meta::get("session", $this->id);
	}
	
	public function addMeta($name, $value){
		/**
		 * Add session meta information
		 *
		 * Parameters:
		 * name: meta attribute name
		 * value: meta attributes value
		 **/
		Meta::add("session", $this->id, $name, $value);
	}
	
	public function updateMeta($name, $value){
		/**
		 * Update session meta information
		 *
		 * Parameters:
		 * name: meta attribute name
		 * value: meta attributes value
		 **/
		Meta::update("session", $this->id, $name, $value);
	}
	
	public function removeMeta($name, $value){
		/**
		 * Delete session meta information
		 *
		 * Parameters:
		 * name: meta attribute name
		 * value: meta attributes value
		 **/
		Meta::remove("session", $this->id, $name, $value);
	}
	
	public function loginPlayer($username, $password){
		/**
		 * Logs player into session (if username and password provided are correct!)
		 **/
		$return = array("messages");
		if(!$username || !$password){
			$return['messages'][] = array("type" => "error", "content" => "Well, you have to enter both unsername and password, you know!");
			return $return;
		}
		$sql = new SqlManager();
		$sql->setQuery("
			SELECT player_id FROM player
			WHERE player_username = '{{username}}'
			AND player_password = '{{password}}'
			");
		$sql->bindParam("{{username}}", $username);
		$sql->bindParam("{{password}}", md5($password . $this->salt));
		$player = $sql->result();
		if($player['player_id']){
			$this->setPlayerID($player['player_id']);
			return;
		}
		$return['messages'][] = array("type" => "error", "content" => "Nope! Have you forgotten your password, laddy?!");
		return $return;
	}
	
	public function logoutPlayer(){
		/**
		 * Regenerates session id to log player out
		 **/
		session_regenerate_id();
		$this->session = new Session();
		return;
	}
	
	public function newPlayer($username, $password, array $options){
		$sql = new SqlManager();
		$sql->setQuery("
			SELECT * FROM player
			WHERE player_username = '{{username}}'
			");
		$sql->bindParam("{{username}}", $username);
		$test = $sql->result();
		if($test['player_id']){
			$return['messages'][] = array("type" => "error", "content" => "Username already taken! Try to be a bit more creative!");
			return $return;
		}
		$new = array("player_username" => $username, "player_password" => md5($password . $this->salt));
		$sql->insert("player", $new);
		$this->setPlayerID($sql->getLastInsertID());
	}
		
}