<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute as AttributeEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Attribute as AttributePropertyEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\AttributeValueName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\MultiValueFieldAdapter;

class VariationAttributesAttributeAdapter extends MultiValueFieldAdapter
{
    /**
     * @return Attribute[]
     */
    public function adapt(ProductEntity $product): array
    {
        return [];
    }

    /**
     * @return Attribute[]
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        $attributes = [];
        foreach ($variation->getAttributeValues() as $variationAttributeValue) {
            $attribute = $this->getRegistryService()->getAttribute($variationAttributeValue->getId());
            if (!$attribute) {
                continue;
            }

            if (!$this->hasName($attribute, $variationAttributeValue)) {
                continue;
            }

            $attributes[] = new Attribute(
                $this->getAttributeName($attribute),
                [$this->getAttributeValue($variationAttributeValue)]
            );
        }

        return $attributes;
    }

    private function hasName(AttributeEntity $attribute, AttributePropertyEntity $variationAttributeValue): bool
    {
        return (
            !Utils::isEmpty($attribute->getBackendName())
            || !Utils::isEmpty($variationAttributeValue->getValue()->getBackendName())
        );
    }

    private function getAttributeName(AttributeEntity $attribute): string
    {
        /** @var Name|null $attributeTranslation */
        $attributeTranslation = Translator::translate($attribute->getNames(), $this->getConfig()->getLanguage());

        return $attributeTranslation ? $attributeTranslation->getName() : $attribute->getBackendName();
    }

    private function getAttributeValue(AttributePropertyEntity $variationAttributeValue): string
    {
        /** @var AttributeValueName $valueTranslation */
        $valueTranslation = Translator::translate(
            $variationAttributeValue->getValue()->getNames(),
            $this->getConfig()->getLanguage()
        );

        return $valueTranslation ?
            $valueTranslation->getName() :
            $variationAttributeValue->getValue()->getBackendName();
    }
}
