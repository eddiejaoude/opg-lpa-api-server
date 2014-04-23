<?php

namespace Infrastructure\Security;

use Infrastructure\Library\InvariantException;

use Zend\Validator\EmailAddress as EmailAddressValidator;

class EmailAddressIdentity implements IdentityInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $emailAddress
     * @throws InvariantException When $emailAddress is empty
     * @throws InvariantException When $emailAddress is not a valid email address
     */
    public function __construct(
        $emailAddress
    )
    {
        if (empty($emailAddress)) {
            throw new InvariantException('$emailAddress cannot be empty');
        }

        $validator = new EmailAddressValidator();
        if (!$validator->isValid($emailAddress)) {
            throw new InvariantException('$emailAddress is not a valid email address (according to RFC2822)');
        }

        $this->emailAddress = $emailAddress;
    }

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->emailAddress;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $emailAddress;
}
