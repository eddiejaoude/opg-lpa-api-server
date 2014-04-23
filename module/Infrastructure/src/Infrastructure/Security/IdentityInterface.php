<?php

namespace Infrastructure\Security;

interface IdentityInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function get();
}
