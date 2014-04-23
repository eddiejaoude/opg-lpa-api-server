<?php

namespace Opg\Model\Element;

use Infrastructure\Library\SerializableInterface;

class PropertyFinanceApplication extends AbstractApplication implements SerializableInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $charges
     * @param string $guidance
     * @param string $restrictions
     * @param string $whenToStartInstruction
     */
    public function __construct(
        Donor $donor,
        AttorneyCollection $attorneys,
        AttorneyDecisionInstructions $attorneyDecisionInstructions,
        AttorneyCollection $replacementAttorneys,
        ReplacementAttorneyDecisionInstructions $replacementAttorneyDecisionInstructions,
        CertificateProviderCollection $certificateProviders,
        NotifiedPersonCollection $personsToBeNotified,
        $charges,
        $guidance,
        $restrictions,
        $whenToStartInstruction
    )
    {
        parent::__construct(
            $donor,
            $attorneys,
            $attorneyDecisionInstructions,
            $replacementAttorneys,
            $replacementAttorneyDecisionInstructions,
            $certificateProviders,
            $personsToBeNotified,
            $charges,
            $guidance,
            $restrictions
        );

        $this->whenToStartInstruction = $whenToStartInstruction;
    }

    ### PUBLIC METHODS

    public function getWhenToStartInstruction()
    {
        return $this->whenToStartInstruction;
    }

    ###

    public function getSerializableData()
    {
        throw \RuntimeException('Not Implemented');
    }

    ### PRIVATE MEMBERS

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $whenToStartInstruction;
}
