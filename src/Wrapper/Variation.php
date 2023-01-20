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
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category\CategoryDetails;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\AttributeValueName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Image as PimImage;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\TagName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit\Name as UnitName;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use PhpUnitsOfMeasure\Exception\NonNumericValue;
use PhpUnitsOfMeasure\Exception\NonStringUnitName;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use Psr\Cache\InvalidArgumentException;

class Variation
{
    use CharacteristicAware;
    use PropertyAware;

    protected Config $config;

    protected RegistryService $registryService;

    protected PimVariation $variationEntity;

    protected bool $isMain;

    protected ?int $position;

    protected ?int $vatId;

    protected string $number;

    protected ?string $model;

    protected int $id;

    protected int $itemId;

    /** @var string[] */
    protected array $barcodes = [];

    protected float $price = 0.0;

    protected float $insteadPrice = 0.0;

    protected array $prices = [];

    protected ?Image $image = null;

    protected float $vatRate = 0.0;

    protected ?string $baseUnit = null;

    protected ?string $packageSize = null;

    protected string $variationGroupKey;

    protected int $wrapMode;

    protected bool $hasCategories = false;

    /** @var Attribute[] */
    protected array $attributes = [];

    /** @var Property[] */
    protected array $properties = [];

    /** @var Usergroup[] */
    protected array $groups = [];

    /** @var Keyword[] */
    protected array $tags = [];

    private ?PropertySelectionResponse $propertySelection;

    public function __construct(
        Config $config,
        RegistryService $registryService,
        PimVariation $variationEntity,
        ?PropertySelectionResponse $propertySelection = null,
        int $wrapMode = Product::WRAP_MODE_DEFAULT,
        string $variationGroupKey = ''
    ) {
        $this->config = $config;
        $this->variationEntity = $variationEntity;
        $this->registryService = $registryService;
        $this->variationGroupKey = $variationGroupKey;
        $this->wrapMode = $wrapMode;
        $this->propertySelection = $propertySelection;
    }

    /**
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
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
        $this->processDimensions();
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getVatId(): ?int
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
        return $this->insteadPrice;
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

    public function hasCategories(): bool
    {
        return $this->hasCategories;
    }

    private function processIdentifiers(): void
    {
        $this->position = $this->variationEntity->getBase()->getPosition();
        $this->number = $this->variationEntity->getBase()->getNumber();
        $this->model = $this->variationEntity->getBase()->getModel();
        $this->id = $this->variationEntity->getId();
        $this->itemId = $this->variationEntity->getBase()->getItemId();

        foreach ($this->variationEntity->getBarcodes() as $barcode) {
            $this->barcodes[] = $barcode->getCode();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function processCategories(): void
    {
        $variationCategories = $this->variationEntity->getCategories();
        foreach ($variationCategories as $variationCategory) {
            $category = $this->registryService->getCategory($variationCategory->getId());

            if (!$category) {
                continue;
            }

            if (!$categoryDetail = $this->getCategoryDetailForCurrentPlentyIdAndLanguage($category)) {
                continue;
            }

            $categoryPath = $this->buildCategoryPath($category);

            if ($categoryPath === null) {
                continue;
            }

            $this->attributes[] = new Attribute('cat', [$categoryPath]);
            $this->attributes[] = new Attribute(
                'cat_url',
                [parse_url($categoryDetail->getPreviewUrl(), PHP_URL_PATH)]
            );
            $this->hasCategories = true;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function buildCategoryPath(Category $category): ?string
    {
        $path = [];

        if ($categoryDetail = $this->getCategoryDetailForCurrentPlentyIdAndLanguage($category)) {
            if ($category->getParentCategoryId() !== null) {
                $parentCategory = $this->registryService->getCategory($category->getParentCategoryId());

                if ($parentCategory === null) {
                    return null;
                }

                $path[] = $this->buildCategoryPath($parentCategory);
            }

            $path[] = $categoryDetail->getName();
        }

        return implode('_', $path);
    }

    /**
     * @throws InvalidArgumentException
     */
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

    /**
     * @throws InvalidArgumentException
     */
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

    /**
     * @throws InvalidArgumentException
     */
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

    /**
     * @throws InvalidArgumentException
     */
    private function processTags(): void
    {
        $tags = $this->variationEntity->getTags();
        $storeId = $this->registryService->getWebStore()->getStoreIdentifier();

        $tagIds = [];
        foreach ($tags as $tag) {
            if (!$this->shouldProcessTag($tag, $storeId)) {
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

    private function shouldProcessTag(Tag $tag, int $storeId): bool
    {
        if (!$clients = $tag->getTagData()->getClients()) {
            return false;
        }

        foreach ($clients as $client) {
            if ($client->getPlentyId() === $storeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function processImages(): void
    {
        $images = array_merge($this->variationEntity->getImages(), $this->variationEntity->getBase()->getImages());

        // Sort images by position.
        usort($images, fn(PimImage $a, PimImage $b) => $a->getPosition() <=> $b->getPosition());

        $variantImage = $this->getVariantImage($images);

        if ($variantImage) {
            $this->image = $variantImage;
        }
    }

    /**
     * @param PimImage[] $images
     * @return Image|null
     * @throws InvalidArgumentException
     */
    private function getVariantImage(array $images): ?Image
    {
        $defaultWrapModeImage = null;

        foreach ($images as $image) {
            $imageAvailabilities = $image->getAvailabilities();
            foreach ($imageAvailabilities as $imageAvailability) {
                if ($imageAvailability->getType() !== Availability::STORE) {
                    continue;
                }

                if (!$defaultWrapModeImage) {
                    $defaultWrapModeImage = $image;
                }

                $separatedVariation = new SeparatedVariation($this->variationEntity, $this->registryService);

                $variationAttributes = $separatedVariation->getVariationAttributes(
                    $this->variationGroupKey,
                    $this->wrapMode
                );
                if (!$separatedVariation->isImageAvailable($image->getAttributeValueImages(), $variationAttributes)) {
                    continue;
                }

                return new Image($image->getUrlMiddle());
            }
        }

        if (!$this->image && $defaultWrapModeImage) {
            return new Image($defaultWrapModeImage->getUrlMiddle());
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function processVatRate(): void
    {
        foreach ($this->registryService->getStandardVat()->getVatRates() as $vatRate) {
            if ($vatRate->getId() == $this->vatId) {
                $this->vatRate = $vatRate->getVatRate();

                return;
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function processUnits(): void
    {
        if (!$unitData = $this->variationEntity->getUnit()) {
            return;
        }

        $this->packageSize = $unitData->getContent();

        if ($unitEntity = $this->registryService->getUnit($unitData->getUnitId())) {
            /** @var UnitName|null $name */
            $name = Translator::translate($unitEntity->getNames(), $this->config->getLanguage());
            if ($name) {
                $this->baseUnit = $name->getName();
            }
        }
    }

    /**
     * @throws NonNumericValue
     * @throws NonStringUnitName
     */
    private function processDimensions(): void
    {
        $dimensionUnit = $this->config->getExportDimensionUnit();
        $weightUnit = $this->config->getExportWeightUnit();

        if (!Utils::isEmpty($this->variationEntity->getBase()->getLengthMM())) {
            $length = new Length($this->variationEntity->getBase()->getLengthMM(), 'mm');

            $this->attributes[] = new Attribute(
                'dimensions_length_' . $dimensionUnit,
                [$length->toUnit($dimensionUnit)]
            );
        }

        if (!Utils::isEmpty($this->variationEntity->getBase()->getWidthMM())) {
            $width = new Length($this->variationEntity->getBase()->getWidthMM(), 'mm');

            $this->attributes[] = new Attribute(
                'dimensions_width_' . $dimensionUnit,
                [$width->toUnit($dimensionUnit)]
            );
        }

        if (!Utils::isEmpty($this->variationEntity->getBase()->getHeightMM())) {
            $height = new Length($this->variationEntity->getBase()->getHeightMM(), 'mm');

            $this->attributes[] = new Attribute(
                'dimensions_height_' . $dimensionUnit,
                [$height->toUnit($dimensionUnit)]
            );
        }

        if (!Utils::isEmpty($this->variationEntity->getBase()->getWeightG())) {
            $weight = new Mass($this->variationEntity->getBase()->getWeightG(), 'g');

            $this->attributes[] = new Attribute(
                'dimensions_weight_' . $weightUnit,
                [$weight->toUnit($weightUnit)]
            );
        }

        if (!Utils::isEmpty($this->variationEntity->getBase()->getWeightNetG())) {
            $weight = new Mass($this->variationEntity->getBase()->getWeightNetG(), 'g');

            $this->attributes[] = new Attribute(
                'dimensions_weight_net_' . $weightUnit,
                [$weight->toUnit($weightUnit)]
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     */
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
}
