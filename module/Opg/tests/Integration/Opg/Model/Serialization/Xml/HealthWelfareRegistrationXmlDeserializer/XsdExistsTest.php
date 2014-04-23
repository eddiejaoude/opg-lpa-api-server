<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;

use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class XsdExistsTest extends TestCase
{
    public function testXsdExistsTest()
    {
        $this->assertFileExists(HealthWelfareRegistrationXmlDeserializer::XSD_LOCATION);
    }
}
