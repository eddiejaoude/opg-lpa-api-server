<?php
use Opg\Infrastructure\MongoConnectionProvider;

ini_set('display_errors', 0);
//error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__). "/../data/error.log");
ini_set('date.timezone', 'Europe/London');


require dirname(__FILE__).'/../module/Infrastructure/src/Infrastructure/MongoConnectionProviderInterface.php';
require dirname(__FILE__).'/../module/Opg/src/Opg/Infrastructure/MongoConnectionProvider.php';

$settings = require dirname(__FILE__).'/../config/autoload/local.php';
$conn = $settings['di']['instance']['Opg\Infrastructure\MongoConnectionProvider']['parameters'];
$mongo = new MongoConnectionProvider(
		$conn['dataSourceHostArray'], 
		$conn['dataSourcePortArray'], 
		$conn['dataSourceDatabase'],
		$conn['dataSourceUsername'], 
		$conn['dataSourcePassword'], 
		$conn['replicaSetName'],
		$conn['connectTimeoutMillis'],
		$conn['maxConnectAttempts']);

try {
	$db = $mongo->getMongoConnection();
	header("Content-type: application/json");
	echo json_encode(array('db'=>1));
}catch(Exception $e) {
	header("Content-type: application/json");
	echo json_encode(array('db'=>0));
}
