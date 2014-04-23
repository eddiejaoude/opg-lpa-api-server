<?php

namespace Opg\Controller;

use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;
use Zend\Http\Response as HttpResponse;

class IndexController extends AbstractHttpController
{
    ### CONSTRUCTION

    public function __construct(
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory
    )
    {
        parent::__construct($identifierFactory, $identityFactory);
    }

    ### PUBLIC METHODS

    public function indexAction()
    {
        $method = strtoupper($this->request->getMethod());

        switch ($method) {
            case HttpMethodEnumeration::HEAD    :
            case HttpMethodEnumeration::GET     : return $this->get();
            case HttpMethodEnumeration::OPTIONS : return $this->options();
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }

    ###

    public function get()
    {
        $this->addLink(
            ($this->serverUrl().
             $this->url()->fromRoute('applications')),
            ($rel = 'applications')
        );

        $this->send(HttpResponse::STATUS_CODE_204);
    }

    ###

    public function options()
    {
        $this->addHeader(
            'Allow',
            HttpMethodEnumeration::HEAD.', '.
            HttpMethodEnumeration::GET.', '.
            HttpMethodEnumeration::OPTIONS
        );

        $this->addLink(
            ($this->serverUrl().
             $this->url()->fromRoute('applications')),
            ($rel = 'applications')
        );

        $this->send(HttpResponse::STATUS_CODE_204);
    }
}
