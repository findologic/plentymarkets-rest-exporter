<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use Carbon\Carbon;
use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

abstract class Entity
{
    protected const NESTED_PROPERTIES = ['images' => 'image'];
    abstract public function getData(): array;

    protected function getStringProperty(string $key, array $data, ?string $default = null): ?string
    {
        return isset($data[$key]) ? (string)$data[$key] : $default;
    }

    protected function getBoolProperty(string $key, array $data, ?bool $default = null): ?bool
    {
        return isset($data[$key]) ? Utils::filterBoolean($data[$key]) : $default;
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

    /**
     * This method should not be used for complex structured data, but rather for simple structures,
     * such as an array of options with max. 4 options. In any other case a new Entity should be created instead!
     */
    protected function getArrayProperty(string $key, array $data, ?array $default = null): ?array
    {
        return isset($data[$key]) && is_array($data[$key]) ? $data[$key] : $default;
    }

    /**
     * @param int|string|null $key Provide for data where the index is used as entity name.
     */
    protected function getEntity(string $class, array $data, mixed $key = null): Entity
    {
        if (is_string($key)) {
            return new $class($key, $data);
        }

        return new $class($data);
    }

    /**
     * @return Entity[]
     */
    protected function getEntities(string $entityClass, string $field, array $data): array
    {
        if ((!isset($data[$field]) || $data[$field] === []) && !array_key_exists($field, self::NESTED_PROPERTIES)) {
            return [];
        }

        $entities = [];
        foreach ($data[$field] as $key => $entityData) {
            $shouldUseNestedProperties = $this->shouldUseNestedProperties($field, $entityData);
            if ($shouldUseNestedProperties) {
                $entityData = $entityData[self::NESTED_PROPERTIES[$field]];
            }
            $entities[] = $this->getEntity($entityClass, $entityData, $key);
        }

        return $entities;
    }

    private function shouldUseNestedProperties($field, $entityData): bool
    {
        $nestedProperties = self::NESTED_PROPERTIES;
        if (array_key_exists($field, $nestedProperties) && array_key_exists($nestedProperties[$field], $entityData)) {
            return true;
        }

        return false;
    }
}
