<?php

namespace Infrastructure\Library;

use Infrastructure\Library\SerializableInterface;

interface XmlSerializerInterface
{
    public function serialize(
        SerializableInterface $object
    );
}
