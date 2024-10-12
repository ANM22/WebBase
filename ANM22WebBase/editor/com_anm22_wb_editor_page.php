<?php

require_once __DIR__ . "/WebBaseXmlLogics.php";

/**
 * WebBase page
 */
class com_anm22_wb_editor_page
{

    public $id;
    public $name;
    public $link;
    public $title;
    public $language;
    public $state;
    public $xml;
    public $jsonAssoc;
    public $theme;
    public $template;
    public $templateInlineStyles = [];
    public $pageOptions = [];
    public $layout_header = 1;
    public $layout_footer = 1;
    public $layout_leftside = 0;
    public $layout_body = 1;
    public $layout_rightside = 0;
    public $containers = [];
    public $lastElementId = 0;
    public $elements = [];
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

    /**
     * @deprecated 
     * 
     * Init the page data from XML file
     * 
     * @param SimpleXMLElement $xml XML page data
     * @return void
     */
    public function importXML($xml)
    {
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

        /* Containers Elements Index */
        if ($xml->conteiners) {
            foreach ($xml->conteiners->conteiner as $container) {
                $containerId = (string) $container->id;
                $this->containers[$containerId] = ["items" => []];
                if (@intval($container->conteinerDefault) == 1) {
                    $this->containers[$containerId]['defaultContainer'] = true;
                    foreach ($this->defaultPage->conteiners->conteiner as $defaultContainer) {
                        if (((string) $defaultContainer->id) == $containerId) {
                            if ($defaultContainer->item) {
                                foreach ($defaultContainer->item as $item) {
                                    $this->containers[$containerId]['items'][] = "d" . intval($item);
                                }
                            }
                            break;
                        }
                    }
                } else {
                    if ($container->item) {
                        foreach ($container->item as $item) {
                            $this->containers[$containerId]['items'][] = intval($item);
                        }
                    }
                }
            }
        }
    }

    /**
     * Init the page data
     * 
     * @param mixed[] $data
     * @return void
     */
    public function initData($data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->link = $data['link'];
        $this->title = $data['title'];
        $this->language = $data['language'];
        $this->state = $data['state'];
        $this->theme = $data['theme'];
        $this->template = $data['template'];
        $this->layout_header = $data['layout']['header'];
        $this->layout_leftside = $data['layout']['leftside'];
        $this->layout_body = $data['layout']['body'];
        $this->layout_rightside = $data['layout']['rightside'];
        $this->layout_footer = $data['layout']['footer'];
        $this->lastElementId = $data['lastElementId'];

        $this->site_name = $data['site_name'];
        $this->og_type = $data['og_type'];
        $this->article_publisher = $data['article_publisher'];
        $this->publisher = $data['publisher'];
        $this->twitter_site = $data['twitter_site'];
        $this->description = $data['description'];
        $this->image = $data['image'];
        $this->twitter_card = $data['twitter_card'];

        $defaultPageJsonUrl = "../ANM22WebBase/website/" . $this->language . "/default.json";
        if (file_exists($defaultPageJsonUrl)) {
            $this->defaultPage = json_decode(file_get_contents($defaultPageJsonUrl), true);
        } else {
            $defaultPageXMLUrl = "../ANM22WebBase/website/" . $this->language . "/default.xml";
            $xmlDefaultPage = @simplexml_load_file($defaultPageXMLUrl);
            $this->defaultPage = WebBaseXmlLogics::xmlToAssoc($xmlDefaultPage);
        }

        /* Template Inline Styles */
        if (file_exists("../ANM22WebBase/website/template/" . $this->theme . "/" . $this->template . "_inlineStyles.php")) {
            require_once "../ANM22WebBase/website/template/" . $this->theme . "/" . $this->template . "_inlineStyles.php";
            $this->templateInlineStyles = $inlineStyles;
        }

        /* Template Page Options */
        if ($data['pageOptions']) {
            foreach ($data['pageOptions'] as $option) {
                $this->pageOptions[$option->name . ""] = $option;
            }
        }

        /* Elements Loading */
        if ($data['elements']) {
            foreach ($data['elements'] as $element) {
                $this->initElement($element, false);
            }
        }

        /* Default Elements Loading */
        if ($this->defaultPage) {
            if ($this->defaultPage['elements']) {
                foreach ($this->defaultPage['elements'] as $element) {
                    $this->initElement($element, true);
                }
            }
        }

        //* Containers elements indexes */
        if ($data['containers']) {
            foreach ($data['containers'] as $container) {
                $containerId = $container['id'];
                $this->containers[$containerId] = [
                    "id" => $containerId,
                    "items" => []
                ];
                // Container default content flag
                if ($container['containerDefault'] ?? false) {
                    $this->containers[$containerId]['defaultContainer'] = true;

                    foreach ($this->defaultPage['containers'] as $defaultContainer) {
                        if ($defaultContainer['id'] == $containerId) {
                            if ($defaultContainer['items']) {
                                foreach ($defaultContainer['items'] as $item) {
                                    $this->containers[$containerId]['items'][] = "d" . intval($item);
                                }
                            }
                            break;
                        }
                    }
                } else {
                    if ($container['items'] ?? false) {
                        foreach ($container['items'] as $item) {
                            $this->containers[$containerId]['items'][] = intval($item);
                        }
                    }
                }
            }
        }
    }

    public function initElement($element, $isDefaultElement = false)
    {
        $elementClass = $element['elementClass'];
        $elementPlugin = $element['elementPlugin'];
        require_once dirname(__DIR__) . "/website/plugins/" . $elementPlugin . "/plugin.php";
        $elementObject = new $elementClass();

        if (method_exists($elementObject, "initData")) {
            $elementObject->importData($element, $this);
        } else {
            $xmlElementData = WebBaseXmlLogics::assocToXml($element);
            $elementObject->importXML($xmlElementData, $this);
        }

        // Generate the element key
        if ($isDefaultElement) {
            $elementKey = "d" . $elementObject->getId();
        } else {
            $elementKey = intval($elementObject->getId());
        }

        // Add initialized element to the page object
        $this->elements[$elementKey] = $elementObject;
    }

    public function loadByPage($lang, $page, $get, $post)
    {
        $this->getVariables = $get;
        $this->postVariables = $post;

        // Page (JSON)
        $url = __DIR__ . "/../../ANM22WebBase/website/" . $lang . "/" . $page . ".json";
        if (file_exists($url)) {
            $this->jsonAssoc = json_decode(file_get_contents($url), true);
            $this->initData($this->jsonAssoc);
            return;
        }

        // Page (XML) - deprecated
        $url = __DIR__ . "/../../ANM22WebBase/website/" . $lang . "/" . $page . ".xml";
        if (file_exists($url)) {
            $this->xml = @simplexml_load_file($url);
            $this->jsonAssoc = WebBaseXmlLogics::xmlToAssoc($this->xml);
            $this->initData($this->jsonAssoc);
            return;
        }

        // 404 page (JSON)
        $url = __DIR__ . "/../../ANM22WebBase/website/" . $lang . "/404.json";
        if (file_exists($url)) {
            $this->jsonAssoc = json_decode(file_get_contents($url), true);
            $this->initData($this->jsonAssoc);
            return;
        }

        // 404 page (XML) - deprecated
        $url = __DIR__ . "/../../ANM22WebBase/website/" . $lang . "/404.xml";
        if (file_exists($url)) {
            $this->xml = @simplexml_load_file($url);
            $this->jsonAssoc = WebBaseXmlLogics::xmlToAssoc($this->xml);
            $this->initData($this->jsonAssoc);
            return;
        }
    }

    public function pageShow($args)
    {
        if (!isset($args['get']['page'])) {
            $args['get']['page'] = "index";
        }
        $this->loadByPage($args['lang'], $args['get']['page'], $args['get'], $args['post']);
        if ($this->state && $this->state != "public") {
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

    public function show()
    {
        // 404 Error code
        if ($this->getPageLink() == '404') {
            http_response_code(404);
        }
        include "../ANM22WebBase/website/template/" . $this->theme . "/" . $this->template . ".php";
    }

    public function getHead()
    {
        echo "<title>";
        if ($this->site_name && ($this->site_name != "")) {
            echo $this->site_name . " - ";
        }
        echo $this->title;
        echo "</title>";
        echo '<meta name="robots" content="index, follow">';
        if ($this->site_name && ($this->site_name != "")) {
            echo '<meta property="og:site_name" content="' . $this->site_name . '"/>';
        }
        echo '<meta property="og:title" content="' . $this->title . '"/>';
        echo '<meta itemprop="name" content="' . $this->title . '"/>';
        echo '<meta name="twitter:title" content="' . $this->title . '"/>';
        echo '<meta property="og:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"/>';
        echo '<meta name="twitter:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"/>';
        if ($this->og_type) {
            echo '<meta property="og:type" content="' . $this->og_type . '"/>';
        }
        if ($this->article_publisher && ($this->article_publisher != "")) {
            echo '<meta property="article:publisher" content="' . $this->article_publisher . '"/>';
        }
        if ($this->publisher && ($this->publisher != "")) {
            echo '<link rel="publisher" href="' . $this->publisher . '"/>';
        }
        if ($this->twitter_site && ($this->twitter_site != "")) {
            echo '<meta name="twitter:site" content="' . $this->twitter_site . '"/>';
        }
        if ($this->description && ($this->description != "")) {
            echo '<meta property="og:description" content="' . $this->description . '"/>';
            echo '<meta name="description" content="' . $this->description . '"/>';
            echo '<meta itemprop="description" content="' . $this->description . '"/>';
            echo '<meta name="twitter:description" content="' . $this->description . '"/>';
        }
        if ($this->image && ($this->image != "")) {
            echo '<meta property="og:image" content="' . $this->image . '"/>';
            echo '<meta itemprop="image" content="' . $this->image . '"/>';
            echo '<meta name="twitter:image:src" content="' . $this->image . '"/>';
        }
        if ($this->twitter_card ?? null) {
            echo '<meta name="twitter:card" content="' . $this->twitter_card . '"/>';
        }
        echo '<meta name="revisit-after" content="3 day" />';
        echo $this->getHeadContent();
    }

    public function showContainer($number)
    {
        if ($this->containers[$number] && $this->containers[$number]['items']) {
            foreach ($this->containers[$number]['items'] as $item) {
                if (isset($this->containers[$number]['defaultContainer']) && $this->containers[$number]['defaultContainer']) {
                    $this->elements[$item]->setDefaultPageElement(true);
                }
                $this->elements[$item]->show();
            }
        }
    }

    public function getPageTheme()
    {
        return $this->theme;
    }

    public function getPageTemplate()
    {
        return $this->template;
    }

    public function getThemeFolderRelativePHPURL()
    {
        $url = $this->getHomeFolderRelativePHPURL();
        $url .= "ANM22WebBase/website/template/" . $this->theme . "/";
        return $url;
    }

    public function getThemeFolderRelativeHTMLURL()
    {
        $url = $this->getHomeFolderRelativeHTMLURL();
        $url .= "ANM22WebBase/website/template/" . $this->theme . "/";
        return $url;
    }

    public function getHomeFolderRelativePHPURL()
    {
        return "../";
    }

    public function getHomeFolderRelativeHTMLURL()
    {
        include $url = $this->getHomeFolderRelativePHPURL() . "ANM22WebBase/config/license.php";
        if (isset($anm22_wb_license_language_mode) && ($anm22_wb_license_language_mode == "mono")) {
            $url = $this->getLanguageHomeFolderRelativeHTMLURL();
        } else {
            $url = "../" . $this->getLanguageHomeFolderRelativeHTMLURL();
        }
        return $url;
    }

    public function getLanguageHomeFolderRelativePHPURL()
    {
        return "";
    }

    public function getLanguageHomeFolderRelativeHTMLURL()
    {
        $url = "";
        if (($this->link != "index") and ($this->link != "")) {
            $url .= "../";
        }
        for ($i = 1; $i <= $this->getPageSubLinkNumber(); $i++) {
            if ($this->getPageSubLink($i)) {
                $url .= "../";
            }
        }
        return $url;
    }

    public function getPageOption($name)
    {
        if (isset($this->pageOptions[$name])) {
            return $this->pageOptions[$name];
        } else {
            return null;
        }
    }

    public function getPageLanguage()
    {
        return $this->language;
    }

    public function getPageLink()
    {
        return $this->link;
    }

    /**
     * Get permalink sub directory
     * 
     * @param integer $level Sub directory level
     * @return string|null
     */
    public function getPageSubLink($level = 1)
    {
        if ($level <= 1) {
            if (isset($this->getVariables['sub'])) {
                return $this->getVariables['sub'];
            } else {
                return null;
            }
        } else {
            if (isset($this->getVariables['sub' . $level])) {
                return $this->getVariables['sub' . $level];
            } else {
                return null;
            }
        }
    }

    /**
     * Get last route subdirectory
     * 
     * @return string|null
     */
    public function getPageLastSubLink()
    {
        for ($i = $this->getPageSubLinkNumber(); $i >= 1; $i--) {
            if ($this->getPageSubLink($i)) {
                return $this->getPageSubLink($i);
            }
        }
        return null;
    }

    /**
     * Get number of route subdirectories
     * 
     * @return integer
     */
    public function getPageSubLinkNumber()
    {
        for ($i = 3; $i >= 1; $i--) {
            if ($this->getPageSubLink($i)) {
                return $i;
            }
        }
        return 0;
    }

    public function getLayoutContainerVisibility($container)
    {
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
            default:
                return 1;
        }
    }

    public function getHeadContent()
    {
        return $this->headContent;
    }

    public function setHeadContent($code)
    {
        $this->headContent = $code;
    }

    public function addContentToHead($code)
    {
        $this->headContent .= $code;
        return $this;
    }
}
