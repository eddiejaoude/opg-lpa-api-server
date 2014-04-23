<?php

return array(
	'controllers' => array(

        'invokables' => array(
            'Administration\Controller\Application' => 'Administration\Controller\ApplicationController',
            'Administration\Controller\Registration' => 'Administration\Controller\RegistrationController',
            'Administration\Controller\Stats' => 'Administration\Controller\StatsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'delete-user-data' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/applications/delete/:user_id',
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Application',
                        'action'     => 'delete-user-applications',
                    ),
                ),
            ),
            'get-stats' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/stats[/:backwardmonths]',
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Stats',
                        'action'     => 'get-stats',
                    ),
                ),
            ),
            'get-clone-stats' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/clone-stats[/:backwardmonths]',
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Stats',
                        'action'     => 'get-clone-stats',
                    ),
                ),
            ),
            'lpa-counts' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
            	'options' => array(
                    'route' => '/admin/user-lpa-stats[/:mode][/:stage]',
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Stats',
                        'action'     => 'user-lpa-stats',
                    ),
                ),
    		),
        ),
    ),
    'console'	=> array(
	    'router' => array(
	        'routes' => array(
	            'stats-integrity-check' => array(
	                'options'       => array(
	                    'route' => 'check (stats|clone):statsCollection',
	                    'defaults' => array(
	                        'controller' => 'Administration\Controller\Stats',
	                        'action'     => 'check',
	                    ),
	                ),
	    		),
	    		'stats-recover' => array(
	                'options'       => array(
	                    'route' => 'recover (stats|clone):statsCollection',
	                    'defaults' => array(
	                        'controller' => 'Administration\Controller\Stats',
	                        'action'     => 'recover',
	                    ),
	                ),
	    		),
	            'clone-collection' => array(
	                'options'       => array(
	                    'route' => 'clone stats',
	                    'defaults' => array(
	                        'controller' => 'Administration\Controller\Stats',
	                        'action'     => 'clone',
	                    ),
	                ),
	    		),
	            'drop-collection' => array(
	                'options'       => array(
	                    'route' => 'drop clone',
	                    'defaults' => array(
	                        'controller' => 'Administration\Controller\Stats',
	                        'action'     => 'drop',
	                    ),
	                ),
	    		),
	            'lpa-counts' => array(
	                'options'       => array(
	                    'route' => 'user lpa stats [<mode>] [<stage>]',
	                    'defaults' => array(
	                        'controller' => 'Administration\Controller\Stats',
	                        'action'     => 'user-lpa-stats',
	                    ),
	                ),
	    		),
	            'card-payment' => array(
	                'options'       => array(
	                    'route' => 'card payments',
	                    'defaults' => array(
	                        'controller' => 'Administration\Controller\Stats',
	                        'action'     => 'all-card-payment',
	                    ),
	                ),
	    		),
	    	),
	    ),
    ),
);
