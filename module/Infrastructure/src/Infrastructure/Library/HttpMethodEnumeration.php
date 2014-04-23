<?php

namespace Infrastructure\Library;

final class HttpMethodEnumeration
{
    ### PUBLIC MEMBERS

    const CONNECT = 'CONNECT';
    const DELETE  = 'DELETE';
    const GET     = 'GET';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const PATCH   = 'PATCH';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const TRACE   = 'TRACE';

    ### PUBLIC METHODS

    public static function contains($value)
    {
        return in_array($value, self::enumerate());
    }

    public static function enumerate()
    {
        return array(
            self::CONNECT,
            self::DELETE,
            self::GET,
            self::HEAD,
            self::OPTIONS,
            self::PATCH,
            self::POST,
            self::PUT,
            self::TRACE,
        );
    }
}
