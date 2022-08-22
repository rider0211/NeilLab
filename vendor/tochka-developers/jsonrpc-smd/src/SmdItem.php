<?php

namespace Tochka\JsonRpcSmd;

interface SmdItem
{
    public static function fromArray(array $value);
    public function toArray(): array;
}