<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\ApplicationMetadata;
use Infrastructure\Library\XmlSerializerInterface;
use Infrastructure\Library\XmlValidatorInterface;

class ApplicationMetadataXmlSerializer
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
        $this->xmlValidator = $xmlValidator;
    }

    ### PUBLIC MEMBERS

    /**
     * @var string
     */
    const CONTENT_TYPE = 'application/vnd.opg.lpa.application-metadata+xml';

    ### PUBLIC METHODS

    /**
     * @return string Valid XML
     */
    public function serialize(
        ApplicationMetadata $applicationMetadata
    )
    {
        $xml = ('<application-metadata>'.$this->xmlSerializer->serialize($applicationMetadata).'</application-metadata>');
        $this->xmlValidator->validate($xml/*, self::XSD_LOCATION*/);
        return $xml;
    }
}
