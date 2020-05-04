<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;

class Item extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $position;

    /** @var int */
    private $manufacturerId;

    /** @var int */
    private $stockType;

    /** @var string */
    private $add_cms_page;

    /** @var int */
    private $storeSpecial;

    /** @var int */
    private $condition;

    /** @var string */
    private $amazonFedas;

    /** @var string */
    private $updatedAt;

    /** @var string|null */
    private $free1;

    /** @var string|null */
    private $free2;

    /** @var string|null */
    private $free3;

    /** @var string|null */
    private $free4;

    /** @var string|null */
    private $free5;

    /** @var string|null */
    private $free6;

    /** @var string|null */
    private $free7;

    /** @var string|null */
    private $free8;

    /** @var string|null */
    private $free9;

    /** @var string|null */
    private $free10;

    /** @var string|null */
    private $free11;

    /** @var string|null */
    private $free12;

    /** @var string|null */
    private $free13;

    /** @var string|null */
    private $free14;

    /** @var string|null */
    private $free15;

    /** @var string|null */
    private $free16;

    /** @var string|null */
    private $free17;

    /** @var string|null */
    private $free18;

    /** @var string|null */
    private $free19;

    /** @var string|null */
    private $free20;

    /** @var string */
    private $customsTariffNumber;

    /** @var int */
    private $producingCountryId;

    /** @var int */
    private $revenueAccount;

    /** @var int */
    private $couponRestriction;

    /** @var int */
    private $flagOne;

    /** @var int */
    private $flagTwo;

    /** @var int */
    private $ageRestriction;

    /** @var string */
    private $createdAt;

    /** @var int */
    private $amazonProductType;

    /** @var int|null */
    private $ebayPresetId;

    /** @var int|null */
    private $ebayCategory;

    /** @var int|null */
    private $ebayCategory2;

    /** @var int|null */
    private $ebayStoreCategory;

    /** @var int|null */
    private $ebayStoreCategory2;

    /** @var int */
    private $amazonFbaPlatform;

    /** @var float */
    private $feedback;

    /** @var string */
    private $gimahhot;

    /** @var float */
    private $maxOrderQuantity;

    /** @var bool */
    private $isSubscribable;

    /** @var int */
    private $rakutenCategoryId;

    /** @var bool */
    private $isShippingPackage;

    /** @var int */
    private $conditionApi;

    /** @var bool */
    private $isSerialNumber;

    /** @var bool */
    private $isShippableByAmazon;

    /** @var int|null */
    private $ownerId;

    /** @var string */
    private $itemType;

    /** @var int */
    private $mainVariationId;

    /** @var Text[] */
    private $texts = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->position = (int)$data['position'];
        $this->manufacturerId = (int)$data['manufacturerId'];
        $this->stockType = (int)$data['stockType'];
        $this->add_cms_page = (string)$data['add_cms_page']; // Undocumented
        $this->storeSpecial = (int)$data['storeSpecial'];
        $this->condition = (int)$data['condition'];
        $this->amazonFedas = (string)$data['amazonFedas'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->free1 = is_null($data['free1']) ? null : (string)$data['free1'];
        $this->free2 = is_null($data['free2']) ? null : (string)$data['free2'];
        $this->free3 = is_null($data['free3']) ? null : (string)$data['free3'];
        $this->free4 = is_null($data['free4']) ? null : (string)$data['free4'];
        $this->free5 = is_null($data['free5']) ? null : (string)$data['free5'];
        $this->free6 = is_null($data['free6']) ? null : (string)$data['free6'];
        $this->free7 = is_null($data['free7']) ? null : (string)$data['free7'];
        $this->free8 = is_null($data['free8']) ? null : (string)$data['free8'];
        $this->free9 = is_null($data['free9']) ? null : (string)$data['free9'];
        $this->free10 = is_null($data['free10']) ? null : (string)$data['free10'];
        $this->free11 = is_null($data['free11']) ? null : (string)$data['free11'];
        $this->free12 = is_null($data['free12']) ? null : (string)$data['free12'];
        $this->free13 = is_null($data['free13']) ? null : (string)$data['free13'];
        $this->free14 = is_null($data['free14']) ? null : (string)$data['free14'];
        $this->free15 = is_null($data['free15']) ? null : (string)$data['free15'];
        $this->free16 = is_null($data['free16']) ? null : (string)$data['free16'];
        $this->free17 = is_null($data['free17']) ? null : (string)$data['free17'];
        $this->free18 = is_null($data['free18']) ? null : (string)$data['free18'];
        $this->free19 = is_null($data['free19']) ? null : (string)$data['free19'];
        $this->free20 = is_null($data['free20']) ? null : (string)$data['free20'];
        $this->customsTariffNumber = (string)$data['customsTariffNumber'];
        $this->producingCountryId = (int)$data['producingCountryId'];
        $this->revenueAccount = (int)$data['revenueAccount'];
        $this->couponRestriction = (int)$data['couponRestriction'];
        $this->flagOne = (int)$data['flagOne'];
        $this->flagTwo = (int)$data['flagTwo'];
        $this->ageRestriction = (int)$data['ageRestriction'];
        $this->createdAt = (string)$data['createdAt'];
        $this->amazonProductType = (int)$data['amazonProductType'];
        $this->ebayPresetId = is_null($data['ebayPresetId']) ? null : (int)$data['ebayPresetId'];
        $this->ebayCategory = is_null($data['ebayCategory']) ? null : (int)$data['ebayCategory'];
        $this->ebayCategory2 = is_null($data['ebayCategory2']) ? null : (int)$data['ebayCategory2'];
        $this->ebayStoreCategory = is_null($data['ebayStoreCategory']) ? null : (int)$data['ebayStoreCategory'];
        $this->ebayStoreCategory2 = is_null($data['ebayStoreCategory2']) ? null : (int)$data['ebayStoreCategory2'];
        $this->amazonFbaPlatform = (int)$data['amazonFbaPlatform'];
        $this->feedback = (float)$data['feedback'];
        $this->gimahhot = (string)$data['gimahhot']; // Undocumented
        $this->maxOrderQuantity = is_null($data['maximumOrderQuantity']) ? null : (float)$data['maximumOrderQuantity'];
        $this->isSubscribable = (bool)$data['isSubscribable'];
        $this->rakutenCategoryId = is_null($data['rakutenCategoryId']) ? null : (int)$data['rakutenCategoryId'];
        $this->isShippingPackage = (bool)$data['isShippingPackage'];
        $this->conditionApi = (int)$data['conditionApi'];
        $this->isSerialNumber = (bool)$data['isSerialNumber'];
        $this->isShippableByAmazon = (bool)$data['isShippableByAmazon'];
        $this->ownerId = is_null($data['ownerId']) ? null : (int)$data['ownerId'];
        $this->itemType = (string)$data['itemType'];
        $this->mainVariationId = (int)$data['mainVariationId'];

        if (!empty($data['texts'])) {
            foreach ($data['texts'] as $text) {
                $this->texts[] = new Text($text);
            }
        }
    }

    public function getData(): array
    {
        $texts = [];
        foreach ($this->texts as $text) {
            $texts[] = $text->getData();
        }

        return [
            'id' => $this->id,
            'position' => $this->position,
            'manufacturerId' => $this->manufacturerId,
            'stockType' => $this->stockType,
            'add_cms_page' => $this->add_cms_page,
            'storeSpecial' => $this->storeSpecial,
            'condition' => $this->condition,
            'amazonFedas' => $this->amazonFedas,
            'updatedAt' => $this->updatedAt,
            'free1' => $this->free1,
            'free2' => $this->free2,
            'free3' => $this->free3,
            'free4' => $this->free4,
            'free5' => $this->free5,
            'free6' => $this->free6,
            'free7' => $this->free7,
            'free8' => $this->free8,
            'free9' => $this->free9,
            'free10' => $this->free10,
            'free11' => $this->free11,
            'free12' => $this->free12,
            'free13' => $this->free13,
            'free14' => $this->free14,
            'free15' => $this->free15,
            'free16' => $this->free16,
            'free17' => $this->free17,
            'free18' => $this->free18,
            'free19' => $this->free19,
            'free20' => $this->free20,
            'customsTariffNumber' => $this->customsTariffNumber,
            'producingCountryId' => $this->producingCountryId,
            'revenueAccount' => $this->revenueAccount,
            'couponRestriction' => $this->couponRestriction,
            'flagOne' => $this->flagOne,
            'flagTwo' => $this->flagTwo,
            'ageRestriction' => $this->ageRestriction,
            'createdAt' => $this->createdAt,
            'amazonProductType' => $this->amazonProductType,
            'ebayPresetId' => $this->ebayPresetId,
            'ebayCategory' => $this->ebayCategory,
            'ebayCategory2' => $this->ebayCategory2,
            'ebayStoreCategory' => $this->ebayStoreCategory,
            'ebayStoreCategory2' => $this->ebayStoreCategory2,
            'amazonFbaPlatform' => $this->amazonFbaPlatform,
            'feedback' => $this->feedback,
            'gimahhot' => $this->gimahhot,
            'maximumOrderQuantity' => $this->maxOrderQuantity,
            'isSubscribable' => $this->isSubscribable,
            'rakutenCategoryId' => $this->rakutenCategoryId,
            'isShippingPackage' => $this->isShippingPackage,
            'conditionApi' => $this->conditionApi,
            'isSerialNumber' => $this->isSerialNumber,
            'isShippableByAmazon' => $this->isShippableByAmazon,
            'ownerId' => $this->ownerId,
            'itemType' => $this->itemType,
            'mainVariationId' => $this->mainVariationId,
            'texts' => $texts
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getManufacturerId(): int
    {
        return $this->manufacturerId;
    }

    public function getStockType(): int
    {
        return $this->stockType;
    }

    public function getAddCmsPage(): string
    {
        // Undocumented
        return $this->add_cms_page;
    }

    public function getStoreSpecial(): int
    {
        return $this->storeSpecial;
    }

    public function getCondition(): int
    {
        return $this->condition;
    }

    public function getAmazonFedas(): string
    {
        return $this->amazonFedas;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getFree1(): ?string
    {
        return $this->free1;
    }

    public function getFree2(): ?string
    {
        return $this->free2;
    }

    public function getFree3(): ?string
    {
        return $this->free3;
    }

    public function getFree4(): ?string
    {
        return $this->free4;
    }

    public function getFree5(): ?string
    {
        return $this->free5;
    }

    public function getFree6(): ?string
    {
        return $this->free6;
    }

    public function getFree7(): ?string
    {
        return $this->free7;
    }

    public function getFree8(): ?string
    {
        return $this->free8;
    }

    public function getFree9(): ?string
    {
        return $this->free9;
    }

    public function getFree10(): ?string
    {
        return $this->free10;
    }

    public function getFree11(): ?string
    {
        return $this->free11;
    }

    public function getFree12(): ?string
    {
        return $this->free12;
    }

    public function getFree13(): ?string
    {
        return $this->free13;
    }

    public function getFree14(): ?string
    {
        return $this->free14;
    }

    public function getFree15(): ?string
    {
        return $this->free15;
    }

    public function getFree16(): ?string
    {
        return $this->free16;
    }

    public function getFree17(): ?string
    {
        return $this->free17;
    }

    public function getFree18(): ?string
    {
        return $this->free18;
    }

    public function getFree19(): ?string
    {
        return $this->free19;
    }

    public function getFree20(): ?string
    {
        return $this->free20;
    }

    public function getCustomsTariffNumber(): string
    {
        return $this->customsTariffNumber;
    }

    public function getProducingCountryId(): int
    {
        return $this->producingCountryId;
    }

    public function getRevenueAccount(): int
    {
        return $this->revenueAccount;
    }

    public function getCouponRestriction(): int
    {
        return $this->couponRestriction;
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

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getAmazonProductType(): int
    {
        return $this->amazonProductType;
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

    public function getAmazonFbaPlatform(): int
    {
        return $this->amazonFbaPlatform;
    }

    public function getFeedback(): float
    {
        return $this->feedback;
    }

    public function getGimahhot(): string
    {
        // Undocumented
        return $this->gimahhot;
    }

    public function getMaximumOrderQuantity(): float
    {
        return $this->maxOrderQuantity;
    }

    public function isSubscribable(): bool
    {
        return $this->isSubscribable;
    }

    public function getRakutenCategoryId(): int
    {
        return $this->rakutenCategoryId;
    }

    public function isShippingPackage(): bool
    {
        return $this->isShippingPackage;
    }

    public function getConditionApi(): int
    {
        return $this->conditionApi;
    }

    public function isSerialNumber(): bool
    {
        return $this->isSerialNumber;
    }

    public function isShippableByAmazon(): bool
    {
        return $this->isShippableByAmazon;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function getMainVariationId(): int
    {
        return $this->mainVariationId;
    }

    /**
     * @return Text[]
     */
    public function getTexts(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->texts;
    }
}
