<?php

namespace Tests\Unit\Infrastructure\Security\NullAuthorisationService;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class InterfaceTest extends TestCase
{
    public function testRequiredInterfacesAreImplemented()
    {
        $class = new ReflectionClass('Infrastructure\Security\NullAuthorisationService');
        $this->assertTrue(
            in_array('Infrastructure\Security\AuthorisationServiceInterface', $class->getInterfaceNames())
        );
    }
}
