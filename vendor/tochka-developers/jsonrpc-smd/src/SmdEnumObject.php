<?php

namespace Tochka\JsonRpcSmd;

/**
 * Class SmdEnumObject
 *
 * @property SmdEnumValue[] $values
 * @property string $type
 *
 * @package Tochka\JsonRpcSmd
 */
class SmdEnumObject extends SmdBaseObject
{
    public const TYPE_ENUM = 'enum';

    public function __construct()
    {
        $this->objectType = self::TYPE_ENUM;
        $this->values = [];
    }

    public static function fromArray(array $value): self
    {
        /** @var self $instance */
        $instance = parent::fromArray($value);
        $instance->values = $instance->getSmdItemsParameter(SmdEnumValue::class, $value['values'] ?? []);
        $instance->type = $value['type'] ?? null;

        return $instance;
    }

    public function toArray(): array
    {
        $result = parent::toArray();

        $result = $this->setParameterIfIsSet($result, 'type');
        if (!empty($this->values)) {
            $result['values'] = $this->setSmdItemsParameter($this->values);
        }

        return $result;
    }

}