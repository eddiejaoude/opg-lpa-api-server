<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class ReplacementAttorneyDecisionInstructions extends AttorneyDecisionInstructions
{
    ### CONSTRUCTOR

    /**
     * @param string $howAttorneysMakeDecisions
     * @param string $instructions
     * @param string $howAttorneysAreReplaced
     */
    public function __construct(
        $howAttorneysMakeDecisions,
        $instructions,
        $howAttorneysAreReplaced
    )
    {
        parent::__construct($howAttorneysMakeDecisions, $instructions);

        $this->howAttorneysAreReplaced = $howAttorneysAreReplaced;
    }

    ### PUBLIC METHODS

    public function getHowAttorneysAreReplaced()
    {
        return $this->howAttorneysAreReplaced;
    }

    ### PRIVATE MEMBERS

    /**
    * @var string
    */
    private $howAttorneysAreReplaced;
}
