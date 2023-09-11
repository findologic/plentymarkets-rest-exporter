<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use DateTime;
use Exception;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Uri;
use Carbon\CarbonInterface;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\XML\XmlVariant;
use FINDOLOGIC\Export\Data\Ordernumber;
use Psr\Cache\InvalidArgumentException;
use FINDOLOGIC\Export\Helpers\DataHelper;
use FINDOLOGIC\Export\Data\OverriddenPrice;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use PhpUnitsOfMeasure\Exception\NonNumericValue;
use PhpUnitsOfMeasure\Exception\NonStringUnitName;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Manufacturer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as StoreConfiguration;

class Product
{
    public const WRAP_MODE_DEFAULT = 0;
    public const WRAP_MODE_SEPARATE_VARIATIONS = 1;

    private Item $item;

    private Config $config;

    private RegistryService $registryService;

    private ProductEntity $productEntity;

    /** @var PimVariation[] */
    private array $variationEntities;

    private ?string $reason = null;

    private StoreConfiguration $storeConfiguration;

    private Exporter $exporter;

    private int $wrapMode;

    private string $variationGroupKey;

    private ?int $cheapestVariationId = null;

    private array $plentyShopConfig;

    private ?PropertySelectionResponse $propertySelection;

    /** @var Text[] */
    private array $productTexts;

    /**
     * @param PimVariation[] $variationEntities
     * @throws InvalidArgumentException
     */
    public function __construct(
        Exporter $exporter,
        Config $config,
        StoreConfiguration $storeConfiguration,
        RegistryService $registryService,
        ?PropertySelectionResponse $propertySelection,
        ProductEntity $productEntity,
        array $variationEntities,
        int $wrapMode = self::WRAP_MODE_DEFAULT,
        string $variationGroupKey = ''
    ) {
        $this->exporter = $exporter;
        $this->item = $exporter->createItem((string) $productEntity->getId());
        $this->config = $config;
        $this->productEntity = $productEntity;
        $this->registryService = $registryService;
        $this->variationEntities = $variationEntities;
        $this->storeConfiguration = $storeConfiguration;
        $this->wrapMode = $wrapMode;
        $this->variationGroupKey = $variationGroupKey;
        $this->propertySelection = $propertySelection;
        $this->plentyShopConfig = $this->registryService->getPluginConfigurations('Ceres');
        $this->productTexts = Translator::translateMultiple(
            $this->productEntity->getTexts(),
            $this->config->getLanguage()
        );
    }

    /**
     * Returns a libflexport-consumable Item.
     * Returns null if
     *   * The data can not be parsed properly.
     *   * The products are not available for the current store.
     *   * Settings do not allow the product to be exported.
     *   * Product has no variants.
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @see Product::getReason() To get the reason why the product could not be exported.
     */
    public function processProductData(): ?Item
    {
        $this->item->addPrice(0);

        if (count($this->variationEntities) === 0) {
            $this->reason = 'Product has no variations.';

            return null;
        }

        $variationCount = $this->config->getUseVariants() ? $this->processXmlVariants() : $this->processVariations();
        if ($variationCount === 0 && $this->config->isExportUnavailableVariations()) {
            $this->item = $this->exporter->createItem((string) $this->productEntity->getId());
            $variationCount = $this->processVariations(false);
        }
        if ($variationCount === 0) {
            $this->reason =
                'All assigned variations are not exportable (inactive, no longer available, no categories etc.)';

            return null;
        }

        if ($this->wrapMode === self::WRAP_MODE_SEPARATE_VARIATIONS) {
            $itemId = $this->item->getId() . '_' . $this->variationEntities[0]->getId();
            $this->item->setId($itemId);
        }

        $this->setTexts();

        $this->item->addDateAdded(new DateTime($this->productEntity->getCreatedAt()));
        $this->addManufacturer();

        if ($this->config->getExportFreeTextFields()) {
            $this->addFreeTextFields();
        }

        $priceId = $this->registryService->getPriceId();
        $priceIdProperty = new Property('price_id');
        $priceIdProperty->addValue((string)$priceId);
        $this->item->addProperty($priceIdProperty);

        return $this->item;
    }

    /**
     * May return the reason why a product wasn't able to get exported.
     *
     * @codeCoverageIgnore
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    private function getDefaultProductName(): string
    {
        if (!$this->storeConfiguration->getDisplayItemName()) {
            return 'noName';
        }

        $textGetter = 'getName' . $this->storeConfiguration->getDisplayItemName();
        $text = $this->productTexts[array_key_first($this->productTexts)];
        return $text->$textGetter();
    }

    protected function getVariationUrl($variationId): ?string
    {
        if (empty($this->productTexts)) {
            return null;
        }

        return $this->getPlentyShopUrl(
            $this->productTexts[0]->getUrlPath(),
            $this->productEntity->getId() . '_' . $variationId
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function setTexts(): void
    {
        if (!$this->storeConfiguration->getDisplayItemName()) {
            return;
        }

        $textGetter = 'getName' . $this->storeConfiguration->getDisplayItemName();

        foreach ($this->productTexts as $text) {
            $name = $text->$textGetter();
            if (trim($name) !== '') {
                $this->item->addName($name);
            }

            if (trim($text->getShortDescription()) !== '') {
                $this->item->addSummary(preg_replace('/[\x00-\x1F\x7F]/u', '', $text->getShortDescription()));
            }
            if (trim($text->getDescription()) !== '') {
                $this->item->addDescription(preg_replace('/[\x00-\x1F\x7F]/u', '', $text->getDescription()));
            }
            if (trim($text->getKeywords()) !== '') {
                $this->item->addKeyword(new Keyword($text->getKeywords()));
            }

            $this->item->addUrl($this->getPlentyShopUrl($text->getUrlPath()));
        }
    }

    protected function processXmlVariants(bool $checkAvailability = true)
    {
        $hasCategories = false;
        $variationsProcessed = 0;
        $overriddenPrices = [];
        $ordernumbers = [];
        $highestPosition = 0;
        $baseUnit = null;
        $packageSize = null;
        $variationId = null;
        $cheapestVariations = new CheapestVariation($this->item);
        $variants = [];

        foreach ($this->variationEntities as $variationEntity) {
            if (!$this->shouldExportVariation($variationEntity, $checkAvailability)) {
                continue;
            }

            $variation = new Variation(
                $this->config,
                $this->registryService,
                $variationEntity,
                $this->propertySelection,
                $this->wrapMode,
                $this->variationGroupKey
            );

            $variation->processData();
            $variant = new XmlVariant((string)$variation->getId(), $this->item->getId());

            if ($variation->getVariationImages()) {
                $variant->setAllImages($variation->getVariationImages());
            }

            foreach ($variation->getGroups() as $group) {
                $variant->addGroup($group);
            }

            foreach ($variation->getTags() as $tag) {
                $this->item->addKeyword($tag);
            }

            if (!$packageSize) {
                $packageSize = $variation->getPackageSize();
            }

            if (!$baseUnit) {
                $baseUnit = $variation->getBaseUnit();
            }

            if (!$variationId) {
                $variationId = $variation->getId();
            }

            $position = $variation->getPosition();
            if ($variation->isMain() || !$this->item->getSort()->getValues()) {
                if ($variation->getPosition()) {
                    $this->item->addSort($variation->getPosition());
                }
            }
            $highestPosition = $position > $highestPosition ? $position : $highestPosition;

            foreach ($this->getVariationOrdernumbers($variation) as $ordernumber) {
                if (trim($ordernumber) !== '') {
                    $ordernumbers[] = $ordernumber;
                    $orderNumber = new Ordernumber($ordernumber);
                    $variant->addOrdernumber($orderNumber);
                }
            }

            foreach ($variation->getAttributes() as $attribute) {
                $variant->addMergedAttribute($attribute);
            }

            $variantUrl = $this->getVariationUrl($variationEntity->getId());
            if (!empty($variantUrl)) {
                $variant->addUrl($variantUrl);
            }
            $variant->addPrice($variation->getPrice());
            $variant->setAllOverriddenPrices($variant->getOverriddenPrice()->getValues());
            $overriddenPrices[] = $variation->getOverriddenPrice();
            if ($variation->hasCategories()) {
                $hasCategories = true;
            }

            $name = new Name();
            $variationName = $variationEntity->getBase()->getName();

            if ($variationName) {
                $name->setValue($variationName);
            } else {
                $name->setValue($this->getDefaultProductName());
            }

            $variant->setName($name);

            if ($variation->getPrice() !== 0.0) {
                $cheapestVariations->addVariation($variation);
            }

            if (!$hasCategories) {
                return 0;
            }

            if ($overriddenPrices) {
                $overriddenPrice = new OverriddenPrice();
                $overriddenPrice->setValue(min($overriddenPrices));
                $variant->setOverriddenPrice($overriddenPrice);
            }

            $salesFrequency = $this->storeConfiguration->getItemSortByMonthlySales() ? $highestPosition : 0;
            $this->item->addSalesFrequency($salesFrequency);

            if ($baseUnit) {
                $baseUnitProperty = new Property('base_unit');
                $baseUnitProperty->addValue($baseUnit);
                $variant->addProperty($baseUnitProperty);
            }

            if ($packageSize) {
                $packageSizeProperty = new Property('package_size');
                $packageSizeProperty->addValue($packageSize);
                $variant->addProperty($packageSizeProperty);
            }

            $variants[(string)$variation->getId()] = $variant;
            $variationsProcessed++;
        }

        $cheapestVariation = $cheapestVariations->getCheapestVariation();
        if ($cheapestVariation) {
            $this->cheapestVariationId = (int)$cheapestVariation[CheapestVariation::VARIATION_ID];
            $cheapestVariationImages = $cheapestVariation[CheapestVariation::VARIATION_IMAGES];

            if (!empty($cheapestVariationImages)) {
                $this->item->setAllImages($cheapestVariationImages);
            } elseif ($allImages = $cheapestVariation[CheapestVariation::IMAGES]) {
                $this->item->addImage($allImages[0]);
            }

            $this->item->addPrice($cheapestVariation[CheapestVariation::PRICE]);
            $variant = $variants[(string)$cheapestVariation[CheapestVariation::VARIATION_ID]];
            $this->item->setOverriddenPrice($variant->getOverriddenPrice());
            foreach ($variants as $variant) {
                $variant->setParentId((string)$this->productEntity->getId());
            }
            $variationIdProperty = new Property('variation_id');
            $variationIdProperty->addValue((string)$cheapestVariation[CheapestVariation::VARIATION_ID]);
            $this->item->addProperty($variationIdProperty);
        }

        $ordernumbers = array_unique($ordernumbers);
        foreach ($ordernumbers as $ordernumber) {
            $this->addOrdernumber($ordernumber);
        }

        $this->item->setAllVariants(array_values($variants));
        return $variationsProcessed;
    }

    /**
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws InvalidArgumentException
     */
    protected function processVariations(bool $checkAvailability = true): int
    {
        $itemHasImage = false;
        $hasCategories = false;
        $variationsProcessed = 0;
        $prices = [];
        $overriddenPrices = [];
        $ordernumbers = [];
        $highestPosition = 0;
        $baseUnit = null;
        $packageSize = null;
        $variationId = null;
        $cheapestVariations = new CheapestVariation($this->item);
        $defaultImage = null;

        foreach ($this->variationEntities as $variationEntity) {
            if (!$this->shouldExportVariation($variationEntity, $checkAvailability)) {
                continue;
            }

            $variation = new Variation(
                $this->config,
                $this->registryService,
                $variationEntity,
                $this->propertySelection,
                $this->wrapMode,
                $this->variationGroupKey
            );

            $variation->processData();
            if (!$defaultImage && $variation->getImage()) {
                $defaultImage = $variation->getImage();
            }

            if ($variation->getPrice() !== 0.0) {
                $cheapestVariations->addVariation($variation);
            }

            foreach ($variation->getGroups() as $group) {
                $this->item->addGroup($group);
            }

            foreach ($variation->getTags() as $tag) {
                $this->item->addKeyword($tag);
            }

            if (!$packageSize) {
                $packageSize = $variation->getPackageSize();
            }

            if (!$baseUnit) {
                $baseUnit = $variation->getBaseUnit();
            }

            if (!$variationId) {
                $variationId = $variation->getId();
            }

            $position = $variation->getPosition();
            if ($variation->isMain() || !$this->item->getSort()->getValues()) {
                // Only add sort in case the variation has a position.
                if ($variation->getPosition()) {
                    $this->item->addSort($variation->getPosition());
                }
            }
            $highestPosition = $position > $highestPosition ? $position : $highestPosition;

            $ordernumbers = array_merge($ordernumbers, $this->getVariationOrdernumbers($variation));

            foreach ($variation->getAttributes() as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }

            $prices[] = $variation->getPrice();
            $overriddenPrices[] = $variation->getOverriddenPrice();

            if ($variation->hasCategories()) {
                $hasCategories = true;
            }

            $variationsProcessed++;
        }

        $this->cheapestVariationId = $cheapestVariations->addImageAndPrice(
            $defaultImage,
            $prices,
            $itemHasImage
        );

        // If no children have categories, we're skipping this product.
        if (!$hasCategories) {
            return 0;
        }

        if ($overriddenPrices) {
            $overriddenPrice = new OverriddenPrice();
            $overriddenPrice->setValue(min($overriddenPrices));
            $this->item->setOverriddenPrice($overriddenPrice);
        }

        $ordernumbers = array_unique($ordernumbers);
        foreach ($ordernumbers as $ordernumber) {
            $this->addOrdernumber($ordernumber);
        }

        $salesFrequency = $this->storeConfiguration->getItemSortByMonthlySales() ? $highestPosition : 0;
        $this->item->addSalesFrequency($salesFrequency);

        if ($baseUnit) {
            $baseUnitProperty = new Property('base_unit');
            $baseUnitProperty->addValue($baseUnit);
            $this->item->addProperty($baseUnitProperty);
        }

        if ($packageSize) {
            $packageSizeProperty = new Property('package_size');
            $packageSizeProperty->addValue($packageSize);
            $this->item->addProperty($packageSizeProperty);
        }

        $variationId = $this->cheapestVariationId ?? $variationId;
        if ($variationId) {
            $variationIdProperty = new Property('variation_id');
            $variationIdProperty->addValue((string)$variationId);
            $this->item->addProperty($variationIdProperty);
        }

        return $variationsProcessed;
    }

    protected function shouldExportVariation(PimVariation $variation, bool $checkAvailability = true): bool
    {
        if (!$variation->getBase()->isActive()) {
            return false;
        }

        if ($variation->getBase()->getAutomaticListVisibility() < 1) {
            return false;
        }

        /** @var CarbonInterface|null $availableUntil */
        $availableUntil = $variation->getBase()->getAvailableUntil();
        if ($availableUntil !== null && $availableUntil->lessThan(Carbon::now())) {
            return false;
        }

        if ($checkAvailability && $this->config->getAvailabilityId() !== null) {
            if ($variation->getBase()->getAvailability() === $this->config->getAvailabilityId()) {
                return false;
            }
        }

        if ($variation->hasExportExclusionTag($this->config->getLanguage())) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function addManufacturer(): void
    {
        $manufacturerId = $this->productEntity->getManufacturerId();
        if (!Utils::isEmpty($manufacturerId)) {
            $manufacturer = $this->registryService->getManufacturer($manufacturerId);

            if (!$this->hasManufacturerNameSet($manufacturer)) {
                return;
            }

            $manufacturerName = $manufacturer->getExternalName() ?: $manufacturer->getName();

            $vendorAttribute = new Attribute('vendor', [$manufacturerName]);
            $this->item->addMergedAttribute($vendorAttribute);
        }
    }

    private function hasManufacturerNameSet(Manufacturer $manufacturer): bool
    {
        return !(Utils::isEmpty($manufacturer->getExternalName()) && Utils::isEmpty($manufacturer->getName()));
    }

    protected function addFreeTextFields(): void
    {
        foreach (range(1, 20) as $field) {
            $fieldName = 'free' . $field;
            $getter = 'getFree' . $field;

            $value = (string)$this->productEntity->{$getter}();
            if (trim($value) === '' || mb_strlen($value) > DataHelper::ATTRIBUTE_CHARACTER_LIMIT) {
                continue;
            }

            $this->item->addMergedAttribute(new Attribute($fieldName, [$value]));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getPlentyShopUrl(string $urlPath, $itemAndVariationId = null): string
    {
        $productUrl = sprintf(
            '%s://%s%s/%s_%s',
            $this->config->getProtocol(),
            $this->getWebStoreHost(),
            $this->getLanguageUrlPrefix(),
            trim($urlPath, '/'),
            $itemAndVariationId ?: $this->productEntity->getId()
        );

        if ($this->registryService->getPlentyShop()->getItemShowPleaseSelect() || isset($itemAndVariationId)) {
            return $productUrl;
        }

        $cheapestVariationId = ($this->cheapestVariationId !== null) ?
            $this->cheapestVariationId : $this->productEntity->getMainVariationId();

        $variationId = $this->wrapMode ?
            $this->variationEntities[0]->getId() : $cheapestVariationId;

        return sprintf($productUrl . '_%s', $variationId);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getWebStoreHost(): string
    {
        $rawUri = $this->registryService->getWebStore()->getConfiguration()->getDomainSsl() ?? '';
        $uri = new Uri($rawUri);

        return $uri->getHost();
    }

    /**
     * Returns the language URL prefix. This may be relevant for multiple channels.
     * An empty string may be returned if the default store language is already the exported language.
     */
    private function getLanguageUrlPrefix(): ?string
    {
        if ($this->isDefaultLanguage() || !$this->isLanguageAvailable()) {
            return '';
        }

        return '/' . strtolower($this->config->getLanguage());
    }

    /**
     * Returns true if the language of the webStore is the default language. Otherwise, false may be returned.
     *
     * @return bool
     */
    private function isDefaultLanguage(): bool
    {
        $defaultStoreLanguage = $this->storeConfiguration->getDefaultLanguage();

        return strtoupper($defaultStoreLanguage) === strtoupper($this->config->getLanguage());
    }

    private function isLanguageAvailable(): bool
    {
        $availableLanguages = array_map('strtoupper', $this->storeConfiguration->getLanguageList());

        return (in_array(strtoupper($this->config->getLanguage()), $availableLanguages));
    }

    private function getVariationOrdernumbers(Variation $variation): array
    {
        $ordernumbers = [];

        if ($this->config->getExportOrdernumberVariantNumber()) {
            $ordernumbers[] = $variation->getNumber();
        }

        if ($this->config->getExportOrdernumberVariantModel()) {
            $ordernumbers[] = (string)$variation->getModel();
        }

        if ($this->config->getExportOrdernumberVariantId()) {
            $ordernumbers[] = (string)$variation->getId();
        }

        if ($this->config->getExportOrdernumberProductId()) {
            $ordernumbers[] = (string)$variation->getItemId();
        }

        if ($this->config->getExportOrdernumberVariantBarcodes()) {
            foreach ($variation->getBarcodes() as $barcode) {
                $ordernumbers[] = $barcode;
            }
        }

        return $ordernumbers;
    }

    private function addOrdernumber(string $ordernumber): void
    {
        if (trim($ordernumber) === '') {
            return;
        }

        $this->item->addOrdernumber(new Ordernumber($ordernumber));
    }
}
