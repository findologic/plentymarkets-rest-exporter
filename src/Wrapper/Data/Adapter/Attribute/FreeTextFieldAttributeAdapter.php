<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\MultiValueFieldAdapter;

class FreeTextFieldAttributeAdapter extends MultiValueFieldAdapter
{
    /**
     * @return Attribute[]
     */
    public function adapt(ProductEntity $product): array
    {
        $attributes = [];
        foreach (range(1, 20) as $field) {
            $fieldName = 'free' . (string)$field;
            $getter = 'getFree' . (string)$field;

            $value = (string)$product->{$getter}();
            if (trim($value) === '') {
                continue;
            }

            $attributes[] = new Attribute($fieldName, [$value]);
        }

        return $attributes;
    }

    /**
     * @return Attribute[]
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        return [];
    }
}
