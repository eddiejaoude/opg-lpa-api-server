<?php

namespace Opg\Model\Validator\Comparison;

use Opg\Model\Element\Attorney;
use Opg\Model\Element\Donor;

class DonorToAttorneyComparison
{
    ### PUBLIC METHODS

    public function compare(
        Donor $donor,
        Attorney $attorney
    )
    {
        return ($donor->getName() == $attorney->getName()
             && $donor->getDateOfBirth() == $attorney->getDateOfBirth());
    }
}
