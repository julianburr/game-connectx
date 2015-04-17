<?php

/**
 * ConnectX
 * Action Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Handles actions and supplies util functions
 **/

class Action {
	
	private $id = null;
	private $date = null;
	private $ts = null;
	private $name = null;
	
	private $player = null;
	private $game = null;
	
	public function __construct($id){
		/**
		 * Loads specified action from database
		 * Creates Player instance for loaded player id
		 * 
		 * Parameters:
		 * id: action id to be loaded
		 **/
		$sql = new SqlManager();
		$action = $sql->get("action", "action_id", $id);
		if($action['action_id'] != $id){
			throw new Exception("Action '#{$id}' not found!");
		}
		$this->id = $action['action_id'];
		$this->date = $action['action_date'];
		$this->ts = strtotime($action['action_date']);
		$this->name = $action['action_name'];
		$this->player = new Player($action['action_player']);
		$this->game = $action['action_game'];
	}
	
	public function getID(){
		/**
		 * Get instances action id
		 **/
		return $this->id;
	}
	
	public function getPlayer(){
		/**
		 * Get instances player object
		 **/
		return $this->player;
	}
	
	public function getPlayerID(){
		/**
		 * Get instances player id
		 **/
		return $this->player->getID();
	}	
	
	public function getTimeStamp(){
		/**
		 * Get timestamp of action
		 **/
		return $this->ts;
	}
	
	public function getDate(){
		/**
		 * Get datetime of action
		 **/
		return $this->date;
	}
	
	public function getName(){
		/**
		 * Get action's name
		 **/
		return $this->name;
	}
	
}