<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class AttorneyDecisionInstructions extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $howAttorneysMakeDecisions
     * @param string $instructions
     */
    public function __construct(
        $howAttorneysMakeDecisions,
        $instructions
    )
    {
        $this->howAttorneysMakeDecisions = $howAttorneysMakeDecisions;
        $this->instructions = $instructions;
    }

    ### PUBLIC METHODS

    public function getHowAttorneysMakeDecisions()
    {
        return $this->howAttorneysMakeDecisions;
    }

    public function getInstructions()
    {
        return $this->instructions;
    }

    ### PRIVATE MEMBERS

    /**
    * @var string
    */
    private $howAttorneysMakeDecisions;

    /**
    * @var string
    */
    private $instructions;
}
