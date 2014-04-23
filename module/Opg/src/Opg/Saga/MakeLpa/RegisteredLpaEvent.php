<?php

namespace Opg\Saga\MakeLpa;

use Opg\Model\Element\AbstractRegistration;

use Zend\EventManager\Event as ZendEvent;

class RegisteredLpaEvent extends ZendEvent
{
    ### CONSTRUCTOR

    /**
     * @param string $rulesetIdentifier
     */
    public function __construct(
        AbstractRegistration $registration,
        $rulesetIdentifier
    )
    {
        parent::__construct('LPA_REGISTERED', __NAMESPACE__);

        $this->registration      = $registration;
        $this->rulesetIdentifier = $rulesetIdentifier;
    }

    ### PUBLIC METHODS

    function getRegistration()
    {
        return $this->registration;
    }

    function getRulesetIdentifier()
    {
        return $this->rulesetIdentifier;
    }

    ### PRIVATE MEMBERS

    /**
     * @var AbstractRegistration
     */
    private $registration;

    /**
     * @var string
     */
    private $rulesetIdentifier;
}
