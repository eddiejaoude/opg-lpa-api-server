<?php

namespace Tests\Unit\Infrastructure\Security\DispatchListener;

use Infrastructure\Security\DispatchListener;
use PHPUnit_Framework_Constraint_IsAnything;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\MvcEvent;

class AttachTest extends TestCase
{
    /**
     * @covers Infrastructure\Security\DispatchListener
     */
    public function testSuccessfulAttach()
    {
        $mockEventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $mockSecurityController = $this->getMock('Infrastructure\Security\SecurityControllerInterface');

        $mockSecurityController->expects($this->never())
                               ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $dispatchListener = new DispatchListener($mockSecurityController);

        $mockEventManager->expects($this->once())
                         ->method('attach')
                         ->with($this->equalTo(MvcEvent::EVENT_DISPATCH),
                                $this->equalTo(array($dispatchListener, 'onDispatch')));

        $dispatchListener->attach($mockEventManager);
    }

    /**
     * @covers Infrastructure\Security\DispatchListener
     * @expectedException RuntimeException
     * @expectedExceptionMessage Event listener already attached
     */
    public function testAttachWhenCallbackAlreadyAttachedThrows()
    {
        $mockEventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $mockSecurityController = $this->getMock('Infrastructure\Security\SecurityControllerInterface');

        $mockSecurityController->expects($this->never())
                               ->method(new PHPUnit_Framework_Constraint_IsAnything);

        $dispatchListener = new DispatchListener($mockSecurityController);

        $mockEventManager->expects($this->once())
                         ->method('attach')
                         ->will($this->returnValue('EVENT_MANAGER_REFERENCE'));

        $dispatchListener->attach($mockEventManager);
        $dispatchListener->attach($mockEventManager);
    }
}
