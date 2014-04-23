<?php

namespace Infrastructure\Library;

use Infrastructure\Library\FileLocationMissingOrUnreadableException;
use Infrastructure\Library\InvariantException;

class FileLocation
{
    ### CONSTRUCTOR

    /**
     * @param string $fileLocation
     * @throws InvariantException When $fileLocation is not of string type
     * @throws InvariantException When $fileLocation is empty
     * @throws FileLocationMissingOrUnreadableException When $fileLocation is missing or unreadable
     */
    public function __construct(
        $fileLocation
    )
    {
        if (!is_string($fileLocation)) {
            throw new InvariantException('$fileLocation must be of string type');
        }

        if (empty($fileLocation)) {
            throw new InvariantException('$fileLocation cannot be empty');
        }

        if (!is_readable($fileLocation)) {
            throw new FileLocationMissingOrUnreadableException('File location missing or unreadable: '.$fileLocation);
        }

        $this->fileLocation = $fileLocation;
    }

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->fileLocation;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $fileLocation;
}
