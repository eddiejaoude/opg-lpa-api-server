<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\PropertyFinanceApplication;

use Infrastructure\Library\FileLocation;
use Infrastructure\Library\XmlSerializerInterface;
use Infrastructure\Library\XmlValidatorInterface;

class PropertyFinanceApplicationXmlSerializer
{
    ### COLLABORATORS

    /**
     * @var XmlSerializerInterface
     */
    private $xmlSerializer;

    /**
     * @var XmlValidatorInterface
     */
    private $xmlValidator;

    ### CONSTRUCTOR

    public function __construct(
        XmlSerializerInterface $xmlSerializer,
        XmlValidatorInterface $xmlValidator
    )
    {
        $this->xmlSerializer = $xmlSerializer;
        $this->xmlValidator  = $xmlValidator;
    }

    ### PUBLIC MEMBERS

    /**
     * @var string
     */
    const CONTENT_TYPE = 'application/vnd.opg.lpa.application.property-finance+xml';

    /**
     * @var string
     */
    const XSD_LOCATION = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceApplication.xsd';

    ### PUBLIC METHODS

    /**
     * @return string Valid XML
     */
    public function serialize(
        PropertyFinanceApplication $application
    )
    {
        $xmlDocument = ('<application>'.$this->xmlSerializer->serialize($application).'</application>');

        $xsdFileLocation = new FileLocation(self::XSD_LOCATION);
        $this->xmlValidator->validate($xmlDocument, $xsdFileLocation);

        return $xmlDocument;
    }
}
