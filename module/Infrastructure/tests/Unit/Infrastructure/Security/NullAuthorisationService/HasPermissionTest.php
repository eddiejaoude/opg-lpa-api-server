<?php

namespace Tests\Unit\Infrastructure\Security\NullAuthorisationService;

use Infrastructure\Security\AuthorisationServiceInterface;
use Infrastructure\Security\NullAuthorisationService;

use PHPUnit_Framework_TestCase as TestCase;

class HasPermissionTest extends TestCase
{
    public function testHasPermissionAlwaysReturnsTrue()
    {
        $mockIdentityInterface = $this->getMock('Infrastructure\Security\IdentityInterface');
        $nullAuthorisationService = new NullAuthorisationService();
        $hasPermission = $nullAuthorisationService->hasPermission($mockIdentityInterface, 'wherever', 'whenever');
        $this->assertTrue($hasPermission, "NullAuthorisationService always returns true (Null Object Pattern)");
    }
}
