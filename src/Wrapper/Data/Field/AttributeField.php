<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category\CategoryDetails;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\AttributeValueName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\TagName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field\Requirement\ConfigAware;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field\Requirement\RegistryAware;

class AttributeField implements MultiValueField
{
    use RegistryAware;
    use ConfigAware;

    /** @var Attribute[] */
    protected array $attributes = [];
    protected bool $hasCategories = false;

    /**
     * @param ProductEntity $product
     * @return Attribute[]
     */
    public function parseProduct(ProductEntity $product): array
    {
        $this->parseManufacturer($product);
        $this->parseFreeTextFields($product);

        return $this->attributes;
    }

    /**
     * @param VariationEntity $variation
     * @return Attribute[]
     */
    public function parseVariation(VariationEntity $variation): array
    {
        $this->parseCategories();
        $this->parseVariationAttributes();

        return $this->attributes;
    }

    public function reset(): void
    {
        $this->attributes = [];
    }

    protected function parseManufacturer(ProductEntity $product): void
    {
        $manufacturerId = $product->getManufacturerId();
        if (Utils::isEmpty($manufacturerId)) {
            return;
        }

        $manufacturer = $this->registryService->getManufacturer($manufacturerId);
        if (Utils::isEmpty($manufacturer->getName())) {
            return;
        }

        $this->attributes[] = new Attribute('vendor', [$manufacturer->getName()]);
    }

    protected function parseFreeTextFields(ProductEntity $product): void
    {
        foreach (range(1, 20) as $field) {
            $fieldName = 'free' . (string)$field;
            $getter = 'getFree' . (string)$field;

            $value = (string)$product->{$getter}();
            if (trim($value) === '') {
                continue;
            }

            $this->attributes[] = new Attribute($fieldName, [$value]);
        }
    }

    protected function parseCategories(): void
    {
        foreach ($this->variationEntities as $variationEntity) {
            $variationCategories = $variationEntity->getCategories();
            foreach ($variationCategories as $variationCategory) {
                $category = $this->registryService->getCategory($variationCategory->getId());

                if (!$category) {
                    continue;
                }

                if (!$categoryDetail = $this->getCategoryDetailForCurrentPlentyIdAndLanguage($category)) {
                    continue;
                }

                $this->attributes[] = new Attribute('cat', [$this->buildCategoryPath($category)]);
                $this->attributes[] = new Attribute(
                    'cat_url',
                    [parse_url($categoryDetail->getPreviewUrl(), PHP_URL_PATH)]
                );

                $this->hasCategories = true;
            }
        }
    }

    protected function parseVariationAttributes(): void
    {
        foreach ($this->variationEntities as $variationEntity) {
            foreach ($variationEntity->getAttributeValues() as $variationAttributeValue) {
                $attribute = $this->registryService->getAttribute($variationAttributeValue->getId());
                if (!$attribute) {
                    continue;
                }

                $emptyName = Utils::isEmpty($attribute->getBackendName());
                if ($emptyName || Utils::isEmpty($variationAttributeValue->getValue()->getBackendName())) {
                    continue;
                }

                $attributeName = $attribute->getBackendName();
                /** @var Name|null $attributeTranslation */
                $attributeTranslation = Translator::translate($attribute->getNames(), $this->config->getLanguage());
                if ($attributeTranslation) {
                    $attributeName = $attributeTranslation->getName();
                }

                $value = $variationAttributeValue->getValue()->getBackendName();
                /** @var AttributeValueName $valueTranslation */
                $valueTranslation = Translator::translate(
                    $variationAttributeValue->getValue()->getNames(),
                    $this->config->getLanguage()
                );
                if ($valueTranslation) {
                    $value = $valueTranslation->getName();
                }

                $this->attributes[] = new Attribute(
                    $attributeName,
                    [$value]
                );
            }
        }
    }

    protected function parseTags(): void
    {
        $tags = [];
        foreach ($this->variationEntities as $variationEntity) {
            $tags = $variationEntity->getTags();
        }

        $tagIds = [];
        foreach ($tags as $tag) {
            if (!$this->shouldProcessTag($tag)) {
                continue;
            }

            $tagIds[] = $tag->getId();

            $tagName = $tag->getTagData()->getName();

            /** @var TagName|null $translatedTag */
            $translatedTag = Translator::translate($tag->getTagData()->getNames(), $this->config->getLanguage());
            if ($translatedTag) {
                $tagName = $translatedTag->getName();
            }

            $this->tags[] = new Keyword($tagName);
        }

        if ($tagIds) {
            $this->attributes[] = new Attribute('cat_id', $tagIds);
        }
    }

    private function getCategoryDetailForCurrentPlentyIdAndLanguage(Category $category): ?CategoryDetails
    {
        /** @var CategoryDetails[] $translatedCategoryDetails */
        $translatedCategoryDetails = Translator::translateMultiple(
            $category->getDetails(),
            $this->config->getLanguage()
        );

        foreach ($translatedCategoryDetails as $categoryDetail) {
            if ($categoryDetail->getPlentyId() === $this->registryService->getWebStore()->getStoreIdentifier()) {
                return $categoryDetail;
            }
        }

        return null;
    }

    private function buildCategoryPath(Category $category): string
    {
        $path = [];
        if ($categoryDetail = $this->getCategoryDetailForCurrentPlentyIdAndLanguage($category)) {
            if ($this->hasParentCategory($category)) {
                $path[] = $this->buildCategoryPath(
                    $this->registryService->getCategory($category->getParentCategoryId())
                );
            }

            $path[] = $categoryDetail->getName();
        }

        return implode('_', $path);
    }

    private function hasParentCategory(Category $category): bool
    {
        return (
            $category->getParentCategoryId() !== null
            && $this->registryService->getCategory($category->getParentCategoryId())
        );
    }

    private function shouldProcessTag(Tag $tag): bool
    {
        if (!$clients = $tag->getTagData()->getClients()) {
            return false;
        }

        foreach ($clients as $client) {
            if ($client->getPlentyId() === $this->registryService->getWebStore()->getStoreIdentifier()) {
                return true;
            }
        }

        return false;
    }
}
