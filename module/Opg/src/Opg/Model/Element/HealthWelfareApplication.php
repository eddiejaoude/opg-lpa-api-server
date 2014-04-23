<?php

namespace Opg\Model\Element;

use Infrastructure\Library\SerializableInterface;

class HealthWelfareApplication extends AbstractApplication implements SerializableInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $charges
     * @param string $guidance
     * @param string $restrictions
     * @param string $isGivingLifeSustainingAuthority
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
        $isGivingLifeSustainingAuthority
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

        $this->isGivingLifeSustainingAuthority = $isGivingLifeSustainingAuthority;
    }

    ### PUBLIC METHODS

    public function isGivingLifeSustainingAuthority()
    {
        return $this->isGivingLifeSustainingAuthority;
    }

    ###

    public function getSerializableData()
    {
        throw \RuntimeException('Not Implemented');
    }

    ### PRIVATE MEMBERS

    /**
     * @hint YesOrNo
     * @var string
     */
    private $isGivingLifeSustainingAuthority;
}
