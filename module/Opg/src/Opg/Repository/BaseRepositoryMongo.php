<?php

namespace Opg\Repository;

class BaseRepositoryMongo
{
    protected $jsonSerializer;
    protected $jsonDeserializer;
    protected $mongoConnectionProvider;
    
    private $db;
    private $defaultCollectionName;
    
    ### CONSTRUCTOR
    
    public function __construct(
    	$mongoConnectionProvider,
        $defaultCollectionName
    )
    {
        $this->mongoConnectionProvider = $mongoConnectionProvider;
    	$this->db = $this->mongoConnectionProvider->getMongoConnection();
    	$this->defaultCollectionName = $defaultCollectionName;
    }
    
    protected function find($criteria = array(), $collectionName = null)
    {
    	$collection = $this->db->selectCollection($collectionName?$collectionName:$this->defaultCollectionName);
        $queryCursor = $collection->find($criteria);
        
        return $queryCursor;
    }
    
    protected function keyExists($key, $collectionName = null)
    {
    	$collection = $this->db->selectCollection($collectionName?$collectionName:$this->defaultCollectionName);
    	return ($this->find($key, $collectionName)->count() > 0);
    }

    protected function remove($key, $collectionName = null)
    {
    	$collection = $this->db->selectCollection($collectionName?$collectionName:$this->defaultCollectionName);
        return $collection->remove($key);
    }
    
    protected function insert($data, $collectionName = null)
    {
    	$collection = $this->db->selectCollection($collectionName?$collectionName:$this->defaultCollectionName);
    	return $collection->insert($data);
    }
    
    protected function update($key, $data, $collectionName = null)
    {
    	$collection = $this->db->selectCollection($collectionName?$collectionName:$this->defaultCollectionName);
    	
    	return $collection->update($key, $data);
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
    	$collection = $this->db->selectCollection($toCollection);
		$countDestinationCollection = $collection->count();
		
		// Exit if destination collection exists and not empty
		if($countDestinationCollection > 0) {
			echo "Collection '".$toCollection."' already exists. You may backup to different collection.".PHP_EOL;
			return false;
		}
		
		// check if the source collection already exists and not empty
    	$collection = $this->db->selectCollection($fromCollection);
		$countSourceCollection = $collection->count();
		
		// Exit if source collection exists and not empty
		if(!$countSourceCollection) {
			echo "Collection '".$fromCollection."' does not exist or maybe empty. Backup failed.".PHP_EOL;
			return false;
		}
		
		// Get first document in the collection 
		$queryResult = $this->find(array(), $fromCollection);
		echo "Starting backup from collection '".$fromCollection."' to collection '".$toCollection."'".PHP_EOL;
		
		// loop through collection documents
		$numberOfItems = 0;
		foreach($queryResult as $row)
		{
			// copy document to the destination collection
			$this->insert($row, $toCollection);
			echo ++$numberOfItems.'. Copy id: '.$row['_id']. ' from '.$fromCollection.' to '.$toCollection.PHP_EOL;
			usleep(10000);
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
    	$collection = $this->db->selectCollection($fromCollection);
		$countSourceCollection = $collection->count();
		
		// Exit if source collection not exists or empty
		if(!$countSourceCollection) {
			echo "Collection '".$fromCollection."' does not exist or does not have data. Restore failed.".PHP_EOL;
			return false;
		}
		
		echo "Starting restore from collection '".$fromCollection."' to collection '".$toCollection."'".PHP_EOL;
		
		// Drop destination collection if exists and not empty
		$collection = $this->db->selectCollection($toCollection);
		$countDestinationCollection = $collection->count();
		if($countDestinationCollection > 0) {
			$dropCollectionResult = $collection->drop();
			
			if($dropCollectionResult['ok'] == 0) {
				echo "Unable to drop collection '".$toCollection."'. Operation failed.".PHP_EOL;
				exit;
			}
			else {
				echo "Collection '".$toCollection."' is dropped.".PHP_EOL;
			}
		}
		
		// restore data document by document
		$queryResult = $this->find(array(), $fromCollection);
		$numberOfItems = 0;
		foreach($queryResult as $row)
		{
			// Copy document from source to destination 
			$this->insert($row, $toCollection);
			echo ++$numberOfItems. '. Copy id: '.$row['_id']. ' from '.$fromCollection.' to '.$toCollection.PHP_EOL;
			usleep(10000);
		}
		
		// drop source collection
		$collection = $this->db->selectCollection($fromCollection);
		$dropCollectionResult = $collection->drop();
		if($dropCollectionResult['ok'] == 0) {
			echo "Drop collection '".$fromCollection."' failed.".PHP_EOL;
		}
		else {
			echo "Collection '".$fromCollection."' is dropped.".PHP_EOL;
		}
		
		echo "Restore data complete.".PHP_EOL.PHP_EOL;
		return true;
	}
	
	public function dropCollection($collectionName)
	{
		$collection = $this->db->selectCollection($collectionName);
		$dropCollectionResult = $collection->drop();
		if($dropCollectionResult['ok'] == 0) {
			echo "Unable to drop collection '".$collection."'.".PHP_EOL;
		}
		else {
			echo "Collection '".$collection."' is dropped.".PHP_EOL;
		}
	}
}

