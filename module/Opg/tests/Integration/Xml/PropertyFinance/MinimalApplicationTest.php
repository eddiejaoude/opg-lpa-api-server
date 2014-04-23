<?php

namespace Tests\Integration\Xml\PropertyFinance;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class ValidateMinimalApplicationTest extends TestCase
{
    public function testApplicationIsValidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceApplication.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->load('module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalApplication.xml');
        $isValid = $document->schemaValidate($xsdLocation);
        $this->assertTrue($isValid);
    }
}
