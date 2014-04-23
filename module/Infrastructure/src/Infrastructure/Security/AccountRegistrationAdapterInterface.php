<?php

namespace Infrastructure\Security;

use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;

interface AccountRegistrationAdapterInterface
{
    /**
     * @return bool True when registration succeeds, false when registration fails
     */
    public function register(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash
    );
}
