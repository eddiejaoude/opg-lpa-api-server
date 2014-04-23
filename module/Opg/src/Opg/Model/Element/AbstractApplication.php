<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;
use Opg\Model\Element\ApplicationMetadata;
use Infrastructure\Library\InvariantException;
use Infrastructure\Library\UndefinedPropertyValueException;

abstract class AbstractApplication extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $charges
     * @param string $guidance
     * @param string $restrictions
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
        $restrictions
    )
    {
        $this->donor = $donor;
        $this->attorneys = $attorneys;
        $this->attorneyDecisionInstructions = $attorneyDecisionInstructions;
        $this->replacementAttorneys = $replacementAttorneys;
        $this->replacementAttorneyDecisionInstructions = $replacementAttorneyDecisionInstructions;
        $this->certificateProviders = $certificateProviders;
        $this->personsToBeNotified = $personsToBeNotified;
        $this->charges = $charges;
        $this->guidance = $guidance;
        $this->restrictions = $restrictions;
    }

    ### PUBLIC METHODS

    public function getDonor()
    {
        return $this->donor;
    }

    ###

    public function getAttorneys()
    {
        return $this->attorneys;
    }
    
    ###

    public function getAttorneyDecisionInstructions()
    {
        return $this->attorneyDecisionInstructions;
    }

    ###
    
    public function getReplacementAttorneys()
    {
        return $this->replacementAttorneys;
    }

    ###

    public function getReplacementAttorneyDecisionInstructions()
    {
        return $this->replacementAttorneyDecisionInstructions;
    }

    ###

    public function getCertificateProviders()
    {
        return $this->certificateProviders;
    }

    ###

    public function getPersonsToBeNotified()
    {
        return $this->personsToBeNotified;
    }

    ###

    public function getCharges()
    {
        return $this->charges;
    }

    ###

    public function getGuidance()
    {
        return $this->guidance;
    }

    ###

    public function getRestrictions()
    {
        return $this->restrictions;
    }

    ###

    /**
     * @throws UndefinedPropertyValueException
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            throw new UndefinedPropertyValueException('$metadata has not been set');
        }

        return $this->metadata;
    }

    ###

    /**
     * @throws InvariantException
     */
    public function setMetadata(
        ApplicationMetadata $metadata
    )
    {
        if ($this->metadata !== null) {
            throw new InvariantException('$metadata has already been set');
        }

        $this->metadata = $metadata;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\Donor
     */
    private $donor;

    /**
     * @var \Opg\Model\Element\AttorneyCollection
     */
    private $attorneys;

    /**
     * @var \Opg\Model\Element\AttorneyDecisionInstructions
     */
    private $attorneyDecisionInstructions;

    /**
     * @var \Opg\Model\Element\AttorneyCollection
     */
    private $replacementAttorneys;

    /**
     * @var \Opg\Model\Element\ReplacementAttorneyDecisionInstructions
     */
    private $replacementAttorneyDecisionInstructions;

    /**
     * @var \Opg\Model\Element\CertificateProviderCollection
     */
    private $certificateProviders;

    /**
     * @var \Opg\Model\Element\NotifiedPersonCollection
     */
    private $personsToBeNotified;

    /**
     * @var string
     */
    private $charges;

    /**
     * @var string
     */
    private $guidance;

    /**
     * @var string
     */
    private $restrictions;

    /**
     * @var ApplicationMetadata
     */
    private $metadata;
}
