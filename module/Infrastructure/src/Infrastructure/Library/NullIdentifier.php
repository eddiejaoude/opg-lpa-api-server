<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\NullObjectInterface;

use RuntimeException;

class NullIdentifier implements IdentifierInterface, NullObjectInterface
{
    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    ###

    public function get()
    {
        throw new RuntimeException('NullIdentifier has no value');
    }
}
