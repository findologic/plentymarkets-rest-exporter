<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Item\Text;

class Item extends Entity
{
    private int $id;
    private int $position;
    private int $manufacturerId;
    private int $stockType;
    private string $add_cms_page;
    private int $storeSpecial;
    private int $condition;
    private string $amazonFedas;
    private string $updatedAt;
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
    private string $customsTariffNumber;
    private int $producingCountryId;
    private int $revenueAccount;
    private int $couponRestriction;
    private int $flagOne;
    private int $flagTwo;
    private int $ageRestriction;
    private string $createdAt;
    private int $amazonProductType;
    private ?int $ebayPresetId;
    private ?int $ebayCategory;
    private ?int $ebayCategory2;
    private ?int $ebayStoreCategory;
    private ?int $ebayStoreCategory2;
    private int $amazonFbaPlatform;
    private float $feedback;
    private string $gimahhot;
    private ?float $maxOrderQuantity;
    private bool $isSubscribable;
    private ?int $rakutenCategoryId;
    private bool $isShippingPackage;
    private int $conditionApi;
    private bool $isSerialNumber;
    private bool $isShippableByAmazon;
    private ?int $ownerId;
    private string $itemType;
    private int $mainVariationId;
    /** @var Text[] */
    private array $texts = [];

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
        $this->free1 = $this->getStringProperty('free1', $data);
        $this->free2 = $this->getStringProperty('free2', $data);
        $this->free3 = $this->getStringProperty('free3', $data);
        $this->free4 = $this->getStringProperty('free4', $data);
        $this->free5 = $this->getStringProperty('free5', $data);
        $this->free6 = $this->getStringProperty('free6', $data);
        $this->free7 = $this->getStringProperty('free7', $data);
        $this->free8 = $this->getStringProperty('free8', $data);
        $this->free9 = $this->getStringProperty('free9', $data);
        $this->free10 = $this->getStringProperty('free10', $data);
        $this->free11 = $this->getStringProperty('free11', $data);
        $this->free12 = $this->getStringProperty('free12', $data);
        $this->free13 = $this->getStringProperty('free13', $data);
        $this->free14 = $this->getStringProperty('free14', $data);
        $this->free15 = $this->getStringProperty('free15', $data);
        $this->free16 = $this->getStringProperty('free16', $data);
        $this->free17 = $this->getStringProperty('free17', $data);
        $this->free18 = $this->getStringProperty('free18', $data);
        $this->free19 = $this->getStringProperty('free19', $data);
        $this->free20 = $this->getStringProperty('free20', $data);
        $this->customsTariffNumber = (string)$data['customsTariffNumber'];
        $this->producingCountryId = (int)$data['producingCountryId'];
        $this->revenueAccount = (int)$data['revenueAccount'];
        $this->couponRestriction = (int)$data['couponRestriction'];
        $this->flagOne = (int)$data['flagOne'];
        $this->flagTwo = (int)$data['flagTwo'];
        $this->ageRestriction = (int)$data['ageRestriction'];
        $this->createdAt = (string)$data['createdAt'];
        $this->amazonProductType = (int)$data['amazonProductType'];
        $this->ebayPresetId = $this->getIntProperty('ebayPresetId', $data);
        $this->ebayCategory = $this->getIntProperty('ebayCategory', $data);
        $this->ebayCategory2 = $this->getIntProperty('ebayCategory2', $data);
        $this->ebayStoreCategory = $this->getIntProperty('ebayStoreCategory', $data);
        $this->ebayStoreCategory2 = $this->getIntProperty('ebayStoreCategory2', $data);
        $this->amazonFbaPlatform = (int)$data['amazonFbaPlatform'];
        $this->feedback = (float)$data['feedback'];
        $this->gimahhot = (string)$data['gimahhot']; // Undocumented
        $this->maxOrderQuantity = $this->getFloatProperty('maximumOrderQuantity', $data);
        $this->isSubscribable = (bool)$data['isSubscribable'];
        $this->rakutenCategoryId = $this->getIntProperty('rakutenCategoryId', $data);
        $this->isShippingPackage = (bool)$data['isShippingPackage'];
        $this->conditionApi = (int)$data['conditionApi'];
        $this->isSerialNumber = (bool)$data['isSerialNumber'];
        $this->isShippableByAmazon = (bool)$data['isShippableByAmazon'];
        $this->ownerId = $this->getIntProperty('ownerId', $data);
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
