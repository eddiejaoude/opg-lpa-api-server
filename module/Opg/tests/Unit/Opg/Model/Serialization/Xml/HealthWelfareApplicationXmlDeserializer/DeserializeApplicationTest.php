<?php

namespace Tests\Unit\Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;

use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;

use PHPUnit_Framework_TestCase as TestCase;

class DeserializeApplicationTest extends TestCase
{
    public function testHealthWelfareApplicationIsDeserialized()
    {
        $xml = 'SOME_GOOD_XML';

        $mockApplicationInterfaceToClassMapProvider = $this->getMock('Opg\Model\Serialization\Xml\ApplicationInterfaceToClassMapProvider');

        $mockApplicationInterfaceToClassMapProvider->expects($this->once())
                                                   ->method('getInterfaceToClassMap')
                                                   ->will($this->returnValue(array('Map and be happy')));

        $mockXmlDeserializer = $this->getMock('Infrastructure\Library\XmlDeserializerInterface');

        $mockXmlDeserializer->expects($this->once())
                            ->method('deserialize')
                            ->with($this->equalTo(HealthWelfareApplicationXmlDeserializer::CLASS_NAME),
                                   $this->equalTo($xml),
                                   $this->equalTo(array('Map and be happy')))
                            ->will($this->returnValue('Moose'));

        $mockXmlValidator = $this->getMock('Infrastructure\Library\XmlValidatorInterface');

        $mockXmlValidator->expects($this->once())
                         ->method('validate')
                         ->with($this->equalTo($xml),
                                $this->equalTo(HealthWelfareApplicationXmlDeserializer::XSD_LOCATION));

        $applicationXmlDeserializer = new HealthWelfareApplicationXmlDeserializer($mockApplicationInterfaceToClassMapProvider, $mockXmlDeserializer, $mockXmlValidator);
        $application = $applicationXmlDeserializer->deserialize($xml);

        $this->assertEquals('Moose', $application);
    }
}
