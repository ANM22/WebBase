<?php

/**
 * WebBase page element
 */
class com_anm22_wb_editor_page_element
{

    var $id;
    var $elementClass = "com_anm22_wb_editor_page_element";
    var $elementPlugin = "com_anm22_wb_editor";
    var $page = null;
    var $defaultPageElement = false;

    function getId()
    {
        return $this->id;
    }

    function setId($newId)
    {
        $this->id = $newId;
    }

    function getElementClass()
    {
        return $this->elementClass;
    }

    function getElementPlugin()
    {
        return $this->elementPlugin;
    }

    /**
     * @deprecated since editor 3.0
     * 
     * Method to init element data by XML object.
     * This method is deprecated and it will replaced with initData method.
     * 
     * @param SimpleXMLElement $xml Element data
     * @param com_anm22_wb_editor_page $page Page
     * @return void
     */
    function importXML($xml, $page)
    {
        $this->id = $xml->id;
        $this->page = $page;
        $this->elementClass = $xml->elementClass;
        $this->elementPlugin = $xml->elementPlugin;
        $this->importXMLdoJob($xml);
    }

    /**
     * @deprecated since editor 3.0
     * 
     * @param SimpleXMLElement $xml Element data
     * @return void
     */
    function importXMLdoJob($xml) {}

    /**
     * Method to import and init element data
     * 
     * @param mixed[] $data Element data
     * @param com_anm22_wb_editor_page $page Page
     * @return void
     */
    function importData($data, $page)
    {
        $this->id = $data['id'];
        $this->page = $page;
        $this->elementClass = $data['elementClass'];
        $this->elementPlugin = $data['elementPlugin'];
        $this->initData($data);
    }

    /**
     * Method to init element data
     * 
     * @param mixed[] $data Element data
     * @return void
     */
    function initData($data) {}

    /**
     * Method to render the element
     */
    function show() {}

    /**
     * Get element parent page
     * 
     * @return com_anm22_wb_editor_page|null
     */
    function getPage()
    {
        return $this->page;
    }

    /**
     * Set element parent page
     * 
     * @param com_anm22_wb_editor_page|null $page Page
     * @retutn void
     */
    function setPage($page)
    {
        $this->page = $page;
    }

    function getDefaultPageElement()
    {
        return $this->defaultPageElement;
    }

    function setDefaultPageElement($defaultPageElement)
    {
        $this->defaultPageElement = $defaultPageElement;
    }
}
