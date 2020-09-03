<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\IterableResponse;

class PropertySelectionResponse extends IterableResponse implements CollectionInterface, IterableResponseInterface
{
    use EntityCollection;

    /** @var Selection[] */
    private $selections;

    /**
     * @param Selection[] $selections
     */
    public function __construct(
        int $page,
        int $totalsCount,
        bool $isLastPage,
        array $selections,
        int $lastPageNumber = 1,
        int $firstOnPage = 1,
        int $lastOnPage = 1,
        int $itemsPerPage = 100
    ) {
        $this->page = $page;
        $this->totalsCount = $totalsCount;
        $this->isLastPage = $isLastPage;
        $this->selections = $selections;
        $this->lastPageNumber = $lastPageNumber;
        $this->firstOnPage = $firstOnPage;
        $this->lastOnPage = $lastOnPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return Selection|null
     */
    public function first(): ?Entity
    {
        return $this->getFirstEntity($this->selections);
    }

    /**
     * @return Selection[]
     */
    public function all(): array
    {
        return $this->selections;
    }

    /**
     * @param array $criteria
     * @return Selection|null
     */
    public function findOne(array $criteria): ?Entity
    {
        return $this->findOneEntityByCriteria($this->selections, $criteria);
    }

    /**
     * @param array $criteria
     * @return Selection[]
     */
    public function find(array $criteria): array
    {
        return $this->findEntitiesByCriteria($this->selections, $criteria);
    }

    public function getPropertySelectionValues(int $id, string $lang): array
    {
        $propertySelections = $this->find(['propertyId' => $id]); // TODO I think I implemented the find method with the intention that you can actually explicitly can get specific data.
        $values = [];
        foreach ($propertySelections as $propertySelection) {
            if (!$relation = $propertySelection->getRelation()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            foreach ($relation->getRelationValues() as $relationValue) {
                if (strtoupper($relationValue->getLang()) == strtoupper($lang)) {
                    $values[] = $relationValue->getValue();
                }
            }
        }

        return $values;
    }
}
