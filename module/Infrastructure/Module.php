<?php

namespace Infrastructure;

use Infrastructure\StaticDefinitionCompiler;
use Infrastructure\StaticLogger;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\ServiceManager;

class Module implements AutoloaderProviderInterface, BootstrapListenerInterface, ConfigProviderInterface
{
    ### PUBLIC MEMBERS

    const HIGH_PRIORITY = 100;
    const LOW_PRIORITY = -100;

    ### PUBLIC METHODS

    public function getAutoloaderConfig()
    {
        $classPath = $this->getClassPath();

        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(__NAMESPACE__ => $classPath),
            ),
        );
    }

    ###

    public function getConfig()
    {
        $classPath = $this->getClassPath();
        $configuration = include(__DIR__.'/config/module.config.php');

        if (defined('USE_COMPILED_DI_DEFINITIONS')
            && USE_COMPILED_DI_DEFINITIONS) {

            $definitionFileLocation = ('./data/cache/'.str_replace('\\', '', __NAMESPACE__).'Definition.php');
            StaticDefinitionCompiler::compileToFile($classPath, $definitionFileLocation);

            if (file_exists($definitionFileLocation)) {
                $configuration['di']['definition']['compiler'] = array($definitionFileLocation);
            }
        }

        return $configuration;
    }

    ###

    public function onBootstrap(
        EventInterface $event
    )
    {
        $application    = $event->getApplication();
        $eventManager   = $application->getEventManager();
        $serviceManager = $application->getServiceManager();

        $this->attachErrorLogger($eventManager, $serviceManager);
        $this->attachLayoutDispatchListener($eventManager, $serviceManager);
        $this->attachSecurityDispatchListener($eventManager, $serviceManager);

        $this->initializeStaticLogger($serviceManager);
    }

    ### PRIVATE METHODS

    private function attachErrorLogger(
        EventManagerInterface $eventManager,
        ServiceManager $serviceManager
    )
    {
        if ($serviceManager->has('Logger')) {
            $errorListener = $serviceManager->get('ErrorListener');
            $errorListener->setupPhpHandlers();
            $eventManager->attach($errorListener, self::LOW_PRIORITY);
        }
    }

    ###

    private function attachLayoutDispatchListener(
        EventManagerInterface $eventManager,
        ServiceManager $serviceManager
    )
    {
        if ($serviceManager->has('LayoutController')) {
            $layoutDispatchListener = $serviceManager->get('LayoutDispatchListener');
            $eventManager->attach($layoutDispatchListener, self::HIGH_PRIORITY);
        }
    }

    ###

    private function attachSecurityDispatchListener(
        EventManagerInterface $eventManager,
        ServiceManager $serviceManager
    )
    {
        if ($serviceManager->has('SecurityController')) {
            $securityDispatchListener = $serviceManager->get('SecurityDispatchListener');
            $eventManager->attach($securityDispatchListener, self::HIGH_PRIORITY);
        }
    }

    ###

    private function getClassPath()
    {
        return (__DIR__.'/src/'.str_replace('\\', '/', __NAMESPACE__));
    }

    ###

    private function initializeStaticLogger(
        ServiceManager $serviceManager
    )
    {
        if ($serviceManager->has('Logger')) {
            $logger = $serviceManager->get('Zend\Log\LoggerInterface');
            StaticLogger::setLogger($logger);
        }
    }
}
