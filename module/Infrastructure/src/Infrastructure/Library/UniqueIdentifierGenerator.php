<?php

namespace Infrastructure\Library;

use Infrastructure\Library\IdentifierGeneratorInterface;
use Infrastructure\Library\UniqueIdentifier;

use Zend\Math\Rand;

class UniqueIdentifierGenerator implements IdentifierGeneratorInterface
{
    ### PUBLIC METHODS

    /**
     * @return UniqueIdentifier
     */
    public function generate()
    {
    	$randomNumber = $this->generateRandomString(6);
    	$twoDigitYear = date('y');
    	$composite = 'A' . $randomNumber . '/' . $twoDigitYear;
    	
        return new UniqueIdentifier($composite);
    }
    
    private function generateRandomString($length) 
    {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }    
}
