<?php

namespace Infrastructure\Security;

use Zend\Authentication\Adapter\AdapterInterface as AuthenticationAdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;

class NullAuthenticationAdapter implements AuthenticationAdapterInterface
{
    /**
     * Null Object Pattern - always returns a successful AuthenticationResult
     *
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        return new AuthenticationResult(AuthenticationResult::SUCCESS, true);
    }
}
