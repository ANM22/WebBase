<?php

include __DIR__ . "/../ANM22WebBase/editor.php";

if (!($_GET['page'] ?? false)) {
    header("Location: ../");
    exit();
}

$pageObject = new com_anm22_wb_editor_page();
$pageObject->pageShow([
    'lang' => "mail",
    'get' => $_GET,
    'post' => $_POST
]);
