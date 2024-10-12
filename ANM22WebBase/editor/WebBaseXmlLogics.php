<?php

class WebBaseXmlLogics
{
    /**
     * Convert associative array to a XML object
     * 
     * @param mixed[] $associativeArray $data to convert
     * @return SimpleXMLElement
     */
    public static function assocToXml($associativeArray)
    {
        // Create a new SimpleXMLElement object
        $xmlData = new SimpleXMLElement('<?xml version="1.0"?><root></root>');

        // Call the method to convert the array
        self::recursiveAssocToXml($associativeArray, $xmlData);

        return $xmlData;
    }

    public static function recursiveAssocToXml($data, &$xmlData)
    {
        foreach ($data as $key => $value) {
            // Manage the tag of the array items
            if (is_numeric($key)) {
                $key = 'item';
            }

            // If the value is an array, apply the recursive conversion
            if (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                self::recursiveAssocToXml($value, $subnode);
            } else {
                // Add element as a XML tag
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * Convert XML object to an associative array 
     * 
     * @param SimpleXMLElement $xmlObject to convert
     * @return mixed[]
     */
    public static function xmlToAssoc($xmlObject)
    {
        return self::recursiveXmlToAssoc($xmlObject);
    }

    public static function recursiveXmlToAssoc($xmlObject, $out = [])
    {
        foreach ((array)$xmlObject as $index => $node) {
            if (is_object($node)) {
                // Property name exceptions
                if ($index === 'defaultConteiners') {
                    $out['defaultContainer'] = empty($node) ? false : true;
                } else if ($index === 'conteinerDefault') {
                    $out['containerDefault'] = empty($node) ? false : true;
                } else {
                    if ($index == "conteiners") {
                        $index = "containers";
                    }

                    // If the node is a SimpleXMLElement object, call the method again
                    $out[$index] = self::recursiveXmlToAssoc($node);
                }
            } else {
                if ($index === 'defaultConteiners') {
                    $out['defaultContainer'] = true;
                } else if ($index === 'conteinerDefault') {
                    $out['containerDefault'] = true;
                } else if ($index == "item") {
                    if (is_string($node)) {
                        $out["items"] = [intval($node)];
                    } else {
                        $out["items"] = $node;
                    }
                } else if ($index == "newsColumns" || $index == "newsRows") {
                    $out[$index] = intval($node);
                } else if (is_array($node)) {
                    $out = [];
                    foreach ($node as $key => $nodeArrayItem) {
                        if (is_object($nodeArrayItem)) {
                            $out[$key] = self::recursiveXmlToAssoc($nodeArrayItem);
                        } else {
                            $out[$key] = $nodeArrayItem;
                        }
                    }
                } else {
                    $out[$index] = $node;
                }
            }
        }
        return $out;
    }

    /**
     * Convert XML object to an associative array 
     * 
     * @param SimpleXMLElement $xmlObject to convert
     * @return mixed[]
     */
    public static function pagesIndexXmlToAssoc($xmlObject)
    {
        return self::pagesIndexRecursiveXmlToAssoc($xmlObject);
    }

    public static function pagesIndexRecursiveXmlToAssoc($xmlObject, $out = [])
    {
        foreach ((array)$xmlObject as $index => $node) {
            if (is_object($node)) {
                // Property name exceptions
                if ($index === 'defaultConteiners') {
                } else {
                    if ($index == "conteiners") {
                        $index = "containers";
                    }

                    // If the node is a SimpleXMLElement object, call the method again
                    $out[$index] = self::pagesIndexRecursiveXmlToAssoc($node);
                }
            } else {
                if (is_array($node)) {
                    $out = [];
                    foreach ($node as $key => $nodeArrayItem) {
                        if (is_object($nodeArrayItem)) {
                            $out[$key] = self::pagesIndexRecursiveXmlToAssoc($nodeArrayItem);
                        } else {
                            $out[$key] = $nodeArrayItem;
                        }
                    }
                } else if ($index == "id" || $index == "lastId" || $index == "layerParent") {
                    $out[$index] = intval($node);
                } else {
                    $out[$index] = $node;
                }
            }
        }
        return $out;
    }
}
