<?php

namespace Infrastructure\Security;

use Infrastructure\PdoConnectionProviderInterface;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;

use Zend\Authentication\Adapter\AdapterInterface as ZendAuthenticationAdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;

class PdoAccountAuthenticationAdapter implements AccountAuthenticationAdapterInterface,
                                                 ZendAuthenticationAdapterInterface
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
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        if (!($this->identity instanceof IdentityInterface)) {
            throw new RuntimeException('$identity is not set to an instance of Infrastructure\Security\IdentityInterface');
        }

        if (!($this->passwordHash instanceof PasswordHashInterface)) {
            throw new RuntimeException('$passwordHash is not set to an instance of Infrastructure\Security\PasswordHashInterface');
        }

        if ($this->isAuthenticated()) {
            return new AuthenticationResult(AuthenticationResult::SUCCESS, $this->identity);
        } else {
            return new AuthenticationResult(AuthenticationResult::FAILURE_CREDENTIAL_INVALID, $this->identity);
        }
    }

    ###

    /**
     * @return IdentityInterface|null
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    ###

    /**
     * @return PasswordHashInterface|null
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    ###

    public function setIdentity(
        IdentityInterface $identity
    )
    {
        $this->identity = $identity;
    }

    ###

    public function setPasswordHash(
        PasswordHashInterface $passwordHash
    )
    {
        $this->passwordHash = $passwordHash;
    }

    ### PRIVATE MEMBERS

    /**
     * @var IdentityInterface|null
     */
    private $identity;

    /**
     * @var PasswordHashInterface|null
     */
    private $passwordHash;

    ### PRIVATE METHODS

    private function isAuthenticated()
    {
        $db = $this->pdoConnectionProvider->getPdoConnection();

        $sql = ("SELECT identity
                   FROM user
                  WHERE identity = ".$db->quote($this->identity)."
                    AND password_hash = ".$db->quote($this->passwordHash));

        $result = $db->query($sql);
        $row = $result->fetch();

        $isAuthenticated = ($row !== false);

        return $isAuthenticated;
    }
}
