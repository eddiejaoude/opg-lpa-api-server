<?php

namespace Opg\Repository;

use Zend\Http\Client;

class BaseRepositoryMongoHttp
{
    protected $jsonSerializer;
    protected $jsonDeserializer;
    
    private $defaultCollectionName;
    private $baseUri;
    
    ### CONSTRUCTOR
    
    public function __construct(
        $mongoHttpConnectionProvider,
        $defaultCollectionName
    )
    {
        $this->defaultCollectionName = $defaultCollectionName;
        $this->baseUri = $mongoHttpConnectionProvider->getBaseMongoHttpApiUri();
    }
    
    private function getClient($method, $collectionName, $action)
    {
        if ($collectionName == null) {
            $collectionName = $this->defaultCollectionName;
        }

       	$client = new Client(null, array('timeout'=>60));
        
        $client->setMethod($method);
        $client->setUri($this->baseUri . '/' . $collectionName . '/_' . $action);

        return $client;
    }
    
    protected function find($criteria = null, $collectionName = null, $batchSize = PHP_INT_MAX)
    {
        $client = $this->getClient('GET', $collectionName, 'find');
        
        $uri = $client->getUri();
        $uri .= '?batch_size=' . $batchSize;
        
        if ($criteria != null) {
            $uri .= '&criteria=' . stripslashes(json_encode($criteria));
        }
        $client->setUri($uri);
        
        $body = $client->send()->getBody();
        
        if (!empty($body)) {
        	$result = json_decode($body, true);
            return $result['results'];
        } else {
            return null;
        }
    }
    
    protected function findOne($criteria = null, $collectionName = null)
    {
        $client = $this->getClient('GET', $collectionName, 'find');
        
        $uri = $client->getUri();
        $uri .= '?batch_size=1';
        
        if ($criteria != null) {
            $uri .= '&criteria=' . stripslashes(json_encode($criteria));
        }
        $client->setUri($uri);
        
        $body = $client->send()->getBody();
        
        if (!empty($body)) {
            return json_decode($body);
        } else {
            return null;
        }
    }
    
    protected function findMore($cursonId, $collectionName = null)
    {
        $client = $this->getClient('GET', $collectionName, 'more');
        
        $uri = $client->getUri();
        $uri .= '?id='.$cursonId;
        $uri .= '&batch_size=1';
        
        $client->setUri($uri);
        
        $body = $client->send()->getBody();
        
        if (!empty($body)) {
            return json_decode($body);
        } else {
            return null;
        }
    }
    
    protected function findFields($fields = null, $criteria = null, $collectionName = null, $batchSize = PHP_INT_MAX)
    {
        $client = $this->getClient('GET', $collectionName, 'find');
        
        $uri = $client->getUri();
        $uri .= '?batch_size=' . $batchSize;
        
        if ($criteria != null) {
            $uri .= '&criteria=' . stripslashes(json_encode($criteria));
        }
        if ($fields != null) {
            $uri .= '&fields=' . stripslashes(json_encode($fields));
        }
        $client->setUri($uri);
        
        $body = $client->send()->getBody();
        
        if (!empty($body)) {
            return json_decode($body)->results;
        } else {
            return null;
        }
    }
    
    protected function cmd($cmd)
    {
    	static $client;
    	
        if($client===null) {
        	$client = new Client();
        }
        
        $client->setMethod('POST');
        
        $uri = $this->baseUri . '/_cmd';
        
        $client->setParameterPost(['cmd'=>json_encode($cmd)]);
        
        $client->setUri($uri);
        
        $body = $client->send()->getBody();
        
        if (!empty($body)) {
            return json_decode($body);
        } else {
            return null;
        }
    }
    
    protected function keyExists($criteria, $collectionName = null)
    {
        return $this->find($criteria, $collectionName) != null;
    }

    protected function remove($key, $collectionName = null)
    {
        $client = $this->getClient('POST', $collectionName, 'remove');
        $client->setParameterPost(['criteria'=>json_encode($key)]);
        
        $body = $client->send()->getBody();
        
        $ok = json_decode($body)->ok;
    }
    
    protected function insert($data, $collectionName = null)
    {
        $client = $this->getClient('POST', $collectionName, 'insert');
        $client->setParameterPost(['docs'=>json_encode($data)]);
    
        $client->send();
    }
    
    protected function update($key, $data, $collectionName = null)
    {

        $client = $this->getClient('POST', $collectionName, 'update');

        $criteria = json_encode($key);
        $newobj = json_encode($data);
        $client->setParameterPost(
            [
                "criteria"=>$criteria, 
                "newobj"=>$newobj
            ]
        );
        
        $client->send();
    }
    
    protected function serialize($object)
    {
        $data = $this->jsonSerializer->serialize($object);
        return $data;
    }
    
    protected function unserialize($data)
    {
        $object = $this->jsonDeserializer->deserialize($data);
        return $object;
    }
    
    
	public function backupCollection($fromCollection=null, $toCollection)
	{
		if($fromCollection == null) {
			$fromCollection = $this->defaultCollectionName;
		}
		
		// Check if the destination collection already exists and not empty
		$countDestinationCollection = $this->cmd(array('count'=>$toCollection));
		
		// Exit if destination collection exists and not empty
		if($countDestinationCollection->n > 0) {
			echo "Collection '".$toCollection."' already exists. You may backup to different collection.".PHP_EOL;
			return false;
		}
		
		// check if the source collection already exists and not empty
		$countSourceCollection = $this->cmd(array('count'=>$fromCollection));
		
		// Exit if source collection exists and not empty
		if($countSourceCollection->n == 0) {
			echo "Collection '".$fromCollection."' does not exist or maybe empty. Backup failed.".PHP_EOL;
			return false;
		}
		
		// Get first document in the collection 
		$queryResult = $this->findOne(null, $fromCollection);
		
		echo "Starting backup from collection '".$fromCollection."' to collection '".$toCollection."'".PHP_EOL;
		
		// loop through collection documents
		$numberOfItems = 0;
		while(isset($queryResult->results) && !empty($queryResult->results))
		{
			$cursonId = $queryResult->id;
			$dataObj = $queryResult->results[0];
			
			// copy document to the destination collection
			$this->insert($dataObj, $toCollection);
			echo ++$numberOfItems.'. Copy id: '.$dataObj->_id. ' from '.$fromCollection.' to '.$toCollection.PHP_EOL;
			
			// find next document
			$queryResult = $this->findMore($cursonId);
		}
		echo "Backup complete.".PHP_EOL.PHP_EOL;
		return true;
	}
	
	public function restoreCollection($fromCollection, $toCollection=null)
	{
		if($toCollection == null) {
			$toCollection = $this->defaultCollectionName;
		}
		
		// Check if the source collection has data
		$countSourceCollection = $this->cmd(array('count'=>$fromCollection));
		
		// Exit if source collection not exists or empty
		if($countSourceCollection->n == 0) {
			echo "Collection '".$fromCollection."' does not exist or does not have data. Restore failed.".PHP_EOL;
			return false;
		}
		
		echo "Starting restore from collection '".$fromCollection."' to collection '".$toCollection."'".PHP_EOL;
		
		// Drop destination collection if exists and not empty
		$countDestinationCollection = $this->cmd(array('count'=>$toCollection));
		if($countDestinationCollection->n > 0) {
			$dropCollectionResult = $this->cmd(array('drop'=>$toCollection));
			if($dropCollectionResult->ok == 0) {
				echo "Unable to drop collection '".$toCollection."'. Operation failed.".PHP_EOL;
				exit;
			}
			else {
				echo "Collection '".$toCollection."' is dropped.".PHP_EOL;
			}
		}
		
		// restore data document by document
		$queryResult = $this->findOne(null, $fromCollection);
		$numberOfItems = 0;
		while(isset($queryResult->results) && !empty($queryResult->results))
		{
			$cursonId = $queryResult->id;
			$dataObj = $queryResult->results[0];
			
			// Copy document from source to destination 
			$this->insert($dataObj, $toCollection);
			echo ++$numberOfItems. '. Copy id: '.$dataObj->_id. ' from '.$fromCollection.' to '.$toCollection.PHP_EOL;
			
			// find next document
			$queryResult = $this->findMore($cursonId);
		}
		
		// drop backup collection
		$dropCollectionResult = $this->cmd(array('drop'=>$fromCollection));
		if($dropCollectionResult->ok == 0) {
			echo "Drop collection '".$fromCollection."' failed.".PHP_EOL;
		}
		else {
			echo "Collection '".$fromCollection."' is dropped.".PHP_EOL;
		}
		
		echo "Restore data complete.".PHP_EOL.PHP_EOL;
		return true;
	}
	
	public function dropCollection($collection)
	{
		$dropCollectionResult = $this->cmd(array('drop'=>$collection));
		if($dropCollectionResult->ok == 0) {
			echo "Unable to drop collection '".$collection."'.".PHP_EOL;
		}
		else {
			echo "Collection '".$collection."' is dropped.".PHP_EOL;
		}
	}
}

