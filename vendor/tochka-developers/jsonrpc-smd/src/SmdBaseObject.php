<?php

namespace Tochka\JsonRpcSmd;

/**
 * Class SmdBaseObject
 *
 * @property string $name
 * @property string $description
 * @property string $objectType
 *
 * @package Tochka\JsonRpcSmd
 */
abstract class SmdBaseObject implements SmdItem
{
    use Helpers;

    protected const TYPES = [
        SmdSimpleObject::TYPE_OBJECT => SmdSimpleObject::class,
        SmdEnumObject::TYPE_ENUM => SmdEnumObject::class,
    ];

    public static function fromArray(array $value)
    {
        if (static::class === self::class) {
            $objectType = $value['objectType'] ?? array_keys(self::TYPES)[0];
            $className = self::TYPES[$objectType] ?? array_values(self::TYPES)[0];

            /** @var self $instance */
            return $className::fromArray($value);
        }

        $instance = new static();

        $instance->name = $value['name'] ?? null;
        $instance->description = $value['description'] ?? null;

        return $instance;
    }

    public function toArray(): array
    {
        $result = [
            'name'       => $this->name,
            'objectType' => $this->objectType,
        ];

        $result = $this->setParameterIfIsSet($result, 'description');

        return $result;
    }
}