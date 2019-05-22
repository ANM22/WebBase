<?php

if (!isset($_SERVER['HTTPS'])) {
    $http = "http://";
} else {
    $http = "https://";
}

$domain = $_SERVER['HTTP_HOST'];
$wb_folder = trim(dirname($_SERVER[REQUEST_URI]));

if ($wb_folder and ($wb_folder != "") and ($wb_folder != "/")) {
    $wb_folder.="/";
}

$urlPrefix = $http.$domain.$wb_folder;

?>
# robots.txt generate by ANM22 WebBase

Sitemap: <?=$urlPrefix?>sitemap.xml