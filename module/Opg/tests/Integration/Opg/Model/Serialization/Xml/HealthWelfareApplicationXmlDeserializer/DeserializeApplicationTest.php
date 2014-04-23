<?php

namespace Tests\Integration\Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;

use Opg\Model\Serialization\Xml\ApplicationInterfaceToClassMapProvider;
use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;
use Infrastructure\Library\DomDocumentFactory;
use Infrastructure\Library\DomDocumentLoader;
use Infrastructure\Library\HyphenatedWordsToCamelCaseConversionStrategy;
use Infrastructure\Library\XmlDeserializer;
use Infrastructure\Library\XmlValidator;
use PHPUnit_Framework_TestCase as TestCase;

class DeserializeApplicationTest extends TestCase
{
    public function testHealthWelfareApplicationIsDeserialized()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $domDocumentLoader = new DomDocumentLoader($domDocumentFactory);

        $interfaceToClassMapProvider = new ApplicationInterfaceToClassMapProvider();

        $nodeToParameterNameConversionStrategy = new HyphenatedWordsToCamelCaseConversionStrategy();
        $xmlDeserializer = new XmlDeserializer($domDocumentLoader, $nodeToParameterNameConversionStrategy);
        $xmlValidator = new XmlValidator($domDocumentLoader);

        $xml = file_get_contents('module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalApplication.xml');

        $applicationXmlDeserializer = new HealthWelfareApplicationXmlDeserializer($interfaceToClassMapProvider, $xmlDeserializer, $xmlValidator);
        $application = $applicationXmlDeserializer->deserialize($xml);

        $this->assertTrue($application instanceof \Opg\Model\Element\HealthWelfareApplication);

        $this->assertTrue($application->getDonor() instanceof \Opg\Model\Element\Donor);
        $this->assertTrue($application->getDonor()->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue($application->getDonor()->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);

        $this->assertTrue($application->getAttorneys() instanceof \Opg\Model\Element\AttorneyCollection);
        $this->assertTrue(count($application->getAttorneys()) == 2);
        $this->assertTrue($application->getAttorneys()[0] instanceof \Opg\Model\Element\Attorney);
        $this->assertTrue($application->getAttorneys()[0]->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue($application->getAttorneys()[0]->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);
        $this->assertTrue($application->getAttorneys()[0]->getDxAddress() instanceof \Opg\Model\Element\DxAddress);
        $this->assertTrue($application->getAttorneys()[1] instanceof \Opg\Model\Element\Attorney);
        $this->assertTrue($application->getAttorneys()[1]->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue($application->getAttorneys()[1]->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);
        $this->assertTrue($application->getAttorneys()[1]->getDxAddress() instanceof \Opg\Model\Element\DxAddress);

        $this->assertTrue($application->getReplacementAttorneys() instanceof \Opg\Model\Element\AttorneyCollection);
        $this->assertTrue(count($application->getReplacementAttorneys()) == 1);
        $this->assertTrue($application->getReplacementAttorneys()[0] instanceof \Opg\Model\Element\Attorney);
        $this->assertTrue($application->getReplacementAttorneys()[0]->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue($application->getReplacementAttorneys()[0]->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);
        $this->assertTrue($application->getReplacementAttorneys()[0]->getDxAddress() instanceof \Opg\Model\Element\DxAddress);

        $this->assertTrue($application->getAttorneyDecisionInstructions() instanceof \Opg\Model\Element\AttorneyDecisionInstructions);

        $this->assertTrue($application->getCertificateProviders() instanceof \Opg\Model\Element\CertificateProviderCollection);
        $this->assertTrue(count($application->getCertificateProviders()) == 1);
        $this->assertTrue($application->getCertificateProviders()[0] instanceof \Opg\Model\Element\CertificateProvider);
        $this->assertTrue($application->getCertificateProviders()[0]->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue($application->getCertificateProviders()[0]->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);
        $this->assertTrue($application->getCertificateProviders()[0]->getQualification() instanceof \Opg\Model\Element\CertificateProviderQualification);
        
        $this->assertTrue($application->getPersonsToBeNotified() instanceof \Opg\Model\Element\NotifiedPersonCollection);
        $this->assertTrue(count($application->getPersonsToBeNotified()) == 1);
        $this->assertTrue($application->getPersonsToBeNotified()[0] instanceof \Opg\Model\Element\NotifiedPerson);
        $this->assertTrue($application->getPersonsToBeNotified()[0]->getName() instanceof \Opg\Model\Element\PersonName);
        $this->assertTrue($application->getPersonsToBeNotified()[0]->getPostalAddress() instanceof \Opg\Model\Element\PostalAddress);

        $this->assertEquals('Mr', $application->getDonor()->getTitle());
        $this->assertEquals('Donor', $application->getDonor()->getName()->getForename());
        $this->assertEquals('Mid', $application->getDonor()->getName()->getMiddlenames());
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

        $this->assertEquals('Mr', $application->getAttorneys()[0]->getTitle());
        $this->assertEquals('Attorney', $application->getAttorneys()[0]->getName()->getForename());
        $this->assertEquals('', $application->getAttorneys()[0]->getName()->getMiddlenames());
        $this->assertEquals('General', $application->getAttorneys()[0]->getName()->getSurname());
        $this->assertEquals('attorney@general.example.com', $application->getAttorneys()[0]->getEmailAddress());
        $this->assertEquals('456 Any Street', $application->getAttorneys()[0]->getPostalAddress()->getAddressLine1());
        $this->assertEquals('', $application->getAttorneys()[0]->getPostalAddress()->getAddressLine2());
        $this->assertEquals('', $application->getAttorneys()[0]->getPostalAddress()->getAddressLine3());
        $this->assertEquals('Any Town', $application->getAttorneys()[0]->getPostalAddress()->getTown());
        $this->assertEquals('Any County', $application->getAttorneys()[0]->getPostalAddress()->getCounty());
        $this->assertEquals('ANY 456', $application->getAttorneys()[0]->getPostalAddress()->getPostcode());
        $this->assertEquals('UK', $application->getAttorneys()[0]->getPostalAddress()->getCountry());
        $this->assertEquals('01234567890', $application->getAttorneys()[0]->getPhoneNumber());
        $this->assertEquals('Luke ... I am your father', $application->getAttorneys()[0]->getDonorRelationship());
        $this->assertEquals('1950-01-02', $application->getAttorneys()[0]->getDateOfBirth());
        $this->assertEquals('yes', $application->getAttorneys()[0]->isBankruptOrSubjectToDebtReliefOrder());
        $this->assertEquals('Galactic Empire', $application->getAttorneys()[0]->getCompanyName());
        $this->assertEquals('Dark Lord of the Sith', $application->getAttorneys()[0]->getOccupation());
        $this->assertEquals('1', $application->getAttorneys()[0]->getDxAddress()->getDxNumber());
        $this->assertEquals('Death Star', $application->getAttorneys()[0]->getDxAddress()->getDxExchange());

        $this->assertEquals('Mr', $application->getAttorneys()[1]->getTitle());
        $this->assertEquals('Legal', $application->getAttorneys()[1]->getName()->getForename());
        $this->assertEquals('', $application->getAttorneys()[1]->getName()->getMiddlenames());
        $this->assertEquals('General', $application->getAttorneys()[1]->getName()->getSurname());
        $this->assertEquals('legal@general.example.com', $application->getAttorneys()[1]->getEmailAddress());
        $this->assertEquals('789 Any Street', $application->getAttorneys()[1]->getPostalAddress()->getAddressLine1());
        $this->assertEquals('', $application->getAttorneys()[1]->getPostalAddress()->getAddressLine2());
        $this->assertEquals('', $application->getAttorneys()[1]->getPostalAddress()->getAddressLine3());
        $this->assertEquals('Any Town', $application->getAttorneys()[1]->getPostalAddress()->getTown());
        $this->assertEquals('Any County', $application->getAttorneys()[1]->getPostalAddress()->getCounty());
        $this->assertEquals('ANY 789', $application->getAttorneys()[1]->getPostalAddress()->getPostcode());
        $this->assertEquals('UK', $application->getAttorneys()[1]->getPostalAddress()->getCountry());
        $this->assertEquals('01234567890', $application->getAttorneys()[1]->getPhoneNumber());
        $this->assertEquals('Badger', $application->getAttorneys()[1]->getDonorRelationship());
        $this->assertEquals('1950-01-03', $application->getAttorneys()[1]->getDateOfBirth());
        $this->assertEquals('no', $application->getAttorneys()[1]->isBankruptOrSubjectToDebtReliefOrder());
        $this->assertEquals('Legal & General', $application->getAttorneys()[1]->getCompanyName());
        $this->assertEquals('Insurance Salesman', $application->getAttorneys()[1]->getOccupation());
        $this->assertEquals('', $application->getAttorneys()[1]->getDxAddress()->getDxNumber());
        $this->assertEquals('', $application->getAttorneys()[1]->getDxAddress()->getDxExchange());

        $this->assertEquals('Mrs', $application->getReplacementAttorneys()[0]->getTitle());
        $this->assertEquals('X', $application->getReplacementAttorneys()[0]->getName()->getForename());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getName()->getMiddlenames());
        $this->assertEquals('Factor', $application->getReplacementAttorneys()[0]->getName()->getSurname());
        $this->assertEquals('x@factor.example.com', $application->getReplacementAttorneys()[0]->getEmailAddress());
        $this->assertEquals('999 Any Street', $application->getReplacementAttorneys()[0]->getPostalAddress()->getAddressLine1());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getPostalAddress()->getAddressLine2());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getPostalAddress()->getAddressLine3());
        $this->assertEquals('Any Town', $application->getReplacementAttorneys()[0]->getPostalAddress()->getTown());
        $this->assertEquals('Any County', $application->getReplacementAttorneys()[0]->getPostalAddress()->getCounty());
        $this->assertEquals('ANY 999', $application->getReplacementAttorneys()[0]->getPostalAddress()->getPostcode());
        $this->assertEquals('UK', $application->getReplacementAttorneys()[0]->getPostalAddress()->getCountry());
        $this->assertEquals('01234567890', $application->getReplacementAttorneys()[0]->getPhoneNumber());
        $this->assertEquals('Moose', $application->getReplacementAttorneys()[0]->getDonorRelationship());
        $this->assertEquals('1950-01-04', $application->getReplacementAttorneys()[0]->getDateOfBirth());
        $this->assertEquals('no', $application->getReplacementAttorneys()[0]->isBankruptOrSubjectToDebtReliefOrder());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getCompanyName());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getOccupation());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getDxAddress()->getDxNumber());
        $this->assertEquals('', $application->getReplacementAttorneys()[0]->getDxAddress()->getDxExchange());

        $this->assertEquals('', $application->getAttorneyDecisionInstructions()->getHowAttorneysMakeDecisions());
        $this->assertEquals('', $application->getAttorneyDecisionInstructions()->getInstructions());

        $this->assertEquals('Mr', $application->getCertificateProviders()[0]->getTitle());
        $this->assertEquals('Certificate', $application->getCertificateProviders()[0]->getName()->getForename());
        $this->assertEquals('', $application->getCertificateProviders()[0]->getName()->getMiddlenames());
        $this->assertEquals('Provider', $application->getCertificateProviders()[0]->getName()->getSurname());
        $this->assertEquals('certificate@provider.example.com', $application->getCertificateProviders()[0]->getEmailAddress());
        $this->assertEquals('1 Any Street', $application->getCertificateProviders()[0]->getPostalAddress()->getAddressLine1());
        $this->assertEquals('', $application->getCertificateProviders()[0]->getPostalAddress()->getAddressLine2());
        $this->assertEquals('', $application->getCertificateProviders()[0]->getPostalAddress()->getAddressLine3());
        $this->assertEquals('Any Town', $application->getCertificateProviders()[0]->getPostalAddress()->getTown());
        $this->assertEquals('Any County', $application->getCertificateProviders()[0]->getPostalAddress()->getCounty());
        $this->assertEquals('ANY 1', $application->getCertificateProviders()[0]->getPostalAddress()->getPostcode());
        $this->assertEquals('UK', $application->getCertificateProviders()[0]->getPostalAddress()->getCountry());
        $this->assertEquals('', $application->getCertificateProviders()[0]->getQualification()->getQualification());
        $this->assertEquals('', $application->getCertificateProviders()[0]->getQualification()->getQualificationDetails());

        $this->assertEquals('Mr', $application->getPersonsToBeNotified()[0]->getTitle());
        $this->assertEquals('Notified', $application->getPersonsToBeNotified()[0]->getName()->getForename());
        $this->assertEquals('', $application->getPersonsToBeNotified()[0]->getName()->getMiddlenames());
        $this->assertEquals('Person', $application->getPersonsToBeNotified()[0]->getName()->getSurname());
        $this->assertEquals('notified@person.example.com', $application->getPersonsToBeNotified()[0]->getEmailAddress());
        $this->assertEquals('Any Street', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getAddressLine1());
        $this->assertEquals('', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getAddressLine2());
        $this->assertEquals('', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getAddressLine3());
        $this->assertEquals('Any Town', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getTown());
        $this->assertEquals('Any County', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getCounty());
        $this->assertEquals('ANY', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getPostcode());
        $this->assertEquals('Australia', $application->getPersonsToBeNotified()[0]->getPostalAddress()->getCountry());

        $this->assertEquals('10 million dollars', $application->getCharges());
        $this->assertEquals('', $application->getGuidance());
        $this->assertEquals('Only in the event I become a Zombie.', $application->getRestrictions());
        $this->assertEquals('no', $application->isGivingLifeSustainingAuthority());
    }
}
