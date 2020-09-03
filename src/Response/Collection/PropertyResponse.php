<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class PropertyResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Property[] */
    private $properties;

    /**
     * @param Property[] $properties
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $properties,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->properties = $properties;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Property|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->properties);
    }

    /**
     * @return Property[]
     */
    public function all(): array
    {
        return $this->properties;
    }

    /**
     * @param array $criteria
     * @return Property|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->properties, $criteria);
    }

    /**
     * @param array $criteria
     * @return Property[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->properties, $criteria);
    }

    public function getPropertyName(int $id, string $lang): ?string
    {
        if (!$property = $this->findOne(['id' => $id])) {
            // @codeCoverageIgnoreStart
            return null; // TODO In this class there are multiple @codeCoverageIgnoreStart. They seem like they could be easily tested. It also makes sense to test these, so we are sure no exception happens in that case.
            // @codeCoverageIgnoreEnd
        }

        foreach ($property->getNames() as $name) {
            if (strtoupper($name->getLang()) == strtoupper($lang)) {
                return $name->getName();
            }
        }
        // @codeCoverageIgnoreStart
        return null; // TODO In this class there are multiple @codeCoverageIgnoreStart. They seem like they could be easily tested. It also makes sense to test these, so we are sure no exception happens in that case.
        // @codeCoverageIgnoreEnd
    }

    public function getPropertySelectionValue(int $id, int $selectionId, string $lang): ?string
    {
        if (!$property = $this->findOne(['id' => $id])) {
            // @codeCoverageIgnoreStart
            return null; // TODO In this class there are multiple @codeCoverageIgnoreStart. They seem like they could be easily tested. It also makes sense to test these, so we are sure no exception happens in that case.
            // @codeCoverageIgnoreEnd
        }

        foreach ($property->getSelections() as $selection) {
            if ($selection->getId() !== $selectionId) {
                continue;
            }

            foreach ($selection->getRelation()->getRelationValues() as $relationValue) {
                if (strtoupper($relationValue->getLang()) != strtoupper($lang)) {
                    continue;
                }

                return $relationValue->getValue();
            }
        }

        // @codeCoverageIgnoreStart
        return null; // TODO In this class there are multiple @codeCoverageIgnoreStart. They seem like they could be easily tested. It also makes sense to test these, so we are sure no exception happens in that case.
        // @codeCoverageIgnoreEnd
    }
}
