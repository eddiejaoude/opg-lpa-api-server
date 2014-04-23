<?php

namespace Infrastructure\Security;

use Infrastructure\Library\InvariantException;

use Zend\Crypt\Password\Bcrypt;

class PasswordHash implements PasswordHashInterface
{
    ### CONSTRUCTOR

    /**
     * @param string $password
     * @throws InvariantException When $password is empty
     */
    public function __construct(
        $password
    )
    {
        if (empty($password)) {
            throw new InvariantException('$password cannot be empty');
        }

        $this->passwordHash = $this->hashPassword($password);
    }

    ### PUBLIC MEMBERS

    const PASSWORD_HASH_COST = 12;
    const PASSWORD_HASH_SALT = 'BADGERS_LIKE_MASH_POTATO';

    ### PUBLIC METHODS

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->passwordHash;
    }

    ### PRIVATE MEMBERS

    /**
     * @var mixed $passwordHash
     */
    private $passwordHash;
    
    ### PRIVATE METHODS
    
    private function hashPassword(
        $password
    ) {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost(self::PASSWORD_HASH_COST);
        $bcrypt->setSalt(self::PASSWORD_HASH_SALT);
        return $bcrypt->create($password);
    }
}
