<?php

namespace Infrastructure;

use Zend\Session\Container as SessionContainer;

class SessionContainerFactory
{
    ### PUBLIC METHODS

    /**
     * @param string $namespace
     * @return SessionContainer
     * @throws InvariantException When $namespace is empty
     */
    public function create(
        $namespace
    )
    {
        if (empty($namespace)) {
            throw new InvariantException('$namespace cannot be empty');
        }

        return new SessionContainer($namespace);
    }
}
