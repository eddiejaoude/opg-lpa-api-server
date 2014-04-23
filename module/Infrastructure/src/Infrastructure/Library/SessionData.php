<?php

namespace Infrastructure\Library;

use Infrastructure\Library\InvariantException;
use Infrastructure\Library\NamedObjectInterface;

use Zend\Session\Container as SessionContainer;

class SessionData implements PersistentDataInterface
{
    ### COLLABORATORS

    /**
     * @var \Zend\Session\Container
     */
    private $sessionContainer;

    ### CONSTRUCTOR

    public function __construct(
        SessionContainer $sessionContainer
    )
    {
        $this->sessionContainer = $sessionContainer;
    }

    ### PUBLIC METHODS

    /**
     * Data will be bound to the named object $owner
     * 
     * @return \Infrastructure\Library\PersistentDataInterface
     * @throws Infrastructure\Library\InvariantException
     */
    public function bind(
        NamedObjectInterface $owner
    )
    {
        if ($this->owner !== null
            && $this->owner !== $owner) {
            throw new InvariantException('$owner cannot be changed once set');
        }

        $this->owner = $owner;
        return $this;
    }

    /**
     * True if the data is bound to a named object
     * 
     * @return bool
     */
    public function isBound()
    {
        return ($this->owner !== null);
    }

    /**
     * Get persisted data
     * 
     * @return null|mixed
     * @throws Infrastructure\Library\InvariantException
     */
    public function get()
    {
        $key = $this->getKey();
        if ($this->sessionContainer->offsetExists($key)) {
            return $this->sessionContainer->offsetGet($key);
        }
    }

    ###

    /**
     * Set persisted data
     * 
     * @param mixed $data
     * @return \Infrastructure\Library\PersistentDataInterface
     * @throws Infrastructure\Library\InvariantException
     */
    public function set(
        $data
    )
    {
        $key = $this->getKey();
        $this->sessionContainer->offsetSet($key, $data);
        return $this;
    }

    ### PRIVATE MEMBERS

    /**
     * @var null|\Infrastructure\Library\NamedObjectInterface
     */
    private $owner;

    ### PRIVATE METHODS

    /**
     * @return string
     */
    private function getKey()
    {
        if ($this->owner === null) {
            throw new InvariantException('$owner must be set');
        }

        $ownerName = $this->owner->getName();
        if (!$ownerName) {
            throw new InvariantException('$owner name cannot be empty');
        }

        return (get_class($this->owner).'/'.$ownerName.'/data');
    }
}
