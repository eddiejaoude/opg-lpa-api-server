<?php

namespace Opg\Model\Element;

use Opg\Model\Element\ApplicationStatusEnumeration;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\InvalidDateException;
use Infrastructure\Library\InvalidEnumerationValue;
use Infrastructure\Library\RecordedDateTime;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Library\SerializableInterface;

class ApplicationMetadata implements SerializableInterface
{
    ### CONSTRUCTOR

    public function __construct(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    )
    {
        $this->identifier = $identifier;
        $this->userIdentity = $userIdentity;
        $this->status = ApplicationStatusEnumeration::STATUS_ACCEPTED;
        $this->whenCreated = new RecordedDateTime();
        $this->whenUpdated = new RecordedDateTime();
    }

    ### PUBLIC METHODS
    
    public function getIdentifier()
    {
        return $this->identifier;
    }

    ###

    public function getUserIdentity()
    {
        return $this->userIdentity;
    }

    ###

    public function getStatus()
    {
        return $this->status;
    }

    ###

    public function getWhenCreated()
    {
        return $this->whenCreated;
    }

    ###

    public function getWhenUpdated()
    {
        return $this->whenUpdated;
    }

    ###
    
    public function getSerializableData()
    {
    
    }
    
    public function toArray()
    {
        return [
            "identifier" => (string)$this->identifier,
            "userIdentity" => (string)$this->userIdentity,
            "status" => $this->status,
            "whenCreated" => date(\DateTime::ATOM, $this->whenCreated->getTimestamp()),
            "whenUpdated" => date(\DateTime::ATOM, $this->whenUpdated->getTimestamp()),
        ];
    }
    
    ###

    /**
     * @param string $status Mapped to ApplicationStatusEnumeration
     * @throws InvalidEnumerationValue
     */
    public function setStatus(
        $status
    )
    {
        if (!ApplicationStatusEnumeration::contains($status)) {
            throw new InvalidEnumerationValue('$status is not mappable to \Opg\Model\Element\ApplicationStatusEnumeration');
        }

        $this->status = $status;
    }
    
    ###

    public function setWhenUpdated(
        RecordedDateTime $whenUpdated
    )
    {
        $this->whenUpdated = $whenUpdated;
    }

    public function setWhenCreated(
        RecordedDateTime $whenCreated
    )
    {
        $this->whenCreated = $whenCreated;
    }
    
    ### PRIVATE MEMBERS

    /**
     * @var IdentifierInterface
     */
    private $identifier;

    /**
     * @var IdentityInterface
     */
    private $userIdentity;

    /**
     * @var string Mapped to ApplicationStatusEnumeration
     */
    private $status;

    /**
     * @var RecordedDateTime
     */
    private $whenCreated;

    /**
     * @var RecordedDateTime
     */
    private $whenUpdated;
}
