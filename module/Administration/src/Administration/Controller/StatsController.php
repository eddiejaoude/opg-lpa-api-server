<?php

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Console\Prompt\Confirm;

class StatsController extends AbstractActionController
{
    ### COLLABORATORS
    
    ### CONSTRUCTOR

    public function __construct()
    {
    }

    ### DISPATCH ACTIONS
    
    public function getStatsAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	
    	if($this->params('backwardmonths'))
	    	return new JsonModel($statsRepo->getStats('lpaInfo', $this->params('backwardmonths')));
	    else
	    	return new JsonModel($statsRepo->getStats());
    }
    
    public function getCloneStatsAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	
    	if($this->params('backwardmonths'))
	    	return new JsonModel($statsRepo->getStats('lpaInfoClone', $this->params('backwardmonths')));
	    else
	    	return new JsonModel($statsRepo->getStats('lpaInfoClone'));
    }
    
    public function cloneAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	
    	$statsRepo->backupCollection('lpaInfo', 'lpaInfoClone');
    }
    
    public function dropAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	
    	$confirm = new Confirm("Are you sure you want to delete the lpaInfoClone collection? [y/N]:", 'y', "N");
    	$confirm->setIgnoreCase(false);
    	$confirm->setAllowedChars("yN\n");
    	$yes = $confirm->show();
		if ($yes) {
			$statsRepo->dropCollection('lpaInfoClone');
		}
    }
    
    public function checkAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	
    	if($this->request->getParam('statsCollection') == 'stats') {
	    	$statsRepo->check('lpaInfo');
    	}
    	else {
    		$statsRepo->check('lpaInfoClone');
    	}
    }
    
    public function recoverAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	
    	if($this->request->getParam('statsCollection') == 'stats') {
	    	$statsRepo->recover('lpaInfo');
    	}
    	else {
    		$statsRepo->recover('lpaInfoClone');
    	}
    }
    
    public function userLpaStatsAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	$result = $statsRepo->userLpaStats($this->params('mode'), $this->params('stage'));
    	if($this->request instanceof \Zend\Http\PhpEnvironment\Request) {
    		return new JsonModel($result);
    	}
    	else {
    		print_r($result);
    	}
    }
    
    public function allCardPaymentAction()
    {
    	$statsRepo = $this->getServiceLocator()->get('Opg\Repository\StatsRepositoryMongo');
    	return new JsonModel($statsRepo->allCardPayment());
    }
}
