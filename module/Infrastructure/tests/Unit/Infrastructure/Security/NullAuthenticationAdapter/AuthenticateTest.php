<?php

namespace Tests\Unit\Infrastructure\Security\NullAuthenticationAdapter;

use Infrastructure\Security\NullAuthenticationAdapter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Authentication\Result as AuthenticationResult;

class AuthenticateTest extends TestCase
{
    public function testSuccessfulAuthenticate()
    {
        $nullAuthenticationAdapter = new NullAuthenticationAdapter();

        $result = $nullAuthenticationAdapter->authenticate();

        $this->assertTrue($result instanceof AuthenticationResult);
        $this->assertEquals(AuthenticationResult::SUCCESS, $result->getCode());
        $this->assertEquals(1, $result->getIdentity());
    }
}
