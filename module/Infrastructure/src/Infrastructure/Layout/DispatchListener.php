<?php

namespace Infrastructure\Layout;

use Infrastructure\Layout\LayoutControllerInterface;
use Infrastructure\Library\InvariantException;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;

class DispatchListener implements ListenerAggregateInterface
{
    ### COLLABORATORS

    /**
     * @var LayoutControllerInterface
     */
    private $layoutController;

    ### CONSTRUCTOR

    public function __construct(
        LayoutControllerInterface $layoutController
    )
    {
        $this->layoutController = $layoutController;
    }

    ### PUBLIC METHODS

    /**
     * Attach listeners to an event manager
     */
    public function attach(
        EventManagerInterface $events
    )
    {
        if ($this->renderListener !== null) {
            throw new InvariantException('Event listener already attached');
        }

        if ($this->renderErrorlistener !== null) {
            throw new InvariantException('Event listener already attached');
        }

        $this->renderListener = $events->attach(
            MvcEvent::EVENT_RENDER,
            array($this, 'onRender')
        );

        $this->renderErrorlistener = $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'onRender')
        );
    }

    ###

    /**
     * Detach listeners from an event manager
     */
    public function detach(
        EventManagerInterface $events
    )
    {
        if ($this->renderListener === null) {
            throw new InvariantException('Event listener not attached');
        }

        if ($this->renderErrorlistener === null) {
            throw new InvariantException('Event listener not attached');
        }

        if ($events->detach($this->renderListener)) {
            $this->renderListener = null;
        }

        if ($events->detach($this->renderErrorlistener)) {
            $this->renderErrorlistener = null;
        }
    }

    ###

    /**
     * Handle the MVC "render" and "render error" events
     */
    public function onRender(
        MvcEvent $event
    )
    {
        $viewModel = $event->getViewModel();
        $this->layoutController->prepareViewModel($viewModel);
    }

    ### PRIVATE MEMBERS

    /**
     * @var \CallbackHandler|null
     */
    private $renderListener;

    /**
     * @var \CallbackHandler|null
     */
    private $renderErrorlistener;
}
