<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Registry;

class CsvWrapper extends Wrapper
{
    private string $path;

    private Exporter $exporter;

    private Config $config;

    private Registry $registry;

    public function __construct(string $path, Exporter $exporter, Config $config, Registry $registry)
    {
        $this->path = $path;
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

            if (!$item) {
                continue;
            }

            $items[] = $item;
        }

        $this->exporter->serializeItemsToFile($this->path, $items, $start, count($items), $total);
    }
}
