<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use Carbon\Carbon;
use DateTimeInterface;

abstract class Entity
{
    abstract public function getData(): array;

    protected function getStringProperty(string $key, array $data, ?string $default = null): ?string
    {
        return isset($data[$key]) ? (string)$data[$key] : $default;
    }

    protected function getBoolProperty(string $key, array $data, ?bool $default = null): ?bool
    {
        return isset($data[$key]) ? filter_var($data[$key], FILTER_VALIDATE_BOOLEAN) : $default;
    }

    protected function getIntProperty(string $key, array $data, ?int $default = null): ?int
    {
        return isset($data[$key]) ? (int)$data[$key] : $default;
    }

    protected function getFloatProperty(string $key, array $data, ?float $default = null): ?float
    {
        return isset($data[$key]) ? (float)$data[$key] : $default;
    }

    protected function getDateTimeProperty(
        string $key,
        array $data,
        ?DateTimeInterface $default = null
    ): ?DateTimeInterface {
        return isset($data[$key]) ? Carbon::createFromTimeString($data[$key]) : $default;
    }

    protected function getEntity(string $class, array $data): Entity
    {
        return new $class($data);
    }

    /**
     * @return Entity[]
     */
    protected function getEntities(string $entityClass, string $field, array $data): array
    {
        if (!isset($data[$field]) || $data[$field] === null || $data[$field] === []) {
            return [];
        }

        $entities = [];
        foreach ($data[$field] as $entityData) {
            $entities[] = $this->getEntity($entityClass, $entityData);
        }

        return $entities;
    }
}
