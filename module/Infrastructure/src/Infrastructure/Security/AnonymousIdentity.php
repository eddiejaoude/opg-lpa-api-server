<?php

namespace Infrastructure\Security;

use Infrastructure\Library\InvariantException;

use RuntimeException;

class AnonymousIdentity implements IdentityInterface
{
    ### PUBLIC METHODS

    public function __toString()
    {
        return '';
    }

    ###

    public function get()
    {
        throw new RuntimeException('AnonymousIdentity has no value');
    }
}
