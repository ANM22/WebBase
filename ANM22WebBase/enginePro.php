<?php

/**
 * CMS API endpoint
 *
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 * @copyright 2024 Paname srl
 */

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
            case 'runQuery':
                WebBaseEnginePro::runQuery();
                break;
            case 'runSelectQuery':
                WebBaseEnginePro::runSelectQuery();
                break;
        }
        break;
}

class WebBaseEnginePro
{
    const ENGINE_VERSION = 10;

    /**
     * Method to get the engine version
     * 
     * @return void
     */
    public static function getVersion()
    {
        echo json_encode(["version" => self::ENGINE_VERSION]);
    }

    /**
     * Verify auth
     * 
     * @return void
     */
    public static function verifyAuth()
    {
        require_once __DIR__ . "/config/license.php";

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {

            // Basic authorization
            list($type, $credentials) = explode(' ', $_SERVER['HTTP_AUTHORIZATION'], 2);
            list($clientId, $clientSecret) = explode(':', base64_decode($credentials));
        } else {

            // Old authorization
            $clientId = $db->escape($_GET['client_id']);
            $clientSecret = $db->escape($_GET['client_secret']);
        }

        if ($clientId == $anm22_wb_license && $clientSecret == $anm22_wb_licensePass) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the number of the files inside a directory.
     * Returns of false in case of invalid directory path.
     */
    public static function getNumFiles()
    {
        $response = [];

        $path = $_GET['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid path parameter"]);
            exit();
        }
        $path = urldecode($path);
        $response["path"] = $path;

        $path = "../" . $path;
        
        $count = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != ".." && $file != ".")
                    $count++;
            }
            $response["numFiles"] = $count;
            echo json_encode($response);
        } else {
            http_response_code(404);
            $response["error"] = "Path not found.";
            echo json_encode($response);
            exit();
        }
    }

    public static function createFile()
    {
        $response = [];

        $payload = json_decode(file_get_contents('php://input'), true);

        $path = $payload['path'];
        $fileName = $payload['filename'];
        $file = $payload['file'] ?? null;
        $source = $payload['source'] ?? null;
        $content = $payload['content'] ?? null;

        $response["fileName"] = $fileName;
        $response["path"] = $path;
        if ($path && (substr($path, -1) != "/")) {
            $path .= "/";
        }
        $path = "../" . $path;

        if ($file) {
            //TODO: Complete case
        } else if ($source) {
            $response["source"] = $source;

            $sessione_curl = curl_init($source);
            curl_setopt($sessione_curl, CURLOPT_HEADER, 0);
            curl_setopt($sessione_curl, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($sessione_curl);
            curl_close($sessione_curl);
        }

        $fd = fopen(($path . $fileName), 'w');
        chmod(($path . $fileName), 0755);
        if ($fd) {
            if (fwrite($fd, $content)) {
                $response["result"] = true;
            } else {
                $response["result"] = false;
            }
        } else {
            $response["result"] = false;
        }

        echo json_encode($response);
    }

    /**
     * Delete a file or a directory
     */
    public static function delete()
    {
        $response = [];

        $payload = json_decode(file_get_contents('php://input'), true);
        $path = $payload['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Missing path parameter"]);
            exit();
        }
        $response["path"] = $path;
        $fileName = "../" . $path;

        if (file_exists($fileName) && is_file($fileName)) {
            unlink($fileName);
            echo json_encode(["result" => true]);
            exit();
        } else if (is_dir($fileName)) {
            $handle = opendir($fileName);
            while (false !== ($file = readdir($handle))) {
                if (is_file($fileName . $file)) {
                    unlink($fileName . $file);
                }
            }
            $handle = closedir($handle);
            rmdir($fileName);

            $response["result"] = true;
            echo json_encode($response);
            exit();
        } else {
            $response["result"] = false;
            echo json_encode($response);
            exit();
        }
    }

    /**
     * Delete a directory
     */
    public static function deleteDirectory()
    {
        $response = [];

        $payload = json_decode(file_get_contents('php://input'), true);
        $path = $payload['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Missing path parameter"]);
            exit();
        }
        $response["path"] = $path;

        $path = "../" . $path;
        if (rmdir($path)) {
            $response["result"] = true;
            echo json_encode($response);
        } else {
            $response["result"] = false;
            echo json_encode($response);
        }
    }

    /**
     * Delete a file
     */
    public static function deleteFile()
    {
        $response = [];

        $payload = json_decode(file_get_contents('php://input'), true);
        $path = $payload['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Missing path parameter"]);
            exit();
        }
        $response["path"] = $path;

        $path = "../" . $path;
        if (unlink($path)) {
            $response["result"] = true;
            echo json_encode($response);
        } else {
            $response["result"] = false;
            echo json_encode($response);
        }
    }

    /**
     * Chech if file exists
     */
    public static function fileExists()
    {
        $response = [];
        $path = $_GET['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid path parameter"]);
            exit();
        }
        $path = urldecode($path);
        $response["path"] = $path;
        $path = "../" . $path;

        $response["result"] = file_exists($path);

        echo json_encode($response);
    }

    /**
     * Get file content
     */
    public static function fileGetContent()
    {
        $response = [];

        $path = $_GET['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid path parameter"]);
            exit();
        }
        $path = urldecode($path);
        $response["path"] = $path;
        $path = "../" . $path;

        $content = file_get_contents($path);

        if ($content !== false) {
            $response["content"] = utf8_encode($content);
        } else {
            http_response_code(404);
            $response["error"] = "File not found.";
            $response["content"] = "";
        }

        echo json_encode($response);
    }

    /**
     * Get the list of the files and directories inside a directory
     */
    public static function listingDirectoryItems()
    {
        $response = [
            "directories" => [],
            "files" => [],
        ];

        $path = $_GET['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid path parameter"]);
            exit();
        }
        $path = urldecode($path);
        $response["path"] = $path;
        $path = "../" . $path;        

        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path . $file)) {
                    $response["directories"][] = $file;
                } elseif (is_file($path . $file)) {
                    $response["files"][] = $file;
                }
            }
            $response["result"] = true;
        } else {
            $response["result"] = false;
        }

        echo json_encode($response);
    }

    /**
     * Create a directory
     */
    public static function createDirectory()
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        $path = $payload['path'] ?? null;
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid path parameter"]);
            exit();
        }
        $response = ["path" => $path];
        $path = "../" . $path;

        $response["result"] = mkdir($path, 0755);
        echo json_encode($response);
    }

    /**
     * Rename a file or a directory
     */
    public static function rename()
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        $newName = $payload['newPath'] ?? null;
        $oldName = $payload['oldPath'] ?? null;

        if (!$oldName || !$newName) {
            http_response_code(400);
            echo json_encode(["error" => "Missing new or old path parameter"]);
            exit();
        }

        $newName = "../" . $newName;
        $oldName = "../" . $oldName;

        if (rename($oldName, $newName)) {
            echo json_encode(["result" => true]);
        } else {
            $errorString = error_get_last();
            $response = [
                "result" => false,
                "error" => $errorString,
            ];
            echo json_encode($response);
        }
    }

    /**
     * Method to upload file via API or UI.
     */
    public static function uploadFile()
    {
        $responseType = $_GET["responseType"] ?? 'json';
        $redirectUrl = urldecode($_GET["redirectUrl"] ?? '');

        $path = urldecode($_GET['path'] ?? '');
        $wb_cmd_fileName = urldecode($_GET['filename'] ?? '');
        $wb_cmd_selfName = $_GET['selfname'] ?? ($path ? true : false);
        $wb_cmd_type = $_GET['type'] ?? "ALL";
        $wb_cmd_selfExtention = $_GET['selfextention'] ?? false; // Not implemented

        $wb_cmd_thumb_fileName = $_GET['thumbFilename'];

        if ($path && (substr($path, -1) != "/")) {
            $path .= "/";
        }
        $path = "../" . $path;

        if ($wb_cmd_type == "standard") {
            $allowed = array('png', 'jpg', 'gif', 'zip');
        } else if ($wb_cmd_type == "image") {
            $allowed = array('png', 'jpg', 'jpeg', 'gif');
        }

        if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
            $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
            if (!(!$wb_cmd_type || $wb_cmd_type == "ALL")) {
                if (!in_array(strtolower($extension), $allowed)) {
                    if ($responseType == 'redirect') {
                        header("Location: " . $redirectUrl . "&wb_up_error=1");
                    } else {
                        http_response_code(403);
                        $response = [
                            "status" => "error",
                            "error" => "File extension not allowed",
                        ];
                        echo json_encode($response);
                    }
                    exit();
                }
            }

            // Thumb
            $createThumbAsOriginal = false;
            if ($_GET['wb_cmd_thumb']) {
                list($thumb_original_width, $thumb_original_height, $thumb_original_type, $thumb_original_attr) = getimagesize($_FILES['upl']['tmp_name']);
                if (($thumb_original_width * $thumb_original_height) <= 8000000) {
                    // Resizing
                    if (($_GET['wb_cmd_thumb_width'] / $_GET['wb_cmd_thumb_height']) >= ($thumb_original_width / $thumb_original_height)) {
                        $thumb_width = $_GET['wb_cmd_thumb_width'];
                        $thumb_height = $_GET['wb_cmd_thumb_width'] * $thumb_original_height / $thumb_original_width;
                    } else {
                        $thumb_height = $_GET['wb_cmd_thumb_height'];
                        $thumb_width = $_GET['wb_cmd_thumb_height'] * $thumb_original_width / $thumb_original_height;
                    }
                    // creation thumb
                    $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
                    if ($thumb_original_type == 1) {
                        $source = imagecreatefromgif($_FILES['upl']['tmp_name']);
                    }
                    if ($thumb_original_type == 2) {
                        $source = imagecreatefromjpeg($_FILES['upl']['tmp_name']);
                    }
                    if ($thumb_original_type == 3) {
                        $source = imagecreatefrompng($_FILES['upl']['tmp_name']);
                    }
                    if (isset($source)) {
                        imagecopyresized($thumb, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $thumb_original_width, $thumb_original_height);
                        // saving thumb
                        imagepng($thumb, $path . $wb_cmd_thumb_fileName, 9);
                    } else {
                        $createThumbAsOriginal = true;
                    }
                } else {
                    $createThumbAsOriginal = true;
                }
            }

            if ($wb_cmd_selfName || !$wb_cmd_fileName) {
                $uploadedFileName = $_FILES['upl']['name'];
            } else {
                $uploadedFileName = $wb_cmd_fileName;
            }

            if (move_uploaded_file($_FILES['upl']['tmp_name'], $path . $uploadedFileName)) {
                if ($createThumbAsOriginal) {
                    copy($path . $uploadedFileName, $path . $wb_cmd_thumb_fileName);
                }
                if ($responseType == 'redirect') {
                    header("Location: " . $redirectUrl . "&wb_up_confirl=1");
                } else {
                    echo json_encode(["status" => "success"]);
                }
                exit();
            }
        } else {
            if ($responseType == 'redirect') {
                header("Location: " . $redirectUrl . "&wb_up_error=1");
            } else {
                http_response_code(400);
                $response = [
                    "status" => "error",
                    "error" => "Missing file in the payload",
                ];
                echo json_encode($response);
            }
            exit();
        }
    }

    /**
     * Run a SELECT SQL query
     */
    public static function runSelectQuery()
    {
        $response = [];

        $payload = json_decode(file_get_contents('php://input'), true);

        $source = $payload['source'] ?? null;
        $content = $payload['query'] ?? null;

        if ($source) {
            $response["source"] = $source;

            $sessione_curl = curl_init($source);
            curl_setopt($sessione_curl, CURLOPT_HEADER, 0);
            curl_setopt($sessione_curl, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($sessione_curl);
            curl_close($sessione_curl);
        }

        $response["query"] = $content;

        include __DIR__ . "/config/mysql.php";
        $conn = new mysqli($anm22_wb_myhost, $anm22_wb_myuser, $anm22_wb_mypassw, $anm22_wb_mydb);
        $result = $conn->query($content);

        if ($result) {
            $response["result"] = true;
            $response["rows"] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $response["result"] = false;
        }

        echo json_encode($result);
    }

    /**
     * Run a generic SQL query and ignore the result content
     */
    public static function runQuery()
    {
        $response = [];

        $payload = json_decode(file_get_contents('php://input'), true);

        $source = $payload['source'] ?? null;
        $content = $payload['query'] ?? null;

        if ($source) {
            $response["source"] = $source;

            $sessione_curl = curl_init($source);
            curl_setopt($sessione_curl, CURLOPT_HEADER, 0);
            curl_setopt($sessione_curl, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($sessione_curl);
            curl_close($sessione_curl);
        }

        $response["query"] = $content;

        include __DIR__ . "/config/mysql.php";
        $conn = new mysqli($anm22_wb_myhost, $anm22_wb_myuser, $anm22_wb_mypassw, $anm22_wb_mydb);
        $result = $conn->query($content);

        if ($result) {
            $response["result"] = true;
        } else {
            $response["result"] = false;
        }

        echo json_encode($result);
    }
}
