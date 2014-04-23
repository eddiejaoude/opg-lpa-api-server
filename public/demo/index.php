<?php

use Guzzle\Http\Client;

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);

if (!strpos($_SERVER['REMOTE_ADDR'], '192.168.') === 0
    && !in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
    '10.59.5.49',
))) {

    return;
}

// Tip: Use this library for REST rather than Zend Framework
// http://guzzlephp.org/

include 'guzzle.phar';

$client = new Client('http://opg-lpa-api.local');
$client->getDefaultHeaders()->set('Token', 'bob@example.com');

$request  = $client->get('/applications');
$response = $request->send();

$doc = new SimpleXMLElement($response->getBody());
$applications = $doc->xpath('//application/@href');

$timeStarted = microtime(true);

$requests = [];
foreach ($applications as $application) {
    $url = (string) $application['href'];

    $requests[] = $client->get($url);
    $requests[] = $client->get($url.'/metadata');
}

$responses = $client->send($requests);

$timeCompleted = microtime(true);

echo '<hr>';
echo '<h2>';
echo 'Sent/Received '.count($responses).' HTTP requests in ';
echo round(($timeCompleted - $timeStarted)* 1000).'ms :-';
echo '</h2>';
echo '<p>(<a href="'.$_SERVER['REQUEST_URI'].'">refresh</a>)</p>';
echo '<hr>';

foreach ($responses as $response) {

    echo '<pre style="color: green; font-weight: bold">'.htmlspecialchars($response->getRequest()).'</pre>';
    echo '<pre style="color: blue">'. htmlspecialchars($response).'</pre>';
}
