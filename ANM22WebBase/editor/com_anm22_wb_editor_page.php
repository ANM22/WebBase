<?php
class com_anm22_wb_editor_page {

    public $id;
    public $name;
    public $link;
    public $title;
    public $language;
    public $state;
    public $xml;
    public $theme;
    public $template;
    public $templateInlineStyles = array();
    public $pageOptions = array();
    public $layout_header = 1;
    public $layout_footer = 1;
    public $layout_leftside = 0;
    public $layout_body = 1;
    public $layout_rightside = 0;
    public $conteiners = array();
    public $lastElementId = 0;
    public $elements = array();
    public $getVariables;
    public $postVariables;
    public $defaultPage;
    private $headContent;

    /* SEO */
    public $site_name;
    public $og_type = "website";
    public $article_publisher;
    public $publisher;
    public $twitter_site;
    public $description;
    public $image;
    public $twitter_card = "summary_large_image";

    public function importXML($xml) {
        $this->id = $xml->id;
        $this->name = $xml->name;
        $this->link = $xml->link;
        $this->title = $xml->title;
        $this->language = $xml->language;
        $this->state = $xml->state;
        $this->theme = $xml->theme;
        $this->template = $xml->template;
        $this->layout_header = $xml->layout->header;
        $this->layout_leftside = $xml->layout->leftside;
        $this->layout_body = $xml->layout->body;
        $this->layout_rightside = $xml->layout->rightside;
        $this->layout_footer = $xml->layout->footer;
        $this->lastElementId = $xml->lastElementId;

        $this->site_name = $xml->site_name;
        $this->og_type = $xml->og_type;
        $this->article_publisher = $xml->article_publisher;
        $this->publisher = $xml->publisher;
        $this->twitter_site = $xml->twitter_site;
        $this->description = $xml->description;
        $this->image = $xml->image;
        $this->twitter_card = $xml->twitter_card;

        $urlDefaultPage = "../ANM22WebBase/website/" . $this->language . "/default.xml";
        $this->defaultPage = @simplexml_load_file($urlDefaultPage);

        /* Template Inline Styles */
        if (file_exists("../ANM22WebBase/website/template/" . $this->theme . "/" . $this->template . "_inlineStyles.php")) {
            require_once "../ANM22WebBase/website/template/" . $this->theme . "/" . $this->template . "_inlineStyles.php";
            $this->templateInlineStyles = $inlineStyles;
        }

        /* Template Page Options */
        if ($xml->pageOptions) {
            foreach ($xml->pageOptions->option as $option) {
                $this->pageOptions[$option->name . ""] = $option->value;
            }
        }

        /* Elements Loading */
        if ($xml->elements) {
            foreach ($xml->elements->element as $element) {
                $elementClass = (string) $element->elementClass;
                $elementPlugin = (string) $element->elementPlugin;
                require_once "../ANM22WebBase/website/plugins/" . $elementPlugin . "/plugin.php";
                $elementObject = new $elementClass();
                $elementObject->importXML($element, $this);
                $this->elements[intval($elementObject->getId())] = $elementObject;
            }
        }

        /* Default Elements Loading */
        if ($this->defaultPage->elements) {
            foreach ($this->defaultPage->elements->element as $element) {
                $elementClass = (string) $element->elementClass;
                $elementPlugin = (string) $element->elementPlugin;
                require_once "../ANM22WebBase/website/plugins/" . $elementPlugin . "/plugin.php";
                $elementObject = new $elementClass();
                $elementObject->importXML($element, $this);
                $this->elements["d" . intval($elementObject->getId())] = $elementObject;
            }
        }

        /* Conteiners Elements Index */
        if ($xml->conteiners) {
            foreach ($xml->conteiners->conteiner as $conteiner) {
                $conteinerId = (string) $conteiner->id;
                $this->conteiners[$conteinerId] = array("items"=>array());
                if (@intval($conteiner->conteinerDefault) == 1) {
                    $this->conteiners[$conteinerId]['defaultContainer'] = true;
                    foreach ($this->defaultPage->conteiners->conteiner as $defaultConteiner) {
                        if (((string) $defaultConteiner->id) == $conteinerId) {
                            if ($defaultConteiner->item) {
                                foreach ($defaultConteiner->item as $item) {
                                    $this->conteiners[$conteinerId]['items'][] = "d" . intval($item);
                                }
                            }
                            break;
                        }
                    }
                } else {
                    if ($conteiner->item) {
                        foreach ($conteiner->item as $item) {
                            $this->conteiners[$conteinerId]['items'][] = intval($item);
                        }
                    }
                }
            }
        }
    }

    public function loadByXMLFile($url) {
        if (file_exists($url)) {
            $this->xml = @simplexml_load_file($url);
        } else {
            header("Location: ../404/");
            exit();
        }
        $this->importXML($this->xml);
    }

    public function loadByPage($lang, $page, $get, $post) {
        $this->getVariables = $get;
        $this->postVariables = $post;
        $url = "../ANM22WebBase/website/" . $lang . "/" . $page . ".xml";
        $this->loadByXMLFile($url);
    }

    public function pageShow($args) {
        if (!isset($args['get']['page'])) {
            $args['get']['page'] = "index";
        }
        $this->loadByPage($args['lang'], $args['get']['page'], $args['get'], $args['post']);
        if ($this->state != "public" and $this->state) {
            if ($_SESSION['com_anm22_wb_login']) {
                /* include "../ANM22WebBase/config/license.php";
                  if ($_SESSION['com_anm22_wb_website_'.$anm22_wb_license]>intval(trim($this->state)))
                  {
                  header( "Location: ../login/" );
                  exit;
                  } */
            } else {
                header("Location: ../login/?permissioneDenied=1" . $_SESSION['com_anm22_wb_login']);
                exit;
            }
        }
        $this->show();
    }

    public function show() {
        include "../ANM22WebBase/website/template/" . $this->theme . "/" . $this->template . ".php";
    }

    public function getHead() {
        echo "<title>";
            if ($this->site_name and ( $this->site_name != "")) { 
                echo $this->site_name . " - ";
            }
            echo $this->title;
        echo "</title>";
        echo '<meta name="robots" content="index, follow">';
        if ($this->site_name and ( $this->site_name != "")) {
            echo '<meta property="og:site_name" content="' . $this->site_name . '"/>';
        }
        echo '<meta property="og:title" content="' . $this->title . '"/>';
        echo '<meta itemprop="name" content="' . $this->title . '"/>';
        echo '<meta name="twitter:title" content="' . $this->title . '"/>';
        echo '<meta property="og:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"/>';
        echo '<meta name="twitter:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"/>';
        echo '<meta property="og:type" content="' . $this->og_type . '"/>';
        if ($this->article_publisher and ( $this->article_publisher != "")) {
            echo '<meta property="article:publisher" content="' . $this->article_publisher . '"/>';
        }
        if ($this->publisher and ( $this->publisher != "")) {
            echo '<link rel="publisher" href="' . $this->publisher . '"/>';
        }
        if ($this->twitter_site and ( $this->twitter_site != "")) {
            echo '<meta name="twitter:site" content="' . $this->twitter_site . '"/>';
        }
        if ($this->description and ( $this->description != "")) {
            echo '<meta property="og:description" content="' . $this->description . '"/>';
            echo '<meta name="description" content="' . $this->description . '"/>';
            echo '<meta itemprop="description" content="' . $this->description . '"/>';
            echo '<meta name="twitter:description" content="' . $this->description . '"/>';
        }
        if ($this->image and ( $this->image != "")) {
            echo '<meta property="og:image" content="' . $this->image . '"/>';
            echo '<meta itemprop="image" content="' . $this->image . '"/>';
            echo '<meta name="twitter:image:src" content="' . $this->image . '"/>';
        }
        echo '<meta name="twitter:card" content="' . $this->twitter_card . '"/>';
        echo '<meta name="revisit-after" content="3 day" />';
        echo $this->getHeadContent();
    }
    
    public function showContainer($number) {
        if ($this->conteiners[$number] and $this->conteiners[$number]['items']) {
            foreach ($this->conteiners[$number]['items'] as $item) {
                if (isset($this->conteiners[$number]['defaultContainer']) and $this->conteiners[$number]['defaultContainer']) {
                    $this->elements[$item]->setDefaultPageElement(true);
                }
                $this->elements[$item]->show();
            }
        }
    }
    
    public function getPageTheme() {
        return $this->theme;
    }

    public function getPageTemplate() {
        return $this->template;
    }

    public function getThemeFolderRelativePHPURL() {
        $url = $this->getHomeFolderRelativePHPURL();
        $url .= "ANM22WebBase/website/template/" . $this->theme . "/";
        return $url;
    }

    public function getThemeFolderRelativeHTMLURL() {
        $url = $this->getHomeFolderRelativeHTMLURL();
        $url .= "ANM22WebBase/website/template/" . $this->theme . "/";
        return $url;
    }

    public function getHomeFolderRelativePHPURL() {
        return "../";
    }

    public function getHomeFolderRelativeHTMLURL() {
        $url = "../" . $this->getLanguageHomeFolderRelativeHTMLURL();
        return $url;
    }

    public function getLanguageHomeFolderRelativePHPURL() {
        return "";
    }

    public function getLanguageHomeFolderRelativeHTMLURL() {
        $url = "";
        if (($this->link != "index") and ( $this->link != "")) {
            $url .= "../";
        }
        if ($this->getPageSubLink()) {
            $url .= "../";
        }
        return $url;
    }

    public function getPageOption($name) {
        if (isset($this->pageOptions[$name])) {
            return $this->pageOptions[$name];
        } else {
            return null;
        }
    }

    public function getPageLanguage() {
        return $this->language;
    }

    public function getPageLink() {
        return $this->link;
    }

    public function getPageSubLink() {
        if (isset($this->getVariables['sub'])) {
            return $this->getVariables['sub'];
        } else {
            return null;
        }
    }

    public function getLayoutContainerVisibility($container) {
        switch ($container) {
            case "h":
                return $this->layout_header;
                break;
            case "l":
                return $this->layout_leftside;
                break;
            case "b":
                return $this->layout_body;
                break;
            case "r":
                return $this->layout_rightside;
                break;
            case "f":
                return $this->layout_footer;
                break;
            default :
                return 1;
        }
    }
    
    public function getHeadContent() {
        return $this->headContent;
    }
    
    public function setHeadContent($code) {
        $this->headContent = $code;
    }
    
    public function addContentToHead($code) {
        $this->headContent .= $code;
        return $this;
    }

}