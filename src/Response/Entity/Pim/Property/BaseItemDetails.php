<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class BaseItemDetails extends Entity
{
    private int $id;
    private int $position;
    private string $addCmsPage;
    private int $condition;
    private ?string $free1;
    private ?string $free2;
    private ?string $free3;
    private ?string $free4;
    private ?string $free5;
    private ?string $free6;
    private ?string $free7;
    private ?string $free8;
    private ?string $free9;
    private ?string $free10;
    private ?string $free11;
    private ?string $free12;
    private ?string $free13;
    private ?string $free14;
    private ?string $free15;
    private ?string $free16;
    private ?string $free17;
    private ?string $free18;
    private ?string $free19;
    private ?string $free20;
    private string $gimahhot;
    private int $storeSpecial;
    private ?int $ownerId;
    private ?int $manufacturerId;
    private ?int $producingCountryId;
    private float $revenueAccount;
    private int $couponRestriction;
    private int $conditionApi;
    private bool $isSubscribable;
    private int $amazonFbaPlatform;
    private bool $isShippableByAmazon;
    private int $amazonProductType;
    private string $amazonFedas;
    private ?int $ebayPresetId;
    private ?int $ebayCategory;
    private ?int $ebayCategory2;
    private ?int $ebayStoreCategory;
    private ?int $ebayStoreCategory2;
    private ?int $rakutenCategoryId;
    private int $flagOne;
    private int $flagTwo;
    private ?int $ageRestriction;
    private int $feedback;
    private string $itemType;
    private int $stockType;
    private string $sitemapPublished;
    private bool $isSerialNumber;
    private bool $isShippingPackage;
    private ?int $maximumOrderQuantity;
    private int $variationCount;
    private string $customsTariffNumber;
    private int $mainVariationId;
    private bool $inactive;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->addCmsPage = $this->getStringProperty('add_cms_page', $data);
        $this->condition = $this->getIntProperty('condition', $data);

        foreach (range(1, 20) as $fieldNumber) {
            $fieldName = 'free' . $fieldNumber;

            $this->{$fieldName} = $this->getStringProperty($fieldName, $data);
        }

        $this->gimahhot = $this->getStringProperty('gimahhot', $data);
        $this->storeSpecial = $this->getIntProperty('storeSpecial', $data);
        $this->ownerId = $this->getIntProperty('ownerId', $data);
        $this->manufacturerId = $this->getIntProperty('manufacturerId', $data);
        $this->producingCountryId = $this->getIntProperty('producingCountryId', $data);
        $this->revenueAccount = $this->getFloatProperty('revenueAccount', $data);
        $this->couponRestriction = $this->getIntProperty('couponRestriction', $data);
        $this->conditionApi = $this->getIntProperty('conditionApi', $data);
        $this->isSubscribable = $this->getBoolProperty('isSubscribable', $data);
        $this->amazonFbaPlatform = $this->getIntProperty('amazonFbaPlatform', $data);
        $this->isShippableByAmazon = $this->getBoolProperty('isShippableByAmazon', $data);
        $this->amazonProductType = $this->getIntProperty('amazonProductType', $data);
        $this->amazonFedas = $this->getStringProperty('amazonFedas', $data);
        $this->ebayPresetId = $this->getIntProperty('ebayPresetId', $data);
        $this->ebayCategory = $this->getIntProperty('ebayCategory', $data);
        $this->ebayCategory2 = $this->getIntProperty('ebayCategory2', $data);
        $this->ebayStoreCategory = $this->getIntProperty('ebayStoreCategory', $data);
        $this->ebayStoreCategory2 = $this->getIntProperty('ebayStoreCategory2', $data);
        $this->rakutenCategoryId = $this->getIntProperty('rakutenCategoryId', $data);
        $this->flagOne = $this->getIntProperty('flagOne', $data);
        $this->flagTwo = $this->getIntProperty('flagTwo', $data);
        $this->ageRestriction = $this->getIntProperty('ageRestriction', $data);
        $this->feedback = $this->getIntProperty('feedback', $data);
        $this->itemType = $this->getStringProperty('itemType', $data);
        $this->stockType = $this->getIntProperty('stockType', $data);
        $this->sitemapPublished = $this->getStringProperty('sitemapPublished', $data);
        $this->isSerialNumber = $this->getBoolProperty('isSerialNumber', $data);
        $this->isShippingPackage = $this->getBoolProperty('isShippingPackage', $data);
        $this->maximumOrderQuantity = $this->getIntProperty('maximumOrderQuantity', $data);
        $this->variationCount = $this->getIntProperty('variationCount', $data);
        $this->customsTariffNumber = $this->getStringProperty('customsTariffNumber', $data);
        $this->mainVariationId = $this->getIntProperty('mainVariationId', $data);
        $this->inactive = $this->getBoolProperty('inactive', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        $freeTextFields = [];
        foreach (range(1, 20) as $fieldNumber) {
            $freeTextFields['free' . $fieldNumber] = $this->{'free' . $fieldNumber};
        }

        return array_merge([
            'id' => $this->id,
            'position' => $this->position,
            'add_cms_page' => $this->addCmsPage,
            'condition' => $this->condition,
            'gimahhot' => $this->gimahhot,
            'storeSpecial' => $this->storeSpecial,
            'ownerId' => $this->ownerId,
            'manufacturerId' => $this->manufacturerId,
            'producingCountryId' => $this->producingCountryId,
            'revenueAccount' => $this->revenueAccount,
            'couponRestriction' => $this->couponRestriction,
            'conditionApi' => $this->conditionApi,
            'isSubscribable' => $this->isSubscribable,
            'amazonFbaPlatform' => $this->amazonFbaPlatform,
            'isShippableByAmazon' => $this->isShippableByAmazon,
            'amazonProductType' => $this->amazonProductType,
            'amazonFedas' => $this->amazonFedas,
            'ebayPresetId' => $this->ebayPresetId,
            'ebayCategory' => $this->ebayCategory,
            'ebayCategory2' => $this->ebayCategory2,
            'ebayStoreCategory' => $this->ebayStoreCategory,
            'ebayStoreCategory2' => $this->ebayStoreCategory2,
            'rakutenCategoryId' => $this->rakutenCategoryId,
            'flagOne' => $this->flagOne,
            'flagTwo' => $this->flagTwo,
            'ageRestriction' => $this->ageRestriction,
            'feedback' => $this->feedback,
            'itemType' => $this->itemType,
            'stockType' => $this->stockType,
            'sitemapPublished' => $this->sitemapPublished,
            'isSerialNumber' => $this->isSerialNumber,
            'isShippingPackage' => $this->isShippingPackage,
            'maximumOrderQuantity' => $this->maximumOrderQuantity,
            'variationCount' => $this->variationCount,
            'customsTariffNumber' => $this->customsTariffNumber,
            'mainVariationId' => $this->mainVariationId,
            'inactive' => $this->inactive,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ], $freeTextFields);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getAddCmsPage(): string
    {
        return $this->addCmsPage;
    }

    public function getCondition(): int
    {
        return $this->condition;
    }

    public function getFree1(): ?string
    {
        return $this->free1;
    }

    public function getFree2(): ?string
    {
        return $this->free1;
    }

    public function getFree3(): ?string
    {
        return $this->free1;
    }

    public function getFree4(): ?string
    {
        return $this->free1;
    }

    public function getFree5(): ?string
    {
        return $this->free1;
    }

    public function getFree6(): ?string
    {
        return $this->free1;
    }

    public function getFree7(): ?string
    {
        return $this->free1;
    }

    public function getFree8(): ?string
    {
        return $this->free1;
    }

    public function getFree9(): ?string
    {
        return $this->free1;
    }

    public function getFree10(): ?string
    {
        return $this->free1;
    }

    public function getFree11(): ?string
    {
        return $this->free1;
    }

    public function getFree12(): ?string
    {
        return $this->free1;
    }

    public function getFree13(): ?string
    {
        return $this->free1;
    }

    public function getFree14(): ?string
    {
        return $this->free1;
    }

    public function getFree15(): ?string
    {
        return $this->free1;
    }

    public function getFree16(): ?string
    {
        return $this->free1;
    }

    public function getFree17(): ?string
    {
        return $this->free1;
    }

    public function getFree18(): ?string
    {
        return $this->free1;
    }

    public function getFree19(): ?string
    {
        return $this->free1;
    }

    public function getFree20(): ?string
    {
        return $this->free1;
    }

    public function getGimahhot(): string
    {
        return $this->gimahhot;
    }

    public function getStoreSpecial(): int
    {
        return $this->storeSpecial;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function getManufacturerId(): ?int
    {
        return $this->manufacturerId;
    }

    public function getProducingCountryId(): ?int
    {
        return $this->producingCountryId;
    }

    public function getRevenueAccount(): float
    {
        return $this->revenueAccount;
    }

    public function getCouponRestriction(): int
    {
        return $this->couponRestriction;
    }

    public function getConditionApi(): int
    {
        return $this->conditionApi;
    }

    public function isSubscribable(): bool
    {
        return $this->isSubscribable;
    }

    public function getAmazonFbaPlatform(): int
    {
        return $this->amazonFbaPlatform;
    }

    public function isShippableByAmazon(): bool
    {
        return $this->isShippableByAmazon;
    }

    public function getAmazonProductType(): int
    {
        return $this->amazonProductType;
    }

    public function getAmazonFedas(): string
    {
        return $this->amazonFedas;
    }

    public function getEbayPresetId(): ?int
    {
        return $this->ebayPresetId;
    }

    public function getEbayCategory(): ?int
    {
        return $this->ebayCategory;
    }

    public function getEbayCategory2(): ?int
    {
        return $this->ebayCategory2;
    }

    public function getEbayStoreCategory(): ?int
    {
        return $this->ebayStoreCategory;
    }

    public function getEbayStoreCategory2(): ?int
    {
        return $this->ebayStoreCategory2;
    }

    public function getRakutenCategoryId(): int
    {
        return $this->rakutenCategoryId;
    }

    public function getFlagOne(): int
    {
        return $this->flagOne;
    }

    public function getFlagTwo(): int
    {
        return $this->flagTwo;
    }

    public function getAgeRestriction(): int
    {
        return $this->ageRestriction;
    }

    public function getFeedback(): int
    {
        return $this->feedback;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function getStockType(): int
    {
        return $this->stockType;
    }

    public function getSitemapPublished(): string
    {
        return $this->sitemapPublished;
    }

    public function isSerialNumber(): bool
    {
        return $this->isSerialNumber;
    }

    public function isShippingPackage(): bool
    {
        return $this->isShippingPackage;
    }

    public function getMaximumOrderQuantity(): int
    {
        return $this->maximumOrderQuantity;
    }

    public function getVariationCount(): int
    {
        return $this->variationCount;
    }

    public function getCustomsTariffNumber(): string
    {
        return $this->customsTariffNumber;
    }

    public function getMainVariationId(): int
    {
        return $this->mainVariationId;
    }

    public function isInactive(): bool
    {
        return $this->inactive;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
