<?php

namespace Infrastructure\Controller;

use Exception;
use Infrastructure\Exception\HttpException;
use Infrastructure\Library\HttpMethodEnumeration;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Library\InvariantException;
use Infrastructure\Library\NullIdentifier;
use Infrastructure\Security\IdentityFactoryInterface;
use Infrastructure\Security\WorldpayAccessIdentity;
use Infrastructure\Security\StatsAccessIdentity;
use RuntimeException;
use Zend\Http\Header\ContentType;
use Zend\Http\Header\KeepAlive;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\Http\Client;

class AbstractHttpController extends AbstractActionController
{
    ### COLLABORATORS

    /**
     * @var IdentifierFactoryInterface
     */
    protected $identifierFactory;

    /**
     * @var IdentityFactoryInterface
     */
    protected $identityFactory;

    ### CONSTRUCTION

    public function __construct(
        IdentifierFactoryInterface $identifierFactory,
        IdentityFactoryInterface $identityFactory
    )
    {
        $this->identifierFactory = $identifierFactory;
        $this->identityFactory = $identityFactory;
    }

    ### PUBLIC METHODS

    public function onDispatch(
        MvcEvent $event
    )
    {
        $actionResponse = parent::onDispatch($event);

        if (!$actionResponse) {
            $actionResponse = $this->response;
        }

        $event->setResult($actionResponse);

        return $actionResponse;
    }

    ### PROTECTED METHODS

    protected function addHeader(
        $name,
        $value = ''
    )
    {
        $headers = $this->response->getHeaders();
        $headers->addHeaderLine($name, $value);
    }

    ###

    protected function addLink(
        $url,
        $rel  = null,
        $type = null
    )
    {
        $this->addLinks(
            [[
                'url'  => $url,
                'rel'  => $rel,
                'type' => $type,
            ]]
        );
    }

    ###

    protected function addLinks(
        array $links
    )
    {
        if (empty($links)) {
            return;
        }

        $value = '';

        foreach ($links as $link) {

            if (empty($link['url'])) {
                continue;
            }

            $value .= ('<'.$link['url'].'>');

            if (!empty($link['rel'])) {
                $value .= ('; rel="'.$link['rel'].'"');
            }

            if (!empty($link['type'])) {
                $value .= ('; type="'.$link['type'].'"');
            }

            $value .= ', ';
        }

        $value = substr($value, 0, -2);

        $this->addHeader('Link', $value);
    }

    ###

    protected function getEntityIdentifier()
    {
        $id = $this->params('id');
        if (!$id) {
            return new NullIdentifier();
        }

        try {
            return $this->identifierFactory->fromString($id);
        } catch (RuntimeException $exception) {
            $this->throwHttpException(HttpResponse::STATUS_CODE_404);
        }
    }

    ###

    protected function getUserIdentity()
    {
        $token = $this->header('Token');
        
        // check if WorldpayApiToken is in the hearder, and validate it.
        $headers = $this->params()->fromHeader();
        if(isset($headers['Worldpay-Api-Token'])) {
        	$worldpayToken = $headers['Worldpay-Api-Token'];
        	$config = $this->getServiceLocator()->get('config');
        	if(isset($config['worldpayTokenSecret'])) {
	        	if($worldpayToken == md5((string)$this->getEntityIdentifier() . $config['worldpayTokenSecret'])) {
	        		return new WorldpayAccessIdentity();
	        	}
        	}
        }
        elseif(isset($headers['Stats-Api-Token'])) {
       		return new StatsAccessIdentity();
        }
        
        $userId = $this->getUserIdFromToken($token);
        
        try {
            return $this->identityFactory->fromString($userId);
        } catch (RuntimeException $exception) {
            $this->throwHttpException(HttpResponse::STATUS_CODE_401);
        }
    }
    
    ###
    
    private function getUserIdFromToken(
        $token
    )
    {
        $config = $this->getServiceLocator()->get('config');
        $authServerBaseUri = $config['authenticationServerBaseUri'];
        if (empty($authServerBaseUri)) {
            throw new \Exception("Authentication server is not found, please check authenticationServerBaseUri config value.");
        }
        $uri = $config['authenticationServerBaseUri'] . '/tokeninfo?access_token=' . $token.'&client=api';
        $client = new Client();
        $client->setMethod('GET');
        $client->setUri($uri);
        $client->setOptions(array(
            "connectTimeoutMS" => 60
        ));
        $client->send();
        $response = $client->getResponse();
        $code = $response->getStatusCode();
        if ($code == HttpResponse::STATUS_CODE_200) {
            $json = json_decode($response->getBody());
            if (isset($json->user_id)) {
                return $json->user_id;
            } else {
                $this->throwHttpException(HttpResponse::STATUS_CODE_502);
            }
        } else {
            $this->throwHttpException($code);
        }
    }

    ###

    protected function header(
        $name
    )
    {
        return $this->params()
                    ->fromHeader($name)
                    ->getFieldValue();
    }

    ###

    protected function post(
        $name
    )
    {
        return $this->params()
                    ->fromPost($name);
    }

    ###

    protected function query(
        $name
    )
    {
        return $this->params()
                    ->fromQuery($name);
    }

    ###

    protected function send(
        $code,
        $content = '',
        $contentType = null
    )
    {
        $this->sendContentHeaders($code, $content, $contentType);

        $method = strtoupper($this->request->getMethod());
        if ($method != HttpMethodEnumeration::HEAD) {

            $this->sendContent($content);
        }
    }

    ###

    protected function sendContent(
        $content
    )
    {
        $this->response->setContent($content);
    }

    ###

    protected function sendContentHeaders(
        $code,
        $content = '',
        $contentType = null
    )
    {
        $headers = $this->response->getHeaders();

        $headers->addHeaderLine('Content-Length', strlen($content));

        if ($contentType !== null) {
            $this->setContentType($contentType);
        }

        $this->response->setStatusCode($code);
    }

    ###

    protected function sendLocation(
        $code,
        $location
    )
    {
        $headers = $this->response->getHeaders();

        $headers->addHeaderLine('Content-Length', 0);
        $headers->addHeaderLine('Location', $location);

        $this->response->setContent('');
        $this->response->setStatusCode($code);
    }

    ###

    protected function setContentType(
        $contentType
    )
    {
        $headers = $this->response->getHeaders();

        $contentTypeHeader = $headers->get('Content-Type');
        if ($contentTypeHeader) {
            $headers->removeHeader($contentTypeHeader);
        }

        $headers->addHeaderLine('Content-Type', $contentType);
    }
    
    ###

    protected function throwHttpException(
        $code,
        $message = null,
        Exception $previousException = null
    )
    {
        $this->response->setStatusCode($code);
        if ($message === null) {
            $message = $this->response->getReasonPhrase();
        }

        throw new HttpException($message, $code, $previousException);
    }

    ###

    protected function verifyContentType(
        $allowedContentTypes
    )
    {
        $allowedContentTypes = (array) $allowedContentTypes;

        $headerContentType = $this->request->getHeader('Content-Type');
        if ($headerContentType) {

            $contentType = $headerContentType->getFieldValue();

            foreach ($allowedContentTypes as $allowedContentType) {

                if (stripos($contentType, $allowedContentType) !== false) {
                    return;
                }
            }
        }

        if (count($allowedContentTypes) == 1) {

            $this->throwHttpException(
                HttpResponse::STATUS_CODE_415,
                ('Content-Type must be: '.
                 implode('', $allowedContentTypes))
            );

        } else {

            $this->throwHttpException(
                HttpResponse::STATUS_CODE_415,
                ('Content-Type must be one of: '.
                 implode(' or ', $allowedContentTypes))
            );
        }
    }

    ###

    protected function verifyMethod(
        array $allowedMethods
    )
    {
        $method = strtoupper($this->request->getMethod());

        foreach ($allowedMethods as $allowedMethod) {
            if (strtoupper($method) == strtoupper($allowedMethod)) {
                return;
            }
        }

        $this->throwHttpException(HttpResponse::STATUS_CODE_405);
    }
}
