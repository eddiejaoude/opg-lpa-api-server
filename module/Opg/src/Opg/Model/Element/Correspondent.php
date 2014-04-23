<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class Correspondent extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $title
     * @param string $emailAddress
     * @param string $phoneNumber
     * @param string $companyName
     * @param string $companyReference
     */
    public function __construct(
        $title,
        PersonName $name,
        $emailAddress,
        PostalAddress $postalAddress,
        $phoneNumber,
        $companyName,
        $companyReference,
        DxAddress $dxAddress
    )
    {
        $this->title = $title;
        $this->name = $name;
        $this->emailAddress = $emailAddress;
        $this->postalAddress = $postalAddress;
        $this->phoneNumber = $phoneNumber;
        $this->companyName = $companyName;
        $this->companyReference = $companyReference;
        $this->dxAddress = $dxAddress;
    }

    ### PUBLIC METHODS

    public function getTitle()
    {
        return $this->title;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function getCompanyReference()
    {
        return $this->companyReference;
    }

    public function getDxAddress()
    {
        return $this->dxAddress;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $title;

    /**
     * @var \Opg\Model\Element\PersonName
     */
    private $name;

    /**
     * @hint EmailAddress
     * @var string
     */
    private $emailAddress;

    /**
     * @var \Opg\Model\Element\PostalAddress
     */
    private $postalAddress;

    /**
     * @hint PhoneNumber
     * @var string
     */
    private $phoneNumber;

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $companyReference;

    /**
     * @var \Opg\Model\Element\DxAddress
     */
    private $dxAddress;
}
