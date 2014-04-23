<?php

namespace Tests\Unit\Infrastructure\Security\NullAuthenticationAdapter;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class InterfaceTest extends TestCase
{
    public function testRequiredInterfacesAreImplemented()
    {
        $class = new ReflectionClass('Infrastructure\Security\NullAuthenticationAdapter');
        $this->assertTrue(
            in_array('Zend\Authentication\Adapter\AdapterInterface', $class->getInterfaceNames())
        );
    }
}
