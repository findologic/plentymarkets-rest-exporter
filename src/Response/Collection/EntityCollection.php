<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Exception\UnknownGetterException;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use InvalidArgumentException;

trait EntityCollection
{
    private static array $GETTER_PREFIXES = ['get', 'is', 'has', ''];

    /**
     * @param Entity[] $entities
     * @return Entity|null
     */
    protected function getFirstEntity(array $entities): ?Entity
    {
        $entitiesWithoutKeys = array_values($entities);

        if (!isset($entitiesWithoutKeys[0])) {
            return null;
        }

        return $entitiesWithoutKeys[0];
    }

    /**
     * @param Entity[] $entities
     * @param array $criteria
     *
     * @return Entity|null
     */
    protected function findOneEntityByCriteria(array $entities, array $criteria): ?Entity
    {
        foreach ($entities as $entity) {
            if ($this->isCriteriaMatching($criteria, $entity)) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param Entity[] $entities
     * @return Entity[]
     */
    protected function findEntitiesByCriteria(array $entities, array $criteria): array
    {
        $entitiesMatchingCriteria = [];
        foreach ($entities as $entity) {
            if (!$this->isCriteriaMatching($criteria, $entity)) {
                continue;
            }

            $entitiesMatchingCriteria[] = $entity;
        }

        return $entitiesMatchingCriteria;
    }

    private function isCriteriaMatching(array $criteria, Entity $entity): bool
    {
        foreach ($criteria as $criterion => $value) {
            try {
                $getterReturnValue = $this->callGetter($entity, $criterion);
            } catch (UnknownGetterException) {
                continue;
            }

            if (is_array($value) || $value instanceof Entity) {
                return $this->handleSubCriteria($criterion, $value, $getterReturnValue);
            }

            if (!$this->assertCriteriaValue($value, $getterReturnValue)) {
                return false;
            }
        }

        return true;
    }

    private function assertCriteriaValue($expected, $actual): bool
    {
        return ($expected === $actual);
    }

    /**
     * @throws UnknownGetterException
     */
    private function callGetter(Entity $entity, string $property)
    {
        foreach (self::$GETTER_PREFIXES as $getterPrefix) {
            $getter = $getterPrefix . ucfirst($property);

            if (!method_exists($entity, $getter)) {
                continue;
            }

            return $entity->{$getter}();
        }

        throw new UnknownGetterException(sprintf(
            'Getter for "%s" could not be found in %s.',
            $property,
            get_class($entity)
        ));
    }

    private function handleSubCriteria(string $criterion, array $criteria, $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (!$this->isCriteriaMatching($criteria, $item)) {
                    continue;
                }

                return true;
            }

            return false;
        } elseif ($value instanceof Entity) {
            return $this->isCriteriaMatching($criteria, $value);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Criteria expected "%s" to be of type "array" or "Entity". Returned a value of type "%s".',
                $criterion,
                gettype($value)
            ));
        }
    }
}
