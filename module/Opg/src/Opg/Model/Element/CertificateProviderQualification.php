<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class CertificateProviderQualification extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $qualification
     * @param string $qualificationDetails
     */
    public function __construct(
        $qualification,
        $qualificationDetails
    )
    {
        $this->qualification = $qualification;
        $this->qualificationDetails = $qualificationDetails;
    }

    ### PUBLIC METHODS

    public function getQualification()
    {
        return $this->qualification;
    }

    public function getQualificationDetails()
    {
        return $this->qualificationDetails;
    }

    ### PRIVATE MEMBERS

    /**
    * @var string
    */
    private $qualification;

    /**
    * @var string
    */
    private $qualificationDetails;
}
