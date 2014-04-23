<?php

namespace Administration\Controller;

use Infrastructure\Security\Identity;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ApplicationController extends AbstractActionController
{
    ### COLLABORATORS

    ### CONSTRUCTOR

    public function __construct()
    {
    }

    ### DISPATCH ACTIONS
    
    /**
     * Delete all applications for given user identity
     * 
     *  @return no of deleted documents in json format 
     */
    public function deleteUserApplicationsAction()
    {
    	$applicationRepo = $this->getServiceLocator()->get('Opg\Repository\ApplicationRepositoryMongo');
    	$registrationRepo = $this->getServiceLocator()->get('Opg\Repository\RegistrationRepositoryMongo');
    	
    	$deletedApplications = $applicationRepo->deleteUserApplications(new Identity($this->params('user_id')));
    	$deletedRegistrations = $registrationRepo->deleteUserRegistrations(new Identity($this->params('user_id')));
    	
    	return new JsonModel(array('applicatons'=>$deletedApplications, 'registrations'=>$deletedRegistrations));
    }
}
