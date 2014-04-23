<?php

namespace Opg\Infrastructure;

use Infrastructure\Library\InputException;
use Infrastructure\MongoHttpConnectionProviderInterface;
use Opg\Infrastructure\MongoConnectionInitializer;

class MongoHttpConnectionProvider implements MongoHttpConnectionProviderInterface
{
    ### PRIVATE MEMBERS
    
    /**
     * @var array
     */
    private $baseUri;
    
    ### CONSTRUCTOR

    public function __construct(
        $baseUri
    )
    {
        $this->baseUri = $baseUri;
    }

    ### PUBLIC METHODS

    public function getBaseMongoHttpApiUri()
    {
        return $this->baseUri;
    }
}

