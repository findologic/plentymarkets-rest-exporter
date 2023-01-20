<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class ItemVariationResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var ItemVariation[] */
    private array $itemVariations;

    /**
     * @param ItemVariation[] $itemVariations
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $itemVariations,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->itemVariations = $itemVariations;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return ItemVariation|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->itemVariations);
    }

    /**
     * @return ItemVariation[]
     */
    public function all(): array
    {
        return $this->itemVariations;
    }

    /**
     * @param array $criteria
     * @return ItemVariation|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->itemVariations, $criteria);
    }

    /**
     * @param array $criteria
     * @return ItemVariation[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->itemVariations, $criteria);
    }
}
