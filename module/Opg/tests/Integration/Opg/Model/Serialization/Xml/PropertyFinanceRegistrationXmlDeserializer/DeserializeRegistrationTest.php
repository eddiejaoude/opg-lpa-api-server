<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;

use Opg\Model\Element\Attorney;
use Opg\Model\Element\Donor;
use Opg\Model\Element\PropertyFinanceRegistration;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;
use Opg\Model\Serialization\Xml\RegistrationInterfaceToClassMapProvider;
use Infrastructure\Library\DomDocumentFactory;
use Infrastructure\Library\DomDocumentLoader;
use Infrastructure\Library\HyphenatedWordsToCamelCaseConversionStrategy;
use Infrastructure\Library\XmlDeserializer;
use Infrastructure\Library\XmlValidator;

use PHPUnit_Framework_TestCase as TestCase;

class DeserializeRegistrationTest extends TestCase
{
    public function testPropertyFinanceRegistrationIsDeserialized()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $domDocumentLoader  = new DomDocumentLoader($domDocumentFactory);

        $interfaceToClassMapProvider = new RegistrationInterfaceToClassMapProvider();

        $nodeToParameterNameConversionStrategy = new HyphenatedWordsToCamelCaseConversionStrategy();
        $xmlDeserializer = new XmlDeserializer($domDocumentLoader, $nodeToParameterNameConversionStrategy);
        $xmlValidator    = new XmlValidator($domDocumentLoader);

        $xmlDocument = file_get_contents('module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalRegistration.xml');

        $registrationXmlDeserializer = new PropertyFinanceRegistrationXmlDeserializer($interfaceToClassMapProvider, $xmlDeserializer, $xmlValidator);
        $registration = $registrationXmlDeserializer->deserialize($xmlDocument);

        $this->assertTrue($registration instanceof PropertyFinanceRegistration);
        $this->assertTrue(count($registration->getApplicants()) == 1);
        $this->assertTrue($registration->getApplicants()[0] instanceof Donor);
        $this->assertTrue($registration->getApplicants()[0]->getName()->getForename() == 'Donor');
        // @todo Assert deserialized object here
    }

    ###

    public function testPropertyFinanceRegistrationIsDeserializedWithMultipleApplicants()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $domDocumentLoader  = new DomDocumentLoader($domDocumentFactory);

        $interfaceToClassMapProvider = new RegistrationInterfaceToClassMapProvider();

        $nodeToParameterNameConversionStrategy = new HyphenatedWordsToCamelCaseConversionStrategy();
        $xmlDeserializer = new XmlDeserializer($domDocumentLoader, $nodeToParameterNameConversionStrategy);
        $xmlValidator    = new XmlValidator($domDocumentLoader);

        $xmlDocument = file_get_contents('module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalRegistrationWithMultipleApplicants.xml');

        $registrationXmlDeserializer = new PropertyFinanceRegistrationXmlDeserializer($interfaceToClassMapProvider, $xmlDeserializer, $xmlValidator);
        $registration = $registrationXmlDeserializer->deserialize($xmlDocument);

        $this->assertTrue($registration instanceof PropertyFinanceRegistration);
        $this->assertTrue(count($registration->getApplicants()) == 2);
        $this->assertTrue($registration->getApplicants()[0] instanceof Attorney);
        $this->assertTrue($registration->getApplicants()[1] instanceof Attorney);
        $this->assertTrue($registration->getApplicants()[0]->getName()->getForename() == 'Attorney');
        $this->assertTrue($registration->getApplicants()[1]->getName()->getForename() == 'Legal');
        // @todo Assert deserialized object here
    }

}
