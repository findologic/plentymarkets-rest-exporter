<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Property;

use DateTimeInterface;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Image extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $itemId;

    /** @var string */
    private $md5Checksum;

    /** @var string */
    private $md5ChecksumOriginal;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    private ?int $position;

    /** @var string */
    private $url;

    /** @var string */
    private $fileType;

    /** @var string */
    private $urlMiddle;

    /** @var bool */
    private $hasLinkedVariations;

    /** @var string */
    private $urlPreview;

    /** @var ImageAttributeValue[]. */
    private $attributeValueImages = [];

    /** @var ImageName[] */
    private $names;

    /** @var ImageAvailability[] */
    private $availabilities;

    /** @var string */
    private $type;

    /** @var int */
    private $size;

    /** @var int */
    private $storageProviderId;

    /** @var string */
    private $path;

    /** @var string */
    private $urlSecondPreview;

    /** @var DateTimeInterface */
    private $createdAt;

    /** @var DateTimeInterface */
    private $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->itemId = $this->getIntProperty('itemId', $data);
        $this->md5Checksum = $this->getStringProperty('md5Checksum', $data);
        $this->md5ChecksumOriginal = $this->getStringProperty('md5ChecksumOriginal', $data);
        $this->width = $this->getIntProperty('width', $data);
        $this->height = $this->getIntProperty('height', $data);
        $this->position = $this->getIntProperty('position', $data);
        $this->url = $this->getStringProperty('url', $data);
        $this->fileType = $this->getStringProperty('fileType', $data);
        $this->urlMiddle = $this->getStringProperty('urlMiddle', $data);
        $this->hasLinkedVariations = $this->getBoolProperty('hasLinkedVariations', $data);
        $this->urlPreview = $this->getStringProperty('urlPreview', $data);
        $this->names = $this->getEntities(ImageName::class, 'names', $data);
        $this->availabilities = $this->getEntities(ImageAvailability::class, 'availabilities', $data);
        $this->type = $this->getStringProperty('type', $data);
        $this->size = $this->getIntProperty('size', $data);
        $this->storageProviderId = $this->getIntProperty('storageProviderId', $data);
        $this->path = $this->getStringProperty('path', $data);
        $this->urlSecondPreview = $this->getStringProperty('urlSecondPreview', $data);
        $this->createdAt = $this->getDateTimeProperty('createdAt', $data);
        $this->updatedAt = $this->getDateTimeProperty('updatedAt', $data);
        $this->attributeValueImages = $this->getEntities(ImageAttributeValue::class, 'attributeValueImages', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'itemId' => $this->itemId,
            'md5Checksum' => $this->md5Checksum,
            'md5ChecksumOriginal' => $this->md5ChecksumOriginal,
            'width' => $this->width,
            'height' => $this->height,
            'position' => $this->position,
            'url' => $this->url,
            'fileType' => $this->fileType,
            'urlMiddle' => $this->urlMiddle,
            'attributeValueImages' => $this->attributeValueImages,
            'size' => $this->size,
            'hasLinkedVariations' => $this->hasLinkedVariations,
            'urlPreview' => $this->urlPreview,
            'names' => $this->names,
            'availabilities' => $this->availabilities,
            'type' => $this->type,
            'storageProviderId' => $this->storageProviderId,
            'path' => $this->path,
            'urlSecondPreview' => $this->urlSecondPreview,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getMd5Checksum(): string
    {
        return $this->md5Checksum;
    }

    public function getMd5ChecksumOriginal(): string
    {
        return $this->md5ChecksumOriginal;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getUrlMiddle(): string
    {
        return $this->urlMiddle;
    }

    public function hasLinkedVariations(): bool
    {
        return $this->hasLinkedVariations;
    }

    public function getUrlPreview(): string
    {
        return $this->urlPreview;
    }

    /**
     * @return null
     */
    public function getAttributeValueImages()
    {
        return $this->attributeValueImages;
    }

    /**
     * @return ImageName[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @return ImageAvailability[]
     */
    public function getAvailabilities(): array
    {
        return $this->availabilities;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getStorageProviderId(): int
    {
        return $this->storageProviderId;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getUrlSecondPreview(): string
    {
        return $this->urlSecondPreview;
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
