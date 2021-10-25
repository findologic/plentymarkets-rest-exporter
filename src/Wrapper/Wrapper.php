<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\CategoryAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\FreeTextFieldAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\ManufacturerAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\Attribute\VariationAttributesAttributeAdapter;
use FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter\TagAdapter;

abstract class Wrapper
{
    private Config $config;
    private RegistryService $registryService;

    private CategoryAttributeAdapter $categoryAttributeAdapter;
    private FreeTextFieldAttributeAdapter $freeTextFieldAttributeAdapter;
    private ManufacturerAttributeAdapter $manufacturerAttributeAdapter;
    private VariationAttributesAttributeAdapter $variationAttributesAttributeAdapter;
    private TagAdapter $tagAdapter;

    public function __construct(Config $config, RegistryService $registryService)
    {
        $this->config = $config;
        $this->registryService = $registryService;

        $this->categoryAttributeAdapter = new CategoryAttributeAdapter($config, $registryService);
        $this->freeTextFieldAttributeAdapter = new FreeTextFieldAttributeAdapter($config, $registryService);
        $this->manufacturerAttributeAdapter = new ManufacturerAttributeAdapter($config, $registryService);
        $this->variationAttributesAttributeAdapter = new VariationAttributesAttributeAdapter($config, $registryService);
        $this->tagAdapter = new TagAdapter($config, $registryService);
    }

    /**
     * @param int $start
     * @param int $total
     * @param ItemResponse $products
     * @param PimVariationResponse $variations
     */
    abstract public function wrap(
        int $start,
        int $total,
        ItemResponse $products,
        PimVariationResponse $variations
    ): void;

    abstract public function getExportPath(): string;

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getRegistryService(): RegistryService
    {
        return $this->registryService;
    }

    public function getCategoryAttributeAdapter(): CategoryAttributeAdapter
    {
        return $this->categoryAttributeAdapter;
    }

    public function getFreeTextFieldAttributeAdapter(): FreeTextFieldAttributeAdapter
    {
        return $this->freeTextFieldAttributeAdapter;
    }

    public function getManufacturerAttributeAdapter(): ManufacturerAttributeAdapter
    {
        return $this->manufacturerAttributeAdapter;
    }

    public function getVariationAttributesAttributeAdapter(): VariationAttributesAttributeAdapter
    {
        return $this->variationAttributesAttributeAdapter;
    }

    public function getTagAdapter(): TagAdapter
    {
        return $this->tagAdapter;
    }
}
