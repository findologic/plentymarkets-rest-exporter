<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category\CategoryDetails;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\MultiValueFieldAdapter;

class CategoryAttributeAdapter extends MultiValueFieldAdapter
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
        $variationCategories = $variation->getCategories();
        $attributes = [];
        foreach ($variationCategories as $variationCategory) {
            $category = $this->getRegistryService()->getCategory($variationCategory->getId());

            if (!$category) {
                continue;
            }

            if (!$categoryDetail = $this->getCategoryDetailForCurrentPlentyIdAndLanguage($category)) {
                continue;
            }

            $attributes[] = new Attribute('cat', [$this->buildCategoryPath($category)]);
            $attributes[] = new Attribute(
                'cat_url',
                [parse_url($categoryDetail->getPreviewUrl(), PHP_URL_PATH)]
            );
        }

        return $attributes;
    }

    private function getCategoryDetailForCurrentPlentyIdAndLanguage(Category $category): ?CategoryDetails
    {
        /** @var CategoryDetails[] $translatedCategoryDetails */
        $translatedCategoryDetails = Translator::translateMultiple(
            $category->getDetails(),
            $this->getConfig()->getLanguage()
        );

        foreach ($translatedCategoryDetails as $categoryDetail) {
            if ($categoryDetail->getPlentyId() === $this->getRegistryService()->getWebStore()->getStoreIdentifier()) {
                return $categoryDetail;
            }
        }

        return null;
    }

    private function buildCategoryPath(Category $category): string
    {
        $path = [];
        if ($categoryDetail = $this->getCategoryDetailForCurrentPlentyIdAndLanguage($category)) {
            if ($parent = $this->getParentCategory($category)) {
                $path[] = $this->buildCategoryPath($parent);
            }

            $path[] = $categoryDetail->getName();
        }

        return implode('_', $path);
    }

    private function hasParentCategory(Category $category): bool
    {
        return $category->getParentCategoryId() !== null;
    }

    private function getParentCategory(Category $category): ?Category
    {
        if (!$this->hasParentCategory($category)) {
            return null;
        }

        return $this->getRegistryService()->getCategory($category->getParentCategoryId());
    }
}
