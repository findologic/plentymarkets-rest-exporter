<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Manufacturer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class ManufacturersResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Manufacturer[] */
    private $manufacturers;

    /**
     * @param Manufacturer[] $manufacturers
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $manufacturers,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->manufacturers = $manufacturers;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Manufacturer|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->manufacturers);
    }

    /**
     * @return Manufacturer[]
     */
    public function all(): array
    {
        return $this->manufacturers;
    }

    /**
     * @param array $criteria
     * @return Manufacturer|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->manufacturers, $criteria);
    }

    /**
     * @param array $criteria
     * @return Manufacturer[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->manufacturers, $criteria);
    }
}
