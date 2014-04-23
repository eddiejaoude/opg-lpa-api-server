<?php

namespace Opg\Model\Validator\Rule;

use Opg\Model\AbstractElement;
use Opg\Model\AbstractVisitor;
use Opg\Model\Element\AbstractApplication;
use Opg\Model\Element\Attorney;
use Opg\Model\Element\Donor;
use Opg\Model\Validator\Comparison\DonorToAttorneyComparison;
use Opg\Model\Validator\Rule\RuleInterface;

class DonorIsNotAttorneyRule extends AbstractVisitor implements RuleInterface
{
    ### PUBLIC METHODS

    public function apply(
        AbstractElement $element
    )
    {
        if (!$element instanceof AbstractApplication) {
            return;
        }

        $application = $element;
        $application->acceptVisitor($this);

        if (empty($this->donor)
            || empty($this->attorneys)) {
            return;
        }

        $comparisonHelper = new DonorToAttorneyComparison();
        foreach ($this->attorneys as $attorney) {
            if ($comparisonHelper->compare($this->donor, $attorney)) {

                $application->addValidationErrorMessage('Donor cannot be an Attorney');
                break;
            }
        }
    }

    ###

    public function visitAttorney(
        Attorney $attorney
    )
    {
        $this->attorneys[] = $attorney;
    }

    ###

    public function visitDonor(
        Donor $donor
    )
    {
        $this->donor = $donor;
    }

    ### PRIVATE MEMBERS

    /**
     * @var Attorney[]
     */
    private $attorneys = array();

    /**
     * @var Donor
     */
    private $donor;
}
