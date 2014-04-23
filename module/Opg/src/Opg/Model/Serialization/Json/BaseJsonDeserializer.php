<?php
namespace Opg\Model\Serialization\Json;

use LSS\Array2XML;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;
use Infrastructure\Library\RecordedDateTime;

class BaseJsonDeserializer
{
    protected $healthWelfareXmlDeserializer;
    protected $propertyFinanceXmlDeserializer;
    
    protected $identifierFactory;
    protected $identityFactory;
    
    protected function getXml($applicationArray, $documentType)
    {
        $xmlDocument = Array2XML::createXML(
            $documentType,
            $applicationArray[$documentType]
        );
        
        return $xmlDocument->saveXML();
    }
    
    protected function getDocument($data, $documentType)
    {
    	$serializedDocumentKey = 'serialized_' . $documentType;
    	$applicationAsArray = json_decode(json_encode($data[$serializedDocumentKey]), true);
    	
        $xml = $this->getXml($applicationAsArray, $documentType);
		
        if ($data['type'] == 'hw') {
            $document = $this->healthWelfareXmlDeserializer->deserialize($xml);
        } elseif ($data['type'] == 'pf') {
            $document = $this->propertyFinanceXmlDeserializer->deserialize($xml);
        } else {
            throw new Exception('Unsupported Application');
        }
        
        $classname = str_replace(
            "x",
            ucfirst($documentType),
            "Opg\Model\Element\xMetadata"
        );

        $metadata = $this->getMetadata(
            $data,
            $document,
            $classname
        );
        
        $document->setMetadata($metadata);

        return $document;
    }
    
    protected function getMetadata($data, $object, $classname)
    {
        $metadata = new $classname(
            $this->identifierFactory->fromString($data['_id']),
            $this->identityFactory->fromString($data['user_id'])
        );
        
        $whenCreated = new RecordedDateTime(date(\DateTime::ATOM, $data['when_created']));
        $whenUpdated = new RecordedDateTime(date(\DateTime::ATOM, $data['when_updated']));
        
        $metadata->setStatus($data['status']);
        
        $metadata->setWhenCreated($whenCreated);
        $metadata->setWhenUpdated($whenUpdated);
        
        serialize($metadata);
        
        return $metadata;
    }
    
    protected function extractDocument($data, $documentType)
    {
        $serializedDocumentKey = 'serialized_' . $documentType;
        
        return $this->getDocument(
            $data,
            json_decode($data[$serializedDocumentKey], true),
            $documentType
        );
    }
}
