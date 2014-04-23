<?php

namespace Opg;

use Infrastructure\Exception\ClientException;
use Infrastructure\Exception\HttpException;
use Infrastructure\StaticDefinitionCompiler;

use Zend\EventManager\EventInterface;
use Zend\Http\Response as HttpResponse;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ClearableModelInterface;
use Zend\View\Model\ViewModel;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements AutoloaderProviderInterface,
                        BootstrapListenerInterface,
                        ConfigProviderInterface
{
    ### PUBLIC MEMBERS

    const HIGH_PRIORITY = 100;
    const LOW_PRIORITY = -100;

    ### PUBLIC METHODS

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__),
            ),
        );
    }

    ###

    public function getConfig()
    {
        return include (__DIR__.'/config/module.config.php');
    }

    ###

    public function onBootstrap(
        EventInterface $event
    )
    {
        $application  = $event->getParam('application');
        $eventManager = $application->getEventManager();

        $response = $event->getResponse();
        if ($response instanceof HttpResponse) {

            $response->getHeaders()
                     ->clearHeaders()
                     ->addHeaders(array('Content-Type' => 'text/plain'));
        }

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), self::HIGH_PRIORITY);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onError'), self::LOW_PRIORITY);
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'), self::HIGH_PRIORITY);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'onError'), self::LOW_PRIORITY);
    }

    ###

    public function onDispatch(
        MvcEvent $event
    )
    {
        $request = $event->getRequest();
        $routeParams = $event->getParam('route-match')->getParams();
        
        // bypass token checking if the module in the route is not Opg
        if(!preg_match('/^Opg.*/', $routeParams['controller'])) return;
        
        $token   = $request->getHeader('Token');
      
        if (empty($token)) {
            
            $event->setError(Application::ERROR_EXCEPTION)
                  ->setParam('exception', new HttpException('Missing Token', HttpResponse::STATUS_CODE_401));

            $application  = $event->getApplication();
            $eventManager = $application->getEventManager();
            $eventManager->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
    }

    ###

    public function onError(
        MvcEvent $event
    )
    {
        $error = $event->getError();
        $request = $event->getRequest();
        $response = $event->getResponse();

        // HttpException always overrides the current response status code
        if ($error == Application::ERROR_EXCEPTION) {
            $exception = $event->getParam('exception');
            if ($exception instanceof HttpException) {
                $response->setStatusCode($exception->getCode());
            }
        }

        // Decide which parts of the error to include in the view
        if (($response instanceof HttpResponse) && ($response->isClientError()
            || $response->isServerError())) {

            $contentTypeHeader = $response->getHeaders()
                                          ->get('Content-Type');

            if ($contentTypeHeader) {

                $response->getHeaders()
                         ->removeHeader($contentTypeHeader);
            }

            $response->getHeaders()
                         ->addHeaderLine('Content-Type', 'text/xml');

            $result = $event->getResult();
            if (!$result) {
                $result = new ViewModel();
            }

            $code = ($response->getStatusCode().' '.$response->getReasonPhrase());
            $result->setVariable('code', $code);

            if ($error == Application::ERROR_EXCEPTION) {
            
                $exception = $event->getParam('exception');
                if ($exception instanceof ClientException) {
                
                    $message = $exception->getMessage();
                    $result->setVariable('message', $message);
                }
            }

            $event->setResult($result);

	        $method = $request->getMethod();
	        if ($method == 'HEAD') {
	
	            // Disable View Rendering
	            $viewModel = $event->getViewModel();
	            if ($viewModel instanceof ClearableModelInterface) {
	
	                $viewModel->clearChildren();
	                $viewModel->setTerminal(true);
	            }
	        }
        }
        else {
        	//echo $response->toString().PHP_EOL;
        }
    }

    ###

    public function onRender(
        MvcEvent $event
    )
    {
        if ($event->getError()) {
            return;
        }

        // Disable View Rendering
        $viewModel = $event->getViewModel();
        if ($viewModel instanceof ClearableModelInterface) {

            $viewModel->clearChildren();
            $viewModel->setTerminal(true);
        }
    }
    
    ###

    private function getClassPath()
    {
        return (__DIR__.'/src/'.str_replace('\\', '/', __NAMESPACE__));
    }
}
