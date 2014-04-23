<?php

namespace Infrastructure\Library;

interface SerializableInterface
{
    /**
     * Get data which may be serialized
     * Data must contain only nested arrays or scalar values
     * 
     * @return array
     */
    public function getSerializableData();
}
