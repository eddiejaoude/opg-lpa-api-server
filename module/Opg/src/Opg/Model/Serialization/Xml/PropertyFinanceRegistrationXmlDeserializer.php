<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\PropertyFinanceRegistration;
use Opg\Model\Serialization\Xml\RegistrationInterfaceToClassMapProvider;
use Infrastructure\Library\FileLocation;
use Infrastructure\Library\XmlDeserializerInterface;
use Infrastructure\Library\XmlValidatorInterface;

class PropertyFinanceRegistrationXmlDeserializer
{
    ### COLLABORATORS

    /**
     * @var RegistrationInterfaceToClassMapProvider
     */
    private $interfaceToClassMapProvider;

    /**
     * @var XmlDeserializerInterface
     */
    private $xmlDeserializer;

    /**
     * @var XmlValidatorInterface
     */
    private $xmlValidator;

    ### CONSTRUCTOR

    public function __construct(
        RegistrationInterfaceToClassMapProvider $interfaceToClassMapProvider,
        XmlDeserializerInterface $xmlDeserializer,
        XmlValidatorInterface $xmlValidator
    )
    {
        $this->interfaceToClassMapProvider = $interfaceToClassMapProvider;
        $this->xmlDeserializer = $xmlDeserializer;
        $this->xmlValidator = $xmlValidator;
    }

    ### PUBLIC MEMBERS

    /**
     * @var string
     */
    const CLASS_NAME = '\Opg\Model\Element\PropertyFinanceRegistration';

    /**
     * @var string
     */
    const XSD_LOCATION = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceRegistration.xsd';

    ### PUBLIC METHODS

    /**
     * @param string $xmlDocument
     * @return PropertyFinanceRegistration
     * @throws InputException When $xmlDocument is not of string type
     */
    public function deserialize(
        $xmlDocument
    )
    {
        if (!is_string($xmlDocument)) {
            throw new InputException('$xmlDocument must be of string type');
        }

        $xsdFileLocation = new FileLocation(self::XSD_LOCATION);
        $this->xmlValidator->validate($xmlDocument, $xsdFileLocation);

        $interfaceToClassMap = $this->interfaceToClassMapProvider->getInterfaceToClassMap();

        return $this->xmlDeserializer->deserialize(
            self::CLASS_NAME,
            $xmlDocument,
            $interfaceToClassMap
        );
    }
}
