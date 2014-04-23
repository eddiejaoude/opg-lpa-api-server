<?php

namespace Opg\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Helper\ServerUrl as ServerUrlViewHelper;

class ServerUrl extends AbstractPlugin
{
    /**
     * @return string
     */
    public function __invoke($requestUri = null)
    {
        $serverUrlViewHelper = new ServerUrlViewHelper();
        return $serverUrlViewHelper->__invoke($requestUri);
    }
}
