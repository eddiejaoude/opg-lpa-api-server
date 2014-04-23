<?php

use Infrastructure\StaticLogger;
use Zend\Http\PhpEnvironment\Request;

chdir(dirname(__DIR__));
date_default_timezone_set('Europe/London');
error_reporting(E_ALL|E_STRICT);
ini_set('log_errors', true);

require 'init_autoloader.php';

$application = Zend\Mvc\Application::init(require 'config/application.config.php');

if (isset($_SERVER['REMOTE_ADDR']) && (strpos(@$_SERVER['REMOTE_ADDR'], '192.168.') === 0
    || in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
    '10.59.5.49',
)))) {
        
    ob_start();

    $request = new Request();
    StaticLogger::debug('>> ==========================================================');
    StaticLogger::debug($request->renderRequestLine());

    header('X-Processing-Time:');

    $application->run();

    header('X-Processing-Time: '.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'])* 1000).'ms');
    StaticLogger::debug(http_response_code().' took '.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'])* 1000).'ms');

    ob_end_flush();
    
} else {
    $application->run();
}
