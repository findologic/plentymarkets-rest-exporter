<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\Export\Data\Usergroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

class Variation
{
    use CharacteristicAware;
    use PropertyAware;

    /** @var Config */
    protected $config;

    /** @var RegistryService */
    protected $registryService;

    /** @var PimVariation */
    protected $variationEntity;

    /** @var bool */
    protected $isMain;

    /** @var int */
    protected $position;

    /** @var int */
    protected $vatId;

    /** @var string */
    protected $number;

    /** @var string|null */
    protected $model;

    /** @var int */
    protected $id;

    /** @var int */
    protected $itemId;

    /** @var string[] */
    protected $barcodes = [];

    /** @var float */
    protected $price = 0.0;

    /** @var float */
    protected $insteadPrice = 0.0;

    /** @var array */
    protected $prices = [];

    /** @var Attribute[] */
    protected $attributes = [];

    /** @var Property[] */
    protected $properties = [];

    /** @var Usergroup[] */
    protected $groups = [];

    /** @var Keyword[] */
    protected $tags = [];

    /** @var Image */
    protected $image;

    /** @var float */
    protected $vatRate;

    /** @var string|null */
    protected $baseUnit;

    /** @var string|null */
    protected $packageSize;

    public function __construct(
        Config $config,
        RegistryService $registryService,
        PimVariation $variationEntity
    ) {
        $this->config = $config;
        $this->variationEntity = $variationEntity;
        $this->registryService = $registryService;
    }

    public function processData(): void
    {
        $this->isMain = $this->variationEntity->getBase()->isMain();
        $this->position = $this->variationEntity->getBase()->getPosition();
        $this->vatId = $this->variationEntity->getBase()->getVatId();

        $this->processIdentifiers();
        $this->processCategories();
        $this->processPrices();
        $this->processAttributes();
        $this->processGroups();
        $this->processTags();
        $this->processImages();
        $this->processCharacteristics();
        $this->processProperties();
        $this->processVatRate();
        $this->processUnits();
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getVatId(): int
    {
        return $this->vatId;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @return string[]
     */
    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getInsteadPrice(): float
    {
        return (float)$this->insteadPrice;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return Usergroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return Keyword[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function getVatRate(): ?float
    {
        return $this->vatRate;
    }

    public function getBaseUnit(): ?string
    {
        return $this->baseUnit;
    }

    public function getPackageSize(): ?string
    {
        return $this->packageSize;
    }

    private function processIdentifiers(): void
    {
        $this->number = $this->variationEntity->getBase()->getNumber();
        $this->model = $this->variationEntity->getBase()->getModel();
        $this->id = $this->variationEntity->getId();
        $this->itemId = $this->variationEntity->getBase()->getItemId();

        foreach ($this->variationEntity->getBarcodes() as $barcode) {
            $this->barcodes[] = $barcode->getCode();
        }
    }

    private function processCategories(): void
    {
        $variationCategories = $this->variationEntity->getCategories();
        foreach ($variationCategories as $variationCategory) {
            $category = $this->registryService->getCategory($variationCategory->getId());

            if (!$category) {
                continue;
            }

            foreach ($category->getDetails() as $categoryDetail) {
                if (strtoupper($categoryDetail->getLang()) !== strtoupper($this->config->getLanguage())) {
                    continue;
                }

                $this->attributes[] = new Attribute('cat', [$this->buildCategoryPath($category)]);
                $this->attributes[] = new Attribute(
                    'cat_url',
                    [parse_url($categoryDetail->getPreviewUrl(), PHP_URL_PATH)]
                );
            }
        }
    }

    private function buildCategoryPath(Category $category): string
    {
        $path = [];
        foreach ($category->getDetails() as $categoryDetail) {
            if (strtoupper($categoryDetail->getLang()) !== strtoupper($this->config->getLanguage())) {
                continue;
            }

            if ($category->getParentCategoryId() !== null) {
                $path[] = $this->buildCategoryPath(
                    $this->registryService->getCategory($category->getParentCategoryId())
                );
            }

            $path[] = $categoryDetail->getName();
        }

        return implode('_', $path);
    }

    private function processPrices(): void
    {
        $insteadPriceId = $this->registryService->getRrpId();

        $priceId = $this->registryService->getPriceId();

        foreach ($this->variationEntity->getSalesPrices() as $variationSalesPrice) {
            $price = $variationSalesPrice->getPrice();
            if ($variationSalesPrice->getPrice() == 0) {
                continue;
            }

            if ($variationSalesPrice->getId() === $priceId) {
                // Always take the lowest price.
                if ($price < $this->price || $this->price === 0.0) {
                    $this->price = $price;
                }
            }

            if ($variationSalesPrice->getId() === $insteadPriceId) {
                $this->insteadPrice = $price;
            }
        }
    }

    private function processAttributes(): void
    {
        foreach ($this->variationEntity->getAttributeValues() as $variationAttributeValue) {
            $attribute = $this->registryService->getAttribute($variationAttributeValue->getId());
            if (!$attribute) {
                continue;
            }

            $emptyName = Utils::isEmpty($attribute->getBackendName());
            if ($emptyName || Utils::isEmpty($variationAttributeValue->getValue()->getBackendName())) {
                continue;
            }

            $this->attributes[] = new Attribute(
                $attribute->getBackendName(),
                [$variationAttributeValue->getValue()->getBackendName()]
            );
        }
    }

    private function processGroups(): void
    {
        $stores = $this->registryService->getAllWebStores();
        $variationClients = $this->variationEntity->getClients();
        foreach ($variationClients as $variationClient) {
            if ($store = $stores->getWebStoreByStoreIdentifier($variationClient->getPlentyId())) {
                $this->groups[] = new Usergroup($store->getId() . '_');
            }
        }
    }

    private function processTags(): void
    {
        $tags = $this->variationEntity->getTags();
        $storeId = $this->registryService->getWebStore()->getStoreIdentifier();

        $tagIds = [];
        foreach ($tags as $tag) {
            if (!$this->canProcessTag($tag, $storeId)) {
                continue;
            }

            $tagIds[] = $tag->getId();

            $tagName = $tag->getTagData()->getName();

            foreach ($tag->getTagData()->getNames() as $translatedTag) {
                if ($translatedTag->getLang() === strtolower($this->config->getLanguage())) {
                    $tagName = $translatedTag->getName();

                    break;
                }
            }

            $this->tags[] = new Keyword($tagName);
        }

        if ($tagIds) {
            $this->attributes[] = new Attribute('cat_id', $tagIds);
        }
    }

    private function canProcessTag(Tag $tag, int $storeId): bool
    {
        if (!$clients = $tag->getTagData()->getClients()) {
            return false;
        };

        foreach ($clients as $client) {
            if ($client->getPlentyId() === $storeId) {
                return true;
            }
        }

        return false;
    }

    private function processImages(): void
    {
        $images = array_merge($this->variationEntity->getImages(), $this->variationEntity->getBase()->getImages());

        /** @var ItemImage $image */
        foreach ($images as $image) {
            $imageAvailabilities = $image->getAvailabilities();
            foreach ($imageAvailabilities as $imageAvailability) {
                if ($imageAvailability->getType() === Availability::STORE) {
                    $this->image = new Image($image->getUrlMiddle());

                    return;
                }
            }
        }
    }

    private function processVatRate(): void
    {
        foreach ($this->registryService->getStandardVat()->getVatRates() as $vatRate) {
            if ($vatRate->getId() == $this->vatId) {
                $this->vatRate = $vatRate->getVatRate();

                return;
            }
        }
    }

    private function processUnits(): void
    {
        if (!$unitData = $this->variationEntity->getUnit()) {
            return;
        }

        $this->packageSize = $unitData->getContent();

        if ($unitEntity = $this->registryService->getUnit($unitData->getUnitId())) {
            foreach ($unitEntity->getNames() as $name) {
                if (strtolower($name->getLang()) === (strtolower($this->config->getLanguage()))) {
                    $this->baseUnit = $name->getName();
                }
            }
        }
    }
}
