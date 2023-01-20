<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class PropertyGroupResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var PropertyGroup[] */
    private array $propertyGroups;

    /**
     * @param PropertyGroup[] $propertyGroups
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $propertyGroups,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->propertyGroups = $propertyGroups;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return PropertyGroup|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->propertyGroups);
    }

    /**
     * @return PropertyGroup[]
     */
    public function all(): array
    {
        return $this->propertyGroups;
    }

    /**
     * @param array $criteria
     * @return PropertyGroup|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->propertyGroups, $criteria);
    }

    /**
     * @param array $criteria
     * @return PropertyGroup[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->propertyGroups, $criteria);
    }
}
