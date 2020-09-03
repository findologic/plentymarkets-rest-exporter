<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore\Configuration as StoreConfiguration;

class Product
{
    /** @var Item */
    private $item;

    /** @var Config */
    private $config;

    /** @var Registry */
    private $registry;

    /** @var ProductEntity */
    private $productEntity;

    /** @var ItemVariation[] */
    private $variationEntities;

    /** @var string|null */
    private $reason = null;

    /** @var StoreConfiguration  */
    private $storeConfiguration;

    /** @var Exporter */
    private $exporter;

    public function __construct(
        Exporter $exporter,
        Config $config,
        StoreConfiguration $storeConfiguration,
        Registry $registry,
        ProductEntity $productEntity,
        array $variationEntities
    ) {
        $this->exporter = $exporter;
        $this->item = $exporter->createItem($productEntity->getId());
        $this->config = $config;
        $this->productEntity = $productEntity;
        $this->registry = $registry;
        $this->variationEntities = $variationEntities;
        $this->storeConfiguration = $storeConfiguration;
    }

    /**
     * Returns a libflexport-consumable Item.
     * Returns null if
     *   * The data can not be parsed properly.
     *   * The products are not available for the current store.
     *   * Settings do not allow the product to be exported.
     *
     * @see Product::getReason() To get the reason why the product wasnt able to be exported.
     *
     * @return Item|null
     */
    public function processProductData(): ?Item
    {
        $this->processVariations();

        $this->setTexts();

        $this->item->addSalesFrequency(0);
        $this->item->addDateAdded(new \DateTime($this->productEntity->getCreatedAt()));

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

            $this->item->addName($texts->$textGetter());
            if ($texts->getShortDescription()) {
                $this->item->addSummary($texts->getShortDescription());
            }
            if ($texts->getDescription()) {
                $this->item->addDescription($texts->getDescription());
            }
            $this->item->addUrl($this->buildProductUrl($texts->getUrlPath()));
        }
    }

    protected function processVariations(): void
    {
        foreach ($this->variationEntities as $variationEntity) {
            $variation = new Variation($this->config, $this->registry, $variationEntity);
            $variation->processData();

            if ($variation->isMain()) {
                $this->item->addImage($variation->getImage()); // TODO In case a variation does not have an image, an exception is thrown. Please add a test for that.
                $this->item->addSort($variation->getPosition());
                $this->item->addPrice($variation->getPrice()); // TODO Ensure that the item always has a price. In case it does not have a price, skip it.
                $this->item->setInsteadPrice($variation->getInsteadPrice());
                foreach ($variation->getGroups() as $group) {
                    $this->item->addUsergroup($group);
                }
                $this->item->setAllKeywords($variation->getTags());
            }

            foreach ($variation->getAttributes() as $attribute) {
                $this->item->addMergedAttribute($attribute);
            }
        }
    }

    private function buildProductUrl(string $urlPath): string
    {
        return sprintf(
            '%s://%s%s/%s/a-%s',
            $this->config->getProtocol(),
            $this->config->getDomain(),
            $this->getLanguageUrlPrefix(),
            trim($urlPath, '/'),
            $this->productEntity->getId()
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
}
