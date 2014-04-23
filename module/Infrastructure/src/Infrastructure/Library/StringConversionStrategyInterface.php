<?php

namespace Infrastructure\Library;

interface StringConversionStrategyInterface
{
    /**
     * @param string $input
     * @return string
     */
    public function convert($input);
}
