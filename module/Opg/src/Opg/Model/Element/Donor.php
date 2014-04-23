<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class Donor extends AbstractElement implements ApplicantRoleInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $title
     * @param string $emailAddress
     * @param string $phoneNumber
     * @param string $alias
     * @param string $dateOfBirth
     * @param string $hasAbilityToSign
     */
    public function __construct(
        $title,
        PersonName $name,
        $emailAddress,
        PostalAddress $postalAddress,
        $phoneNumber,
        $alias,
        $dateOfBirth,
        $hasAbilityToSign
    )
    {
        $this->title = $title;
        $this->name = $name;
        $this->emailAddress = $emailAddress;
        $this->postalAddress = $postalAddress;
        $this->phoneNumber = $phoneNumber;
        $this->alias = $alias;
        $this->dateOfBirth = $dateOfBirth;
        $this->hasAbilityToSign = $hasAbilityToSign;
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

    public function getAlias()
    {
        return $this->alias;
    }

    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    public function getHasAbilityToSign()
    {
        return $this->hasAbilityToSign;
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
    private $alias;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateOfBirth;

    /**
     * @hint YesOrNo
     * @var string
     */
    private $hasAbilityToSign;
}
