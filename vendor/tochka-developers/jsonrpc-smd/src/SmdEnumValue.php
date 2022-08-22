<?php

namespace Tochka\JsonRpcSmd;

/**
 * Class SmdEnumValue
 *
 * @property mixed $value
 * @property string $description
 *
 * @package Tochka\JsonRpcSmd
 */
class SmdEnumValue implements SmdItem
{
    use Helpers;

    public static function fromArray(array $value): self
    {
        $instance = new self();

        $instance->value = $value['value'] ?? null;
        $instance->description = $value['description'] ?? null;

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'value'       => $this->value,
            'description' => $this->description,
        ];
    }

}