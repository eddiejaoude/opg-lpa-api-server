<?php

namespace Tests\Unit\Infrastructure\Security\SecurityController;

use Infrastructure\Security\SecurityController;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_Constraint_IsAnything;
use Zend\Http\Response as HttpResponse;

class ApplyPolicyTest extends TestCase
{
    /**
     * @covers Infrastructure\Security\SecurityController
     */
    public function testAuthorisedBehaviour()
    {
        $mockAuthenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $mockAuthorisationService = $this->getMock('Infrastructure\Security\AuthorisationServiceInterface');
        $mockIdentityInterface = $this->getMock('Infrastructure\Security\IdentityInterface');
        $mockResponse = $this->getMock('Zend\Http\Response');
        $mockRouteMatch = $this->getMock('Zend\Mvc\Router\RouteMatch', array(), array(array()));

        $mockAuthenticationService->expects($this->once())
                                  ->method('getIdentity')
                                  ->will($this->returnValue($mockIdentityInterface));

        $mockRouteMatch->expects($this->at(0))
                       ->method('getParam')
                       ->with($this->equalTo('controller'))
                       ->will($this->returnValue('FatController'));

        $mockRouteMatch->expects($this->at(1))
                       ->method('getParam')
                       ->with($this->equalTo('action'))
                       ->will($this->returnValue('ActionMan'));

        $mockAuthorisationService->expects($this->once())
                                 ->method('hasPermission')
                                 ->with($this->equalTo($mockIdentityInterface),
                                        $this->equalTo('FatController'),
                                        $this->equalTo('ActionMan'))
                                 ->will($this->returnValue(true));

        $mockResponse->expects($this->never())
                     ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $securityController = new SecurityController($mockAuthenticationService, $mockAuthorisationService, $mockResponse);

        $securityController->applyPolicy($mockRouteMatch);
    }

    /**
     * @covers Infrastructure\Security\SecurityController
     * @expectedException Infrastructure\Security\NotPermittedException
     * @expectedExceptionMessage You are not permitted to access this resource
     */
    public function testNotAuthorisedBehaviour()
    {
        $mockAuthenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $mockAuthorisationService = $this->getMock('Infrastructure\Security\AuthorisationServiceInterface');
        $mockIdentityInterface = $this->getMock('Infrastructure\Security\IdentityInterface');
        $mockResponse = $this->getMock('Zend\Http\Response');
        $mockRouteMatch = $this->getMock('Zend\Mvc\Router\RouteMatch', array(), array(array()));

        $mockAuthenticationService->expects($this->once())
                                  ->method('getIdentity')
                                  ->will($this->returnValue($mockIdentityInterface));

        $mockRouteMatch->expects($this->at(0))
                       ->method('getParam')
                       ->with($this->equalTo('controller'))
                       ->will($this->returnValue('FatController'));

        $mockRouteMatch->expects($this->at(1))
                       ->method('getParam')
                       ->with($this->equalTo('action'))
                       ->will($this->returnValue('ActionMan'));

        $mockAuthorisationService->expects($this->once())
                                 ->method('hasPermission')
                                 ->with($this->equalTo($mockIdentityInterface),
                                        $this->equalTo('FatController'),
                                        $this->equalTo('ActionMan'))
                                 ->will($this->returnValue(false));

        $mockResponse->expects($this->once())
                     ->method('setStatusCode')
                     ->with($this->equalTo(HttpResponse::STATUS_CODE_401));

        $securityController = new SecurityController($mockAuthenticationService, $mockAuthorisationService, $mockResponse);

        $securityController->applyPolicy($mockRouteMatch);
    }
}
