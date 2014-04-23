<?php

namespace Infrastructure\Security;

use Infrastructure\Security\AnonymousIdentity;
use Infrastructure\Security\Identity;
use Infrastructure\Security\IdentityFactoryInterface;

class IdentityFactory implements IdentityFactoryInterface
{
    /**
     * @return AnonymousIdentity
     */
    public function create()
    {
        return new AnonymousIdentity();
    }

    /**
     * @param string $id
     * @return Identity
     */
    public function fromString(
        $id
    )
    {
        return new Identity($id);
    }
}
