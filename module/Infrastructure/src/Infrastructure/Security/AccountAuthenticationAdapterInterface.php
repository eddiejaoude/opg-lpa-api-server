<?php

namespace Infrastructure\Security;

use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;
use Zend\Authentication\Result as AuthenticationResult;

interface AccountAuthenticationAdapterInterface
{
    /**
     * @return AuthenticationResult
     */
    public function authenticate();

    /**
     * @return IdentityInterface|null
     */
    public function getIdentity();

    /**
     * @return PasswordHashInterface|null
     */
    public function getPasswordHash();

    public function setIdentity(
        IdentityInterface $identity
    );

    public function setPasswordHash(
        PasswordHashInterface $passwordHash
    );
}
