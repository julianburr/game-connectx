<?php

/**
 * ConnectX
 * Game Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Handles game actions, objects and rules
 **/

class Game {
	
	private $id = null;
	
	private $status = "imaginative";
	private $options = array();
	
	private $players = array();
	private $fieldset = array();
	
	private $current_player_key = null;
	
	private $winner_found = false;
	private $winner_player = null;
	private $winner_fields = array();
	
	private $last_action = null;
	
	public function __construct($id){
		/**
		 * Loads game if id is given
		 **/
		if($id){
			$this->id = $id;
			$this->load();
		}
	}
	
	public function setID($id){
		/**
		 * Loads game by given id
		 *
		 * Parameters:
		 * id: id of game
		 **/
		$this->id = $id;
		$this->load();
	}
	
	public function getID(){
		/**
		 * Get id of current game instance
		 **/
		return $this->id;
	}
	
	public function create(array $options){
		/**
		 * Create a new game
		 *
		 * Parameters:
		 * options: array with game options (meta information)
		 **/
		$sql = new SqlManager();
		$new = array( "game_status" => "waiting" );
		$sql->insert("game", $new);
		$this->id = $sql->getLastInsertID();
		$this->addOptions($options);
	}
	
	public function load(){
		/**
		 * Loads game from database
		 * Loads players in array and checks for winner
		 **/
		$sql = new SqlManager();
		$load = $sql->get("game", "game_id", $this->id);
		if($load['game_id'] != $this->id){
			throw new Exception("Game '#{$this->id}' not found!");
		}
		$this->status = $load['game_status'];
		$this->fieldset = unserialize($load['game_fieldset']);
		$this->current_player_key = $load['game_current_player'];
		$this->loadOptions();
		$this->loadPlayers();
		$this->checkWinner();
	}
	
	public function save(){
		/**
		 * Saves current game
		 **/
		if(!$this->id){
			$this->create();
		}
		$sql = new SqlManager();
		$update = array( "game_id" => $this->id, "game_status" => $this->status, "game_fieldset" => serialize($this->fieldset), "game_current_player" => $this->current_player_key );
		$sql->update("game", $update);
	}
	
	public function start(){
		/**
		 * Start game
		 * Only possible if 2 or more players joined
		 **/
		if($this->getPlayerCount() > 1){
			$this->setStatus("running");
			$this->init();
			$this->getNextPlayer();
			$this->save();
		} else {
			throw new Exception("Cannot start game before at least 2 players entered");
		}
	}
	
	public function stop(){
		/**
		 * Stop current game
		 **/
		$this->setStatus("stopped");
		$this->init();
		$this->save();
	}
	
	private function loadOptions(){
		/**
		 * Load game options
		 **/
		$this->options = Meta::get("game", $this->id);
	}
	
	private function getOption($name){
		/**
		 * Get specific game option
		 *
		 * Parameters:
		 * name: meta attribute name
		 **/
		return $this->options[$name];
	}
	
	public function addOptions(array $options){
		/**
		 * Add game options
		 *
		 * Parameters:
		 * options: array with options to be added
		 **/
		foreach($options as $key => $value){
			$this->addOption($key, $value);
		}
	}
	
	public function addOption($name, $value){
		/**
		 * Add game option
		 *
		 * Parameters:
		 * name: meta attribute name
		 * value: meta attribues value
		 **/
		Meta::add("game", $this->id, $name, $value);
	}
	
	public function updateOptions(array $options){
		/**
		 * Update game options
		 *
		 * Parameters:
		 * options: array of options to be updated
		 **/
		foreach($options as $key => $value){
			$this->updateOption($key, $value);
		}
	}
	
	public function updateOption($name, $value){
		/**
		 * Update game option
		 *
		 * Parameters:
		 * name: meta attribute name
		 * value: meta attribues value
		 **/
		Meta::update("game", $this->id, $name, $value);
	}
	
	public function removeOptions(array $options){
		/**
		 * Delete game options
		 *
		 * Parameters:
		 * options: array of options to be removed
		 **/
		foreach($options as $key => $value){
			$this->removeOption($key, $value);
		}
	}
	
	public function removeOption($name, $value){
		/**
		 * Delete game option
		 *
		 * Parameters:
		 * name: meta attribute name
		 * value: meta attribues value
		 **/
		Meta::remove("game", $this->id, $name, $value);
	}
	
	private function loadPlayers(){
		/**
		 * Load players, create player instances and save them in array
		 **/
		$sql = new SqlManager();
		$sql->setQuery("
			SELECT * FROM player2game
			WHERE game_id = {{id}}
			ORDER BY entered
			");
		$sql->bindParam("{{id}}", $this->id, "int");
		$sql->execute();
		$this->players = array();
		while($row = $sql->fetch()){
			$this->players[] = new Player( $row['player_id'] );
		}
		return true;
	}
	
	public function getPlayers(){
		/**
		 * Get current player array
		 **/
		return $this->players;
	}
	
	public function getPlayerIDs(){
		/**
		 * Get array with current player ids
		 **/
		$ids = array();
		foreach($this->players as $player){
			$ids[] = $player->getID();
		}
		return $ids;
	}
	
	public function getStatus(){
		/**
		 * Get current game status
		 **/
		return $this->status;
	}
	
	public function setStatus($status){
		/**
		 * Set game status
		 * Does not save game automatically!
		 **/
		$this->status = $status;
	}
	
	public function isRunning(){
		/**
		 * Check if game is running or not
		 **/
		$bool = false;
		if($this->getStatus() == "running"){
			return true;
		}
	}
	
	public function addPlayer(Player $player){
		/**
		 * Add player to game
		 *
		 * Parameters:
		 * player: player object to be added
		 **/
		if($this->status != "waiting"){
			throw new Exception("Cannot add player in status '{$this->status}'");
		}
		if($player->getID() && !in_array($player->getID(), $this->getPlayerIDs())){
			$sql = new SqlManager();
			$sql->insert("player2game", array( "player_id" => $player->getID(), "game_id" => $this->id, "entered" => date("Y-m-d H:i:s", time()) ));
			$this->players[] = new Player( $player->getID() );
		}
	}
	
	public function removePlayer(Player $player){
		/**
		 * Remove player from game
		 * Delete player from database and reload player array
		 *
		 * Parameters:
		 * player: player object to be removed from game
		 **/
		if($player->getID() && in_array($player->getID(), $this->getPlayerIDs())){
			$sql = new SqlManager();
			$sql->setQuery("
				DELETE FROM player2game
				WHERE player_id = {{player}}
				AND game_id = {{game}}");
			$sql->bindParam("{{player}}", $player->getID(), "int");
			$sql->bindParam("{{game}}", $this->id, "int");
			$sql->execute();
			$this->loadPlayers();
		}
	}
	
	public function getPlayerCount(){
		/**
		 * Get number of players currently in the game
		 **/
		return count($this->players);
	}
	
	public function init(){
		/**
		 * Init game by creating field set and checking for winner
		 **/
		$this->createFieldSet();
		$this->getWinner();
	}
	
	public function createFieldSet(){
		/**
		 * Creates field set (2 dimensional array of field objects)
		 **/
		$width = 7;
		$height = 6;
		if(isset($this->options['fieldsize_x'][0])){
			$width = $this->options['fieldsize_x'][0];
		}
		if(isset($this->options['fieldsize_y'][0])){
			$height = $this->options['fieldsize_y'][0]; 
		}
		$this->fieldset = array();
		for($x=0; $x<$width; $x++){
			for($y=0; $y<$height; $y++){
				$this->fieldset[$x][$y] = new Field(null);
			}
		}
	}
	
	public function getFieldSet(){
		/**
		 * Get current field set array
		 **/
		return $this->fieldset;	
	}
	
	public function setField($row, Player $player){
		/**
		 * Assigns player to field
		 * Also checks its the players turn or not
		 * 
		 * Parameters:
		 * row: x coordinate in which the player should be added 
		 *		(y coordinate will be determined automatically)
		 * player: player object to be assigned to field
		 **/
		$stone_set = false;
		if($player != $this->players[$this->current_player_key]->getID()){
			throw new Exception("Whoa, wait ... it's not your turn!");
		}
		for($i=0; $i<count($this->fieldset[$row]); $i++){
			if(!$this->fieldset[$row][$i]->getPlayerID()){
				$this->fieldset[$row][$i]->setPlayer($player);
				$stone_set = true;
				$this->checkWinner();
				$this->getNextPlayer();
				$this->save();
				break;
			}
		}
		if(!$stone_set){
			throw new Exception("Stone could not be set in row '#{$row}'!");
		}
	}
	
	public function getCurrentPlayer(){
		/**
		 * Get player object of current player (the one whose turn it is)
		 **/
		return $this->players[$this->current_player_key];
	}
	
	public function getCurrentPlayerID(){
		/**
		 * Get id of current player
		 **/
		return $this->players[$this->current_player_key]->getID();
	}
	
	public function getNextPlayer(){
		/**
		 * Determine whose turn it is next
		 **/
		if(is_null($this->current_player_key)){
			$this->current_player_key = 0;
		} else {
			$this->current_player_key++;
			if(!isset($this->players[$this->current_player_key])){
				$this->current_player_key = 0;
			}
		}
		return $this->current_player_key;
	}
	
	public function checkWinner(){
		/**
		 * Check for possible winners
		 * Go through the fieldset and check for connected stones!
		 **/
		$this->winner_player = new Player( null );
		
		$this->checkColumns(); //vertical
		$this->checkRows(); //horizontal
		$this->checkDiagonal(); //diagonal
		
		if($this->winner_found && $this->status != "won"){
			$sql = new SqlManager();
			$new = array( "winner_game" => $this->id, "winner_player" => $this->winner_player->getID(), "winner_fieldset" => serialize($this->fieldset), "winner_winning_fields" => serialize($this->winner_fields) );
			$sql->insert("winner", $new);
			$this->setStatus("won");
			$this->save();
		}
		
		return $this->winner_found;
	}
	
	public function checkColumns(){
		/**
		 * Checks all columns for winner
		 **/
		foreach($this->fieldset as $x => $yset){
			$this->checkColumn($x);
		}
	}
	
	public function checkColumn($column){
		/**
		 * Checks specified column for winner
		 **/
		if($this->winner_found){
			return $this->winner_player->getID();
		}
		$connect_cnt = 0;
		$connect_fields = array();
		$last_player = new Player(null);
		for($i=0; $i<count($this->fieldset[$column]); $i++){
			if(!is_null($this->fieldset[$column][$i]->getPlayerID())){
				if($this->fieldset[$column][$i]->getPlayerID() != $last_player->getID()){
					$connect_cnt = 1;
					$connect_fields = array(array($column, $i));
					$last_player = $this->fieldset[$column][$i]->getPlayer();
				} else {
					$connect_cnt++;
					$connect_fields[] = array($column, $i);
				}
			}
			if($connect_cnt >= 4){
				$this->winner_found = true;
				$this->winner_player = $last_player;
				$this->winner_fields = $connect_fields;
				return $last_player;
			}
		}
	}
	
	public function checkRows(){
		/**
		 * Checks all rows for winner
		 **/
		foreach($this->fieldset[0] as $y => $value){
			$this->checkRow($y);
		}
	}
	
	public function checkRow($row){
		/**
		 * Checks specified row for winner
		 **/
		if($this->winner_found){
			return $this->winner_player;
		}
		$connect_cnt = 0;
		$connect_fields = array();
		$last_player = new Player(null);
		for($i=0; $i<count($this->fieldset); $i++){
			if(!is_null($this->fieldset[$i][$row]->getPlayerID())){
				if($this->fieldset[$i][$row]->getPlayerID() != $last_player->getID()){
					$connect_cnt = 1;
					$connect_fields = array(array($i, $row));
					$last_player = $this->fieldset[$i][$row]->getPlayer();
				} else {
					$connect_cnt++;
					$connect_fields[] = array($i, $row);
				}
			}
			if($connect_cnt >= 4){
				$this->winner_found = true;
				$this->winner_player = $last_player;
				$this->winner_fields = $connect_fields;
				return $last_player;
			}
		}
	}
	
	public function checkDiagonal(){
		/**
		 * Checks all diagonal rows for winner
		 **/
		for($y=1; $y<count($this->fieldset[0]); $y++){
			$this->checkDiagonalRow(0, $y, "right");
		}
		for($x=0; $x<count($this->fieldset); $x++){
			$this->checkDiagonalRow($x, 0, "right");
			$this->checkDiagonalRow($x, 0, "left");
		}
		for($y=count($this->fieldset[0])-1; $y>0; $y--){
			$this->checkDiagonalRow(0, $y, "left");
		}
	}
	
	public function checkDiagonalRow($start_x, $start_y, $direction){
		/**
		 * Checks specified diagonal row for winner
		 **/
		if($this->winner_found){
			return $this->winner_player;
		}
		$connect_cnt = 0;
		$last_player = new Player(null);
		$x = $start_x;
		$y = $start_y;
		while(isset($this->fieldset[$x][$y])){
			if(!is_null($this->fieldset[$x][$y]->getPlayerID())){
				if($this->fieldset[$x][$y]->getPlayerID() != $last_player->getID()){
					$connect_cnt = 1;
					$connect_fields = array(array($x, $y));
					$last_player = $this->fieldset[$x][$y]->getPlayer();
				} else {
					$connect_cnt++;
					$connect_fields[] = array($x, $y);
				}
			}
			if($connect_cnt >= 4){
				$this->winner_found = true;
				$this->winner_player = $last_player;
				$this->winner_fields = $connect_fields;
				return $last_player;
			}
			if($direction == "left"){
				$x--;
			} else {
				$x++;
			}
			$y++;
		}
	}
	
	public function isWon(){
		/**
		 * Check if game is won
		 **/
		return $this->winner_found;
	}
	
	public function getWinner(){
		/**
		 * Get winning player
		 **/
		return $this->winner_player;
	}
	
	public function getWinnerFields(){
		/**
		 * Get array of connected winning fields 
		 **/
		return $this->winner_fields;
	}
	
	public function getCanonicalURL(){
		/**
		 * Get games canonical url
		 **/
		return "game.php?gameID=" . $this->id . "&playerID=" . $_REQUEST['playerID']; //For test purpose incl. player id
	}
	
	public function getLastAction(){
		/**
		 * Get action object of last executed action
		 **/
		if(!$this->last_action){
			$sql = new SqlManager();
			$sql->setQuery("
				SELECT action_id FROM action
				WHERE action_game = {{game}}
				ORDER BY action_date DESC
				LIMIT 1");
			$sql->bindParam("{{game}}", $this->id, "int");
			$action = $sql->result();
			$this->last_action = new Action($action['action_id']);
		}
		return $this->last_action;
	}
	
	public function getLastActionID(){
		/**
		 * Get id of last executed action
		 **/
		$this->getLastAction();
		return $this->last_action->getID();
	}
	
	public function getScore(Player $player){
		/**
		 * Get score of specified player
		 *
		 * Parameters:
		 * player: player object for which the score should be loaded
		 **/
		$sql = new SqlManager();
		$sql->setQuery("
			SELECT COUNT(winner_id) AS cnt FROM winner
			WHERE winner_game = {{game}}
			AND winner_player = {{player}}
			");
		$sql->bindParam("{{game}}", $this->id, "int");
		$sql->bindParam("{{player}}", $player->getID(), "int");
		$score = $sql->result();
		return $score['cnt'];
	}
	
	public function checkPlayer(Player $player){
		/**
		 * Checks if player is in the game
		 **/
		if(in_array($player->getID(), $this->getPlayerIDs())){
			return true;
		}
		return false;
	}
	
}