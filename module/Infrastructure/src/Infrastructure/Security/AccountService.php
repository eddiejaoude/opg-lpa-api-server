<?php

namespace Infrastructure\Security;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Security\AccountAuthenticationAdapterInterface;
use Infrastructure\Security\AccountManagementAdapterInterface;
use Infrastructure\Security\AccountRegistrationAdapterInterface;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\PasswordHashInterface;

use RuntimeException;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\Storage\StorageInterface;

class AccountService extends ZendAuthenticationService
{
    ### COLLABORATORS

    /**
     * @var AccountManagementAdapterInterface
     */
    private $accountManagementAdapter;

    /**
     * @var AccountRegistrationAdapterInterface
     */
    private $accountRegistrationAdapter;

    ### CONSTRUCTOR

    public function __construct(
        StorageInterface $storage,
        AccountAuthenticationAdapterInterface $accountAuthenticationAdapter,
        AccountManagementAdapterInterface $accountManagementAdapter,
        AccountRegistrationAdapterInterface $accountRegistrationAdapter
    )
    {
        parent::__construct($storage, $accountAuthenticationAdapter);

        $this->accountManagementAdapter = $accountManagementAdapter;
        $this->accountRegistrationAdapter = $accountRegistrationAdapter;
    }

    ### PUBLIC METHODS

    /**
     * @return bool True when assignment succeeds, false when assignment fails
     */
    public function assignPasswordResetToken(
        IdentityInterface $identity,
        IdentifierInterface $token
    )
    {
        return $this->accountManagementAdapter->assignPasswordResetToken($identity, $token);
    }

    ###

    /**
     * @return AuthenticationResult
     */
    public function authenticateCredentials(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash
    )
    {
        $adapter = $this->getAdapter();
        if (!($adapter instanceof AccountAuthenticationAdapterInterface)) {
            throw new RuntimeException('Adapter must be instance of Infrastructure\Security\AccountAuthenticationAdapterInterface');
        }

        $adapter->setIdentity($identity);
        $adapter->setPasswordHash($passwordHash);

        return parent::authenticate();
    }

    ###

    /**
     * Returns the identity from storage or AnonymousIdentity if no identity is available
     *
     * @return IdentityInterface
     */
    public function getIdentity()
    {
        $identity = parent::getIdentity();
        if (!($identity instanceof IdentityInterface)) {
            return new AnonymousIdentity();
        } else {
            return $identity;
        }
    }

    ###

    /**
     * @return AuthenticationResult
     */
    public function registerCredentials(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash
    )
    {
        $this->accountRegistrationAdapter->register($identity, $passwordHash);
        return $this->authenticateCredentials($identity, $passwordHash);
    }

    ###

    /**
     * @return bool True when reset succeeds, false when reset fails
     */
    public function resetPassword(
        IdentityInterface $identity,
        PasswordHashInterface $passwordHash,
        IdentifierInterface $token
    )
    {
        return $this->accountManagementAdapter->resetPassword($identity, $passwordHash, $token);
    }
}
