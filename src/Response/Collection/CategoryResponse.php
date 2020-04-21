<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class CategoryResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Category[] */
    private $categories;

    /**
     * @param Category[] $categories
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $categories,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->categories = $categories;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Category|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->categories);
    }

    /**
     * @return Category[]
     */
    public function all(): array
    {
        return $this->categories;
    }

    /**
     * @param array $criteria
     * @return Category|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->categories, $criteria);
    }

    /**
     * @param array $criteria
     * @return Category[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->categories, $criteria);
    }
}
