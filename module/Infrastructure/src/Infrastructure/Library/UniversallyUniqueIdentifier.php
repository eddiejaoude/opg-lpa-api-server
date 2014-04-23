<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\InvariantException;

class UniversallyUniqueIdentifier implements IdentifierInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $uuid
     * @throws InvariantException When $uuid is empty
     * @throws InvariantException When $uuid is not a valid UUID
     */
    public function __construct(
        $uuid
    )
    {
        if (empty($uuid)) {
            throw new InvariantException('$uuid cannot be empty');
        }

        if (!preg_match('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/', $uuid)) {
            throw new InvariantException('$uuid is not a valid UUID');
        }

        $this->uuid = $uuid;
    }

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->uuid;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $uuid;
}
