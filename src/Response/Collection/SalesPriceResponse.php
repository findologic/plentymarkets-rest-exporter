<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CollectionInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\IterableResponseInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\EntityCollection;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\SalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class SalesPriceResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var SalesPrice[] */
    private $salesPrices;

    /**
     * @param SalesPrice[] $salesPrices
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $salesPrices,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->salesPrices = $salesPrices;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return SalesPrice|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->salesPrices);
    }

    /**
     * @return SalesPrice[]
     */
    public function all(): array
    {
        return $this->salesPrices;
    }

    /**
     * @param array $criteria
     * @return SalesPrice|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->salesPrices, $criteria);
    }

    /**
     * @param array $criteria
     * @return SalesPrice[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->salesPrices, $criteria);
    }
}
