<?php
class com_anm22_wb_editor_pages_index {

    var $lastId = 0;
    var $pages = array();
    var $layers = array();
    var $languages = array();

    function importXML($xml) {
        $this->lastId = intval($xml->lastId);
        foreach ($xml->languages->item as $item) {
            $this->languages[$item->language . ""] = $item->value;
        }
        foreach ($xml->pages->item as $item) {
            $this->pages[] = $item;
        }
        foreach ($xml->layers->item as $item) {
            $layer = new com_anm22_wb_editor_pages_layer();
            $layer->importXML($item, $this);
            $this->layers[$item->id . ""] = $layer;
        }
    }

}