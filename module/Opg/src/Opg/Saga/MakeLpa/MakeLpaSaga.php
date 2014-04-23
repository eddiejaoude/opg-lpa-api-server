<?php

namespace Opg\Saga\MakeLpa;

use Opg\Model\Validator\ApplicationValidatorProvider;
use Opg\Model\Validator\RegistrationValidatorProvider;
use Opg\Saga\MakeLpa\StatesEnumeration;
use Infrastructure\Library\IdentifierInterface;

use Zend\EventManager\EventManagerInterface;

class MakeLpaSaga
{
    ### COLLABORATORS

    /**
     * @var ApplicationValidatorProvider
     */
    private $applicationValidatorProvider;

    /**
     * @var RegistrationValidatorProvider
     */
    private $registrationValidatorProvider;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    ### CONSTRUCTOR

    /**
     * @param int $state Mapped to StatesEnumeration
     * @throws \Opg\Saga\InvalidStateException
     */
    public function __construct(
        ApplicationValidatorProvider $applicationValidatorProvider,
        RegistrationValidatorProvider $registrationValidatorProvider,
        EventManagerInterface $eventManager,
        IdentifierInterface $id,
        $state
    )
    {
        if (!StatesEnumeration::contains($state)) {
            throw new InvalidStateException();
        }

        $this->applicationValidatorProvider = $applicationValidatorProvider;
        $this->registrationValidatorProvider = $registrationValidatorProvider;
        $this->eventManager = $eventManager;
        $this->id = $id;
        $this->state = $state;
    }

    ### PUBLIC METHODS

    /**
     * @var IdentifierInterface
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var int Mapped to StatesEnumeration
     */
    public function getState()
    {
        return $this->state;
    }

    public function handle(
        MessageInterface $message
    )
    {
        switch ($this->state) {

            case StatesEnumeration::STATE_STARTED:
                $this->tryCreateLpa($message);
                break;

            case StatesEnumeration::STATE_CREATED:
                $this->tryRegisterLpa($message);
                break;

            default: throw new InvalidStateException();

        }
    }

    ### PRIVATE MEMBERS

    /**
     * @var IdentifierInterface
     */
    private $id;

    /**
     * @var int Mapped to StatesEnumeration
     */
    private $state;

    ### PRIVATE METHODS

    private function tryCreateLpa(
        CreateLpaMessage $message
    )
    {
        $application = $message->getApplication();
        $rulesetIdentifier = $message->getRulesetIdentifier();

        $applicationValidator = $this->applicationValidatorProvider->getBestValidator($rulesetIdentifier);
        $isValid = $applicationValidator->isValid($application);

        if ($applicationIsValid) {
            $this->state = StatesEnumeration::STATE_CREATED;
            $this->eventManager->trigger(
                new CreatedLpaEvent($application, $rulesetIdentifier)
            );
        }
    }

    private function tryRegisterLpa(
        RegisterLpaMessage $message
    )
    {
        $registration = $message->getRegistration();
        $rulesetIdentifier = $message->getRulesetIdentifier();

        $registrationValidator = $this->registrationValidatorProvider->getBestValidator($rulesetIdentifier);
        $isValid = $registrationValidator->isValid($registration);

        if ($registrationIsValid) {
            $this->state = StatesEnumeration::STATE_REGISTERED;
            $this->eventManager->trigger(
                new RegisteredLpaEvent($registration, $rulesetIdentifier)
            );
        }
    }
}
