<?php

/**
 * ANM22 WebBase editor v 3.0
 *
 * @copyright 2024 Paname srl
 */

class com_anm22_wb_editor
{
    public static $version = 3.0;

    function getVersion()
    {
        return self::$version;
    }
}

include_once __DIR__ . '/editor/com_anm22_wb_editor_pages_layer.php';
include_once __DIR__ . '/editor/com_anm22_wb_editor_pages_index.php';
include_once __DIR__ . '/editor/com_anm22_wb_editor_page.php';
include_once __DIR__ . '/editor/com_anm22_wb_editor_page_element.php';
