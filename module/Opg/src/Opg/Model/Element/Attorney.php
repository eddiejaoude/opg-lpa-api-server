<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class Attorney extends AbstractElement implements AttorneyInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $title
     * @param string $emailAddress
     * @param string $phoneNumber
     * @param string $donorRelationship
     * @param string $dateOfBirth
     * @param string $isBankruptOrSubjectToDebtReliefOrder
     * @param string $companyName
     * @param string $occupation
     */
    public function __construct(
        $title,
        PersonName $name,
        $emailAddress,
        PostalAddress $postalAddress,
        $phoneNumber,
        $donorRelationship,
        $dateOfBirth,
        $isBankruptOrSubjectToDebtReliefOrder,
        $companyName,
        $occupation,
        DxAddress $dxAddress
    )
    {
        $this->title = $title;
        $this->name = $name;
        $this->emailAddress = $emailAddress;
        $this->postalAddress = $postalAddress;
        $this->phoneNumber = $phoneNumber;
        $this->donorRelationship = $donorRelationship;
        $this->dateOfBirth = $dateOfBirth;
        $this->isBankruptOrSubjectToDebtReliefOrder = $isBankruptOrSubjectToDebtReliefOrder;
        $this->companyName = $companyName;
        $this->occupation = $occupation;
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

    public function getDonorRelationship()
    {
        return $this->donorRelationship;
    }

    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    public function isBankruptOrSubjectToDebtReliefOrder()
    {
        return $this->isBankruptOrSubjectToDebtReliefOrder;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function getOccupation()
    {
        return $this->occupation;
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
    private $donorRelationship;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateOfBirth;

    /**
     * @hint YesOrNo
     * @var string
     */
    private $isBankruptOrSubjectToDebtReliefOrder;

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $occupation;

    /**
     * @var \Opg\Model\Element\DxAddress
     */
    private $dxAddress;
}
