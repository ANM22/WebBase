<?php

/**
 * CMS API endpoint legacy
 *
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 */

$wb_engine_version = 8.1;

$wb_command = $_GET['cmd'];

if (isset($_GET['xmlPrintDisable']) || isset($_POST['xmlPrintDisable'])) {

    $commandArg = array('xmlPrintDisable' => 1);

    @$wb_command($xml, $_GET, $_POST, $commandArg);
} else {

    $xml = new SimpleXMLElement('<xml/>');

    $xml_command = $xml->addChild('COMMAND', $wb_command);

    if (isset($_GET['wb_debug'])) {
        $xml_command = $xml->addChild('DEBUG');
    }

    if ($wb_command) {
        @$wb_command($xml, $_GET, $_POST, '');
    } else {
        $xml_error = $xml->addChild('ERROR', "1");
    }

    header('Content-type: text/xml');
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    print($xml->asXML());
}



/*
 * FUNCTIONS
 */

/* Engine functions */

function wb_engineVersion($xml, $get, $post, $arg)
{

    global $wb_engine_version;

    if (checkConnection($xml, $get, $post, '')) {
        $xml->addChild('ENGINE', $wb_engine_version);
        $xml->addChild('RETURN', "1");
    } else {
        $xml->addChild('RETURN', "0");
    }
}

/* Connection functions */

function checkConnection($xml, $get, $post, $arg)
{

    include "config/license.php";

    if ($arg['xmlPrintDisable'] ?? false || $get['xmlPrintDisable'] ?? false || $post['xmlPrintDisable'] ?? false) {
        $xmlPrintState = 0;
    } else {
        $xmlPrintState = 1;
    }

    if (($get['license'] ?? null) == $anm22_wb_license && ($get['licensePass'] ?? null) == $anm22_wb_licensePass) {
        if ($xmlPrintState) {
            $xml->addChild('CONNECTION', "1");
        }
        return true;
    } else if (isset($get['license']) && $get['license'] && isset($anm22_wb_licenses[$get['license']]) && ($get['licensePass'] ?? null) == $anm22_wb_licenses[$get['license']]['licensePass']) {
        if ($xmlPrintState) {
            $xml->addChild('CONNECTION', "1");
        }
        return true;
    } else {
        if ($xmlPrintState) {
            $xml->addChild('CONNECTION', "0");
        }
        return false;
    }
}

function wb_testConnection($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $xml->addChild('RETURN', "1");
    } else {
        $xml->addChild('RETURN', "0");
    }
}

/* File functions */

function contafile($dir)
{
    $count = 0;
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != ".." && $file != ".")
                $count++;
        }
        return $count;
    }
}

function wb_countFiles($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $path = "../" . $get['path'];
        $xml->addChild('RETURN', @contafile($path));
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_createFile($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {

        include "config/license.php";

        $path = "../" . $post['wb_cmd_path'];
        $fileName = $post['wb_cmd_fileName'];
        $source = $post['wb_cmd_source'];

        $xml->addChild('WB_CMD_PATH', $path);
        $xml->addChild('WB_CMD_FILENAME', $fileName);
        $xml->addChild('WB_CMD_SOURCE', $source);

        if ($arg['source']) {
            if ($anm22_wb_license_source_mode == "curl") {
                $xml->addChild('WB_CMD_SOURCE_MODE', "curl");
                $sessione_curl = curl_init($source);
                curl_setopt($sessione_curl, CURLOPT_HEADER, 0);
                curl_setopt($sessione_curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($sessione_curl, CURLOPT_BINARYTRANSFER, 1);
                $content = curl_exec($sessione_curl);
                curl_close($sessione_curl);
            } else {
                $xml->addChild('WB_CMD_SOURCE_MODE', "file_get_contents");
                $content = file_get_contents("$source");
            }
        } else {
            $xml->addChild('WB_CMD_SOURCE_MODE', "post");
            $content = $post['wb_cmd_content'];
        }
        $fd = @fopen(($path . $fileName), 'w');
        @chmod(($path . $fileName), 0755);
        if ($fd) {
            if (fwrite($fd, $content)) {
                $xml->addChild('RETURN', "1");
            } else {
                $xml->addChild('RETURN', "0");
            }
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_createFileGET($xml, $get, $post, $arg)
{
    $post['wb_cmd_path'] = $get['wb_cmd_path'];
    $post['wb_cmd_fileName'] = $get['wb_cmd_fileName'];
    $post['wb_cmd_content'] = $get['wb_cmd_content'];
    wb_createFile($xml, $get, $post, $arg);
}

function wb_createFileGETUrlencode($xml, $get, $post, $arg)
{
    $post['wb_cmd_path'] = $get['wb_cmd_path'];
    $post['wb_cmd_fileName'] = $get['wb_cmd_fileName'];
    $post['wb_cmd_content'] = urldecode($get['wb_cmd_content']);
    wb_createFile($xml, $get, $post, $arg);
}

function wb_createFileLinkSource($xml, $get, $post, $arg)
{
    $post['wb_cmd_path'] = $get['wb_cmd_path'];
    $post['wb_cmd_fileName'] = $get['wb_cmd_fileName'];
    $post['wb_cmd_source'] = urldecode($get['wb_cmd_source']);
    wb_createFile($xml, $get, $post, array('source' => 1));
}

function wb_delete($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $fileName = "../" . $_GET['wb_cmd_filename'];
        if (@file_exists($fileName) && @is_file($fileName)) {
            @unlink($fileName);
            $xml->addChild('RETURN', "1");
        } elseif (@is_dir($fileName)) {
            $handle = @opendir($fileName);
            while (false !== ($file = @readdir($handle))) {
                if (@is_file($fileName . $file)) {
                    @unlink($fileName . $file);
                }
            }
            $handle = @closedir($handle);
            @rmdir($fileName);
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_file_exists($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $fileName = $_GET['filename'];
        if (@file_exists($fileName)) {
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_file_get_contents($xml, $get, $post, $arg)
{
    $checkConnectionArg = array('xmlPrintDisable' => 1);
    if (checkConnection($xml, $get, $post, $checkConnectionArg)) {
        $path = "../" . $get['wb_cmd_fullpath'];
        echo file_get_contents($path);
    }
}

function wb_file_get_contents_Urlencode($xml, $get, $post, $arg)
{
    $get['wb_cmd_fullpath'] = urldecode($get['wb_cmd_fullpath']);
    wb_file_get_contents($xml, $get, $post, $arg);
}

function wb_l($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $path = $get['wb_cmd_path'];
        if ($handle = opendir($path)) {
            if ($_GET['wb_debug']) {
                $xml_command = $xml->DEBUG->addChild('LOG', "opendir('" . $path . "') succcess");
            }
            $xml->addChild('DIR');
            $xml->addChild('FILE');
            while (false !== ($file = readdir($handle))) {
                if ($_GET['wb_debug']) {
                    $xml_command = $xml->DEBUG->addChild('LOG', "File selected: " . $file);
                }
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path . $file)) {
                    $xml->DIR->addChild('ITEM', $file);
                } elseif (is_file($path . $file)) {
                    $xml->FILE->addChild('ITEM', $file);
                }
            }
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_mkdir($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $path = "../" . $get['wb_cmd_path'];
        if (@mkdir($path, 0755)) {
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_rename($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $newName = $get['newname'];
        $oldName = $get['oldname'];
        if (@rename($oldName, $newName)) {
            $xml->addChild('RETURN', "1");
        } else {
            $errorString = json_encode(error_get_last());
            $xml->addChild('ERROR', $errorString);
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_rmdir($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $path = $get['path'];
        if (@rmdir($path)) {
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_unlink($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {
        $path = $get['path'];
        if (@unlink($path)) {
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

/* File Upload */

function wb_uploadFile($xml, $get, $post, $arg)
{

    if ($arg['xmlPrintDisable'] ?? false || $get['xmlPrintDisable'] ?? false || $post['xmlPrintDisable'] ?? false) {
        $checkConnectionArg = array('xmlPrintDisable' => 1);
        $xmlPrintState = 0;
        $XHRTool = 1;
    } else if ($arg['xmlHeaderLocation'] ?? false || $get['xmlHeaderLocation'] ?? false || $post['xmlHeaderLocation'] ?? false) {
        $checkConnectionArg = array('xmlPrintDisable' => 1);
        $xmlPrintState = 0;
        $xmlHeaderLocation = urldecode($get['xmlHeaderLocation']);
    } else {
        $checkConnectionArg = '';
        $xmlPrintState = 1;
    }

    if (checkConnection($xml, $get, $post, $checkConnectionArg)) {
        $wb_cmd_path = $get['wb_cmd_path'];
        $wb_cmd_fileName = $get['wb_cmd_filename'];
        $wb_cmd_selfName = $get['wb_cmd_selfname'];
        $wb_cmd_type = $get['wb_cmd_type'];
        $wb_cmd_selfExtention = $get['wb_cmd_selfextention']; // Not implemented

        $wb_cmd_thumb_fileName = $_GET['wb_cmd_thumb_filename'];

        if ($wb_cmd_type == "standard") {
            $allowed = array('png', 'jpg', 'gif', 'zip');
        } else if ($wb_cmd_type == "image") {
            $allowed = array('png', 'jpg', 'jpeg', 'gif');
        }

        if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
            $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
            if (!(!$wb_cmd_type or $wb_cmd_type == "ALL")) {
                if (!in_array(strtolower($extension), $allowed)) {
                    if ($xmlPrintState) {
                        $xml->addChild('RETURN', "0");
                    } else {
                        if ($XHRTool) {
                            echo '{"status":"error"}';
                        } else {
                            header("Location: " . $xmlHeaderLocation . "&wb_up_error=1");
                            exit;
                        }
                    }
                    return 0;
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
                        imagepng($thumb, "../" . $wb_cmd_path . $wb_cmd_thumb_fileName, 9);
                    } else {
                        $createThumbAsOriginal = true;
                    }
                } else {
                    $createThumbAsOriginal = true;
                }
            }

            if ($wb_cmd_selfName or ! $wb_cmd_fileName) {
                $uploadedFileName = $_FILES['upl']['name'];
            } else {
                $uploadedFileName = $wb_cmd_fileName;
            }

            if (move_uploaded_file($_FILES['upl']['tmp_name'], '../' . $wb_cmd_path . $uploadedFileName)) {
                if ($createThumbAsOriginal) {
                    copy('../' . $wb_cmd_path . $uploadedFileName, '../' . $wb_cmd_path . $wb_cmd_thumb_fileName);
                }
                if ($xmlPrintState) {
                    $xml->addChild('RETURN', "1");
                } else {
                    if ($XHRTool) {
                        echo '{"status":"success"}';
                    } else {
                        header("Location: " . $xmlHeaderLocation . "&wb_up_confirm=1");
                        exit;
                    }
                }
            }
        } else {
            if ($xmlPrintState) {
                $xml->addChild('ALLARM', "No file uploaded");
                $xml->addChild('RETURN', "0");
            } else {
                if ($XHRTool) {
                    echo '{"status":"error"}';
                } else {
                    header("Location: " . $xmlHeaderLocation . "&wb_up_error=1");
                    exit;
                }
            }
        }
    } else {
        if ($xmlPrintState) {
            $xml->addChild('RETURN', "0");
        } else {
            if ($XHRTool) {
                echo '{"status":"error"}';
            } else {
                header("Location: " . $xmlHeaderLocation . "&wb_up_error=1");
                exit;
            }
        }
    }
}

/* MySQL functions */

function wb_mysql_read($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {

        include "config/license.php";

        $source = $get['wb_cmd_source'];
        $xml->addChild('WB_CMD_SOURCE', $source);

        if ($anm22_wb_license_source_mode == "curl") {
            $xml->addChild('WB_CMD_SOURCE_MODE', "curl");
            $sessione_curl = curl_init($source);
            curl_setopt($sessione_curl, CURLOPT_HEADER, 0);
            curl_setopt($sessione_curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($sessione_curl, CURLOPT_BINARYTRANSFER, 1);
            $content = curl_exec($sessione_curl);
            curl_close($sessione_curl);
        } else {
            $xml->addChild('WB_CMD_SOURCE_MODE', "file_get_contents");
            $content = file_get_contents("$source");
        }

        $xml->addChild('WB_DB_QUERY', $content);

        include "config/mysql.php";
        $conn = new mysqli($anm22_wb_myhost, $anm22_wb_myuser, $anm22_wb_mypassw, $anm22_wb_mydb);

        $result = $conn->query($content);

        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }

        $xml->addChild('DB_RESPONSE', json_encode($rows));
        $xml->addChild('RETURN', "1");
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_mysql_write($xml, $get, $post, $arg)
{
    if (checkConnection($xml, $get, $post, '')) {

        include "config/license.php";

        $source = $get['wb_cmd_source'];
        $xml->addChild('WB_CMD_SOURCE', $source);

        if ($anm22_wb_license_source_mode == "curl") {
            $xml->addChild('WB_CMD_SOURCE_MODE', "curl");
            $sessione_curl = curl_init($source);
            curl_setopt($sessione_curl, CURLOPT_HEADER, 0);
            curl_setopt($sessione_curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($sessione_curl, CURLOPT_BINARYTRANSFER, 1);
            $content = curl_exec($sessione_curl);
            curl_close($sessione_curl);
        } else {
            $xml->addChild('WB_CMD_SOURCE_MODE', "file_get_contents");
            $content = file_get_contents("$source");
        }

        include "config/mysql.php";
        $conn = new mysqli($anm22_wb_myhost, $anm22_wb_myuser, $anm22_wb_mypassw, $anm22_wb_mydb);
        $result = $conn->query($content);

        $xml->addChild('RETURN', "1");
    } else {
        $xml->addChild('RETURN', "0");
    }
}
