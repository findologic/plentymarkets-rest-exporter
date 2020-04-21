<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\VatConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class VatResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var VatConfiguration[] */
    private $vatConfigurations;

    /**
     * @param VatConfiguration[] $vatConfigurations
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $vatConfigurations,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->vatConfigurations = $vatConfigurations;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return VatConfiguration|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->vatConfigurations);
    }

    /**
     * @return VatConfiguration[]
     */
    public function all(): array
    {
        return $this->vatConfigurations;
    }

    /**
     * @param array $criteria
     * @return VatConfiguration|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->vatConfigurations, $criteria);
    }

    /**
     * @param array $criteria
     * @return VatConfiguration[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->vatConfigurations, $criteria);
    }
}
