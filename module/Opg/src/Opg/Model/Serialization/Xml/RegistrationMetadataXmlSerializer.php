<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\RegistrationMetadata;
use Infrastructure\Library\XmlSerializerInterface;
use Infrastructure\Library\XmlValidatorInterface;

class RegistrationMetadataXmlSerializer
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
    const CONTENT_TYPE = 'application/vnd.opg.lpa.registration-metadata+xml';

    ### PUBLIC METHODS

    /**
     * @return string Valid XML
     */
    public function serialize(
        RegistrationMetadata $registrationMetadata
    )
    {
        $xml = ('<registration-metadata>'.$this->xmlSerializer->serialize($registrationMetadata).'</registration-metadata>');
        $this->xmlValidator->validate($xml/*, self::XSD_LOCATION*/);
        return $xml;
    }
}
