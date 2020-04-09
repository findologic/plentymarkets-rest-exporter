<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

/**
 * Responses that contain multiple entities may implement this interface, to allow easy access to their entities.
 */
interface CollectionInterface
{
    /**
     * Returns the first entity in a collection or null if there are no elements.
     */
    public function first(): ?Entity;

    /**
     * Returns all entities in a collection.
     *
     * @return Entity[]
     */
    public function all(): array;

    /**
     * Finds an entity in the collection based by a given criteria.
     * If there are multiple entities matching the same criteria, only the first one may be returned.
     *
     * Usage:
     *
     * ```
     * $entity = $collection->findOne([
     *     'id' => 1234,
     *     'name' => 'blub',
     * ]);
     * ```
     *
     * Complex Usage:
     *
     * ```
     * $entity = $collection->findOne([
     *     'hasChildren' => false,
     *     'details' => [
     *         'categoryId' => 370
     *     ]
     * );
     * ```
     */
    public function findOne(array $criteria): ?Entity;

    /**
     * Finds all entities in the collection based by a given criteria.
     *
     * Usage:
     *
     * ```
     * $entities = $collection->find([
     *     'name' => 'blub',
     * ]);
     * ```
     *
     * Complex Usage:
     *
     * ```
     * $entity = $collection->find([
     *     'hasChildren' => false,
     *     'details' => [
     *         'name' => 'best category'
     *     ]
     * );
     * ```
     *
     * @return Entity[]
     */
    public function find(array $criteria): array;
}
