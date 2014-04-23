<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\HealthWelfareApplication;

use Infrastructure\Library\FileLocation;
use Infrastructure\Library\XmlSerializerInterface;
use Infrastructure\Library\XmlValidatorInterface;

class HealthWelfareApplicationXmlSerializer
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
    const CONTENT_TYPE = 'application/vnd.opg.lpa.application.health-welfare+xml';

    /**
     * @var string
     */
    const XSD_LOCATION = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareApplication.xsd';

    ### PUBLIC METHODS

    /**
     * @return string Valid XML
     */
    public function serialize(
        HealthWelfareApplication $application
    )
    {
        $xmlDocument = ('<application>'.$this->xmlSerializer->serialize($application).'</application>');

        $xsdFileLocation = new FileLocation(self::XSD_LOCATION);
        $this->xmlValidator->validate($xmlDocument, $xsdFileLocation);

        return $xmlDocument;
    }
}
