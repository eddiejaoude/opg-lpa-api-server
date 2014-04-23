<?php

namespace Infrastructure\Form;

use Infrastructure\Form\Form;
use Infrastructure\Form\InputFilterBuilderInterface;
use Infrastructure\Library\NamedObjectInterface;
use Infrastructure\Library\PersistentDataInterface;

use Traversable;
use Zend\Form\Form as ZendForm;
use Zend\Stdlib\ArrayUtils;

class StatefulForm extends ZendForm implements NamedObjectInterface
{
    ### COLLABORATORS

    /**
     * @var \Infrastructure\Library\PersistentDataInterface
     */
    private $persistentFormData;

    ### CONSTRUCTOR

    public function __construct(
        PersistentDataInterface $persistentFormData
    )
    {
        parent::__construct();

        $this->persistentFormData = $persistentFormData;
    }

    ### PUBLIC METHODS

    /**
     * @return \Infrastructure\Form\StatefulForm $this
     */
    public function resetStatefulData()
    {
        $this->persistentFormData->bind($this);

        $data = (array) $this->persistentFormData->get();
        $data = $this->filterStatefulData($data);
        $this->setData($data, $save = false);

        return $this;
    }

    /**
     * @param array|Traversable $data
     * @return \Infrastructure\Form\StatefulForm $this
     */
    public function saveStatefulData(
        $data
    )
    {
        $this->persistentFormData->bind($this);

        $data = $this->filterStatefulData($data);
        $this->persistentFormData->set($data);

        return $this;
    }

    ###

    /**
     * @param array|Traversable $data
     * @param bool $saveStatefulData
     * @return \Infrastructure\Form\StatefulForm $this
     */
    public function setData(
        $data,
        $saveStatefulData = true
    )
    {
        if ($saveStatefulData) {
            $this->saveStatefulData($data);
        }

        return parent::setData($data);
    }

    ### PROTECTED METHODS

    /**
     * @param array|Traversable $data
     * @return array
     */
    protected function filterStatefulData(
        $data
    )
    {
        if ($data instanceof Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }

        $elements = $this->getElements();
        foreach ($elements as $element) {

            if (   $element instanceof \Zend\Form\Element\Csrf
                || $element instanceof \Zend\Form\Element\Password
                || $element instanceof \Zend\Form\Element\Submit) {

                $elementName = $element->getName();
                if (array_key_exists($elementName, $data)) {

                    unset($data[$elementName]);
                }
            }
        }

        return $data;
    }
}
