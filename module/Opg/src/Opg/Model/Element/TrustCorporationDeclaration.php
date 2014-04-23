<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class TrustCorporationDeclaration extends AbstractElement implements AttorneyDeclarationInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $dateSigned
     */
    public function __construct(
        TrustCorporation $trustCorporation,
        $dateSigned
    )
    {
        $this->trustCorporation = $trustCorporation;
        $this->dateSigned = $dateSigned;
    }

    ### PUBLIC METHODS

    public function getTrustCorporation()
    {
        return $this->trustCorporation;
    }

    ###

    public function getDateSigned()
    {
        return $this->dateSigned;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\TrustCorporation
     */
    private $trustCorporation;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateSigned;
}
