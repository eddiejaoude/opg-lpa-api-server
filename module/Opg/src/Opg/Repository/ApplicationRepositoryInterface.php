<?php

namespace Opg\Repository;

use Opg\Model\Element\AbstractApplication as Application;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Security\IdentityInterface;

interface ApplicationRepositoryInterface
{
    public function delete(
        Application $application 
    );
    
    public function exists(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    );

    public function fetchAll(
        IdentityInterface $userIdentity
    );
    
    public function fetchOne(
        IdentifierInterface $identifier,
        IdentityInterface $userIdentity
    );
    
    public function persist(
        Application $application
    );
    
    public function isApplicationIdExistent(
        IdentifierInterface $entityIdentifier
    );
    
    public function getNewApplicationId(
    );
}
