<?php

namespace Infrastructure\Library\Type;

class String
{
    ### CONSTRUCTOR

    /**
     * @param string $value
     */
    public function __construct(
        $value
    )
    {
        $this->setValue($value);
    }

    ### PUBLIC METHODS

    public function getValue()
    {
        return $this->value;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $value;

    ### PRIVATE METHODS

    public function setValue($value)
    {
        if (!is_string($value)) {
            throw new ValueNotSupportedException();
        }

        $this->value = $value;
    }
}
