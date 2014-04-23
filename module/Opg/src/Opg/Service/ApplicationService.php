<?php

namespace Opg\Service;

use Opg\Model\Element\AbstractApplication as Application;
use Opg\Model\Validator\ApplicationValidatorInterface;
use Opg\Repository\ApplicationRepositoryInterface;
use Infrastructure\Library\RecordedDateTime;
use Infrastructure\Library\IdentifierInterface;

class ApplicationService
{
    ### COLLABORATORS

    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;

    /**
     * @var ApplicationValidatorInterface
     */
    private $applicationValidator;

    ### CONSTRUCTOR

    public function __construct(
        ApplicationRepositoryInterface $applicationRepository,
        ApplicationValidatorInterface $applicationValidator
    )
    {
        $this->applicationRepository = $applicationRepository;
        $this->applicationValidator  = $applicationValidator;
    }

    ### PUBLIC METHODS

    public function delete(
        Application $application
    )
    {
        $this->applicationRepository->delete($application);
    }

    ###

    public function persist(
        Application $application
    )
    {
        $applicationMetadata = $application->getMetadata();
        $applicationMetadata->setWhenUpdated(new RecordedDateTime);

        $this->applicationRepository->persist($application);
    }

    ###

    public function validate(
        Application $application
    )
    {
        $this->applicationValidator->validate($application);
    }
    
    ###
    
    public function isApplicationIdExistent(
        IdentifierInterface $entityIdentifier
    )
    {
        return $this->applicationRepository->isApplicationIdExistent($entityIdentifier);    
    }
    
    ###
    
    public function getNewApplicationId()
    {
        return $this->applicationRepository->getNewApplicationId();
    }
    
}
