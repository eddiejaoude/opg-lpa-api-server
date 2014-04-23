<?php

namespace Infrastructure\Security;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;

interface AccountManagementAdapterInterface
{
    /**
     * @return bool True when assignment succeeds, false when assignment fails
     */
    public function assignPasswordResetToken(
        IdentityInterface $identity,
        IdentifierInterface $token
    );

    ###

    /**
     * @return bool True when reset succeeds, false when reset fails
     */
    public function resetPassword(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash,
        IdentifierInterface $token
    );
}
