<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierInterface;

interface IdentifierGeneratorInterface
{
    /**
     * @return IdentifierInterface
     */
    public function generate();
}
