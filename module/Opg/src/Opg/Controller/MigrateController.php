<?php

namespace Opg\Controller;

use Opg\Model\Serialization\Json\RegistrationJsonSerializer;

use Zend\View\Model\JsonModel;
use Zend\Console\Prompt\Confirm;

use Opg\Repository\ApplicationRepositoryInterface;
use Opg\Repository\RegistrationRepositoryInterface;
use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;

use Opg\Model\Serialization\Json\ApplicationJsonSerializer;
use Opg\Model\Serialization\Json\ApplicationJsonDeserializer;
use Opg\Model\Serialization\Json\RegistrationJsonDeserializer;

use Opg\Model\Serialization\Xml\ApplicationsXmlSerializer;
use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlSerializer;
use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlSerializer;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;
use Opg\Model\Element\HealthWelfareRegistration;
use Opg\Model\Element\PropertyFinanceRegistration;

class MigrateController extends AbstractHttpController
{
    ### COLLABORATORS

    /**
     * @var ApplicationRepositoryInterface
     */
    private $applicationRepository;

    /**
     * @var RegistrationRepositoryInterface
     */
    private $registrationRepository;
    

    ### CONSTRUCTION

    public function __construct(
        ApplicationRepositoryInterface $applicationRepository,
        RegistrationRepositoryInterface $registrationRepository,
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory,
        ApplicationJsonSerializer $applicationJsonSerializer,
        ApplicationJsonDeserializer $applicationJsonDeserializer,
        RegistrationJsonSerializer $registrationJsonSerializer,
        RegistrationJsonDeserializer $registrationJsonDeserializer,
        HealthWelfareApplicationXmlSerializer $healthWelfareApplicationXmlSerializer,
        PropertyFinanceApplicationXmlSerializer $propertyFinanceApplicationXmlSerializer,
        HealthWelfareRegistrationXmlSerializer $healthWelfareRegistrationXmlSerializer,
        PropertyFinanceRegistrationXmlSerializer $propertyFinanceRegistrationXmlSerializer
    )
    {
        parent::__construct($identifierFactory, $identityFactory);
        
        $this->applicationRepository   = $applicationRepository;
        
        $this->registrationRepository  = $registrationRepository;
        
        $this->applicationJsonSerializer = $applicationJsonSerializer;
        $this->applicationJsonDeserializer = $applicationJsonDeserializer;
        
        $this->registrationJsonDeserializer = $registrationJsonDeserializer;
        $this->registrationJsonDeserializer = $registrationJsonDeserializer;
        
        $this->healthWelfareApplicationXmlSerializer = $healthWelfareApplicationXmlSerializer;
        $this->propertyFinanceApplicationXmlSerializer = $propertyFinanceApplicationXmlSerializer;
        
        $this->healthWelfareRegistrationXmlSerializer = $healthWelfareRegistrationXmlSerializer;
        $this->propertyFinanceRegistrationXmlSerializer = $propertyFinanceRegistrationXmlSerializer;
    }

    ### PUBLIC METHODS

    public function migrateAction()
    {
        $collectionType = $this->request->getParam('collectionType');
        
        if($collectionType == 'application') {
	    	$result = $this->applicationRepository->migrateTo($this->request->getParam('collectionName'), $this->request->getParam('tocollection'));
        }
        elseif($collectionType == 'registration') {
        	$result = $this->registrationRepository->migrateTo($this->request->getParam('collectionName'), $this->request->getParam('tocollection'));
        }
        
    	//return new JsonModel(
    	print_r($result);
    	
    }
    
    public function backupAction()
    {
    	$collectionType = $this->request->getParam('collectionType');
    	
    	if($collectionType == 'application') {
	    	$this->applicationRepository->backupCollection($this->request->getParam('fromCollection'), $this->request->getParam('toCollection'));
    	}
    	elseif($collectionType == 'registration') {
	    	$this->registrationRepository->backupCollection($this->request->getParam('fromCollection'), $this->request->getParam('toCollection'));
    	}
    }
    
    public function restoreAction()
    {
    	$collectionType = $this->request->getParam('collectionType');
    	
    	if($collectionType == 'application') {
	    	$this->applicationRepository->restoreCollection($this->request->getParam('fromCollection'), $this->request->getParam('toCollection'));
    	}
    	elseif($collectionType == 'registration') {
    		$this->registrationRepository->restoreCollection($this->request->getParam('fromCollection'), $this->request->getParam('toCollection'));
    	}
    }
    
    public function dropCollectionAction()
    {
    	$collection = $this->request->getParam('collectionName');
    	$confirm = new Confirm("Are you sure you want to delete the '$collection' collection? [y/N]:", 'y', "N");
    	$confirm->setIgnoreCase(false);
    	$confirm->setAllowedChars("yN\n");
    	$yes = $confirm->show();
		if ($yes) {
			$this->applicationRepository->dropCollection($collection);
		}
    }
    
    public function datatestAction()
    {
    	$no_of_apps = 0;
    	$no_of_regs = 0;
	    $allApps = $this->applicationRepository->getDataForValidatingMigrationTest($this->request->getParam('application'));
    	foreach($allApps as $row)
    	{
    		try {
	    		$application = $this->applicationJsonDeserializer->deserialize($row);
    			$type = get_class($application);
    			echo "id: ".$application->getMetadata()->getIdentifier().
    			",\t type: ". $type.
	    		",\t donor: ".$application->getDonor()->getName()->__toString();
    			if($application instanceof HealthWelfareApplication) {
		    		$xml = $this->healthWelfareApplicationXmlSerializer->serialize($application);
		    		echo ",\t xml ok.";
    				$no_of_apps++;
    			}
	    		elseif($application instanceof PropertyFinanceApplication) {
	    			$xml = $this->propertyFinanceApplicationXmlSerializer->serialize($application);
	    			echo ",\t xml ok.";
    				$no_of_apps++;
	    		}
	    		else {
	    			echo '. Application type is invalid.';
	    		}
    		}
    		catch (\Exception $e) {
    			echo $e->getMessage();
    			print_r($row);
    		}
    		
    		echo PHP_EOL;
    		usleep(10000);
    	}
	    
    	$allregs = $this->registrationRepository->getDataForValidatingMigrationTest($this->request->getParam('registration'));
    	foreach($allregs as $row)
    	{
    		$no_of_regs++;
    		try {
	    		$registration = $this->registrationJsonDeserializer->deserialize($row);
	    		$type = get_class($registration);
	    		echo "id: ".$registration->getMetadata()->getIdentifier().
	    			",\t type: ". $type.
		    		",\t no. of applicants: ".$registration->getApplicants()->count();
	    		if($registration instanceof HealthWelfareRegistration) {
		    		$xml = $this->healthWelfareRegistrationXmlSerializer->serialize($registration);
		    		echo ",\t xml ok.";
    				$no_of_apps++;
	    		}
	    		elseif($registration instanceof PropertyFinanceRegistration) {
	    			$xml = $this->propertyFinanceRegistrationXmlSerializer->serialize($registration);
	    			echo ",\t xml ok.";
    				$no_of_apps++;
	    		}
	    		else {
	    			echo '. Registration type is invalid.';
	    		}
    		}
    		catch (\Exception $e) {
    			echo $e->getMessage();
    			print_r($row);
    		}
    		
    		echo PHP_EOL;
    	}
    	
    	echo "Total no. of applications passed test: ".$no_of_apps.PHP_EOL;
    	echo "Total no. of registrations passed test: ".$no_of_regs.PHP_EOL;
    }
}
