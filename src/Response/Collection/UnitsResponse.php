<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class UnitsResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Unit[] */
    private $units;

    /**
     * @param Unit[] $units
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $units,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->units = $units;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Unit|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->units);
    }

    /**
     * @return Unit[]
     */
    public function all(): array
    {
        return $this->units;
    }

    /**
     * @param array $criteria
     * @return Unit|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->units, $criteria);
    }

    /**
     * @param array $criteria
     * @return Unit[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->units, $criteria);
    }
}
