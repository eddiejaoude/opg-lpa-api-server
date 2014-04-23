<?php

namespace Opg\Controller;

use Opg\Model\Element\AbstractApplication as Application;
use Opg\Model\Element\AbstractRegistration as Registration;
use Opg\Model\Element\ApplicationStatusEnumeration;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\HealthWelfareRegistration;
use Opg\Model\Element\PropertyFinanceApplication;
use Opg\Model\Element\PropertyFinanceRegistration;
use Opg\Model\Element\RegistrationMetadata;
use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;
use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlSerializer;
use Opg\Repository\ApplicationRepositoryInterface;
use Opg\Repository\RegistrationRepositoryInterface;
use Opg\Service\ApplicationService;
use Opg\Service\RegistrationService;
use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Security\IdentityFactoryInterface;
use Zend\Http\Response as HttpResponse;

class RegistrationController extends AbstractHttpController
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
     * @var HealthWelfareRegistrationXmlDeserializer
     */
    private $healthWelfareRegistrationXmlDeserializer;

    /**
     * @var HealthWelfareRegistrationXmlSerializer
     */
    private $healthWelfareRegistrationXmlSerializer;

    /**
     * @var PropertyFinanceRegistrationXmlDeserializer
     */
    private $propertyFinanceRegistrationXmlDeserializer;

    /**
     * @var PropertyFinanceRegistrationXmlSerializer
     */
    private $propertyFinanceRegistrationXmlSerializer;

    /**
     * @var RegistrationRepositoryInterface
     */
    private $registrationRepository;

    /**
     * @var RegistrationServiceInterface
     */
    private $registrationService;

    ### CONSTRUCTOR

    public function __construct(
        ApplicationRepositoryInterface $applicationRepository,
        ApplicationService $applicationService,
        HealthWelfareRegistrationXmlDeserializer $healthWelfareRegistrationXmlDeserializer,
        HealthWelfareRegistrationXmlSerializer $healthWelfareRegistrationXmlSerializer,
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory,
        PropertyFinanceRegistrationXmlDeserializer $propertyFinanceRegistrationXmlDeserializer,
        PropertyFinanceRegistrationXmlSerializer $propertyFinanceRegistrationXmlSerializer,
        RegistrationRepositoryInterface $registrationRepository,
        RegistrationService $registrationService
    )
    {
        parent::__construct($identifierFactory, $identityFactory);

        $this->applicationRepository = $applicationRepository;
        $this->applicationService = $applicationService;
        $this->healthWelfareRegistrationXmlDeserializer = $healthWelfareRegistrationXmlDeserializer;
        $this->healthWelfareRegistrationXmlSerializer = $healthWelfareRegistrationXmlSerializer;
        $this->propertyFinanceRegistrationXmlDeserializer = $propertyFinanceRegistrationXmlDeserializer;
        $this->propertyFinanceRegistrationXmlSerializer = $propertyFinanceRegistrationXmlSerializer;
        $this->registrationRepository = $registrationRepository;
        $this->registrationService = $registrationService;
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
            case HttpMethodEnumeration::GET     : return $this->getRegistration();
            case HttpMethodEnumeration::PUT     : return $this->putRegistration();
            case HttpMethodEnumeration::OPTIONS : return $this->options();
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }

    ###

    public function getRegistration()
    {
        $application = $this->getApplicationFromRepository();
        $applicationMetadata = $application->getMetadata();
        $applicationIdentifier = $applicationMetadata->getIdentifier();
        $registration = $this->getRegistrationFromRepository();

        $contentType = $this->inferContentTypeFromApplication($application);

        if ($registration instanceof HealthWelfareRegistration) {
            $registrationXml = $this->healthWelfareRegistrationXmlSerializer->serialize($registration);
            
        } elseif ($registration instanceof PropertyFinanceRegistration) {
            $registrationXml = $this->propertyFinanceRegistrationXmlSerializer->serialize($registration);
        }
        
        $this->addMetadataAndPaymentLinkHeader($applicationIdentifier);
        $this->send(HttpResponse::STATUS_CODE_200, $registrationXml, $contentType);
    }

    ###

    public function putRegistration()
    {
        $application = $this->getApplicationFromRepository();
        $applicationMetadata = $application->getMetadata();
        $applicationIdentifier = $applicationMetadata->getIdentifier();
        $applicationStatus = $applicationMetadata->getStatus();

        $contentType = $this->inferContentTypeFromApplication($application);
        $this->verifyContentType($contentType);

        $registrationExists = $this->checkRegistrationExistsInRepository();
        if ($registrationExists) {
            
            $registration = $this->getRegistrationFromRepository();
            $registrationMetadata = $registration->getMetadata();

        } else {
            
            if ($applicationStatus != ApplicationStatusEnumeration::STATUS_CREATED) {
                $this->throwHttpException(HttpResponse::STATUS_CODE_409, 'Application Not Created');
            }

            $applicationMetadata->setStatus(ApplicationStatusEnumeration::STATUS_COMPLETED);
            $this->applicationService->persist($application);
            
            $entityIdentifier = $this->getEntityIdentifier();
            $userIdentity = $this->getUserIdentity();
            
            $registrationMetadata = new RegistrationMetadata($entityIdentifier, $userIdentity);
        }
        
        $this->persistRegistration($registrationMetadata);
        
        $this->addMetadataAndPaymentLinkHeader($applicationIdentifier);
        $this->send(HttpResponse::STATUS_CODE_204);
    }

    ###

    public function options()
    {
        $application = $this->getApplicationFromRepository();
        $applicationMetadata = $application->getMetadata();
        $applicationIdentifier = $applicationMetadata->getIdentifier();
        $applicationStatus = $applicationMetadata->getStatus();

        $contentType = $this->inferContentTypeFromApplication($application);
        $this->addHeader('Accept', $contentType);

        $registrationExists = $this->checkRegistrationExistsInRepository();
        if ($registrationExists) {

            // @todo Where registration is complete, do not allow PUT and remove Accept header

            $this->addHeader(
                'Allow',
                HttpMethodEnumeration::HEAD.', '.
                HttpMethodEnumeration::GET .', '.
                HttpMethodEnumeration::PUT .', '.
                HttpMethodEnumeration::OPTIONS
            );

            $this->addMetadataAndPaymentLinkHeader($applicationIdentifier);

        } else {

            if ($applicationStatus == ApplicationStatusEnumeration::STATUS_ACCEPTED) {

                $this->addHeader(
                    'Allow',
                    HttpMethodEnumeration::OPTIONS
                );

            } else {

                $this->addHeader(
                    'Allow',
                    HttpMethodEnumeration::PUT.', '.
                    HttpMethodEnumeration::OPTIONS
                );
            }
        }

        $this->send(HttpResponse::STATUS_CODE_204);
    }

    ### PRIVATE METHODS

    private function addMetadataAndPaymentLinkHeader(
        IdentifierInterface $applicationIdentifier
    )
    {
        $metadataUrl = ($this->serverUrl().
                        $this->url()
                             ->fromRoute('application_registration_metadata', 
                                         array('id' => (string) $applicationIdentifier)));

        //$paymentUrl = ($this->serverUrl().
        //               $this->url()
        //                    ->fromRoute('application_payment', 
        //                                array('id' => (string) $applicationIdentifier)));

        $this->addLinks(
            array(
                array(
                    'url' => $metadataUrl,
                    'rel' => 'registration-metadata'
                ),
                //array(
                //    'url' => $paymentUrl,
                //    'rel' => 'payment'
                //),
            )
        );
    }

    ###

    private function checkApplicationExistsInRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity     = $this->getUserIdentity();

        return $this->applicationRepository->exists($entityIdentifier, $userIdentity);
    }

    ###

    private function checkRegistrationExistsInRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity     = $this->getUserIdentity();

        return $this->registrationRepository->exists($entityIdentifier, $userIdentity);
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

    ###

    private function getRegistrationFromRepository()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        $userIdentity     = $this->getUserIdentity();

        $registration = $this->registrationRepository->fetchOne($entityIdentifier, $userIdentity);
        if (!$registration) {
            $this->throwHttpException(HttpResponse::STATUS_CODE_405);
        }

        return $registration;
    }

    ###

    private function inferContentTypeFromApplication(
        Application $application
    )
    {
        if ($application instanceof HealthWelfareApplication) {
            return HealthWelfareRegistrationXmlSerializer::CONTENT_TYPE;
        } elseif ($application instanceof PropertyFinanceApplication) {
            return PropertyFinanceRegistrationXmlSerializer::CONTENT_TYPE;
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_500, 'Unsupported Application Type');
    }

    ###

    private function inferContentTypeFromRegistration(
        Registration $registration
    )
    {
        if ($registration instanceof HealthWelfareRegistration) {
            return HealthWelfareRegistrationXmlSerializer::CONTENT_TYPE;
        } elseif ($registration instanceof PropertyFinanceRegistration) {
            return PropertyFinanceRegistrationXmlSerializer::CONTENT_TYPE;
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_500, 'Unsupported Registration Type');
    }

    ###

    private function persistRegistration(
        RegistrationMetadata $registrationMetadata
    )
    {
        $healthWelfareContentType = HealthWelfareRegistrationXmlSerializer::CONTENT_TYPE;
        $PropertyFinanceContentType = PropertyFinanceRegistrationXmlSerializer::CONTENT_TYPE;
        
        $this->verifyContentType(array($healthWelfareContentType,
                                       $PropertyFinanceContentType));

        try {

            $contentTypeHeader = $this->request->getHeader('Content-Type');
            $contentType = $contentTypeHeader->getFieldValue();

            $isHealthWelfareRegistration = (stripos($contentType, $healthWelfareContentType) !== false);
            $isPropertyFinanceRegistration = (stripos($contentType, $PropertyFinanceContentType) !== false);

            $registrationXml = $this->request->getContent();
            
            if ($isHealthWelfareRegistration) {
                $registration = $this->healthWelfareRegistrationXmlDeserializer->deserialize($registrationXml);
            } elseif ($isPropertyFinanceRegistration) {
                $registration = $this->propertyFinanceRegistrationXmlDeserializer->deserialize($registrationXml);
            }
            $registration->setMetadata($registrationMetadata);
            
            $this->registrationService->persist($registration);
            $this->registrationService->validate($registration);
            
        } catch (InvalidXmlException $exception) {
            $this->throwHttpException(400, $exception->getMessage(), $exception);

        } catch (XmlDeserializerException $exception) {
            $this->throwHttpException(500, null, $exception);
        }
    }
}
