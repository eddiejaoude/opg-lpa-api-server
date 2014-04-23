<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierGeneratorInterface;
use Infrastructure\Library\UniversallyUniqueIdentifier;

class UniversallyUniqueIdentifierGenerator implements IdentifierGeneratorInterface
{
    ### PUBLIC METHODS

    /**
     * http://en.wikipedia.org/wiki/Universally_unique_identifier
     *
     * @return UniversallyUniqueIdentifier
     */
    public function generate()
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        return new UniversallyUniqueIdentifier($uuid);
    }
}
