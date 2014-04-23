<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class PostalAddress extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $addressLine1
     * @param string $addressLine2
     * @param string $addressLine3
     * @param string $town
     * @param string $county
     * @param string $postcode
     * @param string $country
     */
    public function __construct(
        $addressLine1,
        $addressLine2,
        $addressLine3,
        $town,
        $county,
        $postcode,
        $country
    )
    {
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->addressLine3 = $addressLine3;
        $this->town = $town;
        $this->county = $county;
        $this->postcode = $postcode;
        $this->country = $country;
    }

    ### PUBLIC METHODS

    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    public function getTown()
    {
        return $this->town;
    }

    public function getCounty()
    {
        return $this->county;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getCountry()
    {
        return $this->country;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $addressLine1;

    /**
     * @var string
     */
    private $addressLine2;

    /**
     * @var string
     */
    private $addressLine3;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $county;

    /**
     * @var string
     */
    private $postcode;

    /**
     * @var string
     */
    private $country;
}
