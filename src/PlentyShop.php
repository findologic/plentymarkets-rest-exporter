<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

class PlentyShop
{
    public const KEY_GLOBAL_ENABLE_OLD_URL_PATTERN = 'global.enableOldUrlPattern';
    public const KEY_ITEM_VARIATION_SHOW_TYPE = 'item.variation_show_type';
    public const KEY_ITEM_SHOW_PLEASE_SELECT = 'item.show_please_select';

    public const VARIANT_MODE_ALL = 'all';

    private ?bool $enableOldUrlPattern;

    private ?string $variationShowType;

    private ?bool $itemShowPleaseSelect;

    public function __construct(array $config = [])
    {
        $this->enableOldUrlPattern = isset($config[self::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN]) ?
            Utils::filterBoolean($config[self::KEY_GLOBAL_ENABLE_OLD_URL_PATTERN]) : null;
            
        $this->variationShowType = $config[self::KEY_ITEM_VARIATION_SHOW_TYPE] ?? null;

        $this->itemShowPleaseSelect = isset($config[self::KEY_ITEM_SHOW_PLEASE_SELECT]) ?
            Utils::filterBoolean($config[self::KEY_ITEM_SHOW_PLEASE_SELECT]) : null;
    }

    public function shouldExportGroupableAttributeVariantsSeparately(): bool
    {
        if (!$this->variationShowType) {
            return true;
        }

        return $this->variationShowType === self::VARIANT_MODE_ALL;
    }

    public function getItemShowPleaseSelect(): ?bool
    {
        return $this->itemShowPleaseSelect;
    }

    public function shouldUseLegacyCallistoUrl(): bool
    {
        if (!isset($this->enableOldUrlPattern)) {
            return false;
        }

        return Utils::filterBoolean($this->enableOldUrlPattern);
    }
}
