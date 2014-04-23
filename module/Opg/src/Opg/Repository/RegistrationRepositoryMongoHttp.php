<?php

namespace Opg\Repository;

use Infrastructure\Security\WorldpayAccessIdentity;

use Opg\Model\Element\AbstractRegistration as Registration;

use Infrastructure\MongoHttpConnectionProviderInterface;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Security\IdentityInterface;
use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlSerializer;
use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;
use LSS\Array2XML;
use LSS\XML2Array;
use Opg\Model\Element\HealthWelfareRegistration;
use Opg\Model\Element\PropertyFinanceRegistration;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;
use Opg\Model\Element\RegistrationMetadata;
use Infrastructure\Library\RecordedDateTime;
use Zend\Stdlib\DateTime;
use Opg\Model\Serialization\Json\RegistrationJsonSerializer;
use Opg\Model\Serialization\Json\RegistrationJsonDeserializer;

class RegistrationRepositoryMongoHttp     
    extends BaseRepositoryMongoHttp
    implements RegistrationRepositoryInterface
{
    public function __construct(
        MongoHttpConnectionProviderInterface $mongoHttpConnectionProvider,
        RegistrationJsonSerializer $jsonSerializer,
        RegistrationJsonDeserializer $jsonDeserializer
    )
    {
        parent::__construct($mongoHttpConnectionProvider, 'registration');
        
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
        
        if ($registrations != null && is_array($registrations)) {
            return $this->unserialize(
                $registrations[0]
            );
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
        
        $this->update(array("_id" => (string)$identifier, 'user_id' => (string)$userIdentity), $statsData, 'lpaInfo');
    }
    
    public function migrate2($newCollectionName)
    {
        $registrations = $this->find();
        
        $deserializedRegistrations = array();
        
        $numberAttempted = 0;
        $numberSucceeded = 0;
        $donorNames = [];
        
        if ($registrations) {
        	echo "Converting registration data:".PHP_EOL;
            foreach ($registrations as $registration) {
                echo "_id:".$registration->_id. "  user_id:" . $registration->user_id.PHP_EOL;
                $numberAttempted ++;
                
                if (!isset($registration->type)) {
                	$base64DecodedSerializedRegistration = base64_decode($registration->serialized_registration);
                	
                	if(!$base64DecodedSerializedRegistration) {
                		echo "Unable to do base64 decoding:".PHP_EOL;
                		print_r($registration);
                		continue;
                	}
                	
                	$deserializedRegistration = unserialize($base64DecodedSerializedRegistration);
                    
                    if ($deserializedRegistration instanceof Registration) {
                        
                        $numberSucceeded++;
                        $donorNames[] = $deserializedRegistration->getDonorDeclaration()->getDonor()->getName()->__toString();
                        
                        $registrationMetadata = $deserializedRegistration->getMetadata();
                        $status = $registrationMetadata->getStatus();

				        $userIdentity = $registrationMetadata->getUserIdentity();
				        $identifier   = $registrationMetadata->getIdentifier();
                        
				        if ($identifier instanceof NullIdentifier) {
				            throw new Exception('found NullIdentifier');
				            die();
				        }
				
				        $key = array(
				            "_id" => (string)$identifier,
				        );
				    	
				        $insertData = array(
				            "_id" => (string)$identifier,
				            "user_id" => (string)$userIdentity,
				        	"serialized_registration" => $this->serialize($deserializedRegistration),
				            "status" => $status,
				            "when_updated" => $registration->when_updated,
				        	"when_created" => $registration->when_created,
				        	"type" => ($deserializedRegistration instanceof HealthWelfareRegistration)?"hw":"pf"
				        );
				        
				        $updateData = array('$set' => array(
				        	"serialized_registration" => $this->serialize($deserializedRegistration),
				        	"type" => ($deserializedRegistration instanceof HealthWelfareRegistration)?"hw":"pf"
				        ));
				        
				        if(!$this->find($key, $newCollectionName)) {
				        	$this->insert($insertData, $newCollectionName);
				        }
				        else {
				        	$this->update($key, $updateData, $newCollectionName);
				        }
                    }
                    else {
                    	echo "Unable to deserialize:".PHP_EOL;
                    	print_r($deserializedRegistration);
                    }
                }
                else {
                	echo "Unexpected type property found in registration:".PHP_EOL;
                	print_r($registration);
                }
            }
        }
        
        return array('registrations'=>array(
                'total' => $numberAttempted,
                'success' => $numberSucceeded,
                'donorNames' => $donorNames,
        ));
    }
    
	public function migrateTo($collectionName, $toCollection=null)
	{
		$deserializedRegistrations = array();
		
		$numberAttempted = 0;
		$numberSucceeded = 0;
		
		$queryResult = $this->findOne(null, $collectionName);
		
		if(!$queryResult->results) {
			echo "Collection ".$collectionName." does not exist or is empty.".PHP_EOL;
			return;
		}
		
		echo "Converting registration data:".PHP_EOL;
		
		while(isset($queryResult->results) && !empty($queryResult->results))
		{
			$cursonId = $queryResult->id;
			
			$registration = $queryResult->results[0];
			
			echo ++$numberAttempted . ". _id:".$registration->_id. "  user_id:" . $registration->user_id." ";
			
			if (!isset($registration->type)) {
				if(!is_string($registration->serialized_registration)) {
					echo "Unable to do base64 decoding on an object:".PHP_EOL;
					print_r($registration);
					$queryResult = $this->findMore($cursonId);
					continue;
				}
				
				$base64DecodedSerializedRegistration = base64_decode($registration->serialized_registration);
				
				if(!$base64DecodedSerializedRegistration) {
					echo "base64 decoding failed:".PHP_EOL;
					print_r($registration);
					$queryResult = $this->findMore($cursonId);
					continue;
				}
				
				$deserializedRegistration = unserialize($base64DecodedSerializedRegistration);
				
				if ($deserializedRegistration instanceof Registration) {
					$numberSucceeded++;
					
					$registrationMetadata = $deserializedRegistration->getMetadata();
					$status = $registrationMetadata->getStatus();
					
					$userIdentity = $registrationMetadata->getUserIdentity();
					$identifier   = $registrationMetadata->getIdentifier();
					
					if ($identifier instanceof NullIdentifier) {
						throw new Exception('found NullIdentifier');
						die();
					}
					
					$key = array(
						"_id" => (string)$identifier,
					);
					
					$serialized_registration = $this->serialize($deserializedRegistration);
					$insertData = array(
						"_id" => (string)$identifier,
						"user_id" => (string)$userIdentity,
						"serialized_registration" => $serialized_registration,
						"status" => $status,
						"when_updated" => $registration->when_updated,
						"when_created" => $registration->when_created,
						"type" => ($deserializedRegistration instanceof HealthWelfareRegistration)?"hw":"pf"
					);
					
					$updateData = array('$set' => array(
						"serialized_registration" => $serialized_registration,
						"type" => ($deserializedRegistration instanceof HealthWelfareRegistration)?"hw":"pf"
					));
					
					if($toCollection == null) {
						$toCollection = $collectionName;
					}
					
					if(!$this->find($key, $toCollection)) {
						$this->insert($insertData, $toCollection);
						echo "added ".PHP_EOL;
					}
					else {
						$this->update($key, $updateData, $toCollection);
						echo "updated ".PHP_EOL;
					}
				}
				else {
					echo "Unable to deserialize:".PHP_EOL;
					print_r($deserializedRegistration);
				}
			}
			else {
				echo "Unexpected type property found in registration:".PHP_EOL;
				print_r($registration);
			}
            
			$queryResult = $this->findMore($cursonId);
		}
		
		echo "Converting registration data complete:".PHP_EOL;
		
		return ['registrations'=>[
			'total' => $numberAttempted,
			'success' => $numberSucceeded
			]
		];
	}
	
    public function getDataForValidatingMigrationTest()
    {
    	echo "Testing on registration collection is not available when using proxy.".PHP_EOL;
    }
}
