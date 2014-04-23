<?php

namespace Tests\Integration\Xml\PropertyFinance;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class EmptyDocumentTest extends TestCase
{
    public function testEmptyApplicationIsValidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceApplication.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->load('module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/EmptyApplication.xml');
        $isValid = $document->schemaValidate($xsdLocation);
        $this->assertTrue($isValid);
    }

    ###

    public function testEmptyRegistrationIsValidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceRegistration.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->load('module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/EmptyRegistration.xml');
        $isValid = $document->schemaValidate($xsdLocation);
        $this->assertTrue($isValid);
    }
}
