<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class AbstractResponse implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $result = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $attributes = $property->getAttributes(SerializedName::class);

            if (count($attributes) > 0) {
                $name = $attributes[0]->getArguments()[0];
            }

            $result[$name] = $this->{$property->getName()};
        }

        return $result;
    }
}
