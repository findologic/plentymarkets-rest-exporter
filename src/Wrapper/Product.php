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
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Property;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as StoreConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\CategoryAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\CharacteristicsAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\FreeTextFieldAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\ManufacturerAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\PropertyAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\VariationAttributesAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\ClientGroupAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\IdentifierOrdernumberAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\ImageAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\PackageSizePropertyAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\PriceAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\TagAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\UnitPropertyAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\VariationIdPropertyAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\VatRateTaxRateAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\InsteadPrice;
use GuzzleHttp\Psr7\Uri;

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

    private CategoryAttributeAdapter $categoryAttributeAdapter;
    private FreeTextFieldAttributeAdapter $freeTextFieldAttributeAdapter;
    private ManufacturerAttributeAdapter $manufacturerAttributeAdapter;
    private VariationAttributesAttributeAdapter $variationAttributesAttributeAdapter;
    private CharacteristicsAttributeAdapter $characteristicsAttributeAdapter;
    private PropertyAttributeAdapter $propertyAttributeAdapter;
    private TagAdapter $tagAdapter;
    private IdentifierOrdernumberAdapter $identifierOrdernumberAdapter;
    private ImageAdapter $imageAdapter;
    private PriceAdapter $priceAdapter;
    private ClientGroupAdapter $clientGroupAdapter;
    private VatRateTaxRateAdapter $vatRateTaxRateAdapter;
    private UnitPropertyAdapter $unitPropertyAdapter;
    private PackageSizePropertyAdapter $packageSizePropertyAdapter;
    private VariationIdPropertyAdapter $variationIdPropertyAdapter;

    /**
     * @param PimVariation[] $variationEntities
     */
    public function __construct(
        Exporter $exporter,
        Config $config,
        StoreConfiguration $storeConfiguration,
        RegistryService $registryService,
        ProductEntity $productEntity,
        array $variationEntities,
        int $wrapMode = self::WRAP_MODE_DEFAULT
    ) {
        $this->exporter = $exporter;
        $this->item = $exporter->createItem($productEntity->getId());
        $this->config = $config;
        $this->productEntity = $productEntity;
        $this->registryService = $registryService;
        $this->variationEntities = $variationEntities;
        $this->storeConfiguration = $storeConfiguration;
        $this->categoryAttributeAdapter = new CategoryAttributeAdapter($config, $registryService);
        $this->freeTextFieldAttributeAdapter = new FreeTextFieldAttributeAdapter($config, $registryService);
        $this->manufacturerAttributeAdapter = new ManufacturerAttributeAdapter($config, $registryService);
        $this->variationAttributesAttributeAdapter = new VariationAttributesAttributeAdapter($config, $registryService);
        $this->characteristicsAttributeAdapter = new CharacteristicsAttributeAdapter($config, $registryService);
        $this->propertyAttributeAdapter = new PropertyAttributeAdapter($config, $registryService);
        $this->tagAdapter = new TagAdapter($config, $registryService);
        $this->identifierOrdernumberAdapter = new IdentifierOrdernumberAdapter($config, $registryService);
        $this->imageAdapter = new ImageAdapter($config, $registryService);
        $this->priceAdapter = new PriceAdapter($config, $registryService);
        $this->clientGroupAdapter = new ClientGroupAdapter($config, $registryService);
        $this->vatRateTaxRateAdapter = new VatRateTaxRateAdapter($config, $registryService);
        $this->unitPropertyAdapter = new UnitPropertyAdapter($config, $registryService);
        $this->packageSizePropertyAdapter = new PackageSizePropertyAdapter($config, $registryService);
        $this->variationIdPropertyAdapter = new VariationIdPropertyAdapter($config, $registryService);
        $this->wrapMode = $wrapMode;
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
            $variationCount = $this->processVariations(false);
        }
        if ($variationCount === 0) {
            $this->reason =
                'All assigned variations are not exportable (inactive, no longer available, no categories etc.)';

            return null;
        }

        $this->setTexts();

        $this->item->addDateAdded(new DateTime($this->productEntity->getCreatedAt()));
        $this->addManufacturer();
        $this->addFreeTextFields();

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
        $hasImage = false;
        $hasCategories = false;
        $variationsProcessed = 0;
        /** @var Price[] $prices */
        $prices = [];
        /** @var InsteadPrice[] $insteadPrices */
        $insteadPrices = [];
        /** @var Ordernumber[] $ordernumbers */
        $ordernumbers = [];
        $highestPosition = 0;
        /** @var Data\Property|null $baseUnit */
        $baseUnit = null;
        /** @var Data\Property|null $packageSize */
        $packageSize = null;
        /** @var Data\Property|null $variationIdProperty */
        $variationIdProperty = null;

        foreach ($this->variationEntities as $variationEntity) {
            if (!$this->shouldExportVariation($variationEntity, $checkAvailability)) {
                continue;
            }

            $categories = $this->categoryAttributeAdapter->adaptVariation($variationEntity);
            foreach ($categories as $category) {
                $hasCategories = true;
                $this->item->addMergedAttribute($category);
            }

            $variationAttributes = $this->variationAttributesAttributeAdapter->adaptVariation($variationEntity);
            foreach ($variationAttributes as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }

            $tagData = $this->tagAdapter->adaptVariation($variationEntity);
            foreach ($tagData as $serializedField) {
                switch (true) {
                    case $serializedField instanceof Attribute:
                        $this->item->addMergedAttribute($serializedField);
                        break;
                    case $serializedField instanceof Keyword:
                        $this->item->addKeyword($serializedField);
                        break;
                    default:
                        break;
                }
            }

            $variation = new Variation($this->config, $this->registryService, $variationEntity);
            $variation->process();

            if (!$hasImage && $image = $this->imageAdapter->adaptVariation($variationEntity)) {
                $this->item->addImage($image);
                $hasImage = true;
            }

            foreach ($this->clientGroupAdapter->adaptVariation($variationEntity) as $group) {
                $this->item->addUsergroup($group);
            }

//            foreach ($variation->getTags() as $tag) {
//                $this->item->addKeyword($tag);
//            }

            if (!$packageSize) {
                $packageSize = $this->packageSizePropertyAdapter->adaptVariation($variationEntity);
            }

            if (!$baseUnit) {
                $baseUnit = $this->unitPropertyAdapter->adaptVariation($variationEntity);
            }

            if (!$variationIdProperty || $variationEntity->getBase()->isMain()) {
                $variationIdProperty = $this->variationIdPropertyAdapter->adaptVariation($variationEntity);
            }

            $position = $variationEntity->getBase()->getPosition();
            if ($variationEntity->getBase()->isMain() || !$this->item->getSort()->getValues()) {
                // Only add sort in case the variation has a position.
                if ($variationEntity->getBase()->getPosition()) {
                    $this->item->addSort($variationEntity->getBase()->getPosition());
                }
            }
            $highestPosition = $position > $highestPosition ? $position : $highestPosition;

            $ordernumbers = array_merge(
                $ordernumbers,
                $this->identifierOrdernumberAdapter->adaptVariation($variationEntity)
            );

            $characteristicAttributes = $this->characteristicsAttributeAdapter->adaptVariation($variationEntity);
            foreach ($characteristicAttributes as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }
            $propertyAttributes = $this->propertyAttributeAdapter->adaptVariation($variationEntity);
            foreach ($propertyAttributes as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }
//            foreach ($variation->getAttributes() as $attribute) {
//                $this->item->addMergedAttribute($attribute);
//            }

            $allPrices = $this->priceAdapter->adaptVariation($variationEntity);
            foreach ($allPrices as $exportPrice) {
                if ($exportPrice instanceof Price) {
                    $prices[] = $exportPrice;
                }
                if ($exportPrice instanceof InsteadPrice) {
                    $insteadPrices[] = $exportPrice;
                }
            }

//            if ($variation->hasCategories()) {
//                $hasCategories = true;
//            }

            $variationsProcessed++;
        }

        // If no children have categories, we're skipping this product.
        if (!$hasCategories) {
            return 0;
        }

        // VatRate should be set from the last variation, therefore this code outside the foreach loop
        if (isset($variationEntity)) {
            $taxRate = $this->vatRateTaxRateAdapter->adaptVariation($variationEntity);
            if ($taxRate) {
                $this->item->setTaxRate($taxRate->getSimpleValue());
            }
        }

        if ($prices) {
            $this->item->addPrice(Utils::getLowestValue($prices));
        }

        if ($insteadPrices) {
            $this->item->setInsteadPrice(Utils::getLowestValue($insteadPrices));
        }

        $ordernumbers = array_unique($ordernumbers, SORT_REGULAR);
        $this->item->setAllOrdernumbers($ordernumbers);

        $salesFrequency = $this->storeConfiguration->getItemSortByMonthlySales() ? $highestPosition : 0;
        $this->item->addSalesFrequency($salesFrequency);

        if ($baseUnit) {
            $this->item->addProperty($baseUnit);
        }

        if ($packageSize) {
            $this->item->addProperty($packageSize);
        }

        if ($variationIdProperty) {
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
        if ($manufacturerAttribute = $this->manufacturerAttributeAdapter->adapt($this->productEntity)) {
            $this->item->addMergedAttribute($manufacturerAttribute);
        }
    }

    protected function addFreeTextFields(): void
    {
        foreach ($this->freeTextFieldAttributeAdapter->adapt($this->productEntity) as $attribute) {
            $this->item->addMergedAttribute($attribute);
        }
    }

    private function buildProductUrl(string $urlPath): string
    {
        if ($this->shouldUseCallistoUrl()) {
            return sprintf(
                '%s://%s%s/%s/a-%s',
                $this->config->getProtocol(),
                $this->getWebStoreHost(),
                $this->getLanguageUrlPrefix(),
                trim($urlPath, '/'),
                $this->productEntity->getId()
            );
        }

        return sprintf(
            '%s://%s%s/%s_%s_%s',
            $this->config->getProtocol(),
            $this->getWebStoreHost(),
            $this->getLanguageUrlPrefix(),
            trim($urlPath, '/'),
            $this->productEntity->getId(),
            $this->wrapMode ? $this->variationEntities[0]->getId() : $this->productEntity->getMainVariationId()
        );
    }

    private function shouldUseCallistoUrl(): bool
    {
        $config = $this->registryService->getPluginConfigurations('Ceres');

        if (!isset($config['global.enableOldUrlPattern'])) {
            return true;
        }

        return filter_var($config['global.enableOldUrlPattern'], FILTER_VALIDATE_BOOLEAN);
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
}
