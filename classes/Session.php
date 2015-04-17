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
			$this->player = new Player( $load['session_player'] );
		}
		if(isset($_REQUEST['playerID'])){
			$this->setPlayerID( $_REQUEST['playerID'] );
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
		
}