<?php

namespace Infrastructure\Library;

use Infrastructure\Library\DomDocumentFactory;
use Infrastructure\Library\FileLocation;
use Infrastructure\Library\InputException;
use Infrastructure\Library\InvalidXmlException;

use DOMDocument;
use libXMLError;

class DomDocumentLoader
{
    ### COLLABORATORS

    /**
     * @var DomDocumentFactory
     */
    private $domDocumentFactory;

    ### CONSTRUCTOR

    public function __construct(
        DomDocumentFactory $domDocumentFactory
    )
    {
        $this->domDocumentFactory = $domDocumentFactory;
    }

    ### PUBLIC METHODS

    /**
     * @param string $xmlDocument
     * @param FileLocation|null $xsdLocation
     * @return DOMDocument
     * @throws InputException
     * @throws InvalidXmlException
     */
    public function load(
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
        
        $domDocument = $this->domDocumentFactory->create();
        
        $previousXmlErrorsSetting = libxml_use_internal_errors(true);
        $domDocument->loadXml($xmlDocument);
//        libxml_clear_errors();
//        libxml_use_internal_errors($previousXmlErrorsSetting);
//        
//        if ($xsdLocation !== null) {
//            
//            $previousXmlErrorsSetting = libxml_use_internal_errors(true);
//            $isValid = $domDocument->schemaValidate($xsdLocation);
//            $lastError = libxml_get_last_error();
//            libxml_clear_errors();
//            libxml_use_internal_errors($previousXmlErrorsSetting);
//            
//            if (!$isValid) {
//                if ($lastError instanceof libXMLError) {
//                    $lastError = $lastError->message;
//                }
//
//                throw new InvalidXmlException('XML Invalid: '.$lastError);
//            }
//        }
        
        return $domDocument;
    }
}
