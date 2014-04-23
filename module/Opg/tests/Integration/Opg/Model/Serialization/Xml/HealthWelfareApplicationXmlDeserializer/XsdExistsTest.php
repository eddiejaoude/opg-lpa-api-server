<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;

use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class XsdExistsTest extends TestCase
{
    public function testXsdExistsTest()
    {
        $this->assertFileExists(HealthWelfareApplicationXmlDeserializer::XSD_LOCATION);
    }
}
