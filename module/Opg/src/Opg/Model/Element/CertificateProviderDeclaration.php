<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class CertificateProviderDeclaration extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $dateSigned
     */
    public function __construct(
        CertificateProvider $certificateProvider,
        $dateSigned
    )
    {
        $this->certificateProvider = $certificateProvider;
        $this->dateSigned = $dateSigned;
    }

    ### PUBLIC METHODS

    public function getCertificateProvider()
    {
        return $this->certificateProvider;
    }

    ###

    public function getDateSigned()
    {
        return $this->dateSigned;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\CertificateProvider
     */
    private $certificateProvider;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateSigned;
}
