<?php

/**
 * ConnectX
 * Meta Class
 *
 * Author: Julian Burr
 * Version: 0.1
 * Date: 2015/04/17
 *
 * Copyright (c) 2015 Julian Burr
 * License: Published under MIT license
 *
 * Description:
 *		Static util function that handles the meta managemant
 *		Loads and writes meta data in database
 **/

class Meta {
	
	public static function get($table, $table_id, $meta_name=null){
		/**
		 * Loads meta data from database
		 *
		 * Parameters:
		 * table: meta datas table reference
		 * table_id: id for which the meta data should be loaded
		 * meta_name: optional which meta data should be loaded
		 *			  if null, all meta data will be loaded
		 *
		 * Returns:
		 * meta: array[META_NAME][] = [META_VALUE]
		 *		 This way an object can have multiple meta sets with the same name
		 *
		 * See meta table for more information
		 **/
		$sql = new SqlManager();
		$query = "SELECT * FROM meta
			WHERE meta_table = '{{table}}'
			AND meta_table_id = {{id}}";
		if($meta_name){
			$query .= " AND meta_name = '{{name}}'";	
		}
		$sql->setQuery($query);
		$sql->bindParam("{{table}}", $table);
		$sql->bindParam("{{id}}", $table_id, "int");
		$sql->bindParam("{{name}}", $meta_name, "int");
		$sql->execute();
		$meta = array();
		while($row = $sql->fetch()){
			$meta[$row['meta_name']][] = $row['meta_value'];
		}
		if(!$meta_name){
			return $meta;
		}
		return $meta[$meta_name];
	}
	
	public static function add($table, $table_id, $meta_name, $meta_value){
		/**
		 * Adds meta attribute to database
		 *
		 * Parameters:
		 * table: referring data sets table
		 * table_id: referrer id
		 * meta_name: name of meta attribute
		 * meta_value: value to be added
		 **/
		$sql = new SqlManager();
		$new = array( "meta_table" => $table, "meta_table_id" => $table_id, "meta_name" => $meta_name, "meta_value" => $meta_value );
		$sql->insert("meta", $new);
	}
	
	public static function update($table, $table_id, $meta_name, $meta_value){
		/**
		 * Updates meta attribute to database
		 *
		 * Parameters:
		 * table: referring data sets table
		 * table_id: referrer id
		 * meta_name: name of meta attribute
		 * meta_value: value to be updated
		 *
		 * Removes meta attributes with specified name before adding new!
		 **/
		$load = self::get($table, $table_id, $meta_name);
		if($load){
			self::remove($table, $table_id, $meta_name);
		} 
		self::add($table, $table_id, $meta_name, $meta_value);
	}
	
	public static function remove($table, $table_id, $meta_name){
		/**
		 * Removes meta attributes with specified name from database
		 *
		 * Parameters:
		 * table: referring data sets table
		 * table_id: referrer id
		 * meta_name: name of meta attribute
		 * meta_value: value to be deleted
		 *
		 * NOTE: if there are multiple meta sets with the same name, all will be deleted!
		 **/
		$sql = new SqlManager();
		$sql->setQuery("
			DELETE FROM meta
			WHERE meta_table = '{{table}}'
			AND meta_table_id = {{id}}
			AND meta_table_name = '{{name}}'
			");
		$sql->bindParam("{{table}}", $table);
		$sql->bindParam("{{id}}", $table_id);
		$sql->bindParam("{{name}}", $meta_name);
		$sql->execute();
	}
	
}