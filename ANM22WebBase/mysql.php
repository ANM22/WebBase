<?php
/*
 * Author: ANM22
 * Last modified: 21 Aug 2015 - GMT +2 18:47
 *
 * ANM22 Andrea Menghi all rights reserved
 *
 */

$dataMode = $_GET['dm'];
if (!$dataMode) {
	$dataMode = "post";
}

if (checkConnection()) {
	if ($dataMode == "get") {
		$action = $_GET['a'];
	} else {
		$action = $_POST['action'];
	}
	
	if (!trim($action)) {
		$result = array();
		$result['result'] = 0;
		$result['error'] = "No action command";
		echo json_encode($result);
		exit;
	}
	
	$result = @$action();
	echo json_encode($result);
	exit;
} else {
	$result = array();
	$result['result'] = 0;
	$result['error'] = "Wrong license key";
	echo json_encode($result);
	exit;
}

/* Controllo chiavi licenza */
function checkConnection() {
	global $dataMode;
	
	include "config/license.php";
	
	if ($dataMode == "get") {
		$get = $_GET;
	} else {
		$get = $_POST;
	}
	
	if (($get['license'] == $anm22_wb_license) and ($get['licensePass'] == $anm22_wb_licensePass)) {
		return 1;
	} else {
		return 0;
	}
}

/* Connessione database */
function dbConnect() {
	include "config/mysql.php";
	
	if ($mysqlConnection = @mysql_connect($db_host,$db_user,$db_pass)) {
		return @mysql_select_db($db_name,$mysqlConnection);
	} else {
		return 0;
	}
}

/* Disconessione database */
function dbClose() {
	return @mysql_close();
}


/* Query INSERT */
function queryInsert () {
	global $dataMode;
	
	$result = array();
	$result['dataMode'] = $dataMode;
	$result['action'] = "queryInsert";
	
	if (dbConnect()) {
		
		if ($dataMode == "get") {
			$query = $_GET['q'];
		} else {
			$query = stripslashes($_POST['query']);
		}
		
		$result['query'] = $query;
		
		if ($mysqlResult = @mysql_query($query)) {
			$result['result'] = 1;
			$result['insertId'] = @mysql_insert_id();
		} else {
			$result['result'] = 0;
			$result['error'] = mysql_error();
		}
		
		dbClose();
		
	} else {
		$result['return'] = 0;
	}
	
	return $result;
}

/* Query UPDATE */
function queryUpdate () {
	return queryNoReturn();
}

/* Query DELETE */
function queryDelete () {
	return queryNoReturn();
}

/* Query without return data */
function queryNoReturn () {
	global $dataMode;
	
	if (dbConnect()) {
		
		$result = array();
		$result['dataMode'] = $dataMode;
		$result['action'] = "queryUpdate";
		
		if ($dataMode == "get") {
			$query = $_GET['q'];
		} else {
			$query = stripslashes($_POST['query']);
		}
		
		$result['query'] = $query;
		
		if ($mysqlResult = @mysql_query($query)) {
			$result['result'] = 1;
		} else {
			$result['result'] = 0;
			$result['error'] = mysql_error();
		}
		
		dbClose();
		return $result;
	}
}

/* Query SELECT */
function querySelect () {
	global $dataMode;
	
	if (dbConnect()) {
		
		$result = array();
		$result['dataMode'] = $dataMode;
		$result['action'] = "querySelect";
		
		if ($dataMode == "get") {
			$query = $_GET['q'];
		} else {
			$query = stripslashes($_POST['query']);
		}
		
		$result['query'] = $query;
		
		if ($mysqlResult = @mysql_query($query)) {
			$result['result'] = 1;
			$result['data'] = array();
			while ($row = @mysql_fetch_array($mysqlResult)) {
				$result['data'][] = $row;
			}
			$result['numberRows'] = @mysql_num_rows($mysqlResult);
		} else {
			$result['result'] = 0;
			$result['error'] = mysql_error();
		}
		
		dbClose();
		return $result;
	} else {
		$result['result'] = 0;
		$result['error'] = "Mysql connect error: ".mysql_error();
	}
}

?>