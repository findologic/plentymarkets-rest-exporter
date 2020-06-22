<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class ItemResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Item[] */
    private $items;

    /**
     * @param Item[] $items
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $items,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->items = $items;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Item|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->items);
    }

    /**
     * @return Item[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @param array $criteria
     * @return Item|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->items, $criteria);
    }

    /**
     * @param array $criteria
     * @return Item[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->items, $criteria);
    }

    public function getAllIds(): array
    {
        $ids = [];

        foreach ($this->items as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }
}
