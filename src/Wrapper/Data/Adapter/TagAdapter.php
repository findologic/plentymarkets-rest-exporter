<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Keyword;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item as ProductEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\Tag;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property\TagName;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation as VariationEntity;
use FINDOLOGIC\PlentyMarketsRestExporter\Translator;

class TagAdapter extends MultiValueFieldAdapter
{
    /**
     * Filters with this name won't be shown in the filter configuration.
     */
    private const TAG_ID_INTERNAL_ATTRIBUTE_NAME = 'cat_id';

    /**
     * @return array<Attribute|Keyword>
     */
    public function adapt(ProductEntity $product): array
    {
        return [];
    }

    /**
     * @return array<Attribute|Keyword>
     */
    public function adaptVariation(VariationEntity $variation): array
    {
        $data = [];

        $tags = $variation->getTags();
        $tagIds = [];
        foreach ($tags as $tag) {
            if (!$this->shouldProcessTag($tag)) {
                continue;
            }

            $tagIds[] = $tag->getId();
            $data[] = new Keyword($this->getTagName($tag));
        }

        if ($tagIds !== []) {
            $data[] = new Attribute(self::TAG_ID_INTERNAL_ATTRIBUTE_NAME, $tagIds);
        }

        return $data;
    }

    private function shouldProcessTag(Tag $tag): bool
    {
        if (!$clients = $tag->getTagData()->getClients()) {
            return false;
        }

        foreach ($clients as $client) {
            if ($client->getPlentyId() === $this->getRegistryService()->getWebStore()->getStoreIdentifier()) {
                return true;
            }
        }

        return false;
    }

    private function getTagName(Tag $tag): string
    {
        /** @var TagName|null $translatedTag */
        $translatedTag = Translator::translate($tag->getTagData()->getNames(), $this->getConfig()->getLanguage());

        return $translatedTag ? $translatedTag->getName() : $tag->getTagData()->getName();
    }
}
