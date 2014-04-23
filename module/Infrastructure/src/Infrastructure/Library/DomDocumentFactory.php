<?php

namespace Infrastructure\Library;

use DOMDocument;

class DomDocumentFactory
{
    ### PUBLIC METHODS

    /**
     * @return \DOMDocument
     */
    public function create()
    {
        return new DOMDocument();
    }
}
