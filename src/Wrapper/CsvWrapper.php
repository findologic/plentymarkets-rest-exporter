<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper;

use Exception;
use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\Logger\DummyLogger;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductResponseItem;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CsvWrapper extends Wrapper
{
    protected string $exportPath;

    protected ?string $fileNamePrefix;

    private Exporter $exporter;

    private Config $config;

    private RegistryService $registryService;

    private LoggerInterface $internalLogger;

    private LoggerInterface $customerLogger;

    /** @var array<string, int> $wrapFailures */
    private array $wrapFailures = [];

    /** @var int[] */
    private array $skippedProducts = [];

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
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function wrap(
        int $start,
        int $total,
        ItemResponse $products,
        PimVariationResponse $variations,
        ?PropertySelectionResponse $propertySelection = null
    ): void {
        /** @var Item[] $items */
        $items = [];
        foreach ($products->all() as $product) {
            if (!$this->shouldExportProduct($product, $variations)) {
                $this->skippedProducts[] = $product->getId();

                continue;
            }

            $productVariations = $variations->find([
                'base' => [
                    'itemId' => $product->getId()
                ]
            ]);

            list($groupedVariations, $separateVariations) = $this->splitVariationsByGroupability($productVariations);

            foreach ($separateVariations as $key => $separateVariation) {
                $item = $this->wrapItem(
                    $product,
                    $separateVariation,
                    $propertySelection,
                    Product::WRAP_MODE_SEPARATE_VARIATIONS,
                    (string)$key
                );

                if ($item) {
                    $items[] = $item;
                }
            }

            if ($item = $this->wrapItem($product, $groupedVariations, $propertySelection, Product::WRAP_MODE_DEFAULT)) {
                $items[] = $item;
            }
        }

        if ($this->fileNamePrefix) {
            $this->exporter->setFileNamePrefix($this->fileNamePrefix);
        }

        $this->exporter->serializeItemsToFile($this->exportPath, $items, $start, count($items), $total);

        if ($this->skippedProducts !== []) {
            $this->logSkippedProducts();
        }

        $this->logFailures();
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

    /**
     * @param Variation[] $productVariations
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function wrapItem(
        ProductEntity $product,
        array $productVariations,
        ?PropertySelectionResponse $propertySelection,
        int $wrapMode,
        string $variationGroupKey = ''
    ): ?Item {
        $productWrapper = new Product(
            $this->exporter,
            $this->config,
            $this->registryService->getWebStore()->getConfiguration(),
            $this->registryService,
            $propertySelection,
            $product,
            $productVariations,
            $wrapMode,
            $variationGroupKey
        );
        $item = $productWrapper->processProductData();

        if (!$item) {
            $this->registerFailure($productWrapper->getReason(), $product->getId());

            return null;
        }

        return $item;
    }

    /**
     * Splits product variations into those that should be grouped into a single item and those that should be
     * exported as separate items based on their groupable attributes. Product variations that have groupable
     * attributes are exported separately if the "Item view" > "Show variations by type" Ceres config is set to "All".
     *
     * @param Variation[] $productVariations
     * @throws InvalidArgumentException
     */
    private function splitVariationsByGroupability(array $productVariations): array
    {
        if (!$this->registryService->getPlentyShop()->shouldExportGroupableAttributeVariantsSeparately()) {
            return [$productVariations, []];
        }

        $groupedVariations = [];
        $separateVariations = [];

        foreach ($productVariations as $variation) {
            $separateVariation = new SeparatedVariation($variation, $this->registryService);
            $key = $separateVariation->getVariationGroupKey();

            if ($key !== '') {
                $separateVariations[$key][] = $variation;

                continue;
            }

            $groupedVariations[] = $variation;
        }

        return [$groupedVariations, $separateVariations];
    }

    private function shouldExportProduct(ProductResponseItem $product, PimVariationResponse $variations): bool
    {
        $mainVariation = $variations->findOne([
            'base' => [
                'itemId' => $product->getId(),
                'isMain' => true
            ]
        ]);

        if ($mainVariation && $mainVariation->hasExportExclusionTag($this->config->getLanguage())) {
            return false;
        }

        return true;
    }

    private function registerFailure(string $reason, int $itemId): void
    {
        if (!isset($this->wrapFailures[$reason])) {
            $this->wrapFailures[$reason] = [];
        }

        $this->wrapFailures[$reason][] = $itemId;
    }

    private function logSkippedProducts(): void
    {
        $this->customerLogger->notice(
            sprintf(
                'Products with id %s were skipped, as they contain the tag "findologic-exclude"',
                implode(', ', $this->skippedProducts)
            )
        );

        $this->skippedProducts = [];
    }

    private function logFailures(): void
    {
        foreach ($this->wrapFailures as $reason => $itemIds) {
            $this->customerLogger->warning(sprintf(
                'Products with id %s could not be exported. Reason: %s',
                implode(', ', $itemIds),
                $reason
            ));
        }

        $this->wrapFailures = [];
    }
}
