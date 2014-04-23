<?php

namespace Infrastructure\Security;

use Infrastructure\PdoConnectionProviderInterface;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;

class PdoAccountRegistrationAdapter implements AccountRegistrationAdapterInterface
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

    ### PUBLIC METHODS

    /**
     * @return bool True when registration succeeds, false when registration fails
     */
    public function register(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash
    )
    {
        $db = $this->pdoConnectionProvider->getPdoConnection();

        $sql = "SELECT identity
                  FROM user
                 WHERE identity = ".$db->quote($identity);

        $result = $db->query($sql);
        $row = $result->fetch();

        $isAlreadyRegistered = ($row !== false);
        if ($isAlreadyRegistered) {
            return false;
        }

        return (bool) $db->exec(
            "INSERT INTO user ( identity,
                                password_hash )
                       VALUES ( ".$db->quote($identity).",
                                ".$db->quote($passwordHash)." )"
        );
    }
}
