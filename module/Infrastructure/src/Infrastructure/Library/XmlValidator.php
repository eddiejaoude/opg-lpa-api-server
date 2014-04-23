<?php

namespace Infrastructure\Library;

use Infrastructure\Library\DomDocumentLoader;
use Infrastructure\Library\FileLocation;
use Infrastructure\Library\InputException;
use Infrastructure\Library\InvalidXmlException;

class XmlValidator implements XmlValidatorInterface
{
    ### COLLABORATORS

    /**
     * @var DomDocumentLoader
     */
    private $domDocumentLoader;

    ### CONSTRUCTOR

    public function __construct(
        DomDocumentLoader $domDocumentLoader
    )
    {
        $this->domDocumentLoader = $domDocumentLoader;
    }

    ### PUBLIC METHODS

    /**
     * @param string $xmlDocument
     * @param FileLocation|null $xsdLocation
     * @throws InputException
     * @throws InvalidXmlException
     */
    public function validate(
        $xmlDocument,
        FileLocation $xsdLocation = null
    )
    {
        if (!is_string($xmlDocument)) {
            throw new InputException('$xmlDocument must be of string type');
        }

        if (empty($xmlDocument)) {
            throw new InvalidXmlException('XML Invalid');
        }

        $this->domDocumentLoader->load($xmlDocument, $xsdLocation);
    }
}
