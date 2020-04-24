<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class Product extends Entity
{

    public function jsonSerialize(): array
    {
        return [

        ];
    }

    public function getId(): string
    {
        return '123ProductId';
    }

    public function getTexts(): array
    {
        return [
            [
                'lang' => 'de',
                'name1' => 'Best Product!',
                'name2' => '',
                'name3' => '',
                'shortDescription' => 'This is a pretty short description',
                'metaDescription' => 'Metadescription for search engines',
                'description' => str_repeat('long description ', 50),
                'technicalData' => '',
                'urlPath' => 'living-room/chairs-sofas/best-product-',
                'keywords' => 'Keyword'
            ]
        ];
    }
}
