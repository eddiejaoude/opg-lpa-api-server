<?php

namespace Infrastructure\Security;

use Infrastructure\Security\IdentityInterface;

interface IdentityFactoryInterface
{
    /**
     * @return IdentityInterface
     */
    public function create();

    /**
     * @param string $id
     * @return IdentityInterface
     */
    public function fromString(
        $id
    );
}
