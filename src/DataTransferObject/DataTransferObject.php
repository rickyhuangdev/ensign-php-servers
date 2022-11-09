<?php

declare(strict_types=1);

namespace Rickytech\Library\DataTransferObject;

use ReflectionClass;
use ReflectionProperty;
use Rickytech\Library\DataTransferObject\Attributes\CastWith;
use Rickytech\Library\DataTransferObject\Caster\DataTransferObjectCaster;
use Rickytech\Library\DataTransferObject\Exceptions\UnknownProperties;
use Rickytech\Library\DataTransferObject\Exceptions\ValidationException;
use Rickytech\Library\DataTransferObject\Reflection\DataTransferObjectClass;

#[CastWith(DataTransferObjectCaster::class)]
abstract class DataTransferObject
{
    protected array $exceptKeys = [];

    protected array $onlyKeys = [];


    /**
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function __construct(...$args)
    {
        if (is_array($args[0])) {
            $args = $args[0];
        }
        $class = new DataTransferObjectClass($this);
        foreach ($class->getProperties() as $property) {
            $property->setValue(Arr::get($args, $property->name, $property->getDefaultValue()));
            $args = Arr::forget($args, $property->name);
        }
        if ($class->isStrict() && count($args)) {
            throw UnknownProperties::new(static::class, array_keys($args));
        }
        $class->validate();
    }

    public static function arrayOf(array $arrayOfParameters): array
    {
        return array_map(
            fn (mixed $parameters) => new static($parameters),
            $arrayOfParameters
        );
    }
    public function toArray(): array
    {
        if (count($this->onlyKeys)) {
            $array = Arr::only($this->all(), $this->onlyKeys);
        } else {
            $array = Arr::except($this->all(), $this->exceptKeys);
        }

        return $this->parseArray($array);
    }

    public function all(): array
    {
        $data = [];

        $class = new ReflectionClass(static::class);

        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $mapToAttribute = $property->getAttributes(MapTo::class);
            $name = count($mapToAttribute) ? $mapToAttribute[0]->newInstance()->name : $property->getName();

            $data[$name] = $property->getValue($this);
        }

        return $data;
    }

    /**
     * @throws ValidationException
     * @throws UnknownProperties
     */
    public function clone(...$args): static
    {
        return new static(...array_merge($this->toArray(), $args));
    }
    protected function parseArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value instanceof DataTransferObject) {
                $array[$key] = $value->toArray();

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $array[$key] = $this->parseArray($value);
        }

        return $array;
    }
}
