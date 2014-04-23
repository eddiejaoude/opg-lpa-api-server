<?php

namespace Infrastructure;

use Zend\Di\Definition\CompilerDefinition;

class StaticDefinitionCompiler
{
    ### PUBLIC METHODS

    /**
     * @param string $classPath Absolute path to scan for classes
     * @param string $definitionFileLocation File to save compiled definition into
     */
    public static function compileToFile(
        $classPath,
        $definitionFileLocation
    )
    {
        if (!defined('USE_COMPILED_DI_DEFINITIONS')
            || !USE_COMPILED_DI_DEFINITIONS) {

            return;
        }

        if (!file_exists($definitionFileLocation)) {

            $definitionCompiler = new CompilerDefinition();
            $definitionCompiler->addDirectory($classPath);
            $definitionCompiler->compile();

            $definitionArray = $definitionCompiler->toArrayDefinition()
                                                  ->toArray();

            file_put_contents(
                $definitionFileLocation,
                ('<?php return '.var_export($definitionArray, true).';')
            );
        }
    }
}
