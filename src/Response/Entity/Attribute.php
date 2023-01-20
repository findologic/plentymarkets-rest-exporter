<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Attribute\Name;

class Attribute extends Entity
{
    private int $id;

    private string $backendName;

    private int $position;

    private bool $isSurchargePercental;

    private bool $isLinkableToImage;

    private string $amazonAttribute;

    private string $fruugoAttribute;

    private int $pixmaniaAttribute;

    private string $ottoAttribute;

    private string $googleShoppingAttribute;

    private int $neckermannAtEpAttribute;

    private string $typeOfSelectionInOnlineStore;

    private int $laRedouteAttribute;

    private bool $isGroupable;

    private string $updatedAt;

    /** @var Name[] */
    private array $names;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->backendName = (string)$data['backendName'];
        $this->position = (int)$data['position'];
        $this->isSurchargePercental = (bool)$data['isSurchargePercental'];
        $this->isLinkableToImage = (bool)$data['isLinkableToImage'];
        $this->amazonAttribute = (string)$data['amazonAttribute'];
        $this->fruugoAttribute = (string)$data['fruugoAttribute'];
        $this->pixmaniaAttribute = (int)$data['pixmaniaAttribute'];
        $this->ottoAttribute = (string)$data['ottoAttribute'];
        $this->googleShoppingAttribute = (string)$data['googleShoppingAttribute'];
        $this->neckermannAtEpAttribute = (int)$data['neckermannAtEpAttribute'];
        $this->typeOfSelectionInOnlineStore = (string)$data['typeOfSelectionInOnlineStore'];
        $this->laRedouteAttribute = (int)$data['laRedouteAttribute'];
        $this->isGroupable = (bool)$data['isGroupable'];
        $this->updatedAt = (string)$data['updatedAt'];
        $this->names = $this->getEntities(Name::class, 'attributeNames', $data);
    }

    public function getData(): array
    {
        $names = [];
        foreach ($this->names as $name) {
            $names[] = $name->getData();
        }

        return [
            'id' => $this->id,
            'backendName' => $this->backendName,
            'position' => $this->position,
            'isSurchargePercental' => $this->isSurchargePercental,
            'isLinkableToImage' => $this->isLinkableToImage,
            'amazonAttribute' => $this->amazonAttribute,
            'fruugoAttribute' => $this->fruugoAttribute,
            'pixmaniaAttribute' => $this->pixmaniaAttribute,
            'ottoAttribute' => $this->ottoAttribute,
            'googleShoppingAttribute' => $this->googleShoppingAttribute,
            'neckermannAtEpAttribute' => $this->neckermannAtEpAttribute,
            'typeOfSelectionInOnlineStore' => $this->typeOfSelectionInOnlineStore,
            'laRedouteAttribute' => $this->laRedouteAttribute,
            'isGroupable' => $this->isGroupable,
            'updatedAt' => $this->updatedAt,
            'attributeNames' => $names
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBackendName(): string
    {
        return $this->backendName;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isSurchargePercental(): bool
    {
        return $this->isSurchargePercental;
    }

    public function isLinkableToImage(): bool
    {
        return $this->isLinkableToImage;
    }

    public function getAmazonAttribute(): string
    {
        return $this->amazonAttribute;
    }

    public function getFruugoAttribute(): string
    {
        return $this->fruugoAttribute;
    }

    public function getPixmaniaAttribute(): int
    {
        return $this->pixmaniaAttribute;
    }

    public function getOttoAttribute(): string
    {
        return $this->ottoAttribute;
    }

    public function getGoogleShoppingAttribute(): string
    {
        return $this->googleShoppingAttribute;
    }

    public function getNeckermannAtEpAttribute(): int
    {
        return $this->neckermannAtEpAttribute;
    }

    public function getTypeOfSelectionInOnlineStore(): string
    {
        return $this->typeOfSelectionInOnlineStore;
    }

    public function getLaRedouteAttribute(): int
    {
        return $this->laRedouteAttribute;
    }

    public function isGroupable(): bool
    {
        return $this->isGroupable;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }
}
