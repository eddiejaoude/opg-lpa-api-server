<?php

namespace Administration;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
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
        return $configuration;
    }

    ### PRIVATE METHODS

    private function getClassPath()
    {
        return (__DIR__.'/src/'.str_replace('\\', '/', __NAMESPACE__));
    }
}
