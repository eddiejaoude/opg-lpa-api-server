<?php

namespace Opg\Model;

use Opg\Model\AbstractVisitor;
use Opg\Model\AcceptVisitorInterface;

use Infrastructure\Library\StronglyTypedCollection;

/**
* @todo constrain the type to AbstractElement descendants
*/
abstract class AbstractElementCollection extends StronglyTypedCollection implements AcceptVisitorInterface
{
    /**
     * @param \Opg\Model\AbstractVisitor $visitor Entry point for the Visitor Pattern
     */
    public function acceptVisitor(
        AbstractVisitor $visitor
    )
    {
        $visitor->visit($this);

        foreach ($this as $element) {
            $element->acceptVisitor($visitor);
        }
    }
}
