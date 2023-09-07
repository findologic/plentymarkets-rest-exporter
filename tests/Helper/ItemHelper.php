<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\XML\XmlVariant;

trait ItemHelper
{
    public function getMappedAttributes(Item|XmlVariant $item): array
    {
        $attributes = $item->getAttributes();
        return array_reduce($attributes, function (array $list, Attribute $attribute) {
            $list[$attribute->getKey()] = $attribute->getValues();
            return $list;
        }, []);
    }

    public function getOrderNumbers(Item|XmlVariant $item): array
    {
        $orderNumbers = $item->getOrdernumbers()->getValues();
        return array_map(fn ($item) => $item->getValue(), $this->getArrayFirstElement($orderNumbers));
    }

    public function getImages(Item $item): array
    {
        $images = $item->getImages();
        return array_map(fn ($item) => $item, $this->getArrayFirstElement($images));
    }

    public function getItemKeywords(Item $item): array
    {
        $keywords = $item->getKeywords()->getValues();
        return array_map(fn ($item) => $item->getValue(), $this->getArrayFirstElement($keywords));
    }

    public function getItemGroups(Item|XmlVariant $item): array
    {
        $keywords = $item->getGroups();
        return array_map(fn ($item) => $item->getValue(), $keywords);
    }

    public function getArrayFirstElement(array $array)
    {
        return array_key_exists(array_key_first($array), $array) ? $array[array_key_first($array)] : [];
    }
}
