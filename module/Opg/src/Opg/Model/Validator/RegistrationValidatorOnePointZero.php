<?php

namespace Opg\Model\Validator;

use Opg\Model\Element\AbstractRegistration;
use Opg\Model\Validator\RegistrationValidatorInterface;
use Opg\Model\Validator\Rule\RuleInterface;

class RegistrationValidatorOnePointZero implements RegistrationValidatorInterface
{
    ### PUBLIC METHODS

    public function addRule(
        RuleInterface $rule
    )
    {
        $this->rules[] = $rule;
    }

    ###

    public function validate(
        AbstractRegistration $registration
    )
    {
        foreach ($this->rules as $rule) {
            $rule->apply($registration);
        }
    }

    ### PRIVATE MEMBERS

    /**
     * @var RuleInterface[]
     */
    private $rules = array();
}
