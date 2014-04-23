<?php

namespace Opg\Saga\MakeLpa;

final class StatesEnumeration
{
    ### PUBLIC MEMBERS

    const STATE_STARTED = 1;
    const STATE_CREATED = 2;
    const STATE_REGISTERED = 3;

    ### PUBLIC METHODS

    public static function contains($value)
    {
        return in_array($value, self::enumerate());
    }

    public static function enumerate()
    {
        return array(
            self::STATE_STARTED,
            self::STATE_CREATED,
            self::STATE_REGISTERED,
        );
    }
}
