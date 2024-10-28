<?php

/**
 * Script to generate the website sitemap.
 * 
 * This script is part of the ANM22 WebBase CMS.
 */

include 'editor.php';
include 'config/license.php';

$languagesArray = ['en', 'it', 'de', 'es', 'fr', 'ru']; // WebBase languages
$pageIndexObject = new com_anm22_wb_editor_pages_index(); // XML index creation

// Init the pages index
$pageIndexLoaded = false;
if (file_exists(__DIR__ . "/website/pages.json")) {
    $assoc = json_decode(file_get_contents(__DIR__ . "/website/pages.json"), true);
    $pageIndexObject->initData($assoc);
    $pageIndexLoaded = true;
} else if ($xmlPagesIndex = simplexml_load_file("website/pages.xml")) {
    $pageIndexObject->importXML($xmlPagesIndex);
    $pageIndexLoaded = true;
}

// Sitemap division for languages
$websitePages = [];
foreach ($languagesArray as $languageSelected) {
    $websitePages[$languageSelected] = [];
}

$beforePageLevel = [];
$beforeLevelPageId = [];
foreach ($languagesArray as $languageSelected) {
    $beforeLevelPageId[$languageSelected . ""] = [];
}

function scanPageChildren($pagesXMLArray, $level)
{
    global $languagesArray, $beforePageLevel, $beforeLevelPageId, $pageIndexObject, $websitePages;
    if ($pagesXMLArray) {
        foreach ($pagesXMLArray as $pageId) {
            $scanSelLay = $pageIndexObject->layers[$pageId . ""] ?? null;
            foreach ($languagesArray as $languageSelected) {
                if ((intval($scanSelLay->languages[$languageSelected] ?? 0) == 1)) {
                    $datasellayscan = [];
                    $datasellayscan['id'] = $scanSelLay->id;
                    if ($scanSelLay->menuName[$languageSelected] ?? false) {
                        $datasellayscan['name'] = $scanSelLay->menuName[$languageSelected];
                    } else {
                        $datasellayscan['name'] = $scanSelLay->name;
                    }
                    $datasellayscan['link'] = $scanSelLay->link;
                    $datasellayscan['level'] = $level;
                    if (($beforePageLevel[$languageSelected] ?? 0) >= $level) {
                        $datasellayscan['lock'] = 1;
                    } else {
                        $datasellayscan['lock'] = 0;
                    }

                    $datasellayscan['prev-lay'] = $beforeLevelPageId[$languageSelected][$level - 1] ?? null;
                    if (($beforePageLevel[$languageSelected] ?? null) == ($beforeLevelPageId[$languageSelected][$level] ?? null)) {
                        $datasellayscan['same-lay'] = $beforeLevelPageId[$languageSelected][$level] ?? null;
                    }
                    if (($beforePageLevel[$languageSelected] ?? null) == ($beforeLevelPageId[$languageSelected][$level] ?? null)) {
                        $datasellayscan['next-lay'] = $beforeLevelPageId[$languageSelected][$level] ?? null;
                    }
                    $beforePageLevel[$languageSelected] = $level;
                    $beforeLevelPageId[$languageSelected][$level] = $datasellayscan['id'];
                    $websitePages[$languageSelected][$datasellayscan['id'] . ""] = $datasellayscan;
                }
            }
            if ($scanSelLay && $scanSelLay->pages) {
                scanPageChildren($scanSelLay->pages, $level + 1);
            }
        }
    }
}

if ($pageIndexObject->pages) {
    scanPageChildren($pageIndexObject->pages, 1);
}

// HTTP protocol
$http = "https://";

// Website domain
$domain = $_SERVER['HTTP_HOST'];
if ($pageIndexObject->domain) {
    $domain = $pageIndexObject->domain;
}
if ($domain == "legacy.anm22.it") {
    $domain = "www.anm22.it";
}

// Website path
$wb_folder = trim(dirname($_SERVER['REQUEST_URI']));

if ($wb_folder && ($wb_folder != "") && ($wb_folder != "/")) {
    $wb_folder .= "/";
}

// Full page URL prefix
$urlPrefix = $http . $domain . $wb_folder;

// Print the XML sitemap
header("Content-type: text/xml");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">";

if ($pageIndexLoaded) {
    if (isset($anm22_wb_license_language_mode) and ($anm22_wb_license_language_mode == "mono")) {
        $languageSelected = 'it';
        if ($websitePages[$languageSelected]) {
            foreach ($websitePages[$languageSelected] as $xmlPagesIndexElement) {

                $link = "";
                if (((string) $xmlPagesIndexElement['link']) == "index") {
                    $link = "";
                } else {
                    $link = $xmlPagesIndexElement['link'] . "/";
                }

                echo "<url>";

                echo "<loc>" . $urlPrefix . $link . "</loc>";
                echo "<changefreq>weekly</changefreq>"
                    . "<priority>0.8</priority>";

                echo "</url>";
            }
        }
    } else {
        foreach ($languagesArray as $languageSelected) {
            if ($websitePages[$languageSelected]) {
                foreach ($websitePages[$languageSelected] as $xmlPagesIndexElement) {

                    $link = "";
                    if (((string) $xmlPagesIndexElement['link']) == "index") {
                        $link = "";
                    } else {
                        $link = $xmlPagesIndexElement['link'] . "/";
                    }

                    echo "<url>";

                    echo "<loc>" . $urlPrefix . $languageSelected . "/" . $link . "</loc>";
                    // echo "<lastmod>2016-09-25</lastmod>";
                    echo "<changefreq>weekly</changefreq>"
                        . "<priority>0.8</priority>";

                    foreach ($languagesArray as $connectedLanguageSelected) {
                        if (isset($websitePages[$connectedLanguageSelected][$xmlPagesIndexElement['id'] . ""])) {
                            echo "<xhtml:link rel=\"alternate\" hreflang=\"" . $connectedLanguageSelected . "\" href=\"" . $urlPrefix . $connectedLanguageSelected . "/" . $link . "\" />";
                        }
                    }

                    echo "</url>";
                }
            }
        }
    }
}

echo "</urlset>";
