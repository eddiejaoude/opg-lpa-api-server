<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Library\UniqueIdentifier;
use Infrastructure\Library\UniqueIdentifierGenerator;

class UniqueIdentifierFactory implements IdentifierFactoryInterface
{
    ### COLLABORATORS

    /**
     * @var \Infrastructure\Library\UniqueIdentifierGenerator
     */
    private $uniqueIdentifierGenerator;

    ### CONSTRUCTION

    public function __construct(
        UniqueIdentifierGenerator $uniqueIdentifierGenerator
    )
    {
        $this->uniqueIdentifierGenerator = $uniqueIdentifierGenerator;
    }

    ### PUBLIC METHODS

    /**
     * @return UniqueIdentifier
     */
    public function create()
    {
        return $this->uniqueIdentifierGenerator->generate();
    }

    /**
     * @param string $uid
     * @return UniqueIdentifier
     */
    public function fromString(
        $uid
    )
    {
        return new UniqueIdentifier($uid);
    }
}
