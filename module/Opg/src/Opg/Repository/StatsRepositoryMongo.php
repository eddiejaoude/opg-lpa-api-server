<?php

namespace Opg\Repository;

use Opg\Model\Element\AbstractApplication as Application;
use Infrastructure\MongoConnectionProviderInterface;

class StatsRepositoryMongo
    extends BaseRepositoryMongo
{
    
    public function __construct(
        MongoConnectionProviderInterface $mongoConnectionProvider
    )
    {
        parent::__construct($mongoConnectionProvider, 'lpaInfo');
    }
    
    ###
    
    ### PUBLIC METHODS
    
    public function check($statsCollectionName = 'lpaInfo')
    {
    	$db = $this->mongoConnectionProvider->getMongoConnection();
    	
    	// find the corrupt data which is missing user_id and other fields. 
    	$statsWithErrors = $db->selectCollection($statsCollectionName)->find(array('user_id'=>null));
    	
    	echo ("======================= to be removed =======================".PHP_EOL);
    	// stats data that are no longer useful due the application data has been deleted by the user
    	$uc = 0;
    	$ustates = array();
    	foreach($statsWithErrors as $lpaId => $lpaStats) {
    		$state = $lpaStats['state'];
	       	$application = $db->selectCollection('application')->findOne(array('_id'=>$lpaId));
    		if($application === null) {
	    		print_r($lpaStats);
    			$uc++;
    			// stats which are no longer useful. 
    			printf($lpaStats['_id'].", state: %8s".PHP_EOL, $state);
    			
	    		if(isset($ustates[$state])) {
	    			$ustates[$state]++;
	    		}
	    		else {
	    			$ustates[$state] = 1;
	    		}
    		}
    	}
       	
    	$rc = 0;
    	$rstates = array();
    	echo ("======================= to be recovered =======================".PHP_EOL);
    	// check if the missing info in stats can be found from the application or registration
    	foreach($statsWithErrors as $lpaId => $lpaStats) {
    		$state = $lpaStats['state'];
	       	$application = $db->selectCollection('application')->findOne(array('_id'=>$lpaId));
	       	$registration = $db->selectCollection('registration')->findOne(array('_id'=>$lpaId));
	       	
    		if($application !== null) {
    			$rc++;
    			$output = $lpaStats['_id']. ", state: %8s".
    				", started on: ".date('d/m/Y',$application['when_created']).
    				($registration?', created on: '.date('d/m/Y',$registration['when_created']):'');
    			
    			// warn registration data not found.
    			if(!$registration) {
    				echo 'Missing registration data when state is '.$lpaStats['state'].PHP_EOL;
    			}
    			
    			if($lpaStats['state'] == 'created') {
    				if(!isset($lpaStats['when_created'])) {
    					echo 'Missing when_created in stats when state is creted'.PHP_EOL;
    				}
    				
    				if(isset($registration['serialized_registration']['registration']['payment-instructions']['payment-method']) && 
    					!empty($registration['serialized_registration']['registration']['payment-instructions']['payment-method'])) {
    					if(($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] == 'CARD') &&
    						isset($registration['serialized_registration']['registration']['online-payment']['worldpay-ref']) && 
    						!empty($registration['serialized_registration']['registration']['online-payment']['worldpay-ref'])) {
    							$output .= ", signed on: ".date_create_from_format('Y-m-d', $registration['serialized_registration']['registration']['donor-declaration']['date-signed'])->format('d/m/Y');
    							$output .= ", completed on: ".date('d/m/Y',$registration['when_updated']);
    						}
    					elseif($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] != 'CARD') {
    						$output .= ", signed on: ".date_create_from_format('Y-m-d', $registration['serialized_registration']['registration']['donor-declaration']['date-signed'])->format('d/m/Y');
    						$output .= ", completed on: ".date('d/m/Y',$registration['when_updated']);
    					}
    				}
    				elseif(isset($registration['serialized_registration']['registration']['donor-declaration']['date-signed']) &&
    					!empty($registration['serialized_registration']['registration']['donor-declaration']['date-signed'])) {
    					$output .= ", signed on: ".date_create_from_format('Y-m-d', $registration['serialized_registration']['registration']['donor-declaration']['date-signed'])->format('d/m/Y');
    				}
    			}
    			elseif($lpaStats['state'] == 'signed') {
    				if(!isset($lpaStats['when_signed'])) {
    					echo 'Missing when_signed in stats when state is signed'.PHP_EOL;
    				}
    				
    				if(isset($registration['serialized_registration']['registration']['payment-instructions']['payment-method']) && 
    					!empty($registration['serialized_registration']['registration']['payment-instructions']['payment-method'])) {
    					if(($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] == 'CARD') &&
    						isset($registration['serialized_registration']['registration']['online-payment']['worldpay-ref']) &&
    						!empty($registration['serialized_registration']['registration']['online-payment']['worldpay-ref'])) {
    							$output .= ", signed on: ".date('d/m/Y', $lpaStats['when_signed']);
    							$output .= ", completed on :".date('d/m/Y',$registration['when_updated']);
    						}
    					elseif($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] != 'CARD') {
    						$output .= ", signed on: ".date('d/m/Y', $lpaStats['when_signed']);
    						$output .= ", completed on: ".date('d/m/Y',$registration['when_updated']);
    					}
    				}
    				else {
	    				$output .= ', signed on: '.date('d/m/Y', $lpaStats['when_signed']);
    				}
    			}
    			elseif($lpaStats['state'] == 'completed') {
    				if(!isset($lpaStats['when_completed'])) {
    					echo 'Missing when_completed in stats when state is completed'.PHP_EOL;
    				}
    				if(!isset($registration['when_created'])) {
    					echo '$registration[when_created] does not exist when state is completed'.PHP_EOL;
    				}
    				$output .= ', signed on: '.date('d/m/Y', (int) ($lpaStats['when_completed']+$registration['when_created'])/2) . 
    					', completed on: '.date('d/m/Y', $lpaStats['when_completed']);
    			}
    			
    			printf($output.PHP_EOL, $state);
    		
	    		if(isset($rstates[$state])) {
	    			$rstates[$state]++;
	    		}
	    		else {
	    			$rstates[$state] = 1;
	    		}
    		}
    	}
    	
       	\Zend\Debug\Debug::dump($ustates, 'unrecoverable stats rows');
    	\Zend\Debug\Debug::dump($rstates, 'recoverable stats rows');
    	
    	echo "total number of un-recoverable data: ".$uc.PHP_EOL;
    	echo "total number of recoverable data:".$rc.PHP_EOL;
    }
	
    public function recover($statsCollectionName = 'lpaInfo')
    {
    	$testRun = true;
    	
    	if($testRun) {
    		$updateResult= array('updatedExisting'=>1, 'n'=>1);
    	}
    	
    	$db = $this->mongoConnectionProvider->getMongoConnection();
    	
    	$stats = $db->selectCollection($statsCollectionName)->find(array('user_id'=>null));
    	
    	foreach($stats as $lpaId=>$appStats)
    	{
    		echo '============================'.PHP_EOL;
       		$application = $db->selectCollection('application')->findOne(array('_id'=>$lpaId));
        	
        	if($application !== null) {
        		// LPA DATA WHICH RELATES TO THIS STATS EXISTS IN DB 
        		$user_id = $application['user_id'];
        		$type = $application['type'];
        		
        		// this is when user started creating this LPA
        		$whenStarted = (int) $application['when_created'];
        		
        		// work out when LPA was fully created
        		$registration = $db->selectCollection('registration')->findOne(array('_id'=>$lpaId));
        		
        		$whenCreated = (int) $registration['when_created'];
        		
        		// update missing user_id, LPA type and when_started.
        		if($appStats['state'] == 'created') {
        			if(!isset($appStats['when_created'])) {
        				echo 'Missing when_created in stats but state is created'.PHP_EOL;
        			}
        			
    				if(isset($registration['serialized_registration']['registration']['payment-instructions']['payment-method']) && 
    					!empty($registration['serialized_registration']['registration']['payment-instructions']['payment-method'])) {
    					if(($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] == 'CARD') &&
    						isset($registration['serialized_registration']['registration']['online-payment']['worldpay-ref']) && 
    						!empty($registration['serialized_registration']['registration']['online-payment']['worldpay-ref'])) {
    							$updateParams = array('$set'=>array(
    								'user_id'		=> $user_id, 
    								'type'			=> $type, 
    								'when_started'	=> $whenStarted,
    								'when_created'	=> $whenCreated, 
    								'when_signed'	=> date_create_from_format('Y-m-d H:i:s', $registration['serialized_registration']['registration']['donor-declaration']['date-signed'].' 00:00:00')->getTimestamp(),
    								'when_completed'=> $registration['when_updated']
    							));
    						}
						elseif($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] != 'CARD') {
							$updateParams = array('$set'=>array(
								'user_id'		=> $user_id,
								'type'			=> $type,
								'when_started'	=> $whenStarted,
								'when_created'	=> $whenCreated,
								'when_signed'	=> date_create_from_format('Y-m-d H:i:s', $registration['serialized_registration']['registration']['donor-declaration']['date-signed'].' 00:00:00')->getTimestamp(),
								'when_completed'=> $registration['when_updated']
							));
						}
    				}
    				elseif(isset($registration['serialized_registration']['registration']['donor-declaration']['date-signed']) &&
    					!empty($registration['serialized_registration']['registration']['donor-declaration']['date-signed'])) {
						$updateParams = array('$set'=>array(
							'user_id'		=> $user_id, 
							'type'			=> $type,
							'when_started'	=> $whenStarted,
							'when_created'	=> $whenCreated,
							'when_signed'	=> date_create_from_format('Y-m-d H:i:s', $registration['serialized_registration']['registration']['donor-declaration']['date-signed'].' 00:00:00')->getTimestamp()
						));
    				}
        			else {
						$updateParams = array('$set'=>array(
							'user_id'		=> $user_id, 
							'type'			=> $type, 
							'when_started'	=> $whenStarted,
							'when_created'	=> $whenCreated
						));
        			}
        			
        			$testRun or $updateResult = $db->selectCollection($statsCollectionName)->update(
	        			array('_id'=>$lpaId), 
	        			$updateParams
	        		);
	        		
	        		echo $lpaId. " updated: ".$updateResult['updatedExisting'].PHP_EOL;
	        		print_r($updateParams);
        		}
        		
        		// update missing user_id, LPA type and when_started, and when_created
        		if($appStats['state'] == 'signed') {
        			if(!isset($appStats['when_signed'])) {
        				echo 'Missing when_signed in stats but state is signed'.PHP_EOL;
        			}
        			
    				if(isset($registration['serialized_registration']['registration']['payment-instructions']['payment-method']) && 
    					!empty($registration['serialized_registration']['registration']['payment-instructions']['payment-method'])) {
    					if(($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] == 'CARD') &&
    						isset($registration['serialized_registration']['registration']['online-payment']['worldpay-ref']) &&
    						!empty($registration['serialized_registration']['registration']['online-payment']['worldpay-ref'])) {
    							$updateParams = array('$set'=>array(
    								'user_id'		=> $user_id, 
    								'type'			=> $type, 
    								'when_started'	=> $whenStarted,	
    								'when_created'	=> $whenCreated,
    								'when_signed'	=> $appStats['when_signed'],
    								'when_completed'=> $registration['when_updated']
    							));
    						}
    					elseif($registration['serialized_registration']['registration']['payment-instructions']['payment-method'] != 'CARD') {
							$updateParams = array('$set'=>array(
								'user_id'		=> $user_id, 
								'type'			=> $type, 
								'when_started'	=> $whenStarted,
								'when_created'	=> $whenCreated,
								'when_signed'	=> $appStats['when_signed'],
								'when_completed'=> $registration['when_updated']
							));
    					}
    				}
    				else {
						$updateParams = array('$set'=>array(
							'user_id'		=> $user_id, 
							'type'			=> $type, 
							'when_started'	=> $whenStarted,
							'when_created'	=> $whenCreated,
							'when_signed'	=> $appStats['when_signed']
						));
    				}
        			
        			$testRun or $updateResult = $db->selectCollection($statsCollectionName)->update(
	        			array('_id'=>$lpaId), 
	        			$updateParams
	        		);
	        		
	        		echo $lpaId. " updated: ".$updateResult['updatedExisting'].PHP_EOL;
	        		print_r($updateParams);
        		}
        		
        		// update missing user_id, LPA type and when_started, when_created and when_signed
        		if($appStats['state'] == 'completed') {
        			if(!isset($appStats['when_completed'])) {
        				echo 'Missing when_completed in stats but state is completed'.PHP_EOL;
        			}
        			else if(!isset($registration['when_created'])) {
        				echo 'Missing when_created in registration but state is completed'.PHP_EOL;
        			}
        			
        			$whenSigned = (int) (($appStats['when_completed']+$registration['when_created'])/2);
        			$updateParams = array('$set'	=> array( 
        				'user_id'		=> $user_id,
        				'type'			=> $type,
        				'when_started'	=> $whenStarted,
        				'when_created'	=> $whenCreated,
        				'when_signed'	=> $whenSigned,
        				'when_completed'=> $appStats['when_completed']
        			));
	        		
	        		$testRun or $updateResult = $db->selectCollection($statsCollectionName)->update(
	        			array('_id'		=> $lpaId), 
	        			$updateParams
	        		);
	        		
	        		echo $lpaId. " updated: ".$updateResult['updatedExisting'].PHP_EOL;
	        		print_r($updateParams);
        		}
        	}
        	else {
    			/**
    			 *  LPA data has been deleted by the user, but stats data is still there. 
    			 *  Therefore this stats can be removed as we can not find any valueable information 
    			 *  from it and therefore no long be able to link to any user or LPA.
    			 */
        		if($appStats['state'] == 'created') {
	        		$updateParams = array('$set' => array(
        				'user_id'		=> 'deleted0000000000000000000000001',
        				'type'			=> (rand(0,1)?'pf':'hw'),
	        			'when_started'	=> (int)$appStats['when_created'] - 84600,
	        			'when_created'	=> (int)$appStats['when_created'],
	        			'when_deleted'	=> (int)$appStats['when_created'] + 84600
	        		));
        		}
        		elseif($appStats['state'] == 'signed') {
	        		$updateParams = array('$set' => array(
        				'user_id'		=> 'deleted0000000000000000000000001',
        				'type'			=> (rand(0,1)?'pf':'hw'),
	        			'when_started'	=> (int)$appStats['when_signed'] - 2*84600,
	        			'when_created'	=> (int)$appStats['when_signed'] - 84600,
	        			'when_signed'	=> (int)$appStats['when_signed'],
	        			'when_deleted'	=> (int)$appStats['when_signed'] + 84600
	        		));
        		}
        		else {
        			echo '!!: NOT handled for '.$lpaId.' '.$appStats['state'].PHP_EOL;
        		}
        		
        		$testRun or $updateResult = $db->selectCollection($statsCollectionName)->update(array('_id'=>$lpaId), $updateParams);
        		echo $lpaId. " UPDATED: ".$updateResult['n'].PHP_EOL;
        		print_r($updateParams);
         	}
    	}
    }
    
    /**
     * Get stats from back to given months
     * 
     * @param negative int $from
     */
    public function getStats($statsCollectionName='lpaInfo', $from=0)
    {
    	if($from>0) $from = -$from;
		
		$thisMonth = getdate(time());
		if($thisMonth['mon']+$from < 0) {
			$thisMonth['mon'] += 12+$from;
			$thisMonth['year'] -= 1;
		}
		else {
			$thisMonth['mon'] +=  $from;
		}
		
		$curMonthStart = mktime(0,0,0,$thisMonth['mon'],1,$thisMonth['year']);
		$nextMonth = getdate(strtotime("+31 day", $curMonthStart));
		$curMonthEnd = strtotime('-1 second', mktime(0,0,0,$nextMonth['mon'],1,$nextMonth['year']));
		
		$lastMonthEnd = $curMonthStart-1;
		$oneMonthAgo = getdate($lastMonthEnd);
		$lastMonthStart = mktime(0,0,0,$oneMonthAgo['mon'],1,$oneMonthAgo['year']);
		
		$lastTwoMonthEnd = $lastMonthStart-1;
		$twoMonthAgo = getdate($lastTwoMonthEnd);
		$lastTwoMonthStart = mktime(0,0,0,$twoMonthAgo['mon'],1,$twoMonthAgo['year']);
		
		$lastThreeMonthEnd = $lastTwoMonthStart-1;
		$threeMonthAgo = getdate($lastThreeMonthEnd);
		$lastThreeMonthStart = mktime(0,0,0,$threeMonthAgo['mon'],1,$threeMonthAgo['year']);
		
//		echo date('d/m/Y H:i:s', $curMonthStart).' - ';
//		echo date('d/m/Y H:i:s', $curMonthEnd).PHP_EOL;
//		
//		echo date('d/m/Y H:i:s', $lastMonthStart).' - ';
//		echo date('d/m/Y H:i:s', $lastMonthEnd).PHP_EOL;
//		
//		echo date('d/m/Y H:i:s', $lastTwoMonthStart).' - ';
//		echo date('d/m/Y H:i:s', $lastTwoMonthEnd).PHP_EOL;
//		
//		echo date('d/m/Y H:i:s', $lastThreeMonthStart).' - ';
//		echo date('d/m/Y H:i:s', $lastThreeMonthEnd).PHP_EOL;
		
		return array(
	        'total' => $this->find([], $statsCollectionName)->count(),
	        'started' => $this->find(array('state'=>'started'), $statsCollectionName)->count(),
	        'created' => $this->find(array('state'=>'created'), $statsCollectionName)->count(),
        	'signed' => $this->find(array('state'=>'signed'), $statsCollectionName)->count(),
	        'completed' => $this->find(array('state'=>'completed'), $statsCollectionName)->count(),
	        'deleted' => $this->find(array('state'=>'deleted'), $statsCollectionName)->count(),
        	
	        'cur_month_started' => $this->find(array('when_started'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)), $statsCollectionName)->count(),
	        'cur_month_created' => $this->find(array('when_created'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)), $statsCollectionName)->count(),
	        'cur_month_signed' => $this->find(array('when_signed'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)), $statsCollectionName)->count(),
	        'cur_month_completed' => $this->find(array('when_completed'=>array('$gte'=>$curMonthStart, '$lte'=>$curMonthEnd)), $statsCollectionName)->count(),
        	
	        'one_month_ago_started' => $this->find(array('when_started'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)), $statsCollectionName)->count(),
	        'one_month_ago_created' => $this->find(array('when_created'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)), $statsCollectionName)->count(),
	        'one_month_ago_signed' => $this->find(array('when_signed'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)), $statsCollectionName)->count(),
	        'one_month_ago_completed' => $this->find(array('when_completed'=>array('$gte'=>$lastMonthStart, '$lte'=>$lastMonthEnd)), $statsCollectionName)->count(),
        	
        	'two_month_ago_started' => $this->find(array('when_started'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)), $statsCollectionName)->count(),
	        'two_month_ago_created' => $this->find(array('when_created'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)), $statsCollectionName)->count(),
	        'two_month_ago_signed' => $this->find(array('when_signed'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)), $statsCollectionName)->count(),
	        'two_month_ago_completed' => $this->find(array('when_completed'=>array('$gte'=>$lastTwoMonthStart, '$lte'=>$lastTwoMonthEnd)), $statsCollectionName)->count(),
        	
        	'three_month_ago_started' => $this->find(array('when_started'=>array('$gte'=>$lastThreeMonthStart, '$lte'=>$lastThreeMonthEnd)), $statsCollectionName)->count(),
	        'three_month_ago_created' => $this->find(array('when_created'=>array('$gte'=>$lastThreeMonthStart, '$lte'=>$lastThreeMonthEnd)), $statsCollectionName)->count(),
	        'three_month_ago_signed' => $this->find(array('when_signed'=>array('$gte'=>$lastThreeMonthStart, '$lte'=>$lastThreeMonthEnd)), $statsCollectionName)->count(),
	        'three_month_ago_completed' => $this->find(array('when_completed'=>array('$gte'=>$lastThreeMonthStart, '$lte'=>$lastThreeMonthEnd)), $statsCollectionName)->count(),
        	
        	'total_hw' => $this->find(array('type'=>'hw'), $statsCollectionName)->count(),
        	'total_pf' => $this->find(array('type'=>'pf'), $statsCollectionName)->count(),
        	'total_cheque' => $this->find(array('pay_by'=>'CHEQUE'), $statsCollectionName)->count(),
        	'total_card' => $this->find(array('pay_by'=>'CARD', 'when_completed'=>array('$ne'=>null)), $statsCollectionName)->count(),
        	'total_incomplete_card' => $this->find(array('pay_by'=>'CARD', 'when_completed'=>null), $statsCollectionName)->count(),
			'total_uc' => $this->find(array('pay_by'=>'PENDING UNIVERSAL CREDIT'), $statsCollectionName)->count(),
        	'total_benifit' => $this->find(array('receiving_benefits'=>true), $statsCollectionName)->count(),
        	'total_discount' => $this->find(array('applying_discount'=>true), $statsCollectionName)->count(),
        	'total_damage' => $this->find(array('damage_award'=>true), $statsCollectionName)->count(),
        	'hw_started' => $this->find(array('type'=>'hw', 'state'=>'started'), $statsCollectionName)->count(),
        	'hw_created' => $this->find(array('type'=>'hw', 'state'=>'created'), $statsCollectionName)->count(),
        	'hw_signed' => $this->find(array('type'=>'hw', 'state'=>'signed'), $statsCollectionName)->count(),
        	'hw_completed' => $this->find(array('type'=>'hw', 'state'=>'completed'), $statsCollectionName)->count(),
        	'hw_deleted' => $this->find(array('type'=>'hw', 'state'=>'deleted'), $statsCollectionName)->count(),
        	'pf_started' => $this->find(array('type'=>'pf', 'state'=>'started'), $statsCollectionName)->count(),
        	'pf_created' => $this->find(array('type'=>'pf', 'state'=>'created'), $statsCollectionName)->count(),
        	'pf_signed' => $this->find(array('type'=>'pf', 'state'=>'signed'), $statsCollectionName)->count(),
        	'pf_completed' => $this->find(array('type'=>'pf', 'state'=>'completed'), $statsCollectionName)->count(),
        	'pf_deleted' => $this->find(array('type'=>'pf', 'state'=>'deleted'), $statsCollectionName)->count()
        );
    }
    
    public function userLpaStats($mode=null, $stage=null)
    {
    	$statsCollection = $this->mongoConnectionProvider->getMongoConnection()->selectCollection('lpaInfo');
    	
		$modes = array(
			'ByUserCount' => array(
		    		array(
		    			'$match' => array(
		    				'user_id' => array('$nin' => array(null, '', 'deleted0000000000000000000000001'))
		    			)
		    		),
		    		array(
		    			'$group' => array(
		    				'_id'	=> '$user_id',
		    				'total' => array('$sum' => 1)
		    			)
		    		),
		    		array(
		    			'$group' => array(
		    				'_id'	=> '$total',
		    				'no_of_users' => array('$sum' => 1)
		    			)
		    		),
		    		array(
		    			'$sort' => array('no_of_users' => -1)
		    		)
		    	),
			'ByLpaCount' => array(
		    		array(
		    			'$match' => array(
		    				'user_id' => array('$nin' => array(null, '', 'deleted0000000000000000000000001'))
		    			)
		    		),
		    		array(
		    			'$group' => array(
		    				'_id'	=> '$user_id',
		    				'total' => array('$sum' => 1)
		    			)
		    		),
		    		array(
		    			'$group' => array(
		    				'_id'	=> '$total',
		    				'no_of_users' => array('$sum' => 1)
		    			)
		    		),
		    		array(
		    			'$sort' => array('_id' => -1)
		    		)
		    	),
			'All' => array(
		    		array(
		    			'$match' => array(
		    				'user_id' => array('$nin' => array(null, '', 'deleted0000000000000000000000001'))
		    			)
		    		),
		    		array(
		    			'$group' => array(
		    				'_id'	=> '$user_id',
		    				'no_of_lpas' => array('$sum' => 1)
		    			)
		    		),
		    		array(
		    			'$sort' => array('no_of_lpas' => -1)
		    		)
		    	)
		);
		
		$stages = array(
			'started' => 'when_started',
			'created' => 'when_created',
			'signed'  => 'when_signed',
			'completed' => 'when_completed'
		);
		
		if($mode && isset($modes[$mode])) {
			$options = $modes[$mode];
		}
		else {
			$options = $modes['ByLpaCount'];
		}
		
		if($stage && isset($stages[$stage])) {
			$options[0]['$match'][$stages[$stage]] = array('$ne'=>null);
		}
		else {
			$options[0]['$match']['when_completed'] = array('$ne'=>null);
		}
		
    	$results = $statsCollection->aggregate($options);
    	return $results;
    }
    
    /**
     * Get all card payments LPA id from stats and registration clooections
     */
    public function allCardPayment()
    {
    	$db = $this->mongoConnectionProvider->getMongoConnection();
    	$collection = $db->selectCollection('lpaInfo');
    	$result = $collection->find(array('pay_by'=>'CHEQUE', 'when_completed'=>array('$ne'=>null)), array('_id'=>true));
    	$lpaIds = array_keys(iterator_to_array($result));
    	echo json_encode($lpaIds).PHP_EOL;
    	echo 'No. of payments: '.sizeof($lpaIds).PHP_EOL;
    	
    	$collection = $db->selectCollection('registration');
    	$result = $collection->find(array('serialized_registration.registration.payment-result' => array('$ne'=>'')));
    	$lpaIds = array_keys(iterator_to_array($result));
    	echo json_encode($lpaIds).PHP_EOL;
    	echo 'No. of payments: '.sizeof($lpaIds).PHP_EOL;
    	
    }
}
