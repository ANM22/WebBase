<?php

/**
 * WebBase page layer
 */
class com_anm22_wb_editor_pages_layer
{

    var $id;
    var $name;
    var $menuName = array();
    var $link;
    var $layerParent;
    var $layerIndex;
    var $pageObject;
    var $languages = array();
    var $pages = array();

    /**
     * @deprecated since editor version 3.0
     * 
     * Method to init the page layer.
     * This method will be replaced with initData method.
     * 
     * @param SimpleXMLElement $xml Layer data
     * @param int $layerIndex Layer index
     * @return void
     */
    function importXML($xml, $layerIndex)
    {
        $this->layerIndex = $layerIndex;
        $this->id = $xml->id . "";
        $this->name = $xml->name . "";
        $this->link = $xml->link . "";
        $this->layerParent = $xml->layerParent . "";
        if (($xml->menuName->item->language != "") && $xml->menuName->item->language) {
            foreach ($xml->menuName->item as $item) {
                $this->menuName[$item->language . ""] = $item->value;
            }
        }
        foreach ($xml->languages->item as $item) {
            $this->languages[$item->language . ""] = $item->value;
        }
        foreach ($xml->pages->item as $item) {
            $this->pages[] = $item;
        }
    }

    /**
     * Method to init the page layer
     * 
     * @param mixed[] $data Layer data
     * @param int $layerIndex Layer index
     * @return void
     */
    public function initData($data, $layerIndex)
    {
        $this->layerIndex = $layerIndex;
        $this->id = $data['id'] . "";
        $this->name = $data['name'] . "";
        $this->link = $data['link'] . "";
        $this->layerParent = $data['layerParent'] . "";
        if ($data['menuName']) {
            foreach ($data['menuName'] as $item) {
                $this->menuName[$item['language'] . ""] = $item['value'];
            }
        }
        foreach ($data['languages'] as $item) {
            $this->languages[$item['language'] . ""] = $item['value'];
        }
        foreach ($data['pages'] as $item) {
            $this->pages[] = $item;
        }
    }
}
