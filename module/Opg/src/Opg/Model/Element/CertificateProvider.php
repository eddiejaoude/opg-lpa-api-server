<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class CertificateProvider extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $title
     * @param string $emailAddress
     */
    public function __construct(
        $title,
        PersonName $name,
        $emailAddress,
        PostalAddress $postalAddress,
        CertificateProviderQualification $qualification
    )
    {
        $this->title = $title;
        $this->name = $name;
        $this->emailAddress = $emailAddress;
        $this->postalAddress = $postalAddress;
        $this->qualification = $qualification;
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

    public function getQualification()
    {
        return $this->qualification;
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
     * @var \Opg\Model\Element\CertificateProviderQualification
     */
    private $qualification;
}
