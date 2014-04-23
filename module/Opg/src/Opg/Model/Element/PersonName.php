<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class PersonName extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $forename
     * @param string $middlenames
     * @param string $surname
     */
    public function __construct(
        $forename,
        $middlenames,
        $surname
    )
    {
        $this->forename = $forename;
        $this->middlenames = $middlenames;
        $this->surname = $surname;
    }

    ### PUBLIC METHODS

    public function getForename()
    {
        return $this->forename;
    }

    ###

    public function getMiddlenames()
    {
        return $this->middlenames;
    }

    ###

    public function getSurname()
    {
        return $this->surname;
    }
    
    ###
    
    public function __toString()
    {
        $name = $this->forename;
        if ($this->middlenames != '') {
            $name .= ' ' . $this->middlenames;
        }
        $name .= ' ' . $this->surname;
        
        return $name;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $forename;

    /**
     * @var string
     */
    private $middlenames;

    /**
     * @var string
     */
    private $surname;
}
