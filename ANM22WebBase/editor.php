<?php

/**
 * ANM22 WebBase editor v 3.1.2
 *
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 */

class com_anm22_wb_editor
{
    public static $version = "3.1.2";

    /**
     * Method to get the editor version number
     */
    public function getVersion()
    {
        return self::$version;
    }
}

require_once __DIR__ . '/editor/com_anm22_wb_editor_pages_layer.php';
require_once __DIR__ . '/editor/com_anm22_wb_editor_pages_index.php';
require_once __DIR__ . '/editor/com_anm22_wb_editor_page.php';
require_once __DIR__ . '/editor/com_anm22_wb_editor_page_element.php';
require_once __DIR__ . '/editor/WebBaseDatabase.php';
