<?php

namespace Opg\Model;

use Opg\Model\AbstractVisitor;

interface AcceptVisitorInterface
{
    /**
     * @param \Opg\Model\AbstractVisitor $visitor Entry point for the Visitor Pattern
     */
    public function acceptVisitor(
        AbstractVisitor $visitor
    );
}
