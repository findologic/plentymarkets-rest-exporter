<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class AttributeResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Attribute[] */
    private $attributes;

    /**
     * @param Attribute[] $attributes
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $attributes,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->attributes = $attributes;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Attribute|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->attributes);
    }

    /**
     * @return Attribute[]
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $criteria
     * @return Attribute|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->attributes, $criteria);
    }

    /**
     * @param array $criteria
     * @return Attribute[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->attributes, $criteria);
    }
}
