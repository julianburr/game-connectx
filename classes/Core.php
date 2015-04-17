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
	
	private $request = null;
	private $get = null;
	private $post = null;
	
	public function __construct($game_id){
		/**
		 * Inits session, request attributes and game if id is given
		 **/
		$this->session = new Session();
		$this->request = $_REQUEST;
		$this->get = $_GET;
		$this->post = $_POST;
		if($game_id){
			$this->game = new Game($game_id);	
		}
		$this->executeActions();
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
	
	private function executeActions(){
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
			$action = "action_" . $action;
			if(method_exists($this, $action)){
				$this->$action();
				if($this->session->me->getID()){
					$sql = new SqlManager();
					$new = array( "action_date" => date("Y-m-d H:i:s", time()), "action_name" => $action, "action_game" => $this->game->getID(), "action_player" => $this->session->me->getID() );
					$sql->insert("action", $new);
				}
			}
		}
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
	
	/*=======================================================================*/
	/* In the following come the actions that can be called via HTTP request */
	/*=======================================================================*/
	
	private function action_debug(){
		var_dump($this);
	}
	
	private function action_enterGame(){
		$this->game->addPlayer($this->session->me);
	}
	
	private function action_leaveGame(){
		$this->game->removePlayer($this->session->me);
	}
	
	private function action_startGame(){
		$this->game->start();
	}
	
	private function action_stopGame(){
		$this->game->stop();
	}
	
	private function action_setStone(){
		$this->game->setField($this->request['row'], $this->session->me->getID());
	}
	
}