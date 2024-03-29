<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\ImageAttributeValue;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use Psr\Cache\InvalidArgumentException;

class SeparatedVariation
{
    private const MULTIPLE_KEYS_SEPARATOR = '/';
    private const ATTRIBUTE_VALUE_SEPARATOR = '_';

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
     * @param array<int, string> $variationAttributes
     * @return bool
     */
    public function isImageAvailable(array $imageAttributeValues, array $variationAttributes): bool
    {
        $imageAvailable = false;

        foreach ($imageAttributeValues as $imageAttributeValue) {
            foreach ($variationAttributes as $variationAttribute) {
                $attributeData = explode(self::ATTRIBUTE_VALUE_SEPARATOR, $variationAttribute);

                // If variation has only one groupable attribute, it's key value will be main variation id.
                // For this reason $attributeData will not contain an attribute id and attribute value id.
                if (count($attributeData) !== 2) {
                    continue;
                }

                $imageAttributeId = (string)$imageAttributeValue->getAttributeId();
                $imageValueId = (string)$imageAttributeValue->getValueId();
                $variationAttributeId = $attributeData[0];
                $variationValueId = $attributeData[1];

                if ($imageAttributeId !== $variationAttributeId || $imageValueId !== $variationValueId) {
                    continue;
                }

                $imageAvailable = true;
            }
        }

        return $imageAvailable;
    }

    /*
     * This method generates variation group key. It searches for attributes with groupable property
     * and generates keys from groupable attributes ids and values. If there is just one attribute,
     * we are returning a variation id for separating variations by one attribute.
     */
    /**
     * @throws InvalidArgumentException
     */
    public function getVariationGroupKey(): string
    {
        $attributeValues = $this->variation->getAttributeValues();
        $groupableAttributes = 0;
        $key = '';

        foreach ($attributeValues as $attributeValue) {
            if (!$this->isAttributeGroupable($attributeValue)) {
                continue;
            }

            $groupableAttributes++;

            if ($key !== '') {
                $key .= self::MULTIPLE_KEYS_SEPARATOR;
            }

            $key .= $attributeValue->getId() . self::ATTRIBUTE_VALUE_SEPARATOR . $attributeValue->getValue()->getId();
        }

        if ($groupableAttributes === 0 || count($attributeValues) > 1) {
            return $key;
        }

        return (string)$this->variation->getId();
    }

    /**
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function getVariationAttributes(string $variationAttributes, int $wrapMode): array
    {
        if (strpos($variationAttributes, self::ATTRIBUTE_VALUE_SEPARATOR)) {
            return explode(self::MULTIPLE_KEYS_SEPARATOR, $variationAttributes);
        }

        return explode(self::MULTIPLE_KEYS_SEPARATOR, $this->getVariationGroupKeyForImageProcessing($wrapMode));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function isAttributeGroupable(Attribute $attribute): bool
    {
        $attributeData = $this->registryService->getAttribute($attribute->getId());

        return ($attributeData && $attributeData->isGroupable());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getVariationGroupKeyForImageProcessing(int $wrapMode): string
    {
        $attributeValues = $this->variation->getAttributeValues();
        $key = '';

        foreach ($attributeValues as $attributeValue) {
            if ($wrapMode === Product::WRAP_MODE_SEPARATE_VARIATIONS && !$this->isAttributeGroupable($attributeValue)) {
                continue;
            }

            if ($key !== '') {
                $key .= self::MULTIPLE_KEYS_SEPARATOR;
            }

            $key .= $attributeValue->getId() . self::ATTRIBUTE_VALUE_SEPARATOR . $attributeValue->getValue()->getId();
        }

        return $key;
    }
}
