<?php

namespace Tests\Unit\Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;

use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;

use PHPUnit_Framework_TestCase as TestCase;

class DeserializeRegistrationTest extends TestCase
{
    public function testHealthWelfareRegistrationIsDeserialized()
    {
        $xml = 'SOME_GOOD_XML';

        $mockRegistrationInterfaceToClassMapProvider = $this->getMock('Opg\Model\Serialization\Xml\RegistrationInterfaceToClassMapProvider');

        $mockRegistrationInterfaceToClassMapProvider->expects($this->once())
                                                    ->method('getInterfaceToClassMap')
                                                    ->will($this->returnValue(array('Map and be happy')));

        $mockXmlDeserializer = $this->getMock('Infrastructure\Library\XmlDeserializerInterface');

        $mockXmlDeserializer->expects($this->once())
                            ->method('deserialize')
                            ->with($this->equalTo(HealthWelfareRegistrationXmlDeserializer::CLASS_NAME),
                                   $this->equalTo($xml),
                                   $this->equalTo(array('Map and be happy')))
                            ->will($this->returnValue('Moose'));

        $mockXmlValidator = $this->getMock('Infrastructure\Library\XmlValidatorInterface');

        $mockXmlValidator->expects($this->once())
                         ->method('validate')
                         ->with($this->equalTo($xml),
                                $this->equalTo(HealthWelfareRegistrationXmlDeserializer::XSD_LOCATION));

        $applicationXmlDeserializer = new HealthWelfareRegistrationXmlDeserializer($mockRegistrationInterfaceToClassMapProvider, $mockXmlDeserializer, $mockXmlValidator);
        $application = $applicationXmlDeserializer->deserialize($xml);

        $this->assertEquals('Moose', $application);
    }
}
