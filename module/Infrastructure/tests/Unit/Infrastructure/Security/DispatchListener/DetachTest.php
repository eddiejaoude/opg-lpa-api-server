<?php

namespace Tests\Unit\Infrastructure\Security\DispatchListener;

use Infrastructure\Security\DispatchListener;
use PHPUnit_Framework_Constraint_IsAnything;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\MvcEvent;

class DetachTest extends TestCase
{
    /**
     * @covers Infrastructure\Security\DispatchListener
     */
    public function testDetachCallback()
    {
        $mockEventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $mockSecurityController = $this->getMock('Infrastructure\Security\SecurityControllerInterface');

        $mockSecurityController->expects($this->never())
                               ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $dispatchListener = new DispatchListener($mockSecurityController);

        $mockEventManager->expects($this->exactly(2))
                         ->method('attach')
                         ->with($this->equalTo(MvcEvent::EVENT_DISPATCH),
                                $this->equalTo(array($dispatchListener, 'onDispatch')))
                         ->will($this->returnValue('EVENT_MANAGER_REFERENCE'));

        $mockEventManager->expects($this->once())
                         ->method('detach')
                         ->with($this->equalTo('EVENT_MANAGER_REFERENCE'))
                         ->will($this->returnValue(true));

        $dispatchListener->attach($mockEventManager);
        $dispatchListener->detach($mockEventManager);
        $dispatchListener->attach($mockEventManager);
    }

    /**
     * @covers Infrastructure\Security\DispatchListener
     * @expectedException RuntimeException
     * @expectedExceptionMessage Event listener already attached
     */
    public function testDetachCallbackDoesNothingWhenEventManagerReturnsFalse()
    {
        $mockEventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $mockSecurityController = $this->getMock('Infrastructure\Security\SecurityControllerInterface');

        $mockSecurityController->expects($this->never())
                               ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $dispatchListener = new DispatchListener($mockSecurityController);

        $mockEventManager->expects($this->once())
                         ->method('attach')
                         ->with($this->equalTo(MvcEvent::EVENT_DISPATCH),
                                $this->equalTo(array($dispatchListener, 'onDispatch')))
                         ->will($this->returnValue('EVENT_MANAGER_REFERENCE'));

        $mockEventManager->expects($this->once())
                         ->method('detach')
                         ->with($this->equalTo('EVENT_MANAGER_REFERENCE'))
                         ->will($this->returnValue(false));

        $dispatchListener->attach($mockEventManager);
        $dispatchListener->detach($mockEventManager);
        $dispatchListener->attach($mockEventManager);
    }

    /**
     * @covers Infrastructure\Security\DispatchListener
     * @expectedException RuntimeException
     * @expectedExceptionMessage Event listener not attached
     */
    public function testDetachWhenCallbackNotAttachedThrows()
    {
        $mockEventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $mockSecurityController = $this->getMock('Infrastructure\Security\SecurityControllerInterface');

        $mockEventManager->expects($this->never())
                         ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $mockSecurityController->expects($this->never())
                               ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $dispatchListener = new DispatchListener($mockSecurityController);

        $dispatchListener->detach($mockEventManager);
    }
}
