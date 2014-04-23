<?php

namespace Opg\Repository;

use Infrastructure\Security\WorldpayAccessIdentity;

use Opg\Model\Element\AbstractRegistration as Registration;

use Infrastructure\MongoConnectionProviderInterface;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Security\IdentityInterface;

//use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlSerializer;
//use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlSerializer;
//use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;
//use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;
//use LSS\Array2XML;
//use LSS\XML2Array;
use Opg\Model\Element\HealthWelfareRegistration;
//use Opg\Model\Element\PropertyFinanceRegistration;
//use Infrastructure\Library\IdentifierFactoryInterface;
//use Infrastructure\Security\IdentityFactoryInterface;
//use Opg\Model\Element\RegistrationMetadata;
//use Infrastructure\Library\RecordedDateTime;
//use Zend\Stdlib\DateTime;
use Opg\Model\Serialization\Json\RegistrationJsonSerializer;
use Opg\Model\Serialization\Json\RegistrationJsonDeserializer;

class RegistrationRepositoryMongo    
    extends BaseRepositoryMongo
    implements RegistrationRepositoryInterface
{
    public function __construct(
        MongoConnectionProviderInterface $mongoConnectionProvider,
        RegistrationJsonSerializer $jsonSerializer,
        RegistrationJsonDeserializer $jsonDeserializer
    )
    {
        parent::__construct($mongoConnectionProvider, 'registration');
        
        $this->jsonSerializer = $jsonSerializer;
        $this->jsonDeserializer = $jsonDeserializer;
    }

    ### PUBLIC METHODS

    /**
     * @return bool
     */
    public function exists(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    )
    {
        if ($identifier instanceof NullIdentifier) {
            return false;
        }

        $criteria = array(
            '_id' => (string)$identifier,
        );
        
        // worldpay identity does not have a user_id 
        if( !($userIdentity instanceof WorldpayAccessIdentity) ) {
            $criteria['user_id'] = (string)$userIdentity;
        }
        
        return $this->keyExists($criteria);
    }

    ###

    /**
     * @return Registration
     */
    public function fetchOne(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    )
    {
        $criteria = array(
            '_id' => (string)$identifier,
        );
        
        $registrations = $this->find($criteria);
        
        if ($registrations->count() > 0) {
            return $this->unserialize(
                $registrations->getNext()
            );
        }
    }
    
    ###
    
    public function deleteUserRegistrations(
    	IdentityInterface $userIdentity
    )
    {
        $criteria = array(
            'user_id' => (string)$userIdentity,
        );
        
    	$result = $this->remove($criteria);
        if($result['ok']) {
        	return $result['n'];
        }
    }

    ###

    public function persist(
        Registration $registration
    )
    {
        $registgrationMetadata = $registration->getMetadata();

        $identifier   = $registgrationMetadata->getIdentifier();
        $userIdentity = $registgrationMetadata->getUserIdentity();

        $status = $registgrationMetadata->getStatus();

        $serializedRegistration = $this->serialize($registration);

        $timestampNow = time();
        
        $key = array(
        	"_id" => (string)$identifier,
        );
        
        // worldpay identity does not have a user_id 
        if( !($userIdentity instanceof WorldpayAccessIdentity) ) {
            $key['user_id'] = (string)$userIdentity;
        }
        
        $data = array(
            "serialized_registration" => $serializedRegistration,
            "status" => $status,
            "when_updated" => $timestampNow,
        );
        
        if (!$this->exists($identifier, $userIdentity)) {
            $data['when_created'] = $timestampNow;
            $data['type'] = ($registration instanceof HealthWelfareRegistration)?"hw":"pf";
            $this->insert(array_merge($key, $data));
        } else {
            $this->update($key, array('$set'=>$data));
        }
        
        $attorneyDeclarations = $registration->getAttorneyDeclarations();
        $attorneyDeclarationIterator = $attorneyDeclarations->getIterator();
        $isSigned = false;
        foreach ($attorneyDeclarationIterator as $attorneyDeclaration) {
            if ($attorneyDeclaration->getDateSigned()) {
                $isSigned = true;
                break;
            }
        }
        
        $applyingDiscount = $registration->getDonorDiscountClaim()->isApplyingForDiscount();
        $recevingBenefits = $registration->getDonorDiscountClaim()->isReceivingBenefits();
        $damageAward = $registration->getDonorDiscountClaim()->isDamageAwardRecipient();
        if (($applyingDiscount=='yes') && ($recevingBenefits=='yes') && ($damageAward=='no')) 
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
        
        $statsData = array(
        	'pay_by'	=>	$paymentMethod,
        	'applying_discount' => (($applyingDiscount=='yes')?true:false),
        	'receiving_benefits' => (($recevingBenefits=='yes')?true:false),
        	'damage_award'	=> (($damageAward=='yes')?true:false)
        );
        
        if($isComplete) {
        	$statsData['when_completed'] = time(); 
        	$statsData['state'] = 'completed';
        }
        elseif($isSigned) {
        	$statsData['when_signed'] = time(); 
        	$statsData['state'] = 'signed';
        }
        else {
        	$statsData['when_created'] = time(); 
        	$statsData['state'] = 'created';
        }
        
        $key = array(
        	"_id" => (string)$identifier, 
        	'user_id' => (string)$userIdentity
        );
        
        $this->update($key, array('$set' => $statsData), 'lpaInfo');
    }
}
