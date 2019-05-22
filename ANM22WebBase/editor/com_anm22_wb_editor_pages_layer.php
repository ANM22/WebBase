<?php
class com_anm22_wb_editor_pages_layer {

    var $id;
    var $name;
    var $menuName = array();
    var $link;
    var $layerParent;
    var $layerIndex;
    var $pageObject;
    var $languages = array();
    var $pages = array();

    function importXML($xml, $layerIndex) {
        $this->layerIndex = $layerIndex;
        $this->id = $xml->id . "";
        $this->name = $xml->name . "";
        $this->link = $xml->link . "";
        $this->layerParent = $xml->layerParent . "";
        if (($xml->menuName->item->language != "") and $xml->menuName->item->language) {
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

}