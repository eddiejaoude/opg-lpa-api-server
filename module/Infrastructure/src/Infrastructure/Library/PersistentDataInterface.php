<?php

namespace Infrastructure\Library;

use Infrastructure\Library\NamedObjectInterface;

interface PersistentDataInterface
{
    /**
     * Data will be bound to the named object $owner
     * 
     * @return \Infrastructure\Library\PersistentDataInterface
     */
    public function bind(
        NamedObjectInterface $owner
    );

    /**
     * True if the data is bound to a named object
     * 
     * @return bool
     */
    public function isBound();

    /**
     * Get persisted data
     * 
     * @return null|mixed
     */
    public function get();

    /**
     * Set persisted data
     * 
     * @param mixed $data
     * @return \Infrastructure\Library\PersistentDataInterface
     */
    public function set(
        $data
    );
}
