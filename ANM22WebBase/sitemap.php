<?php

include 'editor.php';

/* -> SITEMAP CREATION */
$languagesArray = array('en', 'it', 'de', 'es', 'fr', 'ru'); // WebBase languages
$pageIndexObject = new com_anm22_wb_editor_pages_index(); // XML index creation

// Download pages index
if ($xmlPagesIndex = simplexml_load_file("website/pages.xml")) {
    $pageIndexObject->importXML($xmlPagesIndex);
}

// Sitemap division for languages
$websitePages = array();
foreach ($languagesArray as $languageSelected) {
    $websitePages[$languageSelected] = array();
}

$beforePageLevel = array();
$beforeLevelPageId = array();
foreach ($languagesArray as $languageSelected) {
    $beforeLevelPageId[$languageSelected . ""] = array();
}

function scanPageChildren($pagesXMLArray, $level) {
    global $languagesArray, $beforePageLevel, $beforeLevelPageId, $pageIndexObject, $websitePages;
    if ($pagesXMLArray) {
        foreach ($pagesXMLArray as $pageId) {
            $scanSelLay = $pageIndexObject->layers[$pageId . ""];
            foreach ($languagesArray as $languageSelected) {
                if ((intval($scanSelLay->languages[$languageSelected]) == 1)) {
                    $datasellayscan = array();
                    $datasellayscan['id'] = $scanSelLay->id;
                    if ($scanSelLay->menuName[$languageSelected]) {
                        $datasellayscan['name'] = $scanSelLay->menuName[$languageSelected];
                    } else {
                        $datasellayscan['name'] = $scanSelLay->name;
                    }
                    $datasellayscan['link'] = $scanSelLay->link;
                    $datasellayscan['level'] = $level;
                    if ($beforePageLevel[$languageSelected] >= $level) {
                        $datasellayscan['lock'] = 1;
                    } else {
                        $datasellayscan['lock'] = 0;
                    }

                    $datasellayscan['prev-lay'] = $beforeLevelPageId[$languageSelected][$level - 1];
                    if ($beforePageLevel[$languageSelected] == $beforeLevelPageId[$languageSelected][$level]) {
                        $datasellayscan['same-lay'] = $beforeLevelPageId[$languageSelected][$level];
                    }
                    if ($beforePageLevel[$languageSelected] == $beforeLevelPageId[$languageSelected][$level]) {
                        $datasellayscan['next-lay'] = $beforeLevelPageId[$languageSelected][$level];
                    }
                    $beforePageLevel[$languageSelected] = $level;
                    $beforeLevelPageId[$languageSelected][$level] = $datasellayscan['id'];
                    $websitePages[$languageSelected][$datasellayscan['id'].""] = $datasellayscan;
                }
            }
            if ($scanSelLay->pages) {
                scanPageChildren($scanSelLay->pages, $level + 1);
            }
        }
    }
}

if ($pageIndexObject->pages) {
    scanPageChildren($pageIndexObject->pages, 1);
}

if (!isset($_SERVER['HTTPS'])) {
    $http = "http://";
} else {
    $http = "https://";
}

$domain = $_SERVER['HTTP_HOST'];
$wb_folder = trim(dirname($_SERVER['REQUEST_URI']));

if ($wb_folder and ($wb_folder != "") and ($wb_folder != "/")) {
    $wb_folder.="/";
}

$urlPrefix = $http.$domain.$wb_folder;

// Stampa sitemap
header("Content-type: text/xml");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">";

if ($xmlPagesIndex) {
    foreach ($languagesArray as $languageSelected) {
        if ($websitePages[$languageSelected]) {
            foreach ($websitePages[$languageSelected] as $xmlPagesIndexElement) {
                
                $link = "";
                if (((string) $xmlPagesIndexElement['link']) == "index") {
                    $link = "";
                } else {
                    $link = $xmlPagesIndexElement['link']."/";
                }

                echo "<url>";

                echo "<loc>".$urlPrefix.$languageSelected."/".$link."</loc>";
                // echo "<lastmod>2016-09-25</lastmod>";
                echo "<changefreq>weekly</changefreq>"
                        . "<priority>0.8</priority>";
                
                foreach ($languagesArray as $connectedLanguageSelected) {
                    if (isset($websitePages[$connectedLanguageSelected][$xmlPagesIndexElement['id'].""])) {
                        echo "<xhtml:link rel=\"alternate\" hreflang=\"".$connectedLanguageSelected."\" href=\"".$urlPrefix.$connectedLanguageSelected."/".$link."\" />";
                    }
                }

                echo "</url>";
            }
        }
    }
}

echo "</urlset>";