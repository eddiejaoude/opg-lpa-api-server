<?php

namespace Infrastructure\Security;

use Infrastructure\Security\SecurityControllerInterface;
use RuntimeException;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch as HttpRouteMatch;

class DispatchListener implements ListenerAggregateInterface
{
    ### COLLABORATORS

    /**
     * @var SecurityControllerInterface
     */
    private $securityController;

    ### CONSTRUCTOR

    public function __construct(
        SecurityControllerInterface $securityController
    )
    {
        $this->securityController = $securityController;
    }

    ### PUBLIC METHODS

    /**
     * Attach listeners to an event manager
     */
    public function attach(
        EventManagerInterface $events
    )
    {
        if ($this->listener !== null) {
            throw new RuntimeException('Event listener already attached');
        }

        $this->listener = $events->attach(
            MvcEvent::EVENT_DISPATCH,
            array($this, 'onDispatch')
        );
    }

    /**
     * Detach listeners from an event manager
     */
    public function detach(
        EventManagerInterface $events
    )
    {
        if ($this->listener === null) {
            throw new RuntimeException('Event listener not attached');
        }

        if ($events->detach($this->listener)) {
            $this->listener = null;
        }
    }

    /**
     * Handle the MVC "dispatch" event
     */
    public function onDispatch(
        MvcEvent $event
    )
    {
        $routeMatch = $event->getRouteMatch();
        $this->securityController->applyPolicy($routeMatch);
    }

    ### PRIVATE MEMBERS

    /**
     * @var \CallbackHandler|null
     */
    private $listener;
}
