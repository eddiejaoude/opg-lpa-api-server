<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class Notification extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $dateSent
     */
    public function __construct(
        NotifiedPerson $notifiedPerson,
        $dateSent
    )
    {
        $this->notifiedPerson = $notifiedPerson;
        $this->dateSent = $dateSent;
    }

    ### PUBLIC METHODS

    public function getNotifiedPerson()
    {
        return $this->notifiedPerson;
    }

    public function getDateSent()
    {
        return $this->dateSent;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\NotifiedPerson
     */
    private $notifiedPerson;

    /**
     * @hint RecordedDateTime
     * @var string
     */
    private $dateSent;
}
