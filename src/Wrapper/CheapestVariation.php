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

    /**
     * If variations data is set in the cheapestVariationsData variable
     * this method will return cheapest variation id, otherwise it will return null
     * @param float[] $prices
     */
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

        $cheapestVariationsData = $this->getCheapestVariation();
        $this->item->addPrice($cheapestVariationsData[self::PRICE]);

        if (!$hasImage) {
            $this->item->addImage($cheapestVariationsData[self::IMAGE]);
        }

        return $cheapestVariationsData[self::VARIATION_ID];
    }

    /**
     * @return array<string, string>
     */
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

    /**
     * @param float[] $prices
     */
    private function setDefaultPrice(array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $this->item->addPrice(min($prices));
    }
}
