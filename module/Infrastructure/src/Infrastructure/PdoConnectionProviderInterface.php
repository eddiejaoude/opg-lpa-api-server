<?php

namespace Infrastructure;

use PDO;

interface PdoConnectionProviderInterface
{
    /**
     * @return PDO
     */
    public function getPdoConnection();
}
