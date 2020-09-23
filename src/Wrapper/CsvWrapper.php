<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use Log4Php\Logger;
use Psr\Log\LoggerInterface;

class CsvWrapper extends Wrapper
{
    /** @var string */
    protected $exportPath;

    /** @var Exporter */
    private $exporter;

    /** @var Config */
    private $config;

    /** @var RegistryService */
    private $registryService;

    /** @var LoggerInterface */
    private $internalLogger;

    /** @var LoggerInterface */
    private $customerLogger;

    public function __construct(
        string $path,
        Exporter $exporter,
        Config $config,
        RegistryService $registryService,
        ?LoggerInterface $internalLogger,
        ?LoggerInterface $customerLogger
    ) {
        $this->exportPath = $path;
        $this->exporter = $exporter;
        $this->config = $config;
        $this->registryService = $registryService;
        $this->internalLogger = $internalLogger ?? new DummyLogger();
        $this->customerLogger = $customerLogger ?? new DummyLogger();
    }

    /**
     * @inheritDoc
     */
    public function wrap(
        int $start,
        int $total,
        ItemResponse $products,
        PimVariationResponse $variations
    ): void {
        /** @var Item[] $items */
        $items = [];
        foreach ($products->all() as $product) {
            $productVariations = $variations->find([
                'base' => [
                    'itemId' => $product->getId()
                ]
            ]);

            $productWrapper = new Product(
                $this->exporter,
                $this->config,
                $this->registryService->getWebStore()->getConfiguration(),
                $this->registryService,
                $product,
                $productVariations
            );
            $item = $productWrapper->processProductData();

            if (!$item) {
                $this->customerLogger->warning(sprintf(
                    'Product with id %d could not be exported. Reason: %s',
                    $product->getId(),
                    $productWrapper->getReason()
                ));

                continue;
            }

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
