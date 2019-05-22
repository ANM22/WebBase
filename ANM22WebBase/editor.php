<?php
/*
 * 
 * ANM22 Andrea Menghi all rights reserved
 *
 * v 2.6
 *
 */

class com_anm22_wb_editor {

    public static $version = 2.6;

    function getVersion() {
        return self::$version;
    }

}

include_once '../ANM22WebBase/editor/com_anm22_wb_editor_pages_layer.php';
include_once '../ANM22WebBase/editor/com_anm22_wb_editor_pages_index.php';
include_once '../ANM22WebBase/editor/com_anm22_wb_editor_page.php';
include_once '../ANM22WebBase/editor/com_anm22_wb_editor_page_element.php';