<?php

/**
 * CMS API endpoint
 *
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 */

require_once __DIR__ . "/editor/WebBaseEnginePro.php";
require_once __DIR__ . '/editor/WebBaseDatabase.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: application/json");

$wb_command = $_GET['cmd'] ?? null;
if (!$wb_command) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

if (!WebBaseEnginePro::verifyAuth()) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$httpMethod = $_SERVER['REQUEST_METHOD'];

switch ($httpMethod) {
    case "GET":
        switch ($wb_command) {
            case 'version':
                WebBaseEnginePro::getVersion();
                break;
            case 'numFiles':
                WebBaseEnginePro::getNumFiles();
                break;
            case 'fileExists':
                WebBaseEnginePro::fileExists();
                break;
            case 'fileGetContent':
                WebBaseEnginePro::fileGetContent();
                break;
            case 'listingDirectoryItems':
                WebBaseEnginePro::listingDirectoryItems();
                break;
        }
        break;
    case "POST":
        switch ($wb_command) {
            case 'createFile':
                WebBaseEnginePro::createFile();
                break;
            case 'createDirectory':
                WebBaseEnginePro::createDirectory();
                break;
            case 'uploadFile':
                WebBaseEnginePro::uploadFile();
                break;
            case 'rename':
                WebBaseEnginePro::rename();
                break;
            case 'delete':
                WebBaseEnginePro::delete();
                break;
            case 'deleteDirectory':
                WebBaseEnginePro::deleteDirectory();
                break;
            case 'deleteFile':
                WebBaseEnginePro::deleteFile();
                break;
            case 'convertXmlToJson':
                WebBaseEnginePro::convertXmlToJson();
                break;
            case 'runQuery':
                WebBaseEnginePro::runQuery();
                break;
            case 'runSelectQuery':
                WebBaseEnginePro::runSelectQuery();
                break;
        }
        break;
}