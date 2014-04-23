<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Library\UniversallyUniqueIdentifier;
use Infrastructure\Library\UniversallyUniqueIdentifierGenerator;

class UniversallyUniqueIdentifierFactory implements IdentifierFactoryInterface
{
    ### COLLABORATORS

    /**
     * @var \Infrastructure\Library\UniversallyUniqueIdentifierGenerator
     */
    private $universallyUniqueIdentifierGenerator;

    ### CONSTRUCTION

    public function __construct(
        UniversallyUniqueIdentifierGenerator $universallyUniqueIdentifierGenerator
    )
    {
        $this->universallyUniqueIdentifierGenerator = $universallyUniqueIdentifierGenerator;
    }

    ### PUBLIC METHODS

    /**
     * @return UniversallyUniqueIdentifier
     */
    public function create()
    {
        return $this->universallyUniqueIdentifierGenerator->generate();
    }

    /**
     * @param string $uuid
     * @return UniversallyUniqueIdentifier
     */
    public function fromString(
        $uuid
    )
    {
        return new UniversallyUniqueIdentifier($uuid);
    }
}
