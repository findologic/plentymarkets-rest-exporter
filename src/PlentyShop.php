<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

class PlentyShop
{

    public const KEY_GLOBAL_ENABLE_OLD_URL_PATTERN = 'global.enableOldUrlPattern';
    public const KEY_ITEM_VARIATION_SHOW_TYPE = 'item.variation_show_type';
    public const VARIANT_MODE_ALL = 'all';

    private ?bool $enableOldUrlPattern;
    private ?string $variationShowType;

    public function __construct(array $config)
    {
        $this->enableOldUrlPattern = isset($config[self::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN]) ?
            $config[self::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN] : null;

        $this->variationShowType = isset($config[self::KEY_ITEM_VARIATION_SHOW_TYPE]) ?
            $config[self::KEY_ITEM_VARIATION_SHOW_TYPE] : null;
    }

    public function shouldUseLegacyCallistoUrl(): bool
    {
        if (!$this->enableOldUrlPattern) {
            return true;
        }

        return Utils::filterBoolean($this->enableOldUrlPattern);
    }

    public function shouldExportGroupableAttributeVariantsSeparately(): bool
    {
        if (!$this->variationShowType) {
            return true;
        }

        return $this->variationShowType === self::VARIANT_MODE_ALL;
    }
}
