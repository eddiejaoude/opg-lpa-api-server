<?php

namespace Opg\Controller;

use Zend\View\Model\JsonModel;

use Opg\Model\Element\ApplicationMetadata;
use Opg\Model\Element\ApplicationStatusEnumeration;
use Opg\Model\Serialization\Xml\SummaryXmlSerializer;
use Opg\Repository\ApplicationRepositoryInterface;
use Opg\Repository\RegistrationRepositoryInterface;
use Opg\Service\ApplicationService;
use Opg\Service\RegistrationService;
use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Library\InvalidXmlException;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Library\XmlDeserializerException;
use Infrastructure\Security\IdentityFactoryInterface;
use Zend\Http\Response as HttpResponse;

class SummaryController extends AbstractHttpController
{
    ### COLLABORATORS

    /**
     * @var ApplicationRepositoryInterface
     */
    private $applicationRepository;

    /**
     * @var ApplicationService
     */
    private $applicationService;

    /**
     * @var RegistrationRepositoryInterface
     */
    private $registrationRepository;
    
    /**
     * @var RegistrationService
     */
    private $registrationService;
    
    /**
     * @var SummaryXmlSerializer
     */
    private $summaryXmlSerializer;

    ### CONSTRUCTION

    public function __construct(
        ApplicationRepositoryInterface $applicationRepository,
        ApplicationService $applicationService,
        RegistrationRepositoryInterface $registrationRepository,
        RegistrationService $registrationService,
        SummaryXmlSerializer $summaryXmlSerializer,
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory
    )
    {

        
        parent::__construct($identifierFactory, $identityFactory);
        
        $this->applicationRepository   = $applicationRepository;
        $this->applicationService      = $applicationService;
        
        $this->registrationRepository  = $registrationRepository;
        $this->registrationService     = $registrationService;
        
        $this->summaryXmlSerializer    = $summaryXmlSerializer;
    }

    ### PUBLIC METHODS

    public function indexAction()
    {
        if ($this->request->getMethod() == HttpMethodEnumeration::GET) {
            return $this->getSummary();
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }
    
    ###

    public function getSummary()
    {
        $applications    = $this->getApplicationsFromRepository();
        
        $applicationsXml = $this->summaryXmlSerializer->serialize(
            $this->identifierFactory,
            $this->getUserIdentity(),
            $this->registrationRepository,
            $applications,
            $this->serverUrl() . '/applications/:id',
            $this->serverUrl() . '/applications/:id/registration'
        );
    
        $this->send(HttpResponse::STATUS_CODE_200, $applicationsXml, SummaryXmlSerializer::CONTENT_TYPE);
    }
    
    ###
    
    private function getApplicationsFromRepository()
    {
        $userIdentity = $this->getUserIdentity();

        return $this->applicationRepository->fetchAll($userIdentity);
    }
    
}
