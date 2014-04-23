<?php

namespace Opg\Saga\MakeLpa;

use Opg\Model\Element\AbstractApplication;

use Zend\EventManager\Event as ZendEvent;

class CreatedLpaEvent extends ZendEvent
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
        parent::__construct('LPA_CREATED', __NAMESPACE__);

        $this->application       = $application;
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
