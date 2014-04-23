<?php

namespace Opg\Model\Element;

final class ApplicationStatusEnumeration
{
    ### PUBLIC MEMBERS

    const STATUS_ACCEPTED  = 'Accepted';
    const STATUS_CREATED   = 'Created';
    const STATUS_COMPLETED = 'Completed';

    ### PUBLIC METHODS

    public static function contains($value)
    {
        return in_array($value, self::enumerate());
    }

    public static function enumerate()
    {
        return array(
            self::STATUS_ACCEPTED,
            self::STATUS_CREATED,
            self::STATUS_COMPLETED,
        );
    }
}
