<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\ImageAttributeValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;

class SeparatedVariation
{
    private Variation $variation;

    private RegistryService $registryService;

    public function __construct(
        Variation $variation,
        RegistryService $registryService
    ) {
        $this->variation = $variation;
        $this->registryService = $registryService;
    }

    /**
     * @param ImageAttributeValue[] $imageAttributeValues
     * @param array $variationAttributes
     * @return bool
     */
    public function isImageAvailable(array $imageAttributeValues, array $variationAttributes): bool
    {
        $imageAvailable = false;

        foreach ($imageAttributeValues as $imageAttributeValue) {
            foreach ($variationAttributes as $variationAttribute) {
                $attributeData = explode('_', $variationAttribute);
                $imageAttributeId = $imageAttributeValue->getAttributeId();
                $imageValueId = $imageAttributeValue->getValueId();
                $variationAttributeId = $attributeData[0];
                $variationValueId = $attributeData[1];

                if ($imageAttributeId != $variationAttributeId || $imageValueId != $variationValueId) {
                    continue;
                }

                $imageAvailable = true;
            }
        }

        return $imageAvailable;
    }

    public function getVariationGroupKey(): string
    {
        $attributeValues = $this->variation->getAttributeValues();
        $groupableAttributes = 0;
        $key = '';

        foreach ($attributeValues as $attributeValue) {
            $attribute = $this->registryService->getAttribute($attributeValue->getId());

            if ($attribute && $attribute->isGroupable()) {
                $groupableAttributes++;

                if ($key !== '') {
                    $key .= '/';
                }

                $key .= $attributeValue->getId() . '_' . $attributeValue->getValue()->getId();
            }
        }

        return ($groupableAttributes === 0 || count($attributeValues) > 1) ? $key : (string)$this->variation->getId();
    }
}
