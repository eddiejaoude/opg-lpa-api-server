<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class DonorDeclaration extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $dateSigned
     */
    public function __construct(
        Donor $donor,
        $dateSigned
    )
    {
        $this->donor = $donor;
        $this->dateSigned = $dateSigned;
    }

    ### PUBLIC METHODS

    public function getDonor()
    {
        return $this->donor;
    }

    ###

    public function getDateSigned()
    {
        return $this->dateSigned;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\Donor
     */
    private $donor;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateSigned;
}
