<?php

namespace Infrastructure\Security;

use Infrastructure\Library\InvariantException;

class WorldpayAccessIdentity implements IdentityInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $id
     * @throws InvariantException When $id is empty
     */
    public function __construct(
    )
    {
        $this->id = NULL;
    }

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return 'WORLDPAY';
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $id;
}
