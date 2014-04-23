<?php

namespace Infrastructure\Library;

use SimpleXMLElement;

class XmlSerializer implements XmlSerializerInterface
{
    ### PUBLIC METHODS

    public function serialize(
        SerializableInterface $object
    )
    {
        $data = $object->getSerializableData();

        $simpleXml = new SimpleXMLElement('<data/>');
        $this->copyArrayToXml($data, $simpleXml);

        return $simpleXml->asXml();
    }

    ### PRIVATE METHODS

    private function copyArrayToXml(
        array $data,
        SimpleXMLElement $node
    )
    {
        foreach ($data as $name => $value) {
            $name = is_numeric($name) ? 'item' : $name;

            if (is_array($value)) {
                $childNode = $node->addChild($name, $value);
                $this->copyArrayToXml($value, $childNode);
            }

            if (is_scalar($value)) {
                $node->addChild($name, $value);
            }
        }
    }
}
