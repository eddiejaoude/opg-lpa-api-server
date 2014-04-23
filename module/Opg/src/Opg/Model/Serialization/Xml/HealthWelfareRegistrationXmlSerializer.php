<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\Attorney;
use Opg\Model\Element\Donor;
use Opg\Model\Element\HealthWelfareRegistration;

use Infrastructure\Library\FileLocation;
use Infrastructure\Library\XmlSerializerInterface;
use Infrastructure\Library\XmlValidatorInterface;

class HealthWelfareRegistrationXmlSerializer
{
    ### COLLABORATORS

    /**
     * @var XmlSerializerInterface
     */
    private $xmlSerializer;

    /**
     * @var XmlValidatorInterface
     */
    private $xmlValidator;

    ### CONSTRUCTOR

    public function __construct(
        XmlSerializerInterface $xmlSerializer,
        XmlValidatorInterface $xmlValidator
    )
    {
        $this->xmlSerializer = $xmlSerializer;
        $this->xmlValidator  = $xmlValidator;
    }

    ### PUBLIC MEMBERS

    /**
     * @var string
     */
    const CONTENT_TYPE = 'application/vnd.opg.lpa.registration.health-welfare+xml';

    /**
     * @var string
     */
    const XSD_LOCATION = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/HealthWelfareRegistration.xsd';

    ### PUBLIC METHODS

    /**
     * @return string Valid XML
     */
    public function serialize(
        HealthWelfareRegistration $registration
    )
    {
        $xmlDocument = ('<registration>'.$this->xmlSerializer->serialize($registration).'</registration>');

        $applicants = $registration->getApplicants();
        
        if (count($applicants) > 0) {
            $applicant = $applicants[0];
            
            if ($applicant instanceof Attorney) {
                
                $xmlDocument = str_replace('<applicant-role-interface>',  '<attorney>', $xmlDocument);
                $xmlDocument = str_replace('</applicant-role-interface>', '</attorney>', $xmlDocument);

            } elseif ($applicant instanceof Donor) {
                
                $xmlDocument = str_replace('<applicant-role-interface>',  '<donor>', $xmlDocument);
                $xmlDocument = str_replace('</applicant-role-interface>', '</donor>', $xmlDocument);
            }
        }
        
        $xsdFileLocation = new FileLocation(self::XSD_LOCATION);
        $this->xmlValidator->validate($xmlDocument, $xsdFileLocation);
        return $xmlDocument;
    }
}
