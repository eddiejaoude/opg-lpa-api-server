<?php

namespace Opg\Model\Validator\Rule;

use Opg\Model\AbstractElement;

interface RuleInterface
{
    public function apply(
        AbstractElement $element
    );
}
