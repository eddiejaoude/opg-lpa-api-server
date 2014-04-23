<?php

namespace Infrastructure\Security;

use Infrastructure\Library\InvariantException;

class Identity implements IdentityInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $id
     * @throws InvariantException When $id is empty
     */
    public function __construct(
        $id
    )
    {
        if (empty($id)) {
            throw new InvariantException('$id cannot be empty');
        }

        $this->id = $id;
    }

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->id;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $id;
}
