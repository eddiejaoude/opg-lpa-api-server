<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;

use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class XsdExistsTest extends TestCase
{
    public function testXsdExistsTest()
    {
        $this->assertFileExists(PropertyFinanceRegistrationXmlDeserializer::XSD_LOCATION);
    }
}
