<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

abstract class Entity
{
    abstract public function getData(): array;

    public function getStringProperty(string $key, array $data, ?string $default = null): ?string
    {
        return isset($data[$key]) ? (string)$data[$key] : $default;
    }

    public function getBoolProperty(string $key, array $data, ?bool $default = null): ?bool
    {
        return isset($data[$key]) ? filter_var($data[$key], FILTER_VALIDATE_BOOLEAN) : $default;
    }

    public function getIntProperty(string $key, array $data, ?int $default = null): ?int
    {
        return isset($data[$key]) ? (int)$data[$key] : $default;
    }

    public function getFloatProperty(string $key, array $data, ?float $default = null): ?float
    {
        return isset($data[$key]) ? (float)$data[$key] : $default;
    }
}
