<?php

namespace Tochka\JsonRpcSmd;

use RuntimeException;

/**
 * Class SmdDescription
 *
 * @property string $transport
 * @property string $envelope
 * @property string $SMDVersion
 * @property string $contentType
 * @property string $generator
 * @property string $target
 * @property string $description
 * @property array $additionalHeaders
 * @property bool $namedParameters
 * @property bool $acl
 * @property SmdService[] $services
 * @property SmdBaseObject[] $objects
 *
 * @package Tochka\JsonRpcSmd
 */
class SmdDescription implements SmdItem
{
    use Helpers;

    protected const ENVELOPE = 'JSON-RPC-2.0';
    protected const SMD_VERSION = '2.0';

    public function __construct()
    {
        $this->transport = 'POST';
        $this->envelope = self::ENVELOPE;
        $this->SMDVersion = self::SMD_VERSION;
        $this->contentType = 'application/json';
        $this->generator = 'Tochka/JsonRpc';
        $this->additionalHeaders = [];
        $this->services = [];
        $this->objects = [];
    }

    public static function fromArray(array $value): self
    {
        $instance = new self();
        
        if (($value['envelope'] ?? null) !== self::ENVELOPE || ($value['SMDVersion'] ?? null) !== self::SMD_VERSION) {
            throw new RuntimeException('Invalid SMD-scheme');
        }

        $instance->envelope = $value['envelope'];
        $instance->SMDVersion = $value['SMDVersion'];
        $instance->transport = $value['transport'] ?? 'POST';
        $instance->contentType = $value['contentType'] ?? 'application/json';
        $instance->generator = $value['generator'] ?? 'Tochka/JsonRpc';

        $instance->target = $value['target'] ?? null;
        $instance->description = $value['description'] ?? null;
        $instance->additionalHeaders = $value['additionalHeaders'] ?? [];
        $instance->namedParameters = $value['namedParameters'] ?? true;
        $instance->acl = $value['acl'] ?? false;

        $instance->services = $instance->getSmdItemsParameter(SmdService::class, $value['services'] ?? []);
        $instance->objects = $instance->getSmdItemsParameter(SmdBaseObject::class, $value['objects'] ?? []);

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'transport'         => $this->transport,
            'envelope'          => $this->envelope,
            'SMDVersion'        => $this->SMDVersion,
            'contentType'       => $this->contentType,
            'generator'         => $this->generator,
            'target'            => $this->target,
            'description'       => $this->description,
            'additionalHeaders' => $this->additionalHeaders,
            'namedParameters'   => $this->namedParameters,
            'acl'               => $this->acl,
            'services'          => $this->setSmdItemsParameter($this->services),
            'objects'           => $this->setSmdItemsParameter($this->objects),
        ];
    }

}