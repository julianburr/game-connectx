<?php

/**
 * ConnectX
 * Player Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Main class for player object
 **/

class Player {
	
	private $id = null;
	private $username = null;
	private $loaded = false;
	
	private $meta = array();
	
	public function __construct($id=null){
		//If id is set, load player from id
		if($id){
			$this->id = $id;
			$this->loadPlayer();
		}
	}
	
	public function loadPlayer(){
		/**
		 * Loads player data from database (incl. meta information)
		 * Saves data in instance as sets $this->loaded to true/false 
		 *   weather the player was successfully loaded or not
		 **/
		$sql = new SqlManager();
		$load = $sql->get("player", "player_id", $this->id);
		if(!$load['player_id']){
			$this->loaded = false;
			throw new Exception("Player '#{$this->id}' not found!");
		} else {
			$this->loaded = true;
			$this->username = $load['player_username'];
			$this->loadMeta();
		}
	}
	
	public function loadMeta(){
		/**
		 * Loads meta data from database
		 **/
		$this->meta = Meta::get("player", $this->id);
	}
	
	public function addMeta($name, $value){
		/**
		 * Adds meta data for current player into database
		 *
		 * Parameters:
		 * name: name of meta attribute
		 * value: value of meta attribute
		 **/
		Meta::add("player", $this->id, $name, $value);
	}
	
	public function updateMeta($name, $value){
		/**
		 * Updates specific meta data for current player in database
		 *
		 * Parameters:
		 * name: name of meta attribute
		 * value: value of meta attribute
		 **/
		Meta::update("player", $this->id, $name, $value);
	}
	
	public function removeMeta($name, $value){
		/**
		 * Removes all meta data with specified name for current player from database
		 *
		 * Parameters:
		 * name: name of meta attribute
		 * value: value of meta attribute
		 **/
		Meta::remove("player", $this->id, $name, $value);
	}
	
	public function getID(){
		/**
		 * Get current players id
		 **/
		return $this->id;
	}
	
	private function createPlayer($id=null){
		/**
		 * Creates new player
		 * 
		 * Parameters:
		 * id: optional id to be set as new players id
		 **/
		$sql = new SqlManager();
		$new = array();
		if(!$id){
			$new['player_id'] = $id;
		}
		$sql->insert("player", $new);
	}
	
	public function getName(){
		/**
		 * Get current players name
		 * Fallback if name is not set: Player#[ID]
		 **/
		if(isset($this->meta['name'][0])){
			return $this->meta['name'][0];
		}
		if(!is_null($this->username)){
			return $this->username;
		}
		return "Player#" . $this->id;
	}
	
	public function getStatus(){
		/**
		 * Get current players online status
		 * Possible values: online/offline/mobile
		 **/
		return "online"; //TODO
	}
	
}