<?php

namespace Infrastructure\Library;

use \Infrastructure\Library\StringConversionStrategyInterface;

class HyphenatedWordsToCamelCaseConversionStrategy implements StringConversionStrategyInterface
{
    /**
     * @param string $input
     * @return string
     */
    public function convert($input)
    {
        $parts = explode('-', strtolower($input));
        $parts = array_map('ucfirst', $parts);
        return lcfirst(implode('', $parts));
    }
}
