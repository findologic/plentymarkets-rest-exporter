<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Image as PimImage;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;

class ImageAdapter extends SingleValueFieldAdapter
{
    public function adapt(ProductEntity $product): ?Image
    {
        return null;
    }

    public function adaptVariation(VariationEntity $variation): ?Image
    {
        $images = $this->getSortedImages($variation);

        foreach ($images as $image) {
            $imageAvailabilities = $image->getAvailabilities();
            foreach ($imageAvailabilities as $imageAvailability) {
                if ($imageAvailability->getType() !== Availability::STORE) {
                    continue;
                }

                return new Image($image->getUrlMiddle());
            }
        }

        return null;
    }

    /**
     * @return PimImage[]
     */
    private function getSortedImages(VariationEntity $variation): array
    {
        $images = array_merge($variation->getImages(), $variation->getBase()->getImages());
        usort($images, fn(PimImage $a, PimImage $b) => $a->getPosition() <=> $b->getPosition());

        return $images;
    }
}
