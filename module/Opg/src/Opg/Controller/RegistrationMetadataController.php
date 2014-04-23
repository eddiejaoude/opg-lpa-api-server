<?php

namespace Opg\Controller;

use Opg\Model\Serialization\Xml\RegistrationMetadataXmlSerializer;
use Opg\Repository\RegistrationRepositoryInterface;
use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;
use Zend\Http\Response as HttpResponse;

class RegistrationMetadataController extends AbstractHttpController
{
    ### COLLABORATORS

    /**
     * @var RegistrationMetadataXmlSerializer
     */
    private $registrationMetadataXmlSerializer;

    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    ### CONSTRUCTOR

    public function __construct(
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory,
        RegistrationMetadataXmlSerializer $registrationMetadataXmlSerializer,
        RegistrationRepositoryInterface $registrationRepository
    )
    {
        parent::__construct($identifierFactory, $identityFactory);

        $this->registrationMetadataXmlSerializer = $registrationMetadataXmlSerializer;
        $this->registrationRepository = $registrationRepository;
    }

    ### PUBLIC METHODS

    public function indexAction()
    {
        $registrationExists = $this->checkRegistrationExistsInRepository();
        if (!$registrationExists) {
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
        $registration = $this->getRegistrationFromRepository();
        $registrationMetadata = $registration->getMetadata();
        $registrationMetadataXml = $this->registrationMetadataXmlSerializer->serialize($registrationMetadata);

        $this->send(HttpResponse::STATUS_CODE_200, $registrationMetadataXml, RegistrationMetadataXmlSerializer::CONTENT_TYPE);
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

    private function checkRegistrationExistsInRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity = $this->getUserIdentity();

        return $this->registrationRepository->exists($entityIdentifier, $userIdentity);
    }

    ###

    private function getRegistrationFromRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity = $this->getUserIdentity();

        $registration = $this->registrationRepository->fetchOne($entityIdentifier, $userIdentity);
        if (!$registration) {
            $this->throwHttpException(HttpResponse::STATUS_CODE_404);
        }

        return $registration;
    }
}
