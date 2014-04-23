<?php

namespace Opg\Repository;

use Infrastructure\Security\WorldpayAccessIdentity;

use Opg\Model\Element\AbstractApplication as Application;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;
use Infrastructure\MongoConnectionProviderInterface;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Library\UniqueIdentifier;

use Opg\Model\Serialization\Json\ApplicationJsonSerializer;
use Opg\Model\Serialization\Json\ApplicationJsonDeserializer;
use Opg\Model\Serialization\Json\RegistrationJsonDeserializer;

class ApplicationRepositoryMongo
    extends BaseRepositoryMongo
    implements ApplicationRepositoryInterface
{
    private $registrationJsonDeserializer;
    
    public function __construct(
        MongoConnectionProviderInterface $mongoConnectionProvider,
        ApplicationJsonSerializer $jsonSerializer,
        ApplicationJsonDeserializer $jsonDeserializer,
        RegistrationJsonDeserializer $registrationJsonDeserializer
    )
    {
        parent::__construct($mongoConnectionProvider, 'application');
        
        $this->jsonSerializer = $jsonSerializer;
        $this->jsonDeserializer = $jsonDeserializer;
        
        $this->registrationJsonDeserializer = $registrationJsonDeserializer;
        
    }
    
    ###
    
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
     * @return Application[]
     */
    public function fetchAll(
        IdentityInterface $userIdentity
    )
    {
        $criteria = array(
            'user_id' => (string)$userIdentity,
        );
        
        $applications = $this->find($criteria);
        
        $deserializedApplications = array();
        
        if ($applications->count() > 0) {
            foreach ($applications as $application) {
                if (isset($application['type'])) {
                    $deserializedApplications[] = $this->unserialize(
                        $application
                    );
                }
            }
        }
        
        return $deserializedApplications;
    }

    ###

    /**
     * @return Application
     */
    public function fetchOne(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    )
    {
        $criteria = array(
            '_id' => (string)$identifier,
        );
        
        $applications = $this->find($criteria);
        
        if ($applications->count() > 0) {
            return $this->unserialize(
                $applications->getNext()
            );
        }
    }

    ###

    public function isApplicationIdExistent(
        IdentifierInterface $identifier
    )
    {
        if ($identifier instanceof NullIdentifier) {
            return false;
        }

        $criteria = array(
            '_id' => (string)$identifier,
        );
        
        return $this->keyExists($criteria);    
    }    
    
    ###
    
    public function persist(
        Application $application
    )
    {
        $timestampNow = time();
    
        $applicationMetadata = $application->getMetadata();
        $status = $applicationMetadata->getStatus();
    
        $userIdentity = $applicationMetadata->getUserIdentity();
        $identifier   = $applicationMetadata->getIdentifier();
    
        $key = array(
            "_id" => (string)$identifier,
            "user_id" => (string)$userIdentity,
        );
    
        $insertData = array(
            "_id" => (string)$identifier,
            "user_id" => (string)$userIdentity,
        	"serialized_application" => $this->serialize($application),
            "status" => $status,
            "when_updated" => $timestampNow,
            "when_created" => $timestampNow,
            "type" => ($application instanceof HealthWelfareApplication)?"hw":"pf"
        );
        
        $updateData = array('$set' => array(
        	"serialized_application" => $this->serialize($application),
        	"status" => $status,
            "when_updated" => $timestampNow,
        ));
        
        if (!$this->exists($identifier, $userIdentity)) {
            
            $this->insert($insertData);
            
            $statsData = (array(
                "_id" => (string)$identifier,
                "user_id" => (string)$userIdentity,
                "type"	=> ($application instanceof HealthWelfareApplication)?"hw":"pf",
                'state' => 'started',
                'when_started' => time()
            ));
   
            $this->insert($statsData, 'lpaInfo');
    
        } else {
            $this->update($key, $updateData);
        }
    
    }
    
    ###
    
    public function getNewApplicationId()
    {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return new UniqueIdentifier($randomString);
    }
    
    ###
    
    public function delete(
        Application $application
    )
    {
        $applicationMetadata = $application->getMetadata();
    
        $identifier   = $applicationMetadata->getIdentifier();
        $userIdentity = $applicationMetadata->getUserIdentity();
    
        $key = array(
            '_id' => (string)$identifier,
            'user_id' => (string)$userIdentity,
        );
    
        $this->remove($key, 'application');
        $this->remove($key, 'registration');
    
        // change state to 'deleted' in lpaInfo collection
        $this->update($key, array('$set' => array('state'=>'deleted', 'when_deleted'=>time())), 'lpaInfo');
    }
    
    ###
    
    public function deleteUserApplications(
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
}

