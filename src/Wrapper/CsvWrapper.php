<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;

class CsvWrapper extends Wrapper
{
    /** @var string */
    protected $exportPath;

    /** @var Exporter */
    private $exporter;

    /** @var Config */
    private $config;

    /** @var Registry */
    private $registry;

    public function __construct(string $path, Exporter $exporter, Config $config, Registry $registry)
    {
        $this->exportPath = $path;
        $this->exporter = $exporter;
        $this->config = $config;
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function wrap(
        int $start,
        int $total,
        array $products,
        array $variations
    ): void {
        /** @var Item[] $items */
        $items = [];
        foreach ($products as $product) {
            $productWrapper = new Product($this->exporter, $this->config, $this->registry, $product, $variations);
            $item = $productWrapper->processProductData();

            $items[] = $item;
        }

        $this->exporter->serializeItemsToFile($this->exportPath, $items, $start, count($items), $total);
    }

    /**
     * @codeCoverageIgnore
     */
    public function setExportPath(string $path): self
    {
        $this->exportPath = $path;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getExportPath(): string
    {
        return $this->exportPath;
    }
}