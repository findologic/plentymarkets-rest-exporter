<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Item;

class CheapestVariation
{
    public const VARIATION_ID = 'id';

    public const PRICE = 'price';

    public const IMAGE = 'image';

    private const IS_MAIN = 'isMain';

    public const IMAGES = 'images';

    public const VARIATION_IMAGES = 'variation_images';

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
            self::IMAGE => $variation->getImage(),
            self::IMAGES => $variation->getImages(),
            self::VARIATION_IMAGES => $variation->getVariationImages(),
            self::IS_MAIN => $variation->isMain()
        ];
    }

    /**
     * If variations data is set in the cheapestVariationsData variable
     * this method will return the cheapest variation id, otherwise it will return null
     * @param float[] $prices
     */
    public function addImageAndPrice(
        ?Image $defaultImage,
        array $prices,
        bool $itemHasImage = false
    ): ?int {
        if (empty($this->cheapestVariationsData)) {
            $this->setDefaultImage($defaultImage, $itemHasImage);
            $this->setDefaultPrice($prices);

            return null;
        }

        $cheapestVariationsData = $this->getCheapestVariation();
        $this->item->addPrice($cheapestVariationsData[self::PRICE]);

        if (!$itemHasImage) {
            $this->setItemImage($cheapestVariationsData, $defaultImage, $itemHasImage);
        }

        return $cheapestVariationsData[self::VARIATION_ID];
    }

    /**
     * @return array<string, string|float|int|array>
     */
    public function getCheapestVariation(): array|bool
    {
        $priceColumn = array_column($this->cheapestVariationsData, self::PRICE);
        $isMainColumn = array_column($this->cheapestVariationsData, self::IS_MAIN);
        array_multisort($priceColumn, SORT_ASC, $isMainColumn, SORT_DESC, $this->cheapestVariationsData);

        return reset($this->cheapestVariationsData);
    }

    private function setDefaultImage(?Image $defaultImage, bool $itemHasImage): void
    {
        if ($itemHasImage || !$defaultImage) {
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

    /**
     * @param array<string,string|float|int|Image> $cheapestVariationsData
     */
    private function setItemImage(array $cheapestVariationsData, ?Image $defaultImage, bool $itemHasImage): void
    {
        if (!$cheapestVariationsData[self::IMAGE]) {
            $this->setDefaultImage($defaultImage, $itemHasImage);

            return;
        }

        $this->item->addImage($cheapestVariationsData[self::IMAGE]);
    }
}
