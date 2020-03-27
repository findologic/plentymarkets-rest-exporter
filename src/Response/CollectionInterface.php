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
}
