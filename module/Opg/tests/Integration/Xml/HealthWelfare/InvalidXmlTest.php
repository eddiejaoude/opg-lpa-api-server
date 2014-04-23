<?php

namespace Tests\Integration\Xml\HealthWelfare;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class InvalidXmlTest extends TestCase
{
    
    public function testHealthWelfareApplicationIsInvalidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareApplication.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->loadXML('<badger />');
        $isValid = @$document->schemaValidate($xsdLocation);
        $this->assertFalse($isValid);
    }

    public function testHealthWelfareRegistrationIsInvalidXml()
    {
        $xsdLocation = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareRegistration.xsd';
        $this->assertFileExists($xsdLocation);

        $document = new DOMDocument();
        $document->loadXML('<badger />');
        $isValid = @$document->schemaValidate($xsdLocation);
        $this->assertFalse($isValid);
    }
}
