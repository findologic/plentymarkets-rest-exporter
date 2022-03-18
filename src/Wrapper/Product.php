<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Manufacturer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as StoreConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Psr7\Uri;

class Product
{
    public const WRAP_MODE_DEFAULT = 0;

    public const WRAP_MODE_SEPARATE_VARIATIONS = 1;

    /** @var Item */
    private $item;

    /** @var Config */
    private $config;

    /** @var RegistryService */
    private $registryService;

    /** @var ProductEntity */
    private $productEntity;

    /** @var PimVariation[] */
    private $variationEntities;

    /** @var string|null */
    private $reason = null;

    /** @var StoreConfiguration  */
    private $storeConfiguration;

    /** @var Exporter */
    private $exporter;

    private int $wrapMode;

    private string $variationGroupKey;

    private ?int $cheapestVariationId = null;

    private array $plentyShopConfig;

    private ?PropertySelectionResponse $propertySelection;

    /**
     * @param PimVariation[] $variationEntities
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
        $this->item = $exporter->createItem($productEntity->getId());
        $this->config = $config;
        $this->productEntity = $productEntity;
        $this->registryService = $registryService;
        $this->variationEntities = $variationEntities;
        $this->storeConfiguration = $storeConfiguration;
        $this->wrapMode = $wrapMode;
        $this->variationGroupKey = $variationGroupKey;
        $this->plentyShopConfig = $this->registryService->getPluginConfigurations('Ceres');
        $this->propertySelection = $propertySelection;
    }

    /**
     * Returns a libflexport-consumable Item.
     * Returns null if
     *   * The data can not be parsed properly.
     *   * The products are not available for the current store.
     *   * Settings do not allow the product to be exported.
     *   * Product has no variants.
     *
     * @see Product::getReason() To get the reason why the product could not be exported.
     *
     * @return Item|null
     */
    public function processProductData(): ?Item
    {
        $this->item->addPrice(0);

        if (count($this->variationEntities) === 0) {
            $this->reason = 'Product has no variations.';

            return null;
        }

        $variationCount = $this->processVariations();
        if ($variationCount === 0 && $this->config->isExportUnavailableVariations()) {
            $this->item = $this->exporter->createItem($this->productEntity->getId());
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

    protected function setTexts(): void
    {
        if (!$this->storeConfiguration->getDisplayItemName()) {
            return;
        }

        $textGetter = 'getName' . $this->storeConfiguration->getDisplayItemName();

        /** @var Text[] $texts */
        $texts = Translator::translateMultiple($this->productEntity->getTexts(), $this->config->getLanguage());
        foreach ($texts as $text) {
            $name = $text->$textGetter();
            if (trim($name) !== '') {
                $this->item->addName($name);
            }

            if (trim($text->getShortDescription()) !== '') {
                $this->item->addSummary($text->getShortDescription());
            }
            if (trim($text->getDescription()) !== '') {
                $this->item->addDescription($text->getDescription());
            }
            if (trim($text->getKeywords()) !== '') {
                $this->item->addKeyword(new Keyword($text->getKeywords()));
            }

            $this->item->addUrl($this->buildProductUrl($text->getUrlPath()));
        }
    }

    protected function processVariations(bool $checkAvailability = true): int
    {
        $itemHasImage = false;
        $hasCategories = false;
        $variationsProcessed = 0;
        $prices = [];
        $insteadPrices = [];
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

            if (!$itemHasImage && $variation->getImage() && $this->registryService->shouldUseLegacyCallistoUrl()) {
                $this->item->addImage($variation->getImage());
                $itemHasImage = true;
            }

            if (!$defaultImage && $variation->getImage()) {
                $defaultImage = $variation->getImage();
            }

            if (!$this->registryService->shouldUseLegacyCallistoUrl() && $variation->getPrice() !== 0.0) {
                $cheapestVariations->addVariation($variation);
            }

            foreach ($variation->getGroups() as $group) {
                $this->item->addUsergroup($group);
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
            $insteadPrices[] = $variation->getInsteadPrice();

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

        // VatRate should be set from the last variation, therefore this code outside the foreach loop
        if (isset($variation) && $variation->getVatRate() !== null) {
            $this->item->setTaxRate($variation->getVatRate());
        }

        if ($insteadPrices) {
            $this->item->setInsteadPrice(min($insteadPrices));
        }

        $ordernumbers = array_unique($ordernumbers);
        foreach ($ordernumbers as $ordernumber) {
            $this->addOrdernumber($ordernumber);
        }

        $salesFrequency = $this->storeConfiguration->getItemSortByMonthlySales() ? $highestPosition : 0;
        $this->item->addSalesFrequency($salesFrequency);

        if ($baseUnit) {
            $baseUnitProperty = new Property('base_unit');
            $baseUnitProperty->addValue((string)$baseUnit);
            $this->item->addProperty($baseUnitProperty);
        }

        if ($packageSize) {
            $packageSizeProperty = new Property('package_size');
            $packageSizeProperty->addValue((string)$packageSize);
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
            $fieldName = 'free' . (string)$field;
            $getter = 'getFree' . (string)$field;

            $value = (string)$this->productEntity->{$getter}();
            if (trim($value) === '') {
                continue;
            }

            $this->item->addMergedAttribute(new Attribute($fieldName, [$value]));
        }
    }

    private function buildProductUrl(string $urlPath): string
    {
        if ($this->registryService->shouldUseLegacyCallistoUrl()) {
            return $this->getCallistoUrl($urlPath);
        } else {
            return $this->getPlentyShopUrl($urlPath);
        }
    }

    private function getCallistoUrl(string $urlPath): string
    {
        return sprintf(
            '%s://%s%s/%s/a-%s',
            $this->config->getProtocol(),
            $this->getWebStoreHost(),
            $this->getLanguageUrlPrefix(),
            trim($urlPath, '/'),
            $this->productEntity->getId()
        );
    }

    private function getPlentyShopUrl(string $urlPath): string
    {
        $productUrl = sprintf(
            '%s://%s%s/%s_%s',
            $this->config->getProtocol(),
            $this->getWebStoreHost(),
            $this->getLanguageUrlPrefix(),
            trim($urlPath, '/'),
            $this->productEntity->getId(),
        );

        if (isset($this->plentyShopConfig['item.show_please_select'])) {
            if (Utils::filterBoolean($this->plentyShopConfig['item.show_please_select'])) {
                return $productUrl;
            }
        }

        $cheapestVariationId = ($this->cheapestVariationId !== null) ?
            $this->cheapestVariationId : $this->productEntity->getMainVariationId();

        $variationId = $this->wrapMode ?
            $this->variationEntities[0]->getId() : $cheapestVariationId;

        return sprintf($productUrl . '_%s', $variationId);
    }

    private function getWebStoreHost(): string
    {
        $rawUri = $this->registryService->getWebStore()->getConfiguration()->getDomainSsl() ?? '';
        $uri = new Uri($rawUri);

        return $uri->getHost();
    }

    /**
     * Returns the language URL prefix. This may be relevant for multiple channels.
     * An empty string may be returned if the default store language is already the exported language.
     *
     * @return string
     */
    private function getLanguageUrlPrefix(): ?string
    {
        if ($this->isDefaultLanguage() || !$this->isLanguageAvailable()) {
            return '';
        }

        return '/' . strtolower($this->config->getLanguage());
    }

    /**
     * Returns true if the language of the webStore is the default language. Otherwise false may be returned.
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
            $ordernumbers[] = (string)$variation->getNumber();
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

    private function addOrdernumber(string $ordernumber)
    {
        if (trim($ordernumber) === '') {
            return;
        }

        $this->item->addOrdernumber(new Ordernumber($ordernumber));
    }
}
