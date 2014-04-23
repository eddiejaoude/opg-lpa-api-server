<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlDeserializer;

use Opg\Model\Serialization\Xml\ApplicationInterfaceToClassMapProvider;
use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlDeserializer;
use Infrastructure\Library\DomDocumentFactory;
use Infrastructure\Library\DomDocumentLoader;
use Infrastructure\Library\HyphenatedWordsToCamelCaseConversionStrategy;
use Infrastructure\Library\XmlDeserializer;
use Infrastructure\Library\XmlValidator;
use PHPUnit_Framework_TestCase as TestCase;

class DeserializeApplicationTest extends TestCase
{
    public function testPropertyFinanceApplicationIsDeserialized()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $domDocumentLoader = new DomDocumentLoader($domDocumentFactory);

        $interfaceToClassMapProvider = new ApplicationInterfaceToClassMapProvider();

        $nodeToParameterNameConversionStrategy = new HyphenatedWordsToCamelCaseConversionStrategy();
        $xmlDeserializer = new XmlDeserializer($domDocumentLoader, $nodeToParameterNameConversionStrategy);
        $xmlValidator = new XmlValidator($domDocumentLoader);

        $xml = file_get_contents('module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalApplication.xml');

        $applicationXmlDeserializer = new PropertyFinanceApplicationXmlDeserializer($interfaceToClassMapProvider, $xmlDeserializer, $xmlValidator);
        $application = $applicationXmlDeserializer->deserialize($xml);

        $this->assertTrue($application instanceof \Opg\Model\Element\PropertyFinanceApplication);
        $this->assertTrue($application->getDonor() instanceof \Opg\Model\Element\Donor);
        $this->assertTrue(is_string($application->getDonor()->getTitle()));
        $this->assertTrue($application->getDonor()->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue(is_string($application->getDonor()->getName()->getForename()));
        $this->assertTrue(is_string($application->getDonor()->getName()->getSurname()));
        $this->assertTrue(is_string($application->getDonor()->getEmailAddress()));
        $this->assertTrue($application->getDonor()->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getAddressLine1()));
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getAddressLine2()));
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getAddressLine3()));
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getTown()));
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getCounty()));
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getPostcode()));
        $this->assertTrue(is_string($application->getDonor()->getPostalAddress()->getCountry()));
        $this->assertTrue(is_string($application->getDonor()->getPhoneNumber()));
        $this->assertTrue(is_string($application->getDonor()->getAlias()));
        $this->assertTrue(is_string($application->getDonor()->getDateOfBirth()));
        $this->assertTrue(is_string($application->getDonor()->getHasAbilityToSign()));
        $this->assertTrue($application->getAttorneys() instanceof \Opg\Model\Element\AttorneyCollection);
        $this->assertTrue(count($application->getAttorneys()) == 0);
        $this->assertTrue($application->getReplacementAttorneys() instanceof \Opg\Model\Element\AttorneyCollection);
        $this->assertTrue(count($application->getReplacementAttorneys()) == 0);
        $this->assertTrue($application->getAttorneyDecisionInstructions() instanceof \Opg\Model\Element\AttorneyDecisionInstructions);
        $this->assertTrue(is_string($application->getAttorneyDecisionInstructions()->getHowAttorneysMakeDecisions()));
        $this->assertTrue(is_string($application->getAttorneyDecisionInstructions()->getInstructions()));
        $this->assertTrue($application->getCertificateProviders() instanceof \Opg\Model\Element\CertificateProviderCollection);
        $this->assertTrue(count($application->getCertificateProviders()) == 0);
        $this->assertTrue($application->getPersonsToBeNotified() instanceof \Opg\Model\Element\NotifiedPersonCollection);
        $this->assertTrue(count($application->getPersonsToBeNotified()) == 0);
        $this->assertTrue(is_string($application->getCharges()));
        $this->assertTrue(is_string($application->getGuidance()));
        $this->assertTrue(is_string($application->getRestrictions()));
        $this->assertTrue(is_string($application->getWhenToStartInstruction()));

        $this->assertEquals('Mr', $application->getDonor()->getTitle());
        $this->assertEquals('Donor', $application->getDonor()->getName()->getForename());
        $this->assertEquals('Kebab', $application->getDonor()->getName()->getSurname());
        $this->assertEquals('donor@kebab.example.com', $application->getDonor()->getEmailAddress());
        $this->assertEquals('123 Any Street', $application->getDonor()->getPostalAddress()->getAddressLine1());
        $this->assertEquals('', $application->getDonor()->getPostalAddress()->getAddressLine2());
        $this->assertEquals('', $application->getDonor()->getPostalAddress()->getAddressLine3());
        $this->assertEquals('Any Town', $application->getDonor()->getPostalAddress()->getTown());
        $this->assertEquals('Any County', $application->getDonor()->getPostalAddress()->getCounty());
        $this->assertEquals('ANY 123', $application->getDonor()->getPostalAddress()->getPostcode());
        $this->assertEquals('UK', $application->getDonor()->getPostalAddress()->getCountry());
        $this->assertEquals('01234567890', $application->getDonor()->getPhoneNumber());
        $this->assertEquals('Bob', $application->getDonor()->getAlias());
        $this->assertEquals('1950-01-01', $application->getDonor()->getDateOfBirth());
        $this->assertEquals('yes', $application->getDonor()->getHasAbilityToSign());
        $this->assertEquals('', $application->getAttorneyDecisionInstructions()->getHowAttorneysMakeDecisions());
        $this->assertEquals('', $application->getAttorneyDecisionInstructions()->getInstructions());
        $this->assertEquals('1 billion dollars', $application->getCharges());
        $this->assertEquals('', $application->getGuidance());
        $this->assertEquals('Only in the event I become a Zombie.', $application->getRestrictions());
        $this->assertEquals('DonorLostCapacity', $application->getWhenToStartInstruction());
    }
}
