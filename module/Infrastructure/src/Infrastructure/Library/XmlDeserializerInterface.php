<?php

namespace Infrastructure\Library;

use Infrastructure\Library\XmlDeserializerException;

interface XmlDeserializerInterface
{
    /**
     * @param string $classOrInterfaceName Class or Interface to deserialize into
     * @param string $xmlDocument Valid XML document
     * @param array $interfaceToClassMap Class implementations of Interfaces to be deserialized
     * @throws XmlDeserializerException
     */
    public function deserialize(
        $classOrInterfaceName,
        $xmlDocument,
        array $interfaceToClassMap = array()
    );
}
