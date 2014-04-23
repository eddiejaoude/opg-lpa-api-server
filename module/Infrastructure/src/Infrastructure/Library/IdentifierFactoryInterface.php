<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierInterface;

interface IdentifierFactoryInterface
{
    /**
     * @return IdentifierInterface
     */
    public function create();

    /**
     * @param string $id
     * @return IdentifierInterface
     */
    public function fromString(
        $id
    );
}
