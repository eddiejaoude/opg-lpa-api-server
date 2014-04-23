<?php

namespace Tests\Unit\Infrastructure\Security\DispatchListener;

use Exception;
use Infrastructure\Security\DispatchListener;
use PHPUnit_Framework_TestCase as TestCase;

class OnDispatchTest extends TestCase
{
    /**
     * @covers Infrastructure\Security\DispatchListener
     */
    public function testOnDispatch()
    {
        $mockMvcEvent = $this->getMock('Zend\Mvc\MvcEvent');
        $mockRouteMatch = $this->getMock('Zend\Mvc\Router\Http\RouteMatch', array(), array(array()));
        $mockSecurityController = $this->getMock('Infrastructure\Security\SecurityControllerInterface');

        $dispatchListener = new DispatchListener($mockSecurityController);

        $mockMvcEvent->expects($this->once())
                     ->method('getRouteMatch')
                     ->will($this->returnValue($mockRouteMatch));

        $mockSecurityController->expects($this->once())
                               ->method('applyPolicy')
                               ->with($this->equalTo($mockRouteMatch));

        $dispatchListener->onDispatch($mockMvcEvent);
    }
}
