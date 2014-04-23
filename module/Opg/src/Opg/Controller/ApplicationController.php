<?php

namespace Opg\Controller;

use Opg\Model\Element\ApplicationMetadata;
use Opg\Model\Element\ApplicationStatusEnumeration;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;
use Opg\Model\Serialization\Xml\ApplicationsXmlSerializer;
use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;
use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlDeserializer;
use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlSerializer;
use Opg\Repository\ApplicationRepositoryInterface;
use Opg\Service\ApplicationService;
use Infrastructure\Controller\AbstractHttpController;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierInterface;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Library\InvalidXmlException;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Library\XmlDeserializerException;
use Infrastructure\Security\IdentityFactoryInterface;
use Zend\Http\Response as HttpResponse;

class ApplicationController extends AbstractHttpController
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
     * @var ApplicationsXmlSerializer
     */
    private $applicationsXmlSerializer;

    /**
     * @var HealthWelfareApplicationXmlDeserializer
     */
    private $healthWelfareApplicationXmlDeserializer;

    /**
     * @var HealthWelfareApplicationXmlSerializer
     */
    private $healthWelfareApplicationXmlSerializer;

    /**
     * @var PropertyFinanceApplicationXmlDeserializer
     */
    private $propertyFinanceApplicationXmlDeserializer;

    /**
     * @var PropertyFinanceApplicationXmlSerializer
     */
    private $propertyFinanceApplicationXmlSerializer;

    ### CONSTRUCTION

    public function __construct(
        ApplicationRepositoryInterface $applicationRepository,
        ApplicationService $applicationService,
        ApplicationsXmlSerializer $applicationsXmlSerializer,
        HealthWelfareApplicationXmlDeserializer $healthWelfareApplicationXmlDeserializer,
        HealthWelfareApplicationXmlSerializer $healthWelfareApplicationXmlSerializer,
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory,
        PropertyFinanceApplicationXmlDeserializer $propertyFinanceApplicationXmlDeserializer,
        PropertyFinanceApplicationXmlSerializer $propertyFinanceApplicationXmlSerializer
    )
    {
        parent::__construct($identifierFactory, $identityFactory);
        
        $this->applicationRepository = $applicationRepository;
        $this->applicationService = $applicationService;
        $this->applicationsXmlSerializer = $applicationsXmlSerializer;
        $this->healthWelfareApplicationXmlDeserializer = $healthWelfareApplicationXmlDeserializer;
        $this->healthWelfareApplicationXmlSerializer = $healthWelfareApplicationXmlSerializer;
        $this->propertyFinanceApplicationXmlDeserializer = $propertyFinanceApplicationXmlDeserializer;
        $this->propertyFinanceApplicationXmlSerializer = $propertyFinanceApplicationXmlSerializer;
    }

    ### PUBLIC METHODS

    public function indexAction()
    {
        $entityIdentifier = $this->getEntityIdentifier();
        if ($entityIdentifier instanceof NullIdentifier) {
            $method = strtoupper($this->request->getMethod());
            switch ($method) {

                case HttpMethodEnumeration::HEAD    :
                case HttpMethodEnumeration::GET     : return $this->getApplications();
                case HttpMethodEnumeration::POST    : return $this->postApplication();
                case HttpMethodEnumeration::OPTIONS : return $this->options();
            }

        } else {
            
            $applicationExists = $this->checkApplicationExistsInRepository();
            if (!$applicationExists) {
                $this->throwHttpException(HttpResponse::STATUS_CODE_404);
            }
            
            $method = strtoupper($this->request->getMethod());
            switch ($method) {

                case HttpMethodEnumeration::HEAD    :
                case HttpMethodEnumeration::GET     : return $this->getApplication();
                case HttpMethodEnumeration::PUT     : return $this->putApplication();
                case HttpMethodEnumeration::DELETE  : return $this->deleteApplication();
                case HttpMethodEnumeration::OPTIONS : return $this->options();
            }
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }

    ###
    
    public function summaryAction()
    {
        if ($this->request->getMethod() == HttpMethodEnumeration::GET) {
            return $this->getSummary();
        }
    
        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }
    
    ###

    public function deleteApplication()
    {
        $application = $this->getApplicationFromRepository();
        $this->applicationService->delete($application);

        $this->send(HttpResponse::STATUS_CODE_204);
    }

    ###

    public function getApplication()
    {
        $application = $this->getApplicationFromRepository();
        
        if ($application instanceof HealthWelfareApplication) {

            $applicationXml = $this->healthWelfareApplicationXmlSerializer->serialize($application);
            $contentType    = HealthWelfareApplicationXmlSerializer::CONTENT_TYPE;

        } elseif ($application instanceof PropertyFinanceApplication) {

            $applicationXml = $this->propertyFinanceApplicationXmlSerializer->serialize($application);
            $contentType    = PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE;
            
        } else {
            $this->throwHttpException(HttpResponse::STATUS_CODE_500, 'Unsupported Application');
        }

        $applicationMetadata = $application->getMetadata();
        $applicationIdentifier = $applicationMetadata->getIdentifier();

        $this->addMetadataAndRegistrationLinkHeader($applicationIdentifier);
        
        $this->send(HttpResponse::STATUS_CODE_200, $applicationXml, $contentType);
    }

    ###

    public function getApplications()
    {
        $hrefTemplate = ($this->serverUrl().
                         $this->url()
                              ->fromRoute('applications', array('id' => ':id')));
        
        $applications    = $this->getApplicationsFromRepository();
        $applicationsXml = $this->applicationsXmlSerializer->serialize(
                               $applications, 
                               $hrefTemplate 
                           );

        $this->send(HttpResponse::STATUS_CODE_200, $applicationsXml, ApplicationsXmlSerializer::CONTENT_TYPE);
    }

    ###

    public function getSummary()
    {
        $hrefTemplate = ($this->serverUrl() . '/applications/:id');
    
        $applications    = $this->getApplicationsFromRepository();
        $applicationsXml = $this->applicationsXmlSerializer->serialize(
            $applications,
            $hrefTemplate,
            true
        );
    
        $this->send(HttpResponse::STATUS_CODE_200, $applicationsXml, ApplicationsXmlSerializer::CONTENT_TYPE);
    }
    
    ###
    
    public function options()
    {
        $applicationExists = $this->checkApplicationExistsInRepository();
        if ($applicationExists) {
            $application = $this->getApplicationFromRepository();

            if ($application instanceof HealthWelfareApplication) {
                $contentType = HealthWelfareApplicationXmlSerializer::CONTENT_TYPE;
            } elseif ($application instanceof PropertyFinanceApplication) {
                $contentType = PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE;

            } else {
                $this->throwHttpException(HttpResponse::STATUS_CODE_500, 'Unsupported Application');
            }

            // @todo Where application is submitted, do not allow PUT (and remove Accept header)

            $this->addHeader('Accept', $contentType);
            $this->addHeader(
                'Allow', 
                HttpMethodEnumeration::HEAD  .', '.
                HttpMethodEnumeration::GET   .', '.
                HttpMethodEnumeration::PUT   .', '.
                HttpMethodEnumeration::DELETE.', '.
                HttpMethodEnumeration::OPTIONS
            );

            $applicationMetadata = $application->getMetadata();
            $applicationIdentifier = $applicationMetadata->getIdentifier();
            $this->addMetadataAndRegistrationLinkHeader($applicationIdentifier);

        } else {

            $this->addHeader(
                'Accept',
                HealthWelfareApplicationXmlSerializer::CONTENT_TYPE.', '.
                PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE
            );

            $this->addHeader(
                'Allow',
                HttpMethodEnumeration::HEAD.', '.
                HttpMethodEnumeration::GET .', '.
                HttpMethodEnumeration::POST.', '.
                HttpMethodEnumeration::OPTIONS
            );
        }

        $this->send(HttpResponse::STATUS_CODE_204);
    }

    ###

    public function postApplication()
    {
        $count = 0;
        
        do {
            $entityIdentifier = $this->applicationService->getNewApplicationId();
        } while ($this->applicationService->isApplicationIdExistent($entityIdentifier));
        
        $userIdentity     = $this->getUserIdentity();

        $applicationMetadata = new ApplicationMetadata($entityIdentifier, $userIdentity);
        
        $this->persistApplication($applicationMetadata);
        
        $applicationIdentifier = $applicationMetadata->getIdentifier();
        
        $this->addMetadataAndRegistrationLinkHeader($applicationIdentifier);

        $url = ($this->serverUrl().
                $this->url()
                     ->fromRoute('applications', array('id' => (string) $applicationIdentifier)));

        $this->sendLocation(HttpResponse::STATUS_CODE_201, $url);
    }

    ###

    public function putApplication()
    {
        $application = $this->getApplicationFromRepository();

        if ($application instanceof HealthWelfareApplication) {
            $contentType = HealthWelfareApplicationXmlSerializer::CONTENT_TYPE;
        } elseif ($application instanceof PropertyFinanceApplication) {
            $contentType = PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE;
        } else {

            $this->throwHttpException(HttpResponse::STATUS_CODE_500, 'Unsupported Application');
        }

        $this->verifyContentType($contentType);

        $applicationMetadata = $application->getMetadata();

        $this->persistApplication($applicationMetadata);

        $applicationIdentifier = $applicationMetadata->getIdentifier();

        $this->addMetadataAndRegistrationLinkHeader($applicationIdentifier);
        $this->send(HttpResponse::STATUS_CODE_204);
    }

    ### PRIVATE METHODS

    private function addMetadataAndRegistrationLinkHeader(
        IdentifierInterface $applicationIdentifier
    )
    {
        $metadataUrl = ($this->serverUrl().
                        $this->url()
                             ->fromRoute('application_metadata', 
                                         array('id' => (string) $applicationIdentifier)));

        $registrationUrl = ($this->serverUrl().
                            $this->url()
                                 ->fromRoute('application_registration', 
                                             array('id' => (string) $applicationIdentifier)));

        $this->addLinks(
            array(
                array(
                    'url' => $metadataUrl,
                    'rel' => 'application-metadata'
                ),
                array(
                    'url' => $registrationUrl,
                    'rel' => 'registration'
                ),
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

    private function getApplicationsFromRepository()
    {
        $userIdentity = $this->getUserIdentity();

        return $this->applicationRepository->fetchAll($userIdentity);
    }

    ###

    private function persistApplication(
        ApplicationMetadata $applicationMetadata
    )
    {
        $healthWelfareApplicationContentType   = HealthWelfareApplicationXmlSerializer::CONTENT_TYPE;
        $PropertyFinanceApplicationContentType = PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE;
        
        $this->verifyContentType(array($healthWelfareApplicationContentType,
                                       $PropertyFinanceApplicationContentType));

        try {

            $contentTypeHeader = $this->request->getHeader('Content-Type');
            $contentType       = $contentTypeHeader->getFieldValue();

            $isHealthWelfareApplication   = (stripos($contentType, $healthWelfareApplicationContentType) !== false);
            $isPropertyFinanceApplication = (stripos($contentType, $PropertyFinanceApplicationContentType) !== false);

            $applicationXml = $this->request->getContent();

            if ($isHealthWelfareApplication) {
                $application = $this->healthWelfareApplicationXmlDeserializer->deserialize($applicationXml);
            } elseif ($isPropertyFinanceApplication) {
                $application = $this->propertyFinanceApplicationXmlDeserializer->deserialize($applicationXml);
            }
            
            $application->setMetadata($applicationMetadata);

            $this->applicationService->validate($application);
            
            if ($application->isValid()) {
                $applicationMetadata->setStatus(ApplicationStatusEnumeration::STATUS_CREATED);
            } else {
                $applicationMetadata->setStatus(ApplicationStatusEnumeration::STATUS_ACCEPTED);
            }

            $this->applicationService->persist($application);

        } catch (InvalidXmlException $exception) {
            $this->throwHttpException(400, $exception->getMessage(), $exception);

        } catch (XmlDeserializerException $exception) {
            $this->throwHttpException(500, null, $exception);
        }
    }
}
