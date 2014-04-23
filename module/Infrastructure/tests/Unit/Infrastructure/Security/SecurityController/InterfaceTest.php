<?php

namespace Tests\Unit\Infrastructure\Security\SecurityController;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class InterfaceTest extends TestCase
{
    /**
     * @covers Infrastructure\Security\SecurityControllerInterface
     */
    public function testRequiredInterfacesAreImplemented()
    {
        $class = new ReflectionClass('Infrastructure\Security\SecurityController');
        $this->assertTrue(
            in_array('Infrastructure\Security\SecurityControllerInterface', $class->getInterfaceNames())
        );
    }
}
