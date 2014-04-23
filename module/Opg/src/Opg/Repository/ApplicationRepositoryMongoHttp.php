<?php

namespace Opg\Repository;

use Infrastructure\Security\WorldpayAccessIdentity;

use Opg\Model\Element\AbstractApplication as Application;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;
use Infrastructure\MongoHttpConnectionProviderInterface;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Library\UniqueIdentifier;
use Opg\Model\Serialization\Json\ApplicationJsonSerializer;
use Opg\Model\Serialization\Json\ApplicationJsonDeserializer;
use Opg\Model\Serialization\Json\RegistrationJsonDeserializer;

class ApplicationRepositoryMongoHttp
    extends BaseRepositoryMongoHttp
    implements ApplicationRepositoryInterface
{
    private $registrationJsonDeserializer;
    
    public function __construct(
        MongoHttpConnectionProviderInterface $mongoHttpConnectionProvider,
        ApplicationJsonSerializer $jsonSerializer,
        ApplicationJsonDeserializer $jsonDeserializer,
        RegistrationJsonDeserializer $registrationJsonDeserializer
    )
    {
        parent::__construct($mongoHttpConnectionProvider, 'application');
        
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
        
        if ($applications) {
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
        
        if ($applications != null && is_array($applications)) {
            return $this->unserialize(
                $applications[0]
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
            "type" => ($application instanceof HealthWelfareApplication)?"hw":"pf"
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
    
    public function fetchStates()
    {
        $db = $this->mongoConnectionProvider->getMongoConnection();
        $statsCollection = $db->selectCollection("lpaInfo");
        
		$today = getdate();
		$curMonthStart = mktime(0,0,0,$today['mon'],1,$today['year']);
		
		$nextMonth = getdate(strtotime('+1 month'));
		$curMonthEnd = strtotime('-1 second', mktime(0,0,0,$nextMonth['mon'],1,$nextMonth['year']));
		
		$oneMonthAgo = getdate(strtotime('-1 month'));
		$lastMonthStart = mktime(0,0,0,$oneMonthAgo['mon'],1,$oneMonthAgo['year']);
		$lastMonthEnd = strtotime('-1 second', mktime(0,0,0,$today['mon'],1,$today['year']));
		
		$twoMonthAgo = getdate(strtotime('-2 month'));
		$lastTwoMonthStart = mktime(0,0,0,$twoMonthAgo['mon'],1,$twoMonthAgo['year']);
		$lastTwoMonthEnd = strtotime('-1 second', mktime(0,0,0,$oneMonthAgo['mon'],1,$oneMonthAgo['year']));
		
        
        return array(
	        'total' => $statsCollection->find()->count(),
	        'started' => $statsCollection->find(array('state'=>'started'))->count(),
	        'created' => $statsCollection->find(array('state'=>'created'))->count(),
        	'signed' => $statsCollection->find(array('state'=>'signed'))->count(),
	        'completed' => $statsCollection->find(array('state'=>'completed'))->count(),
	        'deleted' => $statsCollection->find(array('state'=>'deleted'))->count(),
        	
	        'cur_month_started' => $statsCollection->find(array('when_started'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)))->count(),
	        'cur_month_created' => $statsCollection->find(array('when_created'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)))->count(),
	        'cur_month_signed' => $statsCollection->find(array('when_signed'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)))->count(),
	        'cur_month_completed' => $statsCollection->find(array('when_completed'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)))->count(),
        	
	        'one_month_ago_started' => $statsCollection->find(array('when_started'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)))->count(),
	        'one_month_ago_created' => $statsCollection->find(array('when_created'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)))->count(),
	        'one_month_ago_signed' => $statsCollection->find(array('when_signed'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)))->count(),
	        'one_month_ago_completed' => $statsCollection->find(array('when_completed'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)))->count(),
        	
        	'two_month_ago_started' => $statsCollection->find(array('when_started'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)))->count(),
	        'two_month_ago_created' => $statsCollection->find(array('when_created'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)))->count(),
	        'two_month_ago_signed' => $statsCollection->find(array('when_signed'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)))->count(),
	        'two_month_ago_completed' => $statsCollection->find(array('when_completed'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)))->count(),
        	
        	'total_hw' => $statsCollection->find(array('type'=>'hw'))->count(),
        	'total_pf' => $statsCollection->find(array('type'=>'pf'))->count(),
        	'hw_started' => $statsCollection->find(array('type'=>'hw', 'state'=>'started'))->count(),
        	'hw_created' => $statsCollection->find(array('type'=>'hw', 'state'=>'created'))->count(),
        	'hw_signed' => $statsCollection->find(array('type'=>'hw', 'state'=>'signed'))->count(),
        	'hw_completed' => $statsCollection->find(array('type'=>'hw', 'state'=>'completed'))->count(),
        	'hw_deleted' => $statsCollection->find(array('type'=>'hw', 'state'=>'deleted'))->count(),
        	'pf_started' => $statsCollection->find(array('type'=>'pf', 'state'=>'started'))->count(),
        	'pf_created' => $statsCollection->find(array('type'=>'pf', 'state'=>'created'))->count(),
        	'pf_signed' => $statsCollection->find(array('type'=>'pf', 'state'=>'signed'))->count(),
        	'pf_completed' => $statsCollection->find(array('type'=>'pf', 'state'=>'completed'))->count(),
        	'pf_deleted' => $statsCollection->find(array('type'=>'pf', 'state'=>'deleted'))->count()
        );
    }
    
    public function migrate2($newCollectionName)
    {
        $applications = $this->find();
        
        $deserializedApplications = array();
        
        $numberAttempted = 0;
        $numberSucceeded = 0;
        $donorNames = [];
        
        if ($applications) {
        	echo "Converting application data:".PHP_EOL;
            foreach ($applications as $application) {
            	echo "_id:".$application->_id. "  user_id:" . $application->user_id.PHP_EOL;
                $numberAttempted ++;
                
                if (!isset($application->type)) {
                	$base64DecodedSerializedApplication = base64_decode($application->serialized_application);
                	
                	if(!$base64DecodedSerializedApplication) {
                		echo "Unable to do base64 decoding:".PHP_EOL;
                		print_r($application);
                		continue;
                	}
                	
                    $deserializedApplication = unserialize($base64DecodedSerializedApplication);
                    
                    if ($deserializedApplication instanceof Application) {
                        $numberSucceeded ++;
                        $donorNames[] = $deserializedApplication->getDonor()->getName()->__toString();
                        
                        
				        $applicationMetadata = $deserializedApplication->getMetadata();
				        $status = $applicationMetadata->getStatus();

				        $userIdentity = $applicationMetadata->getUserIdentity();
				        $identifier   = $applicationMetadata->getIdentifier();
                        
				        if ($identifier instanceof NullIdentifier) {
				            throw new Exception('found NullIdentifier');
				            die();
				        }
				
				        $key = array(
				            "_id" => (string)$identifier,
				        );
				    	
				        $serialized_application = $this->serialize($deserializedApplication);
				        $insertData = array(
				            "_id" => (string)$identifier,
				            "user_id" => (string)$userIdentity,
				        	"serialized_application" => $serialized_application,
				            "status" => $status,
				            "when_updated" => $application->when_updated,
				        	"when_created" => $application->when_created,
				        	"type" => ($deserializedApplication instanceof HealthWelfareApplication)?"hw":"pf"
				        );
				        
				        $updateData = array('$set' => array(
				        	"serialized_application" => $serialized_application,
				        	"type" => ($deserializedApplication instanceof HealthWelfareApplication)?"hw":"pf"
				        ));
				        
				        if(!$this->find($key, $newCollectionName)) {
				        	$this->insert($insertData, $newCollectionName);
				        }
				        else {
				        	$this->update($key, $data, $newCollectionName);
				        }
                    }
                    else {
                    	echo "Unable to deserialize:".PHP_EOL;
                    	print_r($deserializedApplication);
                    }
                }
                else {
                	echo "Unexpected type property found in application:".PHP_EOL;
                	print_r($application);
                }
            }
        }
        
        return array('applications'=>array(
                'total' => $numberAttempted,
                'success' => $numberSucceeded,
                'donorNames' => $donorNames,
        ));
    }
    
    public function migrateTo($collectionName, $toCollection=null)
    {
        $deserializedApplications = array();
        
        $numberAttempted = 0;
        $numberSucceeded = 0;
        
        $queryResult = $this->findOne(null, $collectionName);
        
		if(!$queryResult->results) {
			echo "Collection ".$collectionName." does not exist or is empty.".PHP_EOL;
			return;
		}
		
        echo "Converting application data:".PHP_EOL;
        
        while(isset($queryResult->results) && !empty($queryResult->results))
        {
			$cursonId = $queryResult->id;
			
			$application = $queryResult->results[0];
			
			echo ++$numberAttempted.". _id:".$application->_id. "  user_id:" . $application->user_id . ' ';
			
			if (!isset($application->type)) {
				if(!is_string($application->serialized_application)) {
					echo "Unable to do base64 decoding on an object:".PHP_EOL;
					print_r($application);
					$queryResult = $this->findMore($cursonId);
					continue;
				}
				
				$base64DecodedSerializedApplication = base64_decode($application->serialized_application);
				
				if(!$base64DecodedSerializedApplication) {
					echo "base64 decoding failed:".PHP_EOL;
					print_r($application);
					$queryResult = $this->findMore($cursonId);
					continue;
				}
				
				$deserializedApplication = unserialize($base64DecodedSerializedApplication);
				
				if ($deserializedApplication instanceof Application) {
					$numberSucceeded ++;
					
					$applicationMetadata = $deserializedApplication->getMetadata();
					$status = $applicationMetadata->getStatus();
					
					$userIdentity = $applicationMetadata->getUserIdentity();
					$identifier   = $applicationMetadata->getIdentifier();
					
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
						"serialized_application" => $this->serialize($deserializedApplication),
						"status" => $status,
						"when_updated" => $application->when_updated,
						"when_created" => $application->when_created,
						"type" => ($deserializedApplication instanceof HealthWelfareApplication)?"hw":"pf"
					);
					
					$updateData = array('$set' => array(
						"serialized_application" => $this->serialize($deserializedApplication),
						"type" => ($deserializedApplication instanceof HealthWelfareApplication)?"hw":"pf"
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
					print_r($deserializedApplication);
				}
			}
			else {
				echo "Unexpected type property found in application:".PHP_EOL;
				print_r($application);
			}
			
			$queryResult = $this->findMore($cursonId);
        }
        
        echo "Converting application data complete:".PHP_EOL;
        
        return array('applications' => array(
                'total' => $numberAttempted,
                'success' => $numberSucceeded
        ));
    }
    
    public function getDataForValidatingMigrationTest()
    {
    	echo "Testing on application collection is not available when using proxy.".PHP_EOL;
    }
}

