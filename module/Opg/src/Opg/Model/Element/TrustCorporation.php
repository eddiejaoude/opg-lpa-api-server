<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class TrustCorporation extends AbstractElement implements AttorneyInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $companyName
     * @param string $companyNumber
     */
    public function __construct(
        $companyName,
        $companyNumber,
        PersonName $authorisedPersonName,
        PostalAddress $postalAddress,
        DxAddress $dxAddress,
        $emailAddress,
        $phoneNumber
    )
    {
        $this->companyName = $companyName;
        $this->companyNumber = $companyNumber;
        $this->authorisedPersonName = $authorisedPersonName;
        $this->postalAddress = $postalAddress;
        $this->dxAddress = $dxAddress;
        $this->emailAddress = $emailAddress;
        $this->phoneNumber = $phoneNumber;
    }

    ### PUBLIC METHODS
    
    public function getCompanyName()
    {
        return $this->companyName;
    }

    ###

    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    ###

    public function getAuthorisedPersonName()
    {
        return $this->authorisedPersonName;
    }

    ###

    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    ###

    public function getDxAddress()
    {
        return $this->dxAddress;
    }

    ###

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    ###

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $companyNumber;

    /**
     * @var \Opg\Model\Element\PersonName
     */
    private $authorisedPersonName;

    /**
     * @var \Opg\Model\Element\PostalAddress
     */
    private $postalAddress;

    /**
     * @var \Opg\Model\Element\DxAddress
     */
    private $dxAddress;

    /**
     * @var string
     */
    private $phoneNumber;

    /**
     * @var string
     */
    private $emailAddress;
}
