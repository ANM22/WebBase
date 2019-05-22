<?php
class com_anm22_wb_editor_page_element {

    var $id;
    var $elementClass = "com_anm22_wb_editor_page_element";
    var $elementPlugin = "com_anm22_wb_editor";
    var $page;
    var $defaultPageElement = false;

    function getId() {
        return $this->id;
    }

    function setId($newId) {
        $this->id = $newId;
    }

    function getElementClass() {
        return $this->elementClass;
    }

    function getElementPlugin() {
        return $this->elementPlugin;
    }

    function importXML($xml, $page) {
        $this->id = $xml->id;
        $this->page = $page;
        $this->elementClass = $xml->elementClass;
        $this->elementPlugin = $xml->elementPlugin;
        $this->importXMLdoJob($xml);
    }

    function importXMLdoJob($xml) {
        
    }

    function show() {
        
    }
    
    
    function getPage() {
        return $this->page;
    }

    function setPage($page) {
        $this->page = $page;
    }

    function getDefaultPageElement() {
        return $this->defaultPageElement;
    }

    function setDefaultPageElement($defaultPageElement) {
        $this->defaultPageElement = $defaultPageElement;
    }

}