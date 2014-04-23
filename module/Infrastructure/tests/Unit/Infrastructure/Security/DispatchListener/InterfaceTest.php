<?php

namespace Tests\Unit\Infrastructure\Security\DispatchListener;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class InterfaceTest extends TestCase
{
    public function testRequiredInterfacesAreImplemented()
    {
        $class = new ReflectionClass('Infrastructure\Security\DispatchListener');
        $this->assertTrue(
            in_array('Zend\EventManager\ListenerAggregateInterface', $class->getInterfaceNames())
        );
    }
}
