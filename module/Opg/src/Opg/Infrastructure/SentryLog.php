<?php
namespace Opg\Infrastructure;

use \Raven_Autoloader;
use \Raven_Client;

use Infrastructure\SentryLogInterface;

class SentryLog implements SentryLogInterface
{
    private $ravenClient;
    
    ### CONSTRUCTOR

    public function __construct(
        $sentryUri
    )
    {
        Raven_Autoloader::register();
         
        $this->ravenClient = new Raven_Client($sentryUri);
    }

    ### PUBLIC METHODS

    public function log(
        $logString
    )
    {
        if ($this->ravenClient) {
            $this->ravenClient->captureMessage($logString);
        }
    }
}
