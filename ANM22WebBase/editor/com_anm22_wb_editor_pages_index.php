<?php

/**
 * WebBase pages index
 */
class com_anm22_wb_editor_pages_index
{

    public $lastId = 0;
    public $pages = [];
    public $layers = [];
    public $languages = [];
    public $domain = null;

    /**
     * @deprecated 
     * 
     * Init the pages index data from XML file
     * 
     * @param SimpleXMLElement $xml XML page data
     * @return void
     */
    function importXML($xml)
    {
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

    /**
     * Method to init the pages index
     * 
     * @param mixed[] $data Pages index data
     * @return void
     */
    public function initData($data)
    {
        if (isset($data['domain']) && $data['domain']) {
            $this->domain = $data['domain'];
        }
        $this->lastId = intval($data['lastId']);
        foreach ($data['languages'] as $item) {
            $this->languages[$item['language'] . ""] = $item['value'];
        }
        foreach ($data['pages'] as $item) {
            $this->pages[] = $item;
        }
        foreach ($data['layers'] as $item) {
            $layer = new com_anm22_wb_editor_pages_layer();
            $layer->initData($item, $this);
            $this->layers[$item['id'] . ""] = $layer;
        }
    }
}
