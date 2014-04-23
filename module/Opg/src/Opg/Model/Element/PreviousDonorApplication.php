<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class PreviousDonorApplication extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $hasAppliedPreviously
     * @param string $previousApplicationDetails
     */
    public function __construct(
        $hasAppliedPreviously,
        $previousApplicationDetails
    )
    {
        $this->hasAppliedPreviously = $hasAppliedPreviously;
        $this->previousApplicationDetails = $previousApplicationDetails;
    }

    ### PUBLIC METHODS

    public function getHasAppliedPreviously()
    {
        return $this->hasAppliedPreviously;
    }

    public function getPreviousApplicationDetails()
    {
        return $this->previousApplicationDetails;
    }

    ### PRIVATE MEMBERS

    /**
     * @hint YesOrNo
     * @var string
     */
    private $hasAppliedPreviously;

    /**
     * @var string
     */
    private $previousApplicationDetails;
}
