<?php

namespace Infrastructure\Library;

interface IdentifierInterface
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
