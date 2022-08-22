<?php

namespace Tochka\JsonRpcSmd;

/**
 * Class SmdService
 *
 * @property string $name
 * @property string $description
 * @property string $group
 * @property string $groupName
 * @property SmdParameter[] $parameters
 * @property SmdReturn $return
 * @property SmdParameter[] $returnParameters
 * @property array $acl
 * @property string $endpoint
 * @property bool $deprecated
 * @property string $note
 * @property string $warning
 * @property string $requestExample
 * @property string $responseExample
 * @property SmdBaseObject[] $objects
 * @property string[] $tags
 *
 * @package Tochka\JsonRpcSmd
 */
class SmdService implements SmdItem
{
    use Helpers;

    public function __construct()
    {
        $this->parameters = [];
        $this->returnParameters = [];
        $this->acl = [];
        $this->deprecated = false;
        $this->objects = [];
        $this->tags = [];
    }

    public static function fromArray(array $value): self
    {
        $instance = new self();

        $instance->name = $value['name'] ?? null;
        $instance->description = $value['description'] ?? null;
        $instance->group = $value['group'] ?? null;
        $instance->groupName = $value['groupName'] ?? null;
        $instance->return = SmdReturn::fromArray($value['return'] ?? []);
        $instance->acl = $value['acl'] ?? [];
        $instance->endpoint = $value['endpoint'] ?? null;
        $instance->deprecated = $value['deprecated'] ?? false;
        $instance->note = $value['note'] ?? null;
        $instance->warning = $value['warning'] ?? null;
        $instance->requestExample = $value['requestExample'] ?? null;
        $instance->responseExample = $value['responseExample'] ?? null;
        $instance->tags = $value['tags'] ?? [];

        $instance->parameters = $instance->getSmdItemsParameter(SmdParameter::class, $value['parameters'] ?? []);
        $instance->returnParameters = $instance->getSmdItemsParameter(SmdParameter::class, $value['returnParameters'] ?? []);
        $instance->objects = $instance->getSmdItemsParameter(SmdBaseObject::class, $value['objects'] ?? []);

        return $instance;
    }

    public function toArray(): array
    {
        $result = [
            'name'        => $this->name,
            'description' => $this->description,
            'group'       => $this->group,
            'parameters'  => $this->setSmdItemsParameter($this->parameters),
            'return'      => $this->return->toArray(),
        ];

        $result = $this->setParameterIfIsSet($result, 'groupName');
        $result = $this->setParameterIfTrue($result, 'acl');
        $result = $this->setParameterIfIsSet($result, 'endpoint');
        $result = $this->setParameterIfTrue($result, 'deprecated');
        $result = $this->setParameterIfIsSet($result, 'note');
        $result = $this->setParameterIfIsSet($result, 'warning');
        $result = $this->setParameterIfIsSet($result, 'requestExample');
        $result = $this->setParameterIfIsSet($result, 'responseExample');
        $result = $this->setParameterIfNotEmpty($result, 'tags');

        if (!empty($this->returnParameters)) {
            $result['returnParameters'] = $this->setSmdItemsParameter($this->returnParameters);
        }

        if (!empty($this->objects)) {
            $result['objects'] = $this->setSmdItemsParameter($this->objects);
        }
        
        return $result;
    }
}