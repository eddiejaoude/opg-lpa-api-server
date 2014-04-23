<?php

namespace Infrastructure\Security;

use Infrastructure\Security\NotPermittedException;

use Zend\Mvc\Router\RouteMatch;

interface SecurityControllerInterface
{
    /**
     * Apply security policy
     *
     * @throws NotPermittedException
     */
    public function applyPolicy(
        RouteMatch $routeMatch
    );
}
