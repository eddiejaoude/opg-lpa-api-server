<?php

namespace Infrastructure\Security;

interface PasswordHashInterface
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
