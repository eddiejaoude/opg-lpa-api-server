<?php

namespace Opg\Saga\MakeLpa;

use Opg\Model\Element\AbstractApplication;

class CreateLpaMessage implements MessageInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $rulesetIdentifier
     */
    public function __construct(
        AbstractApplication $application,
        $rulesetIdentifier
    )
    {
        $this->application = $application;
        $this->rulesetIdentifier = $rulesetIdentifier;
    }

    ### PUBLIC METHODS

    function getApplictaion()
    {
        return $this->application;
    }

    function getRulesetIdentifier()
    {
        return $this->rulesetIdentifier;
    }

    ### PRIVATE MEMBERS

    /**
     * @var AbstractApplication
     */
    private $application;

    /**
     * @var string
     */
    private $rulesetIdentifier;
}
