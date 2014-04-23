<?php

namespace Tests\Integration\Xml\HealthWelfare;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class ValidateMinimalApplicationTest extends TestCase
{
    public function testHealthWelfareApplicationIsValidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareApplication.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->load('module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalApplication.xml');
        $isValid = $document->schemaValidate($xsdLocation);
        $this->assertTrue($isValid);
    }
}
