<?php

namespace Tests\Integration\Xml\PropertyFinance;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class InvalidXmlTest extends TestCase
{
    public function testApplicationIsInvalidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceApplication.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->loadXML('<badger />');
        $isValid = @$document->schemaValidate($xsdLocation);
        $this->assertFalse($isValid);
    }

    public function testRegistrationIsInvalidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/PropertyFinanceRegistration.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->loadXML('<badger />');
        $isValid = @$document->schemaValidate($xsdLocation);
        $this->assertFalse($isValid);
    }
}
