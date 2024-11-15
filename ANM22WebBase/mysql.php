<?php
/**
 * ANM22 WebBase engine MySQL endpoint
 *
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 */

require_once __DIR__ . "/editor/WebBaseEnginePro.php";
require_once __DIR__ . '/editor/WebBaseDatabase.php';

$dataMode = $_GET['dm'] ?? "post";
if (!$dataMode) {
    $dataMode = "post";
}

if (WebBaseEnginePro::verifyAuth()) {
    if ($dataMode == "get") {
        $action = $_GET['a'];
    } else {
        $action = $_POST['action'];
    }

    if (!trim($action)) {
        $result = [];
        $result['result'] = 0;
        $result['error'] = "No action command";
        echo json_encode($result);
        exit;
    }

    $result = @$action();
    echo json_encode($result);
    exit;
} else {
    $result = [];
    $result['result'] = 0;
    $result['error'] = "Wrong license key";
    echo json_encode($result);
    exit;
}

/**
 * @deprecated since WebBase editor v3.1
 * 
 * Open DB connection.
 * 
 * @return WebBaseDatabase
 */
function dbConnect()
{
    $db = new WebBaseDatabase();
    $db->connect();
    return $db;
}

/**
 * @deprecated since WebBase editor v3.1
 * 
 * Close DB connection
 * 
 * @param WebBaseDatabase $db Database connection
 * @return void
 */
function dbClose($db)
{
    return $db->close();
}


/* Query INSERT */
function queryInsert()
{
    global $dataMode;

    $result = [];
    $result['dataMode'] = $dataMode;
    $result['action'] = "queryInsert";

    $db = new WebBaseDatabase();

    if ($db->connect()) {

        if ($dataMode == "get") {
            $query = $_GET['q'];
        } else {
            $query = stripslashes($_POST['query']);
        }

        $result['query'] = $query;

        if ($mysqlResult = $db->query($query)) {
            $result['result'] = 1;
            $result['insertId'] = $db->insert_id;
        } else {
            $result['result'] = 0;
            $result['error'] = "";
        }

        $db->close();
    } else {
        $result['return'] = 0;
    }

    return $result;
}

/* Query UPDATE */
function queryUpdate()
{
    return queryNoReturn();
}

/* Query DELETE */
function queryDelete()
{
    return queryNoReturn();
}

/* Query without return data */
function queryNoReturn()
{
    global $dataMode;

    $db = new WebBaseDatabase();

    if ($db->connect()) {

        $result = [];
        $result['dataMode'] = $dataMode;
        $result['action'] = "queryUpdate";

        if ($dataMode == "get") {
            $query = $_GET['q'];
        } else {
            $query = stripslashes($_POST['query']);
        }

        $result['query'] = $query;

        if ($mysqlResult = $db->query($query)) {
            $result['result'] = 1;
        } else {
            $result['result'] = 0;
            $result['error'] = "";
        }

        $db->close();
        return $result;
    }
}

/* Query SELECT */
function querySelect()
{
    global $dataMode;

    $db = new WebBaseDatabase();

    if ($db->connect()) {

        $result = [];
        $result['dataMode'] = $dataMode;
        $result['action'] = "querySelect";

        if ($dataMode == "get") {
            $query = $_GET['q'];
        } else {
            $query = stripslashes($_POST['query']);
        }

        $result['query'] = $query;

        $queryResult = $db->makeQueryAssoc($query);
        if ($queryResult === false) {
            $result['result'] = 0;
            $result['error'] = "";
        } else {
            $result['result'] = 1;
            $result['data'] = $queryResult;
            $result['numberRows'] = count($queryResult);
        }

        $db->close();
        return $result;
    } else {
        $result['result'] = 0;
        $result['error'] = "Mysql connect error";
    }
}
