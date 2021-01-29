<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class PimVariationResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Variation[] */
    private $pimVariations;

    /**
     * @param Variation[] $pimVariations
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $pimVariations,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->pimVariations = $pimVariations;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Variation|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->pimVariations);
    }

    /**
     * @return Variation[]
     */
    public function all(): array
    {
        return $this->pimVariations;
    }

    /**
     * @param array $criteria
     * @return Variation|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->pimVariations, $criteria);
    }

    /**
     * @param array $criteria
     * @return Variation[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->pimVariations, $criteria);
    }
}
