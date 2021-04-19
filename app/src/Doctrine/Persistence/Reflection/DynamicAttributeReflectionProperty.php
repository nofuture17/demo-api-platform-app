<?php

declare(strict_types=1);

namespace App\Doctrine\Persistence\Reflection;

final class DynamicAttributeReflectionProperty
{
    public string $class;
    public string $name;

    public function __construct(string $class, string $property)
    {
        $this->class = $class;
        $this->name = $property;
    }

    /**
     * @param mixed|null $value
     */
    public function setValue(object $objectOrValue, $value = null): void
    {
        $objectOrValue->{$this->name} = $value;
    }
}
