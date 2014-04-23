<?php

namespace Opg\Saga\MakeLpa;

use Opg\Model\Element\AbstractRegistration;

class RegisterLpaMessage implements MessageInterface
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
        $this->registration = $registration;
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
     * @var \Opg\Model\Element\AbstractRegistration
     */
    private $registration;

    /**
     * @var string
     */
    private $rulesetIdentifier;
}
