<?php

namespace Infrastructure\Security;

use Infrastructure\Security\IdentityInterface;

class NullAuthorisationService implements AuthorisationServiceInterface
{
    /**
     * Null Object Pattern - always returns true, granting permission to all resources
     *
     * @return bool
     */
    public function hasPermission(
        IdentityInterface $identity,
        $controller,
        $action
    )
    {
        return true;
    }
}
