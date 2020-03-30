<?php

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

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
     * $collection->find([
     *     'id' => 1234,
     *     'name' => 'blub',
     * ]);
     * ```
     */
    public function findOne(array $criteria): ?Entity;

    /**
     * Finds all entities in the collection based by a given criteria.
     *
     * Usage:
     *
     * ```
     * $collection->find([
     *     'name' => 'blub',
     * ]);
     * ```
     *
     * @return Entity[]
     */
    public function find(array $criteria): array;
}
