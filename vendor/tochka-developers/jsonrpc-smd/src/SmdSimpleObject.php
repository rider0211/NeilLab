<?php

namespace Tochka\JsonRpcSmd;

/**
 * Class SmdSimpleObject
 *
 * @property SmdParameter[] $parameters
 *
 * @package Tochka\JsonRpcSmd
 */
class SmdSimpleObject extends SmdBaseObject
{
    public const TYPE_OBJECT = 'object';

    public function __construct()
    {
        $this->objectType = self::TYPE_OBJECT;
        $this->parameters = [];
    }

    public static function fromArray(array $value): self
    {
        /** @var self $instance */
        $instance = parent::fromArray($value);
        $instance->parameters = $instance->getSmdItemsParameter(SmdParameter::class, $value['parameters'] ?? []);

        return $instance;
    }

    public function toArray(): array
    {
        $result = parent::toArray();

        if (!empty($this->parameters)) {
            $result['parameters'] = $this->setSmdItemsParameter($this->parameters);
        }

        return $result;
    }

}