<?php

namespace Infrastructure\Library;

use Infrastructure\Library\UndefinedPropertyException;

trait StrictPropertyAccessTrait
{
    /**
     * Never set properties without a defined class member!
     */
    public function __set(
        $name,
        $value
    )
    {
        throw new UndefinedPropertyException($name);
    }
}
