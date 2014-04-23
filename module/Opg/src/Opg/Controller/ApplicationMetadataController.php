<?php

namespace Opg\Controller;

use Opg\Model\Serialization\Xml\ApplicationMetadataXmlSerializer;
use Opg\Repository\ApplicationRepositoryInterface;
use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;
use Zend\Http\Response as HttpResponse;

class ApplicationMetadataController extends AbstractHttpController
{
    ### COLLABORATORS

    /**
     * @var ApplicationMetadataXmlSerializer
     */
    private $applicationMetadataXmlSerializer;

    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;

    ### CONSTRUCTOR

    public function __construct(
        ApplicationMetadataXmlSerializer $applicationMetadataXmlSerializer,
        ApplicationRepositoryInterface $applicationRepository,
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory
    )
    {
        parent::__construct($identifierFactory, $identityFactory);

        $this->applicationMetadataXmlSerializer = $applicationMetadataXmlSerializer;
        $this->applicationRepository = $applicationRepository;
    }

    ### PUBLIC METHODS

    public function indexAction()
    {
        $applicationExists = $this->checkApplicationExistsInRepository();
        
        if (!$applicationExists) {
            $this->throwHttpException(HttpResponse::STATUS_CODE_404);
        }

        $method = strtoupper($this->request->getMethod());
        switch ($method) {

            case HttpMethodEnumeration::HEAD    :
            case HttpMethodEnumeration::GET     : return $this->getMetadata();
            case HttpMethodEnumeration::OPTIONS : return $this->options();
        }

        
        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }

    ###

    public function getMetadata()
    {        

        $application            = $this->getApplicationFromRepository();

        $applicationMetadata    = $application->getMetadata();
        $applicationMetadataXml = $this->applicationMetadataXmlSerializer->serialize($applicationMetadata);

        $this->send(HttpResponse::STATUS_CODE_200, $applicationMetadataXml, ApplicationMetadataXmlSerializer::CONTENT_TYPE);
    }

    ###

    public function options()
    {
        $this->addHeader(
            'Allow',
            HttpMethodEnumeration::HEAD.', '.
            HttpMethodEnumeration::GET .', '.
            HttpMethodEnumeration::OPTIONS
        );


        $this->send(HttpResponse::STATUS_CODE_204);

    }

    ### PRIVATE METHODS

    private function checkApplicationExistsInRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity     = $this->getUserIdentity();

        return $this->applicationRepository->exists($entityIdentifier, $userIdentity);
    }

    ###

    private function getApplicationFromRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity     = $this->getUserIdentity();

        $application = $this->applicationRepository->fetchOne($entityIdentifier, $userIdentity);
        if (!$application) {
            $this->throwHttpException(HttpResponse::STATUS_CODE_404);
        }

        return $application;
    }
}
