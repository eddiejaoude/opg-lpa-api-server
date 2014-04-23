<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\InvariantException;

class UniqueIdentifier implements IdentifierInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $uid
     * @throws InvariantException When $uid is empty
     * @throws InvariantException When $uid is incorrect length
     */
    public function __construct(
        $uid
    )
    {
        if (empty($uid)) {
            throw new InvariantException('$uid cannot be empty');
        }

        if (strlen($uid) < 10) {
            throw new InvariantException('$uid must be ten charaters long');
        }

        $this->uid = $uid;
    }

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->uid;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $uid;
}
