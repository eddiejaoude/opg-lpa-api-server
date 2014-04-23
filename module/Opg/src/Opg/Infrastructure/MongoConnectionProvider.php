<?php

namespace Opg\Infrastructure;

use Infrastructure\Library\InputException;
use Infrastructure\MongoConnectionProviderInterface;
use Opg\Infrastructure\MongoConnectionInitializer;

class MongoConnectionProvider implements MongoConnectionProviderInterface
{
    ### CONSTRUCTOR

    public function __construct(
        $dataSourceHostArray,
        $dataSourcePortArray,
        $dataSourceDatabase,
        $dataSourceUsername,
        $dataSourcePassword,
        $replicaSetName,
        $maxConnectAttempts,
        $connectTimeoutMillis
    )
    {
        if (empty($dataSourceHostArray)) {
            throw new InputException('$dataSourceHostArray cannot be empty');
        }

        if (!is_array($dataSourceHostArray)) {
            throw new InputException('$dataSourceHostArray must be an array');
        }

        if ($dataSourceUsername !== null) {

            if (empty($dataSourceUsername)) {
                throw new InputException('$dataSourceUsername cannot be empty');
            }

            if (!is_string($dataSourceUsername)) {
                throw new InputException('$dataSourceUsername must be of type string');
            }
        }

        if ($dataSourcePassword !== null) {
            if (empty($dataSourcePassword)) {
                throw new InputException('$dataSourcePassword cannot be empty');
            }

            if (!is_string($dataSourcePassword)) {
                throw new InputException('$dataSourcePassword must be of type string');
            }
        }

        $this->dataSourceHostArray = $dataSourceHostArray;
        $this->dataSourcePortArray = $dataSourcePortArray;
        $this->dataSourceUsername = $dataSourceUsername;
        $this->dataSourcePassword = $dataSourcePassword;
        $this->dataSourceDatabase = $dataSourceDatabase;
        $this->replicaSetName = $replicaSetName;
        $this->maxConnectAttempts = $maxConnectAttempts;
        $this->connectTimeoutMillis = $connectTimeoutMillis;
    }

    ### PUBLIC METHODS

    public function getMongoConnection()
    {
        static $db = null;
        if ($db !== null) {
            return $db;
        }
        
        $connectString = 'mongodb://' . $this->dataSourceUsername . ':' . $this->dataSourcePassword . '@';

        $hostCount = count($this->dataSourceHostArray);
        
        for ($i=0; $i<$hostCount; $i++) {
            $connectString .= 
                $this->dataSourceHostArray[$i] . ':' .
                $this->dataSourcePortArray[$i] . ',';
        }

        $connectString = substr($connectString, 0, -1);
        $connectString .= '/' . $this->dataSourceDatabase;
        
        $options = array("connect"=>false, "connectTimeoutMS" => 50, "wTimeoutMS" => 10);
        
        if ($this->replicaSetName == '') {
            $client = new \MongoClient($connectString, $options);
        } else {
            $options['replicaSet'] = $this->replicaSetName;
            $client = new \MongoClient($connectString, $options);
        }

        $attempts = 0;
        
        while ($attempts < $this->maxConnectAttempts) {
            try {
                $client->connect();
                $db = $client->selectDB($this->dataSourceDatabase);
                return $db;
            } catch (Exception $e) {
                $attempts ++;
                // try again... an instance may have failed and a new 
                // primary may be in the process of being assigned
            }
        }
    }
    

    ### PRIVATE MEMBERS

    /**
     * @var array
     */
    private $dataSourceHostArray;

    /**
     * @var array
     */
    private $dataSourcePortArray;

    /**
     * @var string
     */
    private $dataSourceUsername;

    /**
     * @var string
     */
    private $dataSourcePassword;

    /**
     * @var string
     */
    private $dataSourceDatabase;

    /**
     * @var string
     */
    private $replicaSetName;
    
    /**
     * @var integer
     */
    private $maxConnectAttempts;
    
    /**
     * @var integer
     */
    private $connectTimeoutMillis;
    
}

