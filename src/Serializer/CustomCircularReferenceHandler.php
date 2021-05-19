<?php

namespace App\Serializer;

class CustomCircularReferenceHandler
{
    public function __invoke($object)
    {
        return $object->getId();
    }
}
