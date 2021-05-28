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
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as PimVariation;
use Psr\Log\LoggerInterface;

class CsvWrapper extends Wrapper
{
    /** @var string */
    protected $exportPath;

    protected ?string $fileNamePrefix;

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
        ?string $fileNamePrefix,
        Exporter $exporter,
        Config $config,
        RegistryService $registryService,
        ?LoggerInterface $internalLogger,
        ?LoggerInterface $customerLogger
    ) {
        $this->exportPath = $path;
        $this->fileNamePrefix = $fileNamePrefix;
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

            list($groupedVariations, $separateVariations) = $this->splitVariationsByGroupability($productVariations);

            if ($groupedVariations) {
                if ($item = $this->doWrap($product, $groupedVariations)) {
                    $items[] = $item;
                }
            }

            foreach ($separateVariations as $separateVariation) {
                if ($item = $this->doWrap($product, [$separateVariation], true)) {
                    $items[] = $item;
                }
            }
        }

        if ($this->fileNamePrefix) {
            $this->exporter->setFileNamePrefix($this->fileNamePrefix);
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

    // TODO: come up with a better method name
    /**
     * @param PimVariation[] $productVariations
     */
    private function doWrap(
        ProductEntity $product,
        array $productVariations,
        bool $separateVariationMode = false
    ): ?Item {
        $productWrapper = new Product(
            $this->exporter,
            $this->config,
            $this->registryService->getWebStore()->getConfiguration(),
            $this->registryService,
            $product,
            $productVariations,
            $separateVariationMode
        );
        $item = $productWrapper->processProductData();

        if (!$item) {
            $this->customerLogger->warning(sprintf(
                'Product with id %d could not be exported. Reason: %s',
                $product->getId(),
                $productWrapper->getReason()
            ));

            return null;
        }

        return $item;
    }

    /**
     * @param Variation[] $productVariations
     */
    private function splitVariationsByGroupability(array $productVariations): array
    {
        if (!$this->shouldExportGroupableAttributeVariantsSeparately()) {
            return [$productVariations, []];
        }

        $groupedVariations = [];
        $separateVariations = [];

        foreach ($productVariations as $variation) {
            $attributeValues = $variation->getAttributeValues();

            foreach ($attributeValues as $attributeValue) {
                $attribute = $this->registryService->getAttribute($attributeValue->getId());

                if ($attribute && $attribute->isGroupable()) {
                    $separateVariations[] = $variation;
                    continue 2;
                }
            }

            $groupedVariations[] = $variation;
        }

        return [$groupedVariations, $separateVariations];
    }

    private function shouldExportGroupableAttributeVariantsSeparately(): bool
    {
        $config = $this->registryService->getPluginConfigurations('Ceres');

        if (!isset($config['item.variation_show_type'])) {
            return true;
        }

        return $config['item.variation_show_type'] == 'all';
    }
}
