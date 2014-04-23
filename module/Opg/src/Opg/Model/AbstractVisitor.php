<?php

namespace Opg\Model;

use Opg\Model\AcceptVisitorInterface;

abstract class AbstractVisitor
{
    public function visit(
        AcceptVisitorInterface $element
    )
    {
        $classMethods = get_class_methods($this);

        $fullElementClassName = get_class($element);
        if ($fullElementClassName) {
            $elementClassName = substr($fullElementClassName, strrpos($fullElementClassName, '\\')+1);
        }

        while ($fullElementClassName) {

            $expectedVisitMethod = 'visit'.str_replace('\\', '', $elementClassName);
            if (in_array($expectedVisitMethod, $classMethods)) {

                $this->$expectedVisitMethod($element);
                return;
            }

            $fullElementClassName = get_parent_class($fullElementClassName);
            if ($fullElementClassName) {
                $elementClassName = substr($fullElementClassName, strrpos($fullElementClassName, '\\')+1);
            }
        }
    }
}
