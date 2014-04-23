<?php

namespace Opg\Model\Validator;

use Opg\Model\Element\AbstractApplication;

interface ApplicationValidatorInterface
{
    public function validate(
        AbstractApplication $application
    );
}
