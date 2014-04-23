<?php

namespace Infrastructure\Library;

class CamelCaseToHyphenatedWordsConversionStrategy implements StringConversionStrategyInterface
{
    /**
     * @param string $input
     * @return string $output
     */
    public function convert($input)
    {
        return strtolower(
            preg_replace('/([a-zA-Z])(?=[A-Z0-9])/', '$1-', $input)
        );
    }
}
