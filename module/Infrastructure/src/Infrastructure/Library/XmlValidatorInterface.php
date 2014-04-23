<?php

namespace Infrastructure\Library;

use Infrastructure\Library\FileLocation;
use Infrastructure\Library\InputException;
use Infrastructure\Library\InvalidXmlException;

interface XmlValidatorInterface
{
    /**
     * @param string $xmlDocument
     * @param FileLocation|null $xsdLocation
     * @throws InputException
     * @throws InvalidXmlException
     */
    public function validate(
        $xmlDocument,
        FileLocation $xsdLocation = null
    );
}
