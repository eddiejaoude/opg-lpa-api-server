<?php

return array(

    'controller_plugins' => array(

        'invokables' => array(

            'ServerUrl' => 'Opg\Controller\Plugin\ServerUrl',
        )
    ),

    'di' => array(

        'allowed_controllers' => array(

            'Opg\Controller\ApplicationController',
            'Opg\Controller\ApplicationMetadataController',
            'Opg\Controller\IndexController',
            'Opg\Controller\RegistrationController',
            'Opg\Controller\RegistrationMetadataController',
            'Opg\Controller\SummaryController',
            'Opg\Controller\MigrateController',
        ),

        'definition' => array(

            'class' => array(

                'Opg\Model\Validator\ApplicationValidatorOnePointZero' => array(
                    'addRule' => array(
                        'rule' => array(
                            'required' => true,
                            'type'     => 'Opg\Model\Validator\Rule\RuleInterface',
                        ),
                    ),
                ),

                'Opg\Model\Validator\RegistrationValidatorOnePointZero' => array(
                    'addRule' => array(
                        'rule' => array(
                            'required' => true,
                            'type'     => 'Opg\Model\Validator\Rule\RuleInterface',
                        ),
                    ),
                ),
            ),
        ),

        'instance' => array(

            'alias' => array(
                'Logger' => 'Zend\Log\LoggerInterface',
            ),

            'preference' => array(
                'Infrastructure\MongoConnectionProviderInterface'          => 'Opg\Infrastructure\MongoConnectionProvider',
                'Infrastructure\SentryLogInterface'                        => 'Opg\Infrastructure\SentryLog',
                'Infrastructure\Library\IdentifierFactoryInterface'        => 'Infrastructure\Library\UniqueIdentifierFactory',
                'Infrastructure\Library\StringConversionStrategyInterface' => 'Infrastructure\Library\HyphenatedWordsToCamelCaseConversionStrategy',
                'Infrastructure\Library\XmlSerializerInterface'            => 'Opg\Model\Serialization\Xml\AbstractElementXmlSerializer',
                'Infrastructure\Security\IdentityFactoryInterface'         => 'Infrastructure\Security\IdentityFactory',
                'Opg\Model\Validator\ApplicationValidatorInterface'        => 'Opg\Model\Validator\ApplicationValidatorOnePointZero',
                'Opg\Model\Validator\RegistrationValidatorInterface'       => 'Opg\Model\Validator\RegistrationValidatorOnePointZero',
				
				# for direct accessing mongodb  
				'Opg\Repository\ApplicationRepositoryInterface'            => 'Opg\Repository\ApplicationRepositoryMongo',
				'Opg\Repository\RegistrationRepositoryInterface'           => 'Opg\Repository\RegistrationRepositoryMongo',
            	
            	# for accessing mongodb through http proxy
            ),

            'Opg\Model\Validator\ApplicationValidatorOnePointZero' => array(
                'injections' => array(
                    'Opg\Model\Validator\Rule\DonorIsNotAttorneyRule',
                ),
            ),

            'Opg\Model\Validator\RegistrationValidatorOnePointZero' => array(
                'injections' => array(
                    // ...
                ),
            ),
        ),
    ),

    'router' => array(
        'routes' => array(

            'index' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Opg\Controller\IndexController',
                        'action'     => 'index',
                    ),
                ),
            ),

            'applications' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/applications[/:id]',
                    'defaults' => array(
                        'controller' => 'Opg\Controller\ApplicationController',
                        'action'     => 'index',
                    ),
                ),
            ),

            'summary' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/summary',
                    'defaults' => array(
                        'controller' => 'Opg\Controller\SummaryController',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            'application_metadata' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/applications/:id/metadata',
                    'defaults' => array(
                        'controller' => 'Opg\Controller\ApplicationMetadataController',
                        'action'     => 'index',
                    ),
                ),
            ),

            'application_registration' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/applications/:id/registration',
                    'defaults' => array(
                        'controller' => 'Opg\Controller\RegistrationController',
                        'action'     => 'index',
                    ),
                ),
            ),

            'application_registration_metadata' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/applications/:id/registration/metadata',
                    'defaults' => array(
                        'controller' => 'Opg\Controller\RegistrationMetadataController',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    
    'service_manager' => array(

        'factories' => array(
            'Zend\Log\LoggerInterface' => function ($sm) {

                $logger = new Zend\Log\Logger();
                $logger->addWriter(new \Zend\Log\Writer\Stream($sm->get('Config')['log']['path'] . '/error.log'));
                return $logger;
            },
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'not_found_template'       => 'error/index',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
