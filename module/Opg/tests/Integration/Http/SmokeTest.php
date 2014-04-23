<?php

namespace Tests\Integration\Http;

use DOMDocument;
use PDO;
use RuntimeException;
use Zend\Http\Client as HttpClient;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\ApplicationInterface;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class DispatchHttpClient extends HttpClient
{
    ### COLLABORATORS

    /**
     * @var TestCase
     */
    private $testCase;

    ### CONSTRUCTOR

    public function __construct(
        TestCase $testCase
    )
    {
        $this->testCase = $testCase;
    }

    ### PUBLIC METHODS

    public function getRequest()
    {
        $application = $this->testCase->getApplication();
        return $application->getRequest();
    }

    ###

    public function getResponse()
    {
        $application = $this->testCase->getApplication();
        return $application->getResponse();
    }

    ###

    public function send(
        Request $request = NULL
    )
    {
        if ($request !== null) {
            throw new RuntimeException('DispatchHttpClient does not support custom request objects');
        }

        $_SERVER['HTTP_HOST'] = 'opg-lpa-api.local';

        $application = $this->testCase->getApplication();
        $application->run();
        
        return $application->getResponse();
    }
}

class SmokeTest extends TestCase
{
    const BASE_URL = 'http://opg-lpa-api.local';

    private $applicationContentTypes = array(
        'health-welfare'   => 'application/vnd.opg.lpa.application.health-welfare+xml',
        'property-finance' => 'application/vnd.opg.lpa.application.property-finance+xml',
    );

    private $registrationContentTypes = array(
        'health-welfare'   => 'application/vnd.opg.lpa.registration.health-welfare+xml',
        'property-finance' => 'application/vnd.opg.lpa.registration.property-finance+xml',
    );

    private $testToken = null;

    ###

    public function buildUrl(
        $path
    )
    {
        return (self::BASE_URL.$path);
    }

    ###

    public function getHttpClient()
    {
        $this->reset();
        $client = new DispatchHttpClient($this);
        $client->getRequest()->getHeaders()->clearHeaders();
        $client->getRequest()->getHeaders()->addHeaderLine('Token', $this->testToken);
        return $client;
    }

    ###

    public function setUp()
    {
        $localConfig = require 'config/autoload/local.php';
        $baseUri = $localConfig['authenticationServerBaseUri'];
        
        $username = 'test-' . time() . '@opglpa.co.uk';
        $uri = $baseUri . '/users';
        $client = new HttpClient();
        $client->setMethod('POST');
        $client->setParameterPost(
            array(
                'username'=>$username,
                'password'=>'0PGtest123!'
            )
        );
        $client->setUri($uri);
        $client->send();
        $response = $client->getResponse();
        
        $json = json_decode($response->getBody());
        
        $this->assertFalse(empty($json), 'Asserting response from authentication server is not empty');
        
        $uri = $baseUri . '/users/activate';
        $client->setParameterPost(
            array(
                'activation_token'=>$json->activation_token,
            )
        );
        $client->setUri($uri);
        $client->setMethod('POST');
        $client->send();
        $uri = $baseUri . '/token';
        $client->setUri($uri);
        $client->setParameterPost(
            array(
                'username'=>$username,
                'password'=>'0PGtest123!',
                'grant_type'=>'password',
            )
        );
        $client->setMethod('POST');
        $client->send();
        $response = $client->getResponse();
        $json = json_decode($response->getBody());
        
        $this->testToken = $json->access_token;

        $applicationConfiguration = require 'config/application.config.php';
        $this->setApplicationConfig($applicationConfiguration);
        parent::setUp();
    }

    ###

    public function testEndToEnd()
    {
        ///////////////////////////////////////////////////////////////////////////

        $rootUrl = $this->buildUrl('/');

        $this->assertDisallowedMethodsReturn405($rootUrl);
        $this->assertMissingTokenReturns401($rootUrl);

        $this->getRootContent(); // GET
        $this->getRootHeaders(); // HEAD
        $this->getRootOptions(); // OPTIONS
        
        ///////////////////////////////////////////////////////////////////////////

        $applicationsUrl = $this->buildUrl('/applications');

        $this->assertDisallowedMethodsReturn405($applicationsUrl);
        $this->assertMissingTokenReturns401($applicationsUrl);
        
        $this->getApplicationsContent(); // GET
        $this->getApplicationsHeaders(); // HEAD
        $this->getApplicationsOptions(); // OPTIONS

        ///////////////////////////////////////////////////////////////////////////

        $this->postEmptyContentToApplications();
        $this->postInvalidXmlToApplications();
        $this->postValidXmlToApplicationsWithEmptyContentType();
        $this->postValidXmlToApplicationsWithIncorrectContentType();

        $this->getApplicationsAfterCreated(0);

        $healthWelfareApplicationUrl = $this->postHealthWelfareToApplications();

        $this->getApplicationsAfterCreated(1);
        
        $propertyFinanceApplicationUrl = $this->postPropertyFinanceToApplications();

        $this->getApplicationsAfterCreated(2);

        ///////////////////////////////////////////////////////////////////////////

        $this->assertDisallowedMethodsReturn405($healthWelfareApplicationUrl);
        
        $this->assertMissingTokenReturns401($healthWelfareApplicationUrl);
        
        $this->getApplicationContent($healthWelfareApplicationUrl, $this->applicationContentTypes['health-welfare']); // GET
        $this->getApplicationHeaders($healthWelfareApplicationUrl, $this->applicationContentTypes['health-welfare']); // HEAD
        $this->getApplicationOptions($healthWelfareApplicationUrl, $this->applicationContentTypes['health-welfare']); // OPTIONS

        $this->putHealthWelfareApplication($healthWelfareApplicationUrl);
        
        $healthWelfareApplicationMetadataUrl = ($healthWelfareApplicationUrl.'/metadata');

        $this->assertDisallowedMethodsReturn405($healthWelfareApplicationMetadataUrl);
        $this->assertMissingTokenReturns401($healthWelfareApplicationMetadataUrl);
        
        $this->getApplicationMetadataContent($healthWelfareApplicationMetadataUrl); // GET
        $this->getApplicationMetadataHeaders($healthWelfareApplicationMetadataUrl); // HEAD
        $this->getApplicationMetadataOptions($healthWelfareApplicationMetadataUrl); // OPTIONS
        
        $healthWelfareApplicationRegistrationUrl = ($healthWelfareApplicationUrl.'/registration');

        $this->assertDisallowedMethodsReturn405($healthWelfareApplicationRegistrationUrl);
        $this->assertMissingTokenReturns401($healthWelfareApplicationRegistrationUrl);

        $this->getInitalApplicationRegistrationContent($healthWelfareApplicationRegistrationUrl); // GET
        $this->getInitalApplicationRegistrationHeaders($healthWelfareApplicationRegistrationUrl); // HEAD
        $this->getInitalApplicationRegistrationOptions($healthWelfareApplicationRegistrationUrl, $this->registrationContentTypes['health-welfare']); // OPTIONS

        $healthWelfareApplicationRegistrationMetadataUrl = ($healthWelfareApplicationRegistrationUrl.'/metadata');
        
        $this->assertMissingContentReturns404($healthWelfareApplicationRegistrationMetadataUrl);
        
        $this->putHealthWelfareRegistration($healthWelfareApplicationRegistrationUrl);

        $this->assertDisallowedMethodsReturn405($healthWelfareApplicationRegistrationMetadataUrl);
        $this->assertMissingTokenReturns401($healthWelfareApplicationRegistrationMetadataUrl);

        $this->getApplicationRegistrationMetadataContent($healthWelfareApplicationRegistrationMetadataUrl); // GET
        $this->getApplicationRegistrationMetadataHeaders($healthWelfareApplicationRegistrationMetadataUrl); // HEAD
        $this->getApplicationRegistrationMetadataOptions($healthWelfareApplicationRegistrationMetadataUrl); // OPTIONS

        $this->getUpdatedApplicationRegistrationContent($healthWelfareApplicationRegistrationUrl, $this->registrationContentTypes['health-welfare']); // GET
        $this->getUpdatedApplicationRegistrationHeaders($healthWelfareApplicationRegistrationUrl, $this->registrationContentTypes['health-welfare']); // HEAD
        $this->getUpdatedApplicationRegistrationOptions($healthWelfareApplicationRegistrationUrl, $this->registrationContentTypes['health-welfare']); // OPTIONS

        ///////////////////////////////////////////////////////////////////////////

        $this->assertDisallowedMethodsReturn405($propertyFinanceApplicationUrl);
        $this->assertMissingTokenReturns401($propertyFinanceApplicationUrl);

        $this->getApplicationContent($propertyFinanceApplicationUrl, $this->applicationContentTypes['property-finance']); // GET
        $this->getApplicationHeaders($propertyFinanceApplicationUrl, $this->applicationContentTypes['property-finance']); // HEAD
        $this->getApplicationOptions($propertyFinanceApplicationUrl, $this->applicationContentTypes['property-finance']); // OPTIONS

        $this->putPropertyFinanceApplication($propertyFinanceApplicationUrl);

        $propertyFinanceApplicationMetadataUrl = ($propertyFinanceApplicationUrl.'/metadata');

        $this->assertDisallowedMethodsReturn405($propertyFinanceApplicationMetadataUrl);
        $this->assertMissingTokenReturns401($propertyFinanceApplicationMetadataUrl);

        $this->getApplicationMetadataContent($propertyFinanceApplicationMetadataUrl); // GET
        $this->getApplicationMetadataHeaders($propertyFinanceApplicationMetadataUrl); // HEAD
        $this->getApplicationMetadataOptions($propertyFinanceApplicationMetadataUrl); // OPTIONS

        $propertyFinanceApplicationRegistrationUrl = ($propertyFinanceApplicationUrl.'/registration');

        $this->assertDisallowedMethodsReturn405($propertyFinanceApplicationRegistrationUrl);
        $this->assertMissingTokenReturns401($propertyFinanceApplicationRegistrationUrl);

        $this->getInitalApplicationRegistrationContent($propertyFinanceApplicationRegistrationUrl); // GET
        $this->getInitalApplicationRegistrationHeaders($propertyFinanceApplicationRegistrationUrl); // HEAD
        $this->getInitalApplicationRegistrationOptions($propertyFinanceApplicationRegistrationUrl, $this->registrationContentTypes['property-finance']); // OPTIONS

        $propertyFinanceApplicationRegistrationMetadataUrl = ($propertyFinanceApplicationRegistrationUrl.'/metadata');

        $this->assertMissingContentReturns404($propertyFinanceApplicationRegistrationMetadataUrl);

        $this->putPropertyFinanceRegistration($propertyFinanceApplicationRegistrationUrl);

        $this->assertDisallowedMethodsReturn405($propertyFinanceApplicationRegistrationMetadataUrl);
        $this->assertMissingTokenReturns401($propertyFinanceApplicationRegistrationMetadataUrl);

        $this->getApplicationRegistrationMetadataContent($propertyFinanceApplicationRegistrationMetadataUrl); // GET
        $this->getApplicationRegistrationMetadataHeaders($propertyFinanceApplicationRegistrationMetadataUrl); // HEAD
        $this->getApplicationRegistrationMetadataOptions($propertyFinanceApplicationRegistrationMetadataUrl); // OPTIONS

        $this->getUpdatedApplicationRegistrationContent($propertyFinanceApplicationRegistrationUrl, $this->registrationContentTypes['property-finance']); // GET
        $this->getUpdatedApplicationRegistrationHeaders($propertyFinanceApplicationRegistrationUrl, $this->registrationContentTypes['property-finance']); // HEAD
        $this->getUpdatedApplicationRegistrationOptions($propertyFinanceApplicationRegistrationUrl, $this->registrationContentTypes['property-finance']); // OPTIONS

        ///////////////////////////////////////////////////////////////////////////

        $this->getApplicationsAfterCreated(2);

        $this->deleteApplication($healthWelfareApplicationUrl);

        $this->getApplicationsAfterCreated(1);

        $this->deleteApplication($propertyFinanceApplicationUrl);

        $this->getApplicationsAfterCreated(0);

        ///////////////////////////////////////////////////////////////////////////
    }

    ###

    private function assertDisallowedMethodsReturn405(
        $uri
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($uri);
        
        $response = $client->send();
        
        $this->assertEquals(204, $response->getStatusCode());
        
        $allowHeader = $response->getHeaders()
                                ->get('Allow');
        
        if ($allowHeader) {
            $allowValue = $allowHeader->getFieldValue();
            $allowedMethods = explode(',', $allowValue);
            foreach ($allowedMethods as &$allowedMethod) {
                $allowedMethod = strtoupper(trim($allowedMethod));
            }
        }
        
        $methods = array(
            'DELETE',
            'GET',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT'
        );

        foreach ($methods as $method) {
            if (isset($allowedMethods) 
                && in_array($method, $allowedMethods)) {
                continue;
            }

            $client = $this->getHttpClient();
            $client->setMethod($method);
            $client->setUri($uri);

            $response = $client->send();

            $this->assertEquals(405, $response->getStatusCode(), ($method . ' method is disallowed'));
            $this->assertResponseAcceptHeaderIsNotSet($response);
            $this->assertResponseAllowHeaderIsNotSet($response);
            $this->assertResponseContentType($response, 'text/xml');
            $this->assertResponseLinkHeaderIsNotSet($response);
            $this->assertResponseLocationHeaderIsNotSet($response);

            if ($method == 'HEAD') {
                $this->assertEmpty($response->getContent());
            } else {
                $this->assertContains('405 Method Not Allowed', $response->getContent());
                $this->assertContains('Method Not Allowed', $response->getContent());
            }
        }
    }

    ###

    private function assertMissingContentReturns404(
        $uri
    )
    {
        $methods = array(
            'DELETE',
            'GET',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT'
        );

        foreach ($methods as $method) {

            $client = $this->getHttpClient();
            $client->setMethod($method);
            $client->setUri($uri);

            $response = $client->send();

            $this->assertEquals(404, $response->getStatusCode());
            $this->assertResponseAcceptHeaderIsNotSet($response);
            $this->assertResponseAllowHeaderIsNotSet($response);
            $this->assertResponseContentType($response, 'text/xml');
            $this->assertResponseLinkHeaderIsNotSet($response);
            $this->assertResponseLocationHeaderIsNotSet($response);

            if ($method == 'HEAD') {
                $this->assertEmpty($response->getContent());
            } else {
                $this->assertContains('404 Not Found', $response->getContent(), ('Missing Content returns 404 using "'.$method.'" method'));
                $this->assertContains('Not Found', $response->getContent(), ('Missing Content returns 404 using "'.$method.'" method'));
            }
        }
    }

    ###

    private function assertMissingTokenReturns401(
        $uri
    )
    {
        $methods = array(
            'DELETE',
            'GET',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT'
        );

        foreach ($methods as $method) {

            $client = $this->getHttpClient();
            $client->setHeaders(array('M1ssing' => 'T0ken'));
            $client->setMethod($method);
            $client->setUri($uri);

            $response = $client->send();

            $this->assertEquals(401, $response->getStatusCode());
            $this->assertResponseAcceptHeaderIsNotSet($response);
            $this->assertResponseAllowHeaderIsNotSet($response);
            $this->assertResponseContentType($response, 'text/xml');
            $this->assertResponseLinkHeaderIsNotSet($response);
            $this->assertResponseLocationHeaderIsNotSet($response);

            if ($method == 'HEAD') {
                $this->assertEmpty($response->getContent());
            } else {
                $this->assertContains('401 Unauthorized', $response->getContent(), ('Missing Token returns 401 using "'.$method.'" method'));
                $this->assertContains('Missing Token', $response->getContent(), ('Missing Token returns 401 using "'.$method.'" method'));
            }
        }
    }

    ###

    private function assertResponseAcceptedContentType(
        Response $response,
        $expectedAcceptedContentType
    )
    {
        $acceptHeader = $response->getHeaders()
                                 ->get('Accept');

        if (!$acceptHeader) {
            $this->assertTrue(false, 'Accept header not found');
        }

        $acceptValue = $acceptHeader->getFieldValue();
        $acceptedContentTypes = explode(',', $acceptValue);
        foreach ($acceptedContentTypes as &$acceptedContentType) {
            $acceptedContentType = strtolower(trim($acceptedContentType));
        }

        $expectedAcceptedContentType = strtolower(trim($expectedAcceptedContentType));
        $this->assertTrue(
            in_array($expectedAcceptedContentType, $acceptedContentTypes),
            ('"'.$expectedAcceptedContentType.'" content type is accepted')
        );
    }

    ###

    private function assertResponseAcceptHeaderIsNotSet(
        Response $response
    )
    {
        $acceptHeader = $response->getHeaders()
                                 ->get('Accept');

        if ($acceptHeader) {
            $acceptValue = $allowHeader->getFieldValue();
            $this->assertTrue(false, 'Accept header is set to: '.$acceptValue);
        }
    }

    ###

    private function assertResponseAllowedMethods(
        Response $response,
        array $expectedAllowedMethods
    )
    {
        $allowHeader = $response->getHeaders()
                                ->get('Allow');

        if (!$allowHeader) {
            $this->assertTrue(false, 'Allow header not found');
        }

        $allowValue = $allowHeader->getFieldValue();
        $allowedMethods = explode(',', $allowValue);
        foreach ($allowedMethods as &$allowedMethod) {
            $allowedMethod = strtoupper(trim($allowedMethod));
        }

        foreach ($expectedAllowedMethods as $expectedAllowedMethod) {
            $expectedAllowedMethod = strtoupper(trim($expectedAllowedMethod));
            $this->assertTrue(
                in_array($expectedAllowedMethod, $allowedMethods),
                ('"'.$expectedAllowedMethod.'" method is allowed')
            );
        }

        $this->assertEquals(count($expectedAllowedMethods), count($allowedMethods));
    }

    ###

    private function assertResponseAllowHeaderIsNotSet(
        Response $response
    )
    {
        $allowHeader = $response->getHeaders()
                                   ->get('Allow');

        if ($allowHeader) {
            $allowValue = $allowHeader->getFieldValue();
            $this->assertTrue(false, 'Allow header is set to: '.$allowValue);
        }
    }

    ###

    private function assertResponseContentType(
        Response $response,
        $contentType
    )
    {
        $contentTypeHeader = $response->getHeaders()
                                      ->get('Content-Type');

        $this->assertTrue(($contentTypeHeader instanceof ContentType), 'Content Type header not found for: '.$contentType);
        $this->assertEquals($contentType, $contentTypeHeader->getFieldValue());

        $content = $response->getContent();
        if (substr($content, -3) == 'xml') {

            $document = new DOMDocument();
            $loadedSuccessfully = $document->loadXML($content);
            $this->assertTrue($loadedSuccessfully);
        }
    }

    ###

    private function assertResponseLinkCount(
        Response $response,
        $count
    )
    {
        $linkHeader = $response->getHeaders()
                               ->get('Link');

        if (!$linkHeader) {
            $this->assertTrue(($count == 0), 'Link header not found');
            return;
        }

        $linkValue = $linkHeader->getFieldValue();
        $linkValues = explode(',', $linkValue);

        $this->assertCount($count, $linkValues);
    }

    ###

    private function assertResponseLinkHeaderIsNotSet(
        Response $response
    )
    {
        $linkHeader = $response->getHeaders()
                               ->get('Link');

        if ($linkHeader) {
            $linkValue = $linkHeader->getFieldValue();
            $this->assertTrue(false, 'Link header is set to: '.$linkValue);
        }
    }

    ###

    private function assertResponseLinkUrlMatch(
        Response $response,
        $relation,
        $urlMatchExpression
    )
    {
        $linkHeader = $response->getHeaders()
                               ->get('Link');

        if (!$linkHeader) {
            $this->assertTrue(false, 'Link header not found');
        }

        $linkValue = $linkHeader->getFieldValue();

        $this->assertContains(('rel="'.$relation.'"'), $linkValue);
        $this->assertTrue(
            (preg_match(('|<'.$urlMatchExpression.'>|is'), $linkValue) === 1),
            ('Link "'.$linkValue.'" matches: <'.$urlMatchExpression.'>')
        );
    }

    ###

    private function assertResponseLocationHeaderIsNotSet(
        Response $response
    )
    {
        $locationHeader = $response->getHeaders()
                                   ->get('Location');

        if ($locationHeader) {
            $locationValue = $locationHeader->getFieldValue();
            $this->assertTrue(false, 'Location header is set to: '.$locationValue);
        }
    }

    ###

    private function assertResponseLocationUrlMatch(
        Response $response,
        $urlMatchExpression
    )
    {
        $locationHeader = $response->getHeaders()
                                   ->get('Location');

        if (!$locationHeader) {
            $this->assertTrue(false, 'Location header not found');
        }

        $locationValue = $locationHeader->getFieldValue();

        $this->assertTrue(
            (preg_match(('|'.$urlMatchExpression.'|is'), $locationValue) === 1),
            ('Location "'.$locationValue.'" matches: '.$urlMatchExpression)
        );
    }

    ###

    private function getRootContent()
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($this->buildUrl('/'));

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 1);
        $this->assertResponseLinkUrlMatch($response, 'applications', $this->buildUrl('/applications'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getRootHeaders()
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($this->buildUrl('/'));

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 1);
        $this->assertResponseLinkUrlMatch($response, 'applications', $this->buildUrl('/applications'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getRootOptions()
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($this->buildUrl('/'));

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowedMethods($response, array('GET', 'HEAD', 'OPTIONS'));
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 1);
        $this->assertResponseLinkUrlMatch($response, 'applications', $this->buildUrl('/applications'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getApplicationsContent()
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.applications+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('<applications/>', $response->getContent(), 'Applications XML has no child nodes');
    }

    ###

    private function getApplicationsHeaders()
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.applications+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getApplicationsOptions()
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptedContentType($response, $this->applicationContentTypes['health-welfare']);
        $this->assertResponseAcceptedContentType($response, $this->applicationContentTypes['property-finance']);
        $this->assertResponseAllowedMethods($response, array('GET', 'HEAD', 'OPTIONS', 'POST'));
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function postHealthWelfareToApplications()
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalApplication.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', $this->applicationContentTypes['health-welfare']);
        $client->setMethod('POST');
        $client->setRawBody($xml);
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', $this->buildUrl('/applications/[^/]+/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', $this->buildUrl('/applications/[^/]+/registration'));
        $this->assertResponseLocationUrlMatch($response, $this->buildUrl('/applications/[^/]+'));
        $this->assertEmpty($response->getContent());

        return $response->getHeaders()
                        ->get('Location')
                        ->getFieldValue();
    }

    ###

    private function postPropertyFinanceToApplications()
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalApplication.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', $this->applicationContentTypes['property-finance']);
        $client->setMethod('POST');
        $client->setRawBody($xml);
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', $this->buildUrl('/applications/[^/]+/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', $this->buildUrl('/applications/[^/]+/registration'));
        $this->assertResponseLocationUrlMatch($response, $this->buildUrl('/applications/[^/]+'));
        $this->assertEmpty($response->getContent());

        return $response->getHeaders()
                        ->get('Location')
                        ->getFieldValue();
    }

    ###

    private function postEmptyContentToApplications()
    {
        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.opg.lpa.application.health-welfare+xml');
        $client->setMethod('POST');
        $client->setRawBody('');
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('400 Bad Request', $response->getContent());
        $this->assertContains('XML Invalid', $response->getContent());
    }

    ###

    private function postInvalidXmlToApplications()
    {
    	$this->markTestSkipped('Under investigation');
        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.opg.lpa.application.health-welfare+xml');
        $client->setMethod('POST');
        $client->setRawBody('<badgers/>');
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('400 Bad Request', $response->getContent());
        $this->assertContains('XML Invalid', $response->getContent());
    }

    ###

    private function postValidXmlToApplicationsWithEmptyContentType()
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalApplication.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->setMethod('POST');
        $client->setRawBody($xml);
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(415, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('415 Unsupported Media Type', $response->getContent());
        $this->assertContains('Content-Type must be one of:', $response->getContent());
        $this->assertContains('application/vnd.opg.lpa.application.health-welfare+xml', $response->getContent());
        $this->assertContains('application/vnd.opg.lpa.application.property-finance+xml', $response->getContent());
    }

    ###

    private function postValidXmlToApplicationsWithIncorrectContentType()
    {
    	$this->markTestSkipped('Under investigation');
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalApplication.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.opg.lpa.application.property-finance+xml');
        $client->setMethod('POST');
        $client->setRawBody($xml);
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('400 Bad Request', $response->getContent());
        $this->assertContains('XML Invalid', $response->getContent());
    }

    ###

    private function getApplicationsAfterCreated(
        $count
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($this->buildUrl('/applications'));

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.applications+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEquals($count, substr_count($response->getContent(), '<application '), ('Applications XML has '.$count.' child nodes'));
    }

    ###

    private function getApplicationContent(
        $applicationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($applicationUrl);

        $response = $client->send();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, $contentType);
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', ($applicationUrl.'/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', ($applicationUrl.'/registration'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('<application>', $response->getContent());
    }

    ###

    private function getApplicationHeaders(
        $applicationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($applicationUrl);

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, $contentType);
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', ($applicationUrl.'/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', ($applicationUrl.'/registration'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getApplicationOptions(
        $applicationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($applicationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptedContentType($response, $contentType);
        $this->assertResponseAllowedMethods($response, array('DELETE', 'GET', 'HEAD', 'OPTIONS', 'PUT'));
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', ($applicationUrl.'/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', ($applicationUrl.'/registration'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function putHealthWelfareApplication(
        $applicationUrl
    )
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalApplication.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', $this->applicationContentTypes['health-welfare']);
        $client->setMethod('PUT');
        $client->setRawBody($xml);
        $client->setUri($applicationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode(), ('PUT method is allowed after resource has been created'));
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', ($applicationUrl.'/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', ($applicationUrl.'/registration'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function putPropertyFinanceApplication(
        $applicationUrl
    )
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalApplication.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', $this->applicationContentTypes['property-finance']);
        $client->setMethod('PUT');
        $client->setRawBody($xml);
        $client->setUri($applicationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode(), ('PUT method is allowed after resource has been created'));
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkCount($response, 2);
        $this->assertResponseLinkUrlMatch($response, 'application-metadata', ($applicationUrl.'/metadata'));
        $this->assertResponseLinkUrlMatch($response, 'registration', ($applicationUrl.'/registration'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getApplicationMetadataContent(
        $applicationMetadataUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($applicationMetadataUrl);

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.application-metadata+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('<application-metadata>', $response->getContent());
    }

    ###

    private function getApplicationMetadataHeaders(
        $applicationMetadataUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($applicationMetadataUrl);

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.application-metadata+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getApplicationMetadataOptions(
        $applicationMetadataUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($applicationMetadataUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowedMethods($response, array('GET', 'HEAD', 'OPTIONS'));
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getInitalApplicationRegistrationContent(
        $applicationRegistrationUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();

        $this->assertEquals(405, $response->getStatusCode(), ('GET method is disallowed until registration has been created'));
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('405 Method Not Allowed', $response->getContent());
        $this->assertContains('Method Not Allowed', $response->getContent());
    }

    ###

    private function getInitalApplicationRegistrationHeaders(
        $applicationRegistrationUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();

        $this->assertEquals(405, $response->getStatusCode(), ('GET method is disallowed until registration has been created'));
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getInitalApplicationRegistrationOptions(
        $applicationRegistrationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptedContentType($response, $contentType);
        $this->assertResponseAllowedMethods($response, array('OPTIONS', 'PUT'));
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function putHealthWelfareRegistration(
        $applicationRegistrationUrl
    )
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/HealthWelfare/MinimalRegistration.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', $this->registrationContentTypes['health-welfare']);
        $client->setMethod('PUT');
        $client->setRawBody($xml);
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();
        
        $this->assertEquals(204, $response->getStatusCode(), 'PUT method is allowed');
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkUrlMatch($response, 'registration-metadata', ($applicationRegistrationUrl.'/metadata'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function putPropertyFinanceRegistration(
        $applicationRegistrationUrl
    )
    {
        $xmlFileLocation = 'module/Opg/tests/Fixtures/XmlDocuments/PropertyFinance/MinimalRegistration.xml';
        $this->assertFileExists($xmlFileLocation, 'Test XML file exists');
        $xml = file_get_contents($xmlFileLocation);

        $client = $this->getHttpClient();
        $client->getRequest()->getHeaders()->addHeaderLine('Content-Type', $this->registrationContentTypes['property-finance']);
        $client->setMethod('PUT');
        $client->setRawBody($xml);
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode(), 'PUT method is allowed');
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkUrlMatch($response, 'registration-metadata', ($applicationRegistrationUrl.'/metadata'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }
    
    ###

    private function getApplicationRegistrationMetadataContent(
        $applicationRegistrationMetadataUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($applicationRegistrationMetadataUrl);

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.registration-metadata+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('<registration-metadata>', $response->getContent());
    }

    ###

    private function getApplicationRegistrationMetadataHeaders(
        $applicationRegistrationMetadataUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($applicationRegistrationMetadataUrl);

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'application/vnd.opg.lpa.registration-metadata+xml');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getApplicationRegistrationMetadataOptions(
        $applicationRegistrationMetadataUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($applicationRegistrationMetadataUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowedMethods($response, array('GET', 'HEAD', 'OPTIONS'));
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getUpdatedApplicationRegistrationContent(
        $applicationRegistrationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('GET');
        $client->setUri($applicationRegistrationUrl);
        
        $response = $client->send();
        
        $this->assertEquals(200, $response->getStatusCode(), 'GET method is allowed now that a registration has been created');
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, $contentType);
        $this->assertResponseLinkUrlMatch($response, 'registration-metadata', ($applicationRegistrationUrl.'/metadata'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertContains('<registration>', $response->getContent());
    }

    ###

    private function getUpdatedApplicationRegistrationHeaders(
        $applicationRegistrationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('HEAD');
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();

        $this->assertEquals(200, $response->getStatusCode(), 'HEAD method is allowed now that a registration has been created');
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, $contentType);
        $this->assertResponseLinkUrlMatch($response, 'registration-metadata', ($applicationRegistrationUrl.'/metadata'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function getUpdatedApplicationRegistrationOptions(
        $applicationRegistrationUrl,
        $contentType
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('OPTIONS');
        $client->setUri($applicationRegistrationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptedContentType($response, $contentType);
        $this->assertResponseAllowedMethods($response, array('GET', 'HEAD', 'OPTIONS', 'PUT'), 'Allowed methods have been updated');
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkUrlMatch($response, 'registration-metadata', ($applicationRegistrationUrl.'/metadata'));
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }

    ###

    private function deleteApplication(
        $applicationUrl
    )
    {
        $client = $this->getHttpClient();
        $client->setMethod('DELETE');
        $client->setUri($applicationUrl);

        $response = $client->send();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertResponseAcceptHeaderIsNotSet($response);
        $this->assertResponseAllowHeaderIsNotSet($response);
        $this->assertResponseContentType($response, 'text/plain');
        $this->assertResponseLinkHeaderIsNotSet($response);
        $this->assertResponseLocationHeaderIsNotSet($response);
        $this->assertEmpty($response->getContent());
    }
}
