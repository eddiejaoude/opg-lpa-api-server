<?php

namespace Infrastructure;

use Infrastructure\Exception\ClientException;
use Infrastructure\SentryLogInterface;

use Exception;
use ErrorException;
use RuntimeException;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\LoggerInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

class ErrorListener implements ListenerAggregateInterface
{
    ### COLLABORATORS

    /**
     * @var \Zend\Log\Logger
     */
    private $logger;
    
    /**
     * @var \Opg\Infrastructure\SentryLog
     */    
    private $sentryLog;

    ### CONSTRUCTOR

    public function __construct(
        LoggerInterface $logger,
        SentryLogInterface $sentryLog
    )
    {
        $this->logger = $logger;
        $this->sentryLog = $sentryLog;
    }

    ### PUBLIC METHODS

    /**
     * Attach listeners to an event manager
     */
    public function attach(
        EventManagerInterface $events
    )
    {
        if ($this->dispatchErrorListener !== null) {
            throw new RuntimeException('Event listener already attached');
        }

        if ($this->renderErrorListener !== null) {
            throw new RuntimeException('Event listener already attached');
        }

        $logErrors = (bool) ini_get('log_errors');
        if (!$logErrors) {
            return;
        }

        $this->dispatchErrorListener = $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            array($this, 'onError')
        );

        $this->renderErrorListener = $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'onError')
        );
    }

    ###

    /**
     * Detach listeners from an event manager
     */
    public function detach(
        EventManagerInterface $events
    )
    {
        if ($this->dispatchErrorListener === null) {
            throw new RuntimeException('Event listener not attached');
        }

        if ($this->renderErrorListener === null) {
            throw new RuntimeException('Event listener not attached');
        }

        if ($events->detach($this->dispatchErrorListener)) {
            $this->dispatchErrorListener = null;
        }

        if ($events->detach($this->renderErrorListener)) {
            $this->renderErrorListener = null;
        }
    }

    ###

    public function onError(
        MvcEvent $event
    )
    {
        $error = $event->getError();
        if ($error == Application::ERROR_EXCEPTION) {
            $exception = $event->getParam('exception');
            if ($exception instanceof ClientException) {
                return;
            }

            $this->logException($exception);
        }
    }

    ###

    public function onPhpError(
        $errno,
        $errstr,
        $errfile,
        $errline
    )
    {
        $exception = new ErrorException($errstr, $errno, 0, $errfile, $errline);
        $this->logException($exception, "PHP");

        if ($this->previousPhpErrorHandler) {
            call_user_func($this->previousPhpErrorHandler, $errno, $errstr, $errfile, $errline);
        }
    }

    ###

    public function onPhpException(
        Exception $exception
    )
    {
        $this->logException($exception);

        if ($this->previousPhpExceptionHandler) {
            call_user_func($this->previousPhpExceptionHandler, $exception);
        }
    }

    ###

    public function setupPhpHandlers()
    {
        $logErrors = (bool) ini_get('log_errors');
        if (!$logErrors) {
            return;
        }

        $previousPhpErrorHandler = set_error_handler(array($this, 'onPhpError'));
        $this->previousPhpErrorHandler = $previousPhpErrorHandler;

        $previousPhpExceptionHandler = set_exception_handler(array($this, 'onPhpException'));
        $this->previousPhpExceptionHandler = $previousPhpExceptionHandler;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \CallbackHandler|null
     */
    private $dispatchErrorListener;

    /**
     * @var callable|null
     */
    private $previousPhpErrorHandler;

    /**
     * @var callable|null
     */
    private $previousPhpExceptionHandler;

    /**
     * @var \CallbackHandler|null
     */
    private $renderErrorListener;

    ### PRIVATE METHODS

    private function getFormattedMessage(
        Exception $exception
    )
    {
        return trim($exception->getMessage());
    }

    private function getFormattedPreviousMessages(
        Exception $exception
    )
    {
        $previousException = $exception->getPrevious();
        if (!$previousException) {
            return '';
        }

        $message = "\n------------------------------------------------------------";

        while ($previousException !== null) {

            $message .= "\n".'* '.$previousException->getMessage();
            $previousException = $previousException->getPrevious();
        }

        return $message;
    }

    ###

    private function getFormattedTrace(
        Exception $exception
    )
    {
        $trace  = "\n------------------------------------------------------------";
        $trace .= "\n".$exception->getTraceAsString();
        $trace  = str_replace(getcwd().'/', '', $trace);
        $trace  = str_replace(getcwd().'\\', '', $trace);

        $digits = strlen(count($exception->getTrace()));

        $trace = preg_replace_callback(
            '/^(#[0-9]+)\s+/im',
            function ($matches) use ($digits, $exception) {
                return str_pad($matches[1], $digits+1, ' ', STR_PAD_LEFT).' ';
            }, 
            $trace
        );

        $trace = preg_replace(
            '/(.php\([0-9]+\):) /is',
            "$1\n".str_pad(' ~', $digits+1, ' ', STR_PAD_LEFT).str_pad('', $digits-1, ' '),
            $trace
        );

        return $trace;
    }

    ###

    private function logException(
        Exception $exception,
        $type = 'Uncaught'
    )
    {
        $logString =             
            $type.' '.get_class($exception).
            "\n============================================================\n".
            $this->getFormattedMessage($exception).
            $this->getFormattedTrace($exception).
            $this->getFormattedPreviousMessages($exception).
            "\n============================================================";
    
        $this->logger->err($logString);
        $this->sentryLog->log($logString);
    }
}
