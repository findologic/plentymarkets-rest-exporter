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
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as StoreConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;

class Product
{
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

    /**
     * @param PimVariation[] $variationEntities
     */
    public function __construct(
        Exporter $exporter,
        Config $config,
        StoreConfiguration $storeConfiguration,
        RegistryService $registryService,
        ProductEntity $productEntity,
        array $variationEntities
    ) {
        $this->exporter = $exporter;
        $this->item = $exporter->createItem($productEntity->getId());
        $this->config = $config;
        $this->productEntity = $productEntity;
        $this->registryService = $registryService;
        $this->variationEntities = $variationEntities;
        $this->storeConfiguration = $storeConfiguration;
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
            $this->reason = sprintf('Product has no variations.');

            return null;
        }

        $variationCount = $this->processVariations();
        if ($variationCount === 0) {
            $this->reason = 'All assigned variations are not exportable (inactive, no longer available, etc.)';

            return null;
        }

        $this->setTexts();

        $this->item->addDateAdded(new DateTime($this->productEntity->getCreatedAt()));
        $this->addManufacturer();
        $this->addFreeTextFields();

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

        foreach ($this->productEntity->getTexts() as $texts) {
            if (strtoupper($texts->getLang()) !== strtoupper($this->config->getLanguage())) {
                continue;
            }

            $name = $texts->$textGetter();
            if (trim($name) !== '') {
                $this->item->addName($name);
            }

            if (trim($texts->getShortDescription()) !== '') {
                $this->item->addSummary($texts->getShortDescription());
            }
            if (trim($texts->getDescription()) !== '') {
                $this->item->addDescription($texts->getDescription());
            }
            if (trim($texts->getKeywords()) !== '') {
                $this->item->addKeyword(new Keyword($texts->getKeywords()));
            }

            $this->item->addUrl($this->buildProductUrl($texts->getUrlPath()));
        }
    }

    protected function processVariations(): int
    {
        $hasImage = false;
        $variationsProcessed = 0;
        $prices = [];
        $insteadPrices = [];
        $ordernumbers = [];
        $highestPosition = 0;

        foreach ($this->variationEntities as $variationEntity) {
            if (!$this->shouldExportVariation($variationEntity)) {
                continue;
            }

            $variation = new Variation($this->config, $this->registryService, $variationEntity);
            $variation->processData();

            if (!$hasImage && $variation->getImage()) {
                $this->item->addImage($variation->getImage());
                $hasImage = true;
            }

            foreach ($variation->getGroups() as $group) {
                $this->item->addUsergroup($group);
            }

            foreach ($variation->getTags() as $tag) {
                $this->item->addKeyword($tag);
            }

            $position = $variation->getPosition();
            if ($variation->isMain() || !$this->item->getSort()->getValues()) {
                $this->item->addSort($variation->getPosition());
            }
            $highestPosition = $position > $highestPosition ? $position : $highestPosition;

            $ordernumbers = array_merge($ordernumbers, $this->getVariationOrdernumbers($variation));

            foreach ($variation->getAttributes() as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }

            $prices[] = $variation->getPrice();
            $insteadPrices[] = $variation->getInsteadPrice();

            $variationsProcessed++;
        }

        // VatRate should be set from the last variation, therefore this code outside the foreach loop
        if (isset($variation) && $variation->getVatRate() !== null) {
            $this->item->setTaxRate($variation->getVatRate());
        }

        if ($prices) {
            $this->item->addPrice(min($prices));
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

        return $variationsProcessed;
    }

    protected function shouldExportVariation(PimVariation $variation): bool
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

        if ($this->config->getAvailabilityId() !== null) {
            if ($variation->getBase()->getAvailability() === $this->config->getAvailabilityId()) {
                return false;
            }
        }

        return true;
    }

    protected function addManufacturer(): void
    {
        $manufacturerId = $this->productEntity->getManufacturerId();
        if (!Utils::isEmpty($manufacturerId)) {
            $manufacturer = $this->registryService->getManufacturer($manufacturerId);

            if (Utils::isEmpty($manufacturer->getName())) {
                return;
            }

            $vendorAttribute = new Attribute('vendor', [$manufacturer->getName()]);
            $this->item->addMergedAttribute($vendorAttribute);
        }
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
        return sprintf(
            '%s://%s%s/%s_%s_%s',
            $this->config->getProtocol(),
            $this->config->getDomain(),
            $this->getLanguageUrlPrefix(),
            trim($urlPath, '/'),
            $this->productEntity->getId(),
            $this->productEntity->getMainVariationId()
        );
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
        $ordernumberGetters = ['number', 'model', 'id', 'itemId'];
        $ordernumbers = [];

        foreach ($ordernumberGetters as $field) {
            $getter = 'get' . ucfirst($field);
            $ordernumbers[] = (string)$variation->{$getter}();
        }

        foreach ($variation->getBarcodes() as $barcode) {
            $ordernumbers[] = $barcode;
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
