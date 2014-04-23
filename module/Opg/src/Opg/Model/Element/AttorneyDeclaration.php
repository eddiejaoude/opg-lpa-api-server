<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class AttorneyDeclaration extends AbstractElement implements AttorneyDeclarationInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $dateSigned
     */
    public function __construct(
        Attorney $attorney,
        $dateSigned
    )
    {
        $this->attorney = $attorney;
        $this->dateSigned = $dateSigned;
    }

    ### PUBLIC METHODS

    public function getAttorney()
    {
        return $this->attorney;
    }

    ###

    public function getDateSigned()
    {
        return $this->dateSigned;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\Attorney
     */
    private $attorney;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateSigned;
}
