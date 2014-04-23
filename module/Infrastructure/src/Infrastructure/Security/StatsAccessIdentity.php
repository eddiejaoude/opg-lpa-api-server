<?php

namespace Infrastructure\Security;

use Infrastructure\Library\InvariantException;

class StatsAccessIdentity implements IdentityInterface
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
        return 'STATS';
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $id;
}
