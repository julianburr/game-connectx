<?php

/**
 * ConnectX
 * Field Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Main class for field object
 *		Used in Game's fieldset array
 **/

class Field {
	
	private $player = null;
	
	public function __construct($player=null){
		//If player is given, set player
		$this->setPlayer($player);
	}
	
	public function setPlayer($player){
		/**
		 * Assigns player to field
		 * 
		 * Parameters:
		 * player: player id (to create a new instance of Player class)
		 **/
		$this->player = new Player( $player );
	}
	
	public function getPlayer(){
		/**
		 * Get assigned player object
		 **/
		return $this->player;
	}
	
	public function getPlayerID(){
		/**
		 * Get id of assigned player
		 **/
		if(!is_object($this->player)){
			throw new Exception("Ups ... It's not an object!");
		}
		return $this->player->getID();
	}
	
}