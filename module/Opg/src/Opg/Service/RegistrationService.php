<?php

namespace Opg\Service;

use Opg\Model\Element\AbstractRegistration as Registration;
use Opg\Model\Validator\RegistrationValidatorInterface;
use Opg\Repository\RegistrationRepositoryInterface;
use Infrastructure\Library\RecordedDateTime;

class RegistrationService
{
    ### COLLABORATORS

    /**
     * @var RegistrationRepositoryInterface
     */
    private $registrationRepository;

    /**
     * @var RegistrationValidatorInterface
     */
    private $registrationValidator;

    ### CONSTRUCTOR

    public function __construct(
        RegistrationRepositoryInterface $registrationRepository,
        RegistrationValidatorInterface $registrationValidator
    )
    {
        $this->registrationRepository = $registrationRepository;
        $this->registrationValidator  = $registrationValidator;
    }

    ### PUBLIC METHODS

    public function persist(
        Registration $registration
    )
    {
        $registrationMetadata = $registration->getMetadata();
        $registrationMetadata->setWhenUpdated(new RecordedDateTime);

        $this->registrationRepository->persist($registration);
    }

    public function validate(
        Registration $registration
    )
    {
        $this->registrationValidator->validate($registration);
    }
}
