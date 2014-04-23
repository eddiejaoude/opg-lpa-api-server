<?php

namespace Opg\Model\Validator;

use Opg\Model\Element\AbstractRegistration;

interface RegistrationValidatorInterface
{
    public function validate(
        AbstractRegistration $registration
    );
}
