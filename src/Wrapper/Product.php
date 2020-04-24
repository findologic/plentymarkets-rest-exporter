<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Product as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;

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

    /** @var Variation[] */
    private $variations;

    /** @var string|null */
    private $reason = null;

    public function __construct(
        Exporter $exporter,
        Config $config,
        Registry $registry,
        ProductEntity $productEntity,
        array $variations
    ) {
        $this->item = $exporter->createItem($productEntity->getId());
        $this->config = $config;
        $this->productEntity = $productEntity;
        $this->registry = $registry;
        $this->variations = $variations;
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
        $this->setTexts();

        $this->item->addSalesFrequency(0);
        $this->item->addPrice(13.37);
        $this->item->addDateAdded(new \DateTime());

        return $this->item;
    }

    /**
     * May return the reason why a product wasn't able to get exported.
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    protected function setTexts(): void
    {
        /** @var WebStore $webStore */
        $webStore = $this->registry->get('webStore');

        if (!$webStore || !$webStore->getConfiguration()) {
            return;
        }

        $textId = $webStore->getConfiguration()['displayItemName'];

        foreach ($this->productEntity->getTexts() as $texts) {
            if (strtoupper($texts['lang']) !== $this->config->getLanguage()) {
                continue;
            }

            $this->item->addName($texts['name' . $textId]);
            $this->item->addSummary($texts['shortDescription']);
            $this->item->addDescription($texts['description']);
            $this->item->addUrl($this->buildProductUrl($texts['urlPath']));
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
        /** @var WebStore $webStore */
        $webStore = $this->registry->get('webStore');

        return (strtoupper($webStore->getConfiguration()['defaultLanguage']) === $this->config->getLanguage());
    }

    private function isLanguageAvailable(): bool
    {
        /** @var WebStore $webStore */
        $webStore = $this->registry->get('webStore');

        $availableLanguages = explode(',', $webStore->getConfiguration()['languageList']);
        $upperCasedLanguages = array_map('strtoupper', $availableLanguages);

        return (in_array($this->config->getLanguage(), $upperCasedLanguages));
    }
}
