<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Controller\Plugin\ServerUrl as ServerUrlPlugin;
use Opg\Model\Element\AbstractApplication as Application;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;

use Infrastructure\Library\InputException;

use Zend\Mvc\Controller\Plugin\Url as UrlPlugin;

use SimpleXMLElement;
use Opg\Model\Element\HealthWelfareRegistration;

class SummaryXmlSerializer
{
    ### PUBLIC MEMBERS

    /**
     * @var string
     */
    const CONTENT_TYPE = 'application/vnd.opg.lpa.summary+xml';

    ///**
    // * @var string
    // */
    //const XSD_LOCATION = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/Application.xsd';

    ### PUBLIC METHODS

    /**
     * @param Application[] $applications
     * @param string $hrefTemplate __ID__ will be replaced by the application id
     * @return string Valid XML
     */
    public function serialize(
        $identifierFactory,
        $userIdentity,
        $registrationRepository,
        array $applications,
        $applicationsHrefTemplate,
        $registrationsHrefTemplate
    )
    {
        if (strpos($applicationsHrefTemplate, ':id') === false) {
            throw new InputException(
                '$applicationsHrefTemplate missing :id substitution token: ' . $applicationsHrefTemplate
            );
        }

        if (strpos($registrationsHrefTemplate, ':id') === false) {
            throw new InputException(
                '$registrationsHrefTemplate missing :id substitution token: ' . $registrationsHrefTemplate
            );
        }
        
        $xmlBuilder = new SimpleXMLElement('<summary/>');
        
        foreach ($applications as $application) {

            if ($application instanceof HealthWelfareApplication) {
                $contentType = HealthWelfareApplicationXmlSerializer::CONTENT_TYPE;
            } elseif ($application instanceof PropertyFinanceApplication) {
                $contentType = PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE;
            } else {
                throw new InputException('Unrecognised Application type: '.get_class($application));
            }

            $applicationMetadata = $application->getMetadata();
            $id = (string) $applicationMetadata->getIdentifier();

            $lpaNode         = $xmlBuilder->addChild('lpa');
            
            $applicationNode = $lpaNode->addChild('application');
            $applicationNode['href'] = str_replace(':id', $id, $applicationsHrefTemplate);
            $applicationNode['type'] = $contentType;
            
            $applicationNode->addChild('last-modified', $application->getMetadata()->getWhenUpdated()->date);
            $applicationNode->addChild('donor-name', $application->getDonor()->getName());
            
            $entityIdentifier = $identifierFactory->fromString($id);
            
            $registration = $registrationRepository->fetchOne(
                $entityIdentifier, 
                $userIdentity
            );
            
            if ($registration) {
                $registrationNode = $lpaNode->addChild('registration');
                $registrationNode['href'] = str_replace(':id', $id, $registrationsHrefTemplate);
                
                if ($application instanceof HealthWelfareApplication) {
                    $contentType = HealthWelfareRegistrationXmlSerializer::CONTENT_TYPE;
                } elseif ($application instanceof PropertyFinanceApplication) {
                    $contentType = PropertyFinanceRegistrationXmlSerializer::CONTENT_TYPE;
                } else {
                    throw new InputException('Unrecognised Application type: '.get_class($application));
                }
                
                $registrationNode['type'] = $contentType;
                
                $registration instanceof HealthWelfareRegistration;
                $attorneyDeclarations = $registration->getAttorneyDeclarations();
                $attorneyDeclarationIterator = $attorneyDeclarations->getIterator();
                
                $isSigned = false;
                foreach ($attorneyDeclarationIterator as $attorneyDeclaration) {
                    if ($attorneyDeclaration->getDateSigned()) {
                        $isSigned = true;
                        break;
                    }
                }
                
                if (($registration->getDonorDiscountClaim()->isApplyingForDiscount()=='yes') &&
                    ($registration->getDonorDiscountClaim()->isReceivingBenefits()=='yes') &&
                    ($registration->getDonorDiscountClaim()->isDamageAwardRecipient()=='no')
                ) 
                {
                    $isExempt = true;
                } else {
                    $isExempt = false;
                }
                
                $paymentResult = $registration->getPaymentResult();
                $paymentMethod = $registration->getPaymentInstructions()->getPaymentMethod();
                $isPayingOnline = ($paymentMethod == 'CARD');
                $isPaid = trim($paymentResult) != '';
                
                //$isComplete = $isSigned && ($isPaid || $isExempt || !$isPayingOnline) && ($paymentMethod != '' || $isExempt);
                if($isSigned)
                {
                	if($paymentMethod=='') {
                		if($isExempt)
                			$isComplete = true;
                		else
                			$isComplete = false;
                	}
                	else {
                		if($isPayingOnline) {
                			if($isPaid)
                				$isComplete = true;
                			else
                				$isComplete = false;
                		}
                		else {
                			$isComplete = true;
                		}
                	}
                }
                else {
                	$isComplete = false;
                }
                
                
                $registrationNode->addChild('is-signed', $isSigned ? 'yes' : 'no');
                $registrationNode->addChild('is-complete', $isComplete ? 'yes' : 'no');
                $registrationNode->addChild('last-modified', $registration->getMetadata()->getWhenUpdated()->date);
                $registrationNode->addChild('payment-result', $paymentResult);
                $registrationNode->addChild('payment-method', $paymentMethod);
                $registrationNode->addChild('isPayingOnline', $isPayingOnline?'true':'false');
                $registrationNode->addChild('isPaid', $isPaid?'true':'false');
                $registrationNode->addChild('isExempt', $isExempt?'true':'false');
                $registrationNode->addChild('isSigned', $isSigned?'true':'false');
                $registrationNode->addChild('isComplete', $isComplete?'true':'false');
            }
        }

        return $xmlBuilder->asXML();
    }
}
