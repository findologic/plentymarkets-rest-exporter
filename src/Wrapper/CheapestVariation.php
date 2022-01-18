<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Item;

class CheapestVariation
{
    private const VARIATION_ID = 'id';

    private const PRICE = 'price';

    private const IMAGE = 'image';

    private Item $item;

    private array $cheapestVariationsData = [];

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function addVariation(Variation $variation): void
    {
        $this->cheapestVariationsData[] = [
            self::VARIATION_ID => $variation->getId(),
            self::PRICE => $variation->getPrice(),
            self::IMAGE => $variation->getImage()
        ];
    }

    public function addImageAndPrice(
        ?Image $defaultImage,
        array $prices,
        bool $hasImage = false
    ): ?int {
        if (empty($this->cheapestVariationsData)) {
            $this->setDefaultImage($defaultImage, $hasImage);
            $this->setDefaultPrice($prices);

            return null;
        }

        $cheapestVariationData = $this->getCheapestVariation();
        $this->item->addPrice($cheapestVariationData[self::PRICE]);

        if (!$hasImage) {
            $this->item->addImage($cheapestVariationData[self::IMAGE]);
        }

        return $cheapestVariationData[self::VARIATION_ID];
    }

    public function getCheapestVariation(): array
    {
        $priceColumn = array_column($this->cheapestVariationsData, self::PRICE);
        array_multisort($priceColumn, SORT_ASC, $this->cheapestVariationsData);

        return reset($this->cheapestVariationsData);
    }

    private function setDefaultImage(?Image $defaultImage, bool $hasImage): void
    {
        if ($hasImage || !$defaultImage) {
            return;
        }

        $this->item->addImage($defaultImage);
    }

    private function setDefaultPrice(array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $this->item->addPrice(min($prices));
    }
}
