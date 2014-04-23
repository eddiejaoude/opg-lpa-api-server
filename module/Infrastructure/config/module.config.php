<?php

return array(

    'di' => array(

        'instance' => array(

            'alias' => array(
                'ErrorListener'            => 'Infrastructure\ErrorListener',
                'LayoutDispatchListener'   => 'Infrastructure\Layout\DispatchListener',
                'SecurityDispatchListener' => 'Infrastructure\Security\DispatchListener',
            ),

            'preference' => array(
                'Infrastructure\MailSenderInterface'                            => 'Infrastructure\SimpleMailSender',
                'Infrastructure\Form\InputFilterBuilderInterface'               => 'Infrastructure\Form\RequiredInputFilterBuilder',
                'Infrastructure\Form\InputFilterFactoryInterface'               => 'Infrastructure\Form\InputFilterFactory',
                'Infrastructure\Library\PersistentDataInterface'                => 'Infrastructure\Library\SessionData',
                'Infrastructure\Library\XmlDeserializerInterface'               => 'Infrastructure\Library\XmlDeserializer',
                'Infrastructure\Library\XmlSerializerInterface'                 => 'Infrastructure\Library\XmlSerializer',
                'Infrastructure\Library\XmlValidatorInterface'                  => 'Infrastructure\Library\XmlValidator',
                'Infrastructure\Security\AccountAuthenticationAdapterInterface' => 'Infrastructure\Security\PdoAccountAuthenticationAdapter',
                'Infrastructure\Security\AccountManagementAdapterInterface'     => 'Infrastructure\Security\PdoAccountManagementAdapter',
                'Infrastructure\Security\AccountRegistrationAdapterInterface'   => 'Infrastructure\Security\PdoAccountRegistrationAdapter',
                'Infrastructure\Security\AuthorisationServiceInterface'         => 'Infrastructure\Security\NullAuthorisationService',
                'Infrastructure\Security\SecurityControllerInterface'           => 'Infrastructure\Security\SecurityController',
                'Zend\Authentication\AuthenticationService'                     => 'Infrastructure\Security\AccountService',
                'Zend\Authentication\Storage\StorageInterface'                  => 'Zend\Authentication\Storage\Session',
            ),
        ),

    ),

    'service_manager' => array(

        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),

    ),

    'translator' => array(
        'locale' => 'en_GB'
    ),

);
