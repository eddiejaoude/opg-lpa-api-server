<?php

namespace Infrastructure\Security;

use Infrastructure\Security\AuthorisationServiceInterface;
use Infrastructure\Security\IdentityInterface;
use Infrastructure\Security\NotPermittedException;
use Infrastructure\Security\SecurityControllerInterface;

use Zend\Authentication\AuthenticationService;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Router\RouteMatch;

class SecurityController implements SecurityControllerInterface
{
    ### COLLABORATORS

    /**
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authenticationService;

    /**
     * @var \Infrastructure\Security\AuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var \Zend\Http\Response
     */
    private $response;

    ### CONSTRUCTOR

    public function __construct(
        AuthenticationService $authenticationService,
        AuthorisationServiceInterface $authorisationService,
        HttpResponse $response
    )
    {
        $this->authenticationService = $authenticationService;
        $this->authorisationService = $authorisationService;
        $this->response = $response;
    }

    ### PUBLIC METHODS

    /**
     * Apply security policy
     *
     * @throws NotPermittedException
     */
    public function applyPolicy(
        RouteMatch $routeMatch
    )
    {
        $identity = $this->authenticationService->getIdentity();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');

        $hasPermission = $this->authorisationService->hasPermission($identity, $controller, $action);
        if (!$hasPermission) {

            $this->response->setStatusCode(HttpResponse::STATUS_CODE_401);
            throw new NotPermittedException('You are not permitted to access this resource');
        }
    }
}
