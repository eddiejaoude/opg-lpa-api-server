<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlDeserializer;

use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlDeserializer;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class XsdExistsTest extends TestCase
{
    public function testXsdExistsTest()
    {
        $this->assertFileExists(PropertyFinanceApplicationXmlDeserializer::XSD_LOCATION);
    }
}
