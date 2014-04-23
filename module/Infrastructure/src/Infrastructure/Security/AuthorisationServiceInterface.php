<?php

namespace Infrastructure\Security;

interface AuthorisationServiceInterface
{
    /**
     * @param string $controller
     * @param string $action
     * @return bool
     */
    public function hasPermission(
        IdentityInterface $identity, 
        $controller, 
        $action
    );
}
