<?php

namespace Infrastructure\Security;

use Infrastructure\PdoConnectionProviderInterface;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;

class PdoAccountManagementAdapter implements AccountManagementAdapterInterface
{
    ### COLLABORATORS

    /**
     * @var PdoConnectionProviderInterface
     */
    private $pdoConnectionProvider;

    ### CONSTRUCTOR

    public function __construct(
        PdoConnectionProviderInterface $pdoConnectionProvider
    )
    {
        $this->pdoConnectionProvider = $pdoConnectionProvider;
    }

    ### PUBLIC MEMBERS

    const RESET_PASSWORD_TOKEN_EXPIRES_AFTER_DAYS = 1;

    ### PUBLIC METHODS

    /**
     * @return bool True when assignment succeeds, false when assignment fails
     */
    public function assignPasswordResetToken(
        IdentityInterface $identity,
        IdentifierInterface $token
    )
    {
        $db = $this->pdoConnectionProvider->getPdoConnection();

        $sql = ("SELECT identity
                   FROM user
                  WHERE identity = ".$db->quote($identity));

        $result = $db->query($sql);
        $row = $result->fetch();

        $isNotRegistered = (!$row);
        if ($isNotRegistered) {
            return false;
        }

        $sql = ("UPDATE user
                    SET reset_password_token = ".$db->quote($token).",
                        when_reset_password_token_sent = CURRENT_TIMESTAMP
                  WHERE identity = ".$db->quote($identity));

        return (bool) $db->exec($sql);
    }

    /**
     * @return bool True when reset succeeds, false when reset fails
     */
    public function resetPassword(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash,
        IdentifierInterface $token
    )
    {
        $db = $this->pdoConnectionProvider->getPdoConnection();

        $expiresAfterDays = (int) self::RESET_PASSWORD_TOKEN_EXPIRES_AFTER_DAYS;

        $sql = ("SELECT identity
                   FROM user
                  WHERE identity = ".$db->quote($identity)."
                    AND reset_password_token= ".$db->quote($token)."
                    AND when_reset_password_token_sent > DATE( 'now', '-".$expiresAfterDays." day' )");

        $result = $db->query($sql);
        $row = $result->fetch();

        $isValidToken = ($row !== false);
        if (!$isValidToken) {
            return false;
        }

        $sql = ("UPDATE user
                    SET password_hash = ".$db->quote($passwordHash).",
                        reset_password_token = '',
                        when_reset_password_token_sent = DATE( 'now', '-".$expiresAfterDays." day' )
                  WHERE identity = ".$db->quote($identity));

        return (bool) $db->exec($sql);
    }
}
