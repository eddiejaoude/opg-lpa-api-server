<?php

namespace Tests\Integration\Xml\HealthWelfare;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class EmptyDocumentTest extends TestCase
{
    public function testEmptyApplicationIsValidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareApplication.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->load('module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/EmptyApplication.xml');
        $isValid = $document->schemaValidate($xsdLocation);
        $this->assertTrue($isValid);
    }

    ###

    public function testEmptyRegistrationIsValidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareRegistration.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->load('module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/EmptyRegistration.xml');
        $isValid = $document->schemaValidate($xsdLocation);
        $this->assertTrue($isValid);
    }
}
