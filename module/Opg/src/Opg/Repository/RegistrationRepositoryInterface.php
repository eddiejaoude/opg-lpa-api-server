<?php

namespace Opg\Repository;

use Opg\Model\Element\AbstractRegistration as Registration;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Security\IdentityInterface;

interface RegistrationRepositoryInterface
{
    public function exists(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    );
    
    public function fetchOne(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    );
    
    public function persist(
        Registration $registration
    );
    
}
