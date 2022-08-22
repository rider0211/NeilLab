<?php

namespace Tochka\JsonRpcSmd;

/**
 * Class SmdReturn
 *
 * @property string $description
 * @property string[] $types
 *
 * @package Tochka\JsonRpcSmd
 */
class SmdReturn implements SmdItem
{
    use Helpers;

    public function __construct()
    {
        $this->types = [];
    }

    public static function fromArray(array $value): self
    {
        $instance = new self();

        $instance->description = $value['description'] ?? null;
        $instance->types = $value['types'] ?? [];

        return $instance;
    }

    public function toArray(): array
    {
        $result = [
            'type' => implode('|', $this->types),
        ];

        $result = $this->setParameterIfIsSet($result, 'description');
        $result = $this->setParameterIfNotEmpty($result, 'types');

        return $result;
    }

}