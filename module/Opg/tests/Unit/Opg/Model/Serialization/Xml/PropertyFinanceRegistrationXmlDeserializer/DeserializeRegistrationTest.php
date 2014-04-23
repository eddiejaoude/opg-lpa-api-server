<?php

namespace Tests\Unit\Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;

use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;

use PHPUnit_Framework_TestCase as TestCase;

class DeserializeRegistrationTest extends TestCase
{
    public function testPropertyFinanceRegistrationIsDeserialized()
    {
        $xml = 'SOME_GOOD_XML';

        $mockRegistrationInterfaceToClassMapProvider = $this->getMock('Opg\Model\Serialization\Xml\RegistrationInterfaceToClassMapProvider');

        $mockRegistrationInterfaceToClassMapProvider->expects($this->once())
                                                    ->method('getInterfaceToClassMap')
                                                    ->will($this->returnValue(array('Map and be happy')));

        $mockXmlDeserializer = $this->getMock('Infrastructure\Library\XmlDeserializerInterface');

        $mockXmlDeserializer->expects($this->once())
                            ->method('deserialize')
                            ->with($this->equalTo(PropertyFinanceRegistrationXmlDeserializer::CLASS_NAME),
                                   $this->equalTo($xml),
                                   $this->equalTo(array('Map and be happy')))
                            ->will($this->returnValue('Moose'));

        $mockXmlValidator = $this->getMock('Infrastructure\Library\XmlValidatorInterface');

        $mockXmlValidator->expects($this->once())
                         ->method('validate')
                         ->with($this->equalTo($xml),
                                $this->equalTo(PropertyFinanceRegistrationXmlDeserializer::XSD_LOCATION));

        $applicationXmlDeserializer = new PropertyFinanceRegistrationXmlDeserializer($mockRegistrationInterfaceToClassMapProvider, $mockXmlDeserializer, $mockXmlValidator);
        $application = $applicationXmlDeserializer->deserialize($xml);

        $this->assertEquals('Moose', $application);
    }
}
