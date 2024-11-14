<?php

/**
 * ANM22 WebBase editor v 3.0
 *
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 */

class com_anm22_wb_editor
{
    public static $version = "3.0.3";

    /**
     * Method to get the editor version number
     */
    public function getVersion()
    {
        return self::$version;
    }
}

include_once __DIR__ . '/editor/com_anm22_wb_editor_pages_layer.php';
include_once __DIR__ . '/editor/com_anm22_wb_editor_pages_index.php';
include_once __DIR__ . '/editor/com_anm22_wb_editor_page.php';
include_once __DIR__ . '/editor/com_anm22_wb_editor_page_element.php';
