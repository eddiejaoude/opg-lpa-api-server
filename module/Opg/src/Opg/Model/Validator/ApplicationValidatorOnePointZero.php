<?php

namespace Opg\Model\Validator;

use Opg\Model\Element\AbstractApplication;
use Opg\Model\Validator\ApplicationValidatorInterface;
use Opg\Model\Validator\Rule\RuleInterface;

class ApplicationValidatorOnePointZero implements ApplicationValidatorInterface
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
        AbstractApplication $application
    )
    {
        foreach ($this->rules as $rule) {
            $rule->apply($application);
        }
    }

    ### PRIVATE MEMBERS

    /**
     * @var RuleInterface[]
     */
    private $rules = array();
}
