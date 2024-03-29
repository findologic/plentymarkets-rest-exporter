<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Attribute;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Barcode;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Base;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Characteristic;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Image;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\SalesPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\TagName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Unit;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;

class Variation extends Entity
{
    public const EXCLUSION_TAG_NAME = 'findologic-exclude';

    private ?int $id;

    /** @var Category[] */
    private array $categories;

    /** @var Barcode[] */
    private array $barcodes;

    private mixed $additionalSkus = null;

    /** @var Attribute[] */
    private array $attributeValues;

    private mixed $bundleComponents = null;

    /** @var Client[] */
    private array $clients;

    private mixed $defaultCategories = null;

    /** @var SalesPrice[] */
    private array $salesPrices;

    private mixed $skus = null;

    private mixed $supplier = null;

    private mixed $warehouses = null;

    /** @var Property[] */
    private array $properties;

    /** @var Tag[] */
    private array $tags;

    private mixed $comments = null;

    private mixed $timestamps = null;

    private ?Unit $unit = null;

    /** @var Characteristic[] */
    private array $characteristics;

    /** @var Image[] */
    private array $images;

    private ?Base $base;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->categories = $this->getEntities(Category::class, 'categories', $data);
        $this->barcodes = $this->getEntities(Barcode::class, 'barcodes', $data);
        $this->attributeValues = $this->getEntities(Attribute::class, 'attributeValues', $data);
        $this->clients = $this->getEntities(Client::class, 'clients', $data);
        $this->salesPrices = $this->getEntities(SalesPrice::class, 'salesPrices', $data);
        $this->properties = $this->getEntities(Property::class, 'properties', $data);
        $this->tags = $this->getEntities(Tag::class, 'tags', $data);
        $this->images = $this->getEntities(Image::class, 'images', $data);
        $this->base = $this->getEntity(Base::class, $data['base']);
        if ($unit = $data['unit'] ?? null) {
            $this->unit = $this->getEntity(Unit::class, $unit);
        }
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'categories' => $this->categories,
            'barcodes' => $this->barcodes,
            'attributeValues' => $this->attributeValues,
            'clients' => $this->clients,
            'salesPrices' => $this->salesPrices,
            'properties' => $this->properties,
            'tags' => $this->tags,
            'images' => $this->images,
            'base' => $this->base,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @return Barcode[]
     */
    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributeValues(): array
    {
        return $this->attributeValues;
    }

    /**
     * @return Client[]
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * @return SalesPrice[]
     */
    public function getSalesPrices(): array
    {
        return $this->salesPrices;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return Image[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return Base
     */
    public function getBase(): Base
    {
        return $this->base;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function hasExportExclusionTag(string $lang): bool
    {
        foreach ($this->getTags() as $tag) {
            /** @var TagName[] $names */
            $names = Translator::translateMultiple($tag->getTagData()->getNames(), $lang);

            foreach ($names as $name) {
                if (strtolower($name->getName()) === self::EXCLUSION_TAG_NAME) {
                    return true;
                }
            }
        }

        return false;
    }

    public function __get($prop)
    {
        return $this->$prop;
    }

    public function __isset($prop): bool
    {
        return isset($this->$prop);
    }
}
