<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class InterfaceTest extends TestCase
{
    public function testRequiredInterfacesAreImplemented()
    {
        $class = new ReflectionClass('Infrastructure\Library\StronglyTypedCollection');
        $this->assertTrue(
            in_array('ArrayAccess',       $class->getInterfaceNames()) &&
            in_array('Countable',         $class->getInterfaceNames()) &&
            in_array('IteratorAggregate', $class->getInterfaceNames())
        );
    }
}
